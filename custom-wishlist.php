<?php

/*
 * Plugin Name: Custom Wishlist
 * Description: Create wishlists with custom post types
 * Author: Alan Cesarini
 * Version: 1.0.0
 * Author URI: http://alancesarini.com
 * License: GPL2+
 */

if( !class_exists( 'CWL' ) ) {

	class CWL {

		private static $_this;

		private static $_version;

		private static $scripts_version;

		private static $wishlist;

		public static $table_name;

		function __construct() {

			global $wpdb;
		
			if( isset( self::$_this ) )
				wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.', get_class( $this ) ) );
			self::$_this = $this;

			self::$_version = '1.0.0';

			self::$table_name = $wpdb->prefix . 'cwl_wishlists';

			require( 'includes/class_wishlist.php' );

			self::$wishlist = new CWL_Wishlist();

			// Enqueue scripts and styles
			add_action( 'wp_enqueue_scripts', array( $this, 'load_js_css' ) );

			// AJAX action to handle the "add to my wishlist" button click
			add_action( 'wp_ajax_cwl-addtowishlist', array( $this, 'add_to_wishlist' ) );	

			// Render the button in the product page
			//add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_button_wishlist' ), 5 );	

			// Render the wishlist in the "my account" page
			//add_action( 'woocommerce_after_my_account', array( $this, 'render_wishlist' ) );

			// Define javascript vars
			add_action( 'wp_head', array( $this, 'add_js_vars' ) );	

			// Load the textdomain
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			// Create tables on plugin activation
			register_activation_hook( __FILE__, array( 'CWL', 'install' ) );
			
			// If a post is deleted, remove it from the wishlist table
			add_action( 'delete_post', array( $this, 'remove_from_wishlist' ) );

			// Shortcode for displaying the button
			add_shortcode( 'cwl_button', array( $this, 'render_button_wishlist' ) );

			// Shortcode for displaying the wishlist
			add_shortcode( 'cwl_wishlist', array( $this, 'render_wishlist' ) );

		}

		function install() {

			global $wpdb;

			$wpdb->query(
				'CREATE TABLE `' . self::$table_name . '` (
  					`user_id` bigint(20) NOT NULL,
  					`post_id` bigint(20) NOT NULL,
  					PRIMARY KEY (`user_id`,`post_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;'
			);

		}

		function load_textdomain() {

			load_plugin_textdomain( 'cwl', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		}

		function add_js_vars() {

			?>

			<script type="text/javascript">
				var cwl_ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
				var cwl_msg_ok = '<?php _e( 'Added to my wishlist!', 'cwl' ); ?>';
				var cwl_msg_ko = '<?php _e( 'ERROR!', 'cwl' ); ?>';
			</script>

			<?php

		}

		function load_js_css() {

			wp_register_script( 'cwl-main', plugins_url( 'assets/js/main.js', __FILE__ ), array( 'jquery' ), self::$scripts_version, true );
			wp_enqueue_script( 'cwl-main' );
			wp_register_style( 'cwl-css', plugins_url( 'assets/css/styles.css', __FILE__ ), array(), self::$scripts_version, false );
			wp_enqueue_style( 'cwl-css' );

		}

		function render_button_wishlist() {

			global $post;

			// Show the button only if the customer is currently logged in
			if( is_user_logged_in() ) {
				// Check if the customer has already added the post to his wishlist
				// If not, then show the button
				global $post;
				if( !self::$wishlist->post_in_wishlist( $post->ID, get_current_user_id() ) ) {
					self::$wishlist->show_button( $post->ID );
				}
			}
		
		}	

		function add_to_wishlist() {

			$response = false;

			if( is_user_logged_in() ) {
				$post_id = floatval( $_POST[ 'p' ] );
				if( $post_id > 0 ) {
					$response = self::$wishlist->add_post_to_wishlist( $post_id, get_current_user_id() );
				}
			}

			if( $response ) {
				die( json_encode(array( 'response' => 'OK' )));
			} else {
				die( json_encode(array( 'response' => 'KO' )));
			}			

		}	

		function render_wishlist() {

			if( is_user_logged_in() ) {
				echo '<div class="cwl-wishlist"><h2>' . __( 'My wishlist', 'cwl' );
				self::$wishlist->render_list( get_current_user_id() );
				echo '</div>';
			}
		
		}

		function remove_from_wishlist( $post_id ) {

			self::$wishlist->remove_post_from_wishlist( $post_id );

		}	

		static function this() {
		
			return self::$_this;
		
		}

	}

}	

new CWL();
