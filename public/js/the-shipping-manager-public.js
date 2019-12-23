jQuery(document).ready(function($) {
	jQuery('#display-shipping-country').select2();
});

function display_shipping_country(){

	if( jQuery('#display-shipping-country').val() != '' ){
		
		jQuery(".ldio-2nz8jdu01iv > div").show();
		jQuery.ajax({
			url: ajax_object.ajaxurl,
			type: 'post',			
			data: {
				'action':'display_country_wise_shipping_methods',
				'country' : jQuery('#display-shipping-country').val()
			},				
			success: function (data) {
				jQuery(".ldio-2nz8jdu01iv > div").hide();
				jQuery("#the-shipping-info").html(data);
			},
			error: function(errorThrown){
				jQuery(".ldio-2nz8jdu01iv > div").hide();
				//alert(errorThrown);
				console.log(errorThrown);
			}				
		});				
	}
}
