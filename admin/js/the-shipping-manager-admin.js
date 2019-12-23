jQuery(document).ready(function() {
	jQuery('#the-shipping-country').select2();
	
	jQuery('#the-shipping-country').on('change', function(){
		if( jQuery('#the-shipping-country').val() != '' ){
			jQuery.ajax({
				url: ajaxadmin.ajaxurl,
				type: 'post',			
				data: {
					'action':'display_country_shipping_methods',
					'country' : jQuery('#the-shipping-country').val()
				},				
				success: function (data) {
					jQuery("#shipping-methods-display").html(data);
				},
				error: function(errorThrown){
					//alert(errorThrown);
					console.log(errorThrown);
				}				
			});				
		}
	});
	
});