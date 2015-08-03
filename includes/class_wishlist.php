<?php

if( !class_exists( 'CWL_Wishlist' ) ) {
	
	class CWL_Wishlist {

		function CWL_Wishlist() {}

		function post_in_wishlist( $post_id, $user_id ) {

			global $wpdb;

			$post =  $wpdb->get_var( 'SELECT post_id FROM ' . CWL::$table_name . ' WHERE user_id = ' . $user_id . ' AND post_id = ' . $post_id );

			if( $post == NULL ) {
				return false;
			} else {
				return true;
			}

		}	

		function show_button( $post_id ) {
			?>

			<a href="#" class="cwl-btn" data-post="<?php echo $post_id; ?>"><?php _e( 'Add to my wishlist', 'cwl' ); ?></a>
			
			<?php
		}	

		function add_post_to_wishlist( $post_id, $user_id ) {

			global $wpdb;

			$result = $wpdb->insert( 
				CWL::$table_name, 
				array( 
					'post_id' => $post_id, 
					'user_id' => $user_id
				), 
				array( 
					'%d', 
					'%d' 
				) 
			);

			if( !$result ) {
				return false;
			} else {
				return true;
			}

		}	

		function render_list( $user_id ) {

			global $wpdb;

			$items =  $wpdb->get_results( 'SELECT post_id FROM ' . CWL::$table_name . ' WHERE user_id = ' . $user_id );

			if( count( $items ) > 0 ) {
				echo '<table class="cwl-table"><tbody>';
				foreach( $items as $item ) {
					$the_post = get_post( $item->post_id );
					$post_type = get_post_type($item->post_id );
					$post_name = $the_post->post_title;
					$post_thumbnail = get_the_post_thumbnail( $item->post_id, 'thumbnail' );
					$post_permalink = get_the_permalink( $item->post_id );
					echo '<tr><td><a href="' . $post_permalink . '">' . $post_thumbnail . '</a></td><td><a href="' . $post_permalink . '">' . $post_name . '</a></td></tr>';
				}
				echo '</tbody></table>';
			} else {
				echo '<h3>' . __( 'Your wishlist is empty', 'cwl' ) . '</h3>';
			}

		}	

		function remove_post_from_wishlist( $post_id ) {
			
			global $wpdb;

			$result = $wpdb->delete( 
				CWL::$table_name, 
				array( 
					'post_id' => $post_id
				), 
				array( 
					'%d'
				) 
			);

			if( !$result ) {
				return false;
			} else {
				return true;
			}			
		
		}		

	}

}