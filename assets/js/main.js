jQuery(document).ready(function() {

	jQuery('.cwl-btn').click(function(e) {
		e.preventDefault();
		var this_button = jQuery(this);
		var post_id = jQuery(this).data('post');
		this_button.off('click');
		this_button.addClass('cwl-btn-disabled');
	    post_data = 'action=cwl-addtowishlist&p=' + post_id;
	    jQuery.ajax({
			type: 'post',
			url: cwl_ajaxurl,
			data: post_data,
			dataType: 'json',
			error: function(XMLHttpRequest, textStatus, errorThrown){
				this_button.addClass('cwl-btn-ko');
				this_button.text(cwl_msg_ko);				
			},
			success: function(data, textStatus){
				if(data.response && data.response == 'OK') {
					this_button.addClass('cwl-btn-ok');
					this_button.text(cwl_msg_ok);
				} else {
					this_button.addClass('cwl-btn-ko');
					this_button.text(cwl_msg_ko);
				}			
			}
		});
	});

});
