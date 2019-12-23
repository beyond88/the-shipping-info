<?php

/**
 *
 * @link       https://www.fiverr.com/abdullahalawal
 * @since      1.0.0
 *
 * @package    The_Shipping_Manager
 * @subpackage The_Shipping_Manager/public
 * @author     Abdullah Al Awal <odeskcareer@gmail.com>
 */
class The_Shipping_Manager_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		add_action( 'init', array( $this, 'register_shortcodes' ) );
		add_action( 'wp_ajax_display_country_wise_shipping_methods', array( $this, 'display_country_wise_shipping_methods' ) );
		add_action('wp_ajax_nopriv_display_country_wise_shipping_methods', array( $this, 'display_country_wise_shipping_methods' ) );	
		

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/the-shipping-manager-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'select2-public-script', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'the-shipping-manager-public-script', plugin_dir_url( __FILE__ ) . 'js/the-shipping-manager-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script('the-shipping-manager-public-script', 'ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));	

	}


	/**
	 * Shortcode
	 *
	 * @since    1.0.0
	 */
	public function display_shipping_information($atts) {

		return include TSM_PUBLIC_PATH .'partials/the-shipping-manager-public-display.php';
		
	}

	/**
	 * Shortcode
	 *
	 * @since    1.0.0
	 */	
	public function register_shortcodes($atts = [], $content = null, $tag = ''){
		$wporg_atts = shortcode_atts([], $atts, $tag);	
		add_shortcode( 'the-shipping-manager', array($this, 'display_shipping_information') );
	}
	
	/**
	 *
	 * @since    1.0.0
	 */			
	public function display_country_wise_shipping_methods() {
		
		global $wpdb;
		$country 	= $_POST['country'];
		$zone_id 	= $echo = $country_info = $price = '' ;
		$data 		= array();		 
		$woocommerce_shipping_zone_locations 	= $wpdb->prefix.'woocommerce_shipping_zone_locations';
		$woocommerce_shipping_zone_methods 		= $wpdb->prefix.'woocommerce_shipping_zone_methods';
		$q 			= $wpdb->prepare("SELECT zone_id FROM $woocommerce_shipping_zone_locations WHERE location_code=%s LIMIT 1;", $country);
		$data 		= $wpdb->get_row($q);
		if (!empty($data->zone_id)){
			$zone_id  = $data->zone_id;
			$data  	= $wpdb->get_results("SELECT * FROM $woocommerce_shipping_zone_methods WHERE zone_id =".$zone_id."");   			
		}

		$echo .='<hr class="hr_slim">';

		if( is_array($data) && !empty($data) ) {

			$custom_text_label = "shipping_info_".$country."";
			$country_info      = get_option( $custom_text_label );
			foreach( $data as $d ){
				$shipping_method = "woocommerce_".$d->method_id."_".$d->instance_id."_settings";
				$shipping_info = "woocommerce_".$d->method_id."_".$d->instance_id."_days";
				$data = get_option( $shipping_method );
				$existing_data = get_option( $shipping_info );	
				if( !empty($data["cost"]) && is_numeric($data["cost"]) ){
                    
                    $currency = get_woocommerce_currency();
                    if( $currency == "GBP" ) {

                        $pound_rate = get_option( 'alg_currency_switcher_exchange_rate_USD_GBP' );
                        $price = $pound_rate * $data["cost"]; //price * pound
                        $price = wc_price(ceil($price));
                    } else if( $currency == "EUR" ) {

                        $euro_rate = get_option( 'alg_currency_switcher_exchange_rate_USD_EUR' );
                        $price = $euro_rate * $data["cost"]; //price * pound
						$price = wc_price(ceil($price)); 
						                       
                    } else {
                        $price = wc_price(ceil($data["cost"]));
                    }
		
				} else if ( !empty($data["cost"]) && !is_numeric($data["cost"]) ) {
					$price = strtolower($data["cost"]);
				} else {
					$price = '';
				}  							
				$echo .='
						<div class="row row-large">	
							<div class="col medium-6 small-12 large-6">
								<div class="col-inner">
									<span class="subheading">'.$data["title"].'</span>
									<br>
									<span class="medium">'.$existing_data.'</span> business days&emsp;
									<span class="medium">('.$price.')</span>
								</div>	
							</div>
							<div class="col medium-6 small-12 large-6">
								<img src="'.$data["shipping_logo"].'" alt="'.$data["title"].'">
							</div>
						</div>
						<hr class="hr_slim">
					';
			}

			$echo .='
					<br>
					<div class="row row-large">	
						<div class="col medium-6 small-12 large-12">
							<div class="col-inner">
								<div class="em">
									'.$country_info.'
								</div>
							</div>
						</div>		
					</div>';
		} else {

			$data  = $wpdb->get_results("SELECT * FROM $woocommerce_shipping_zone_methods WHERE zone_id =0"); 
			foreach( $data as $d ){
				$shipping_method 	= "woocommerce_".$d->method_id."_".$d->instance_id."_settings";
				$data 				= get_option( $shipping_method );

				if( !empty($data["cost"]) && is_numeric($data["cost"]) ){
                    
                    $currency = get_woocommerce_currency();
                    if( $currency == "GBP" ) {

                        $pound_rate = get_option( 'alg_currency_switcher_exchange_rate_USD_GBP' );
                        $price = $pound_rate * $data["cost"]; //price * pound
						$price = wc_price(ceil($price));
						
                    } else if( $currency == "EUR" ) {

                        $euro_rate = get_option( 'alg_currency_switcher_exchange_rate_USD_EUR' );
                        $price = $euro_rate * $data["cost"]; //price * pound
						$price = wc_price(ceil($price)); 
						                       
                    } else {
                        $price = wc_price(ceil($data["cost"]));
                    }
		
				} else if ( !empty($data["cost"]) && !is_numeric($data["cost"]) ) {
					$price = strtolower($data["cost"]);
				} else {
					$price = '';
				}  							
				$echo .='
						<div class="row row-large">	
							<div class="col medium-6 small-12 large-6">
								<div class="col-inner">
									<span class="subheading">'.$data["title"].'</span>
									<br>
									<span class="medium">('.$price.')</span>
								</div>	
							</div>
							<div class="col medium-6 small-12 large-6">
								<img src="'.$data["shipping_logo"].'" alt="'.$data["title"].'">
							</div>
						</div>
						<hr class="hr_slim">
					';
			}			
			
		}
		wp_send_json($echo);
	}	
}
