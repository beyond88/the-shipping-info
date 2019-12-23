<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fiverr.com/abdullahalawal
 * @since      1.0.0
 *
 * @package    The_Shipping_Manager
 * @subpackage The_Shipping_Manager/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    The_Shipping_Manager
 * @subpackage The_Shipping_Manager/admin
 * @author     Abdullah Al Awal <odeskcareer@gmail.com>
 */
class The_Shipping_Manager_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action( 'admin_menu', array( $this, 'the_shipping_manager_admin_menu' ), 80 );
		add_filter('woocommerce_shipping_instance_form_fields_free_shipping', [
			$this,
			'shipping_logo',
		], 10, 1);
		add_filter('woocommerce_shipping_instance_form_fields_local_pickup', [
			$this,
			'shipping_logo',
		], 10, 1);
		add_filter('woocommerce_shipping_instance_form_fields_flat_rate', [
			$this,
			'shipping_logo',
		], 10, 1);
		add_action( 'wp_ajax_display_country_shipping_methods', array( $this, 'display_country_shipping_methods' ) );
		add_action('wp_ajax_nopriv_display_country_shipping_methods', array( $this, 'display_country_shipping_methods' ) );	

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, TSM_ADMIN_URL . 'css/the-shipping-manager-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'-select2', TSM_ADMIN_URL . 'css/select2.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'the-shipping-manager-admin-script', TSM_ADMIN_URL . 'js/the-shipping-manager-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'select2-full-script', TSM_ADMIN_URL . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script('the-shipping-manager-admin-script', 'ajaxadmin', array('ajaxurl' => admin_url('admin-ajax.php')));		

	}

	/**
	 *
	 * @since    1.0.0
	 */		
	public function shipping_logo($fields) {

		$fields['shipping_logo'] = [
			'title'       => __('Logo URL', 'the-shipping-manager'),
			'type'        => 'text',
			'description' => __('Set shipping method logo url', 'the-shipping-manager'),
			'default'     => '',
			'desc_tip'    => true,
		];

		return $fields;
	}	

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */	
	public function the_shipping_manager_admin_menu() {
		add_menu_page( esc_html__( 'The Shipping Manager', 'the-shipping-manager' ), esc_html__( 'The Shipping Manager', 'the-shipping-manager' ), 'manage_options', 'the-shipping-manager', array($this, 'the_shipping_manager_admin_menu_page'), 'dashicons-admin-generic', 18 );
	}

	/**
	 *
	 * @since    1.0.0
	 */		
	public function the_shipping_manager_admin_menu_page() {
		return include TSM_ADMIN_DIR_PATH . '/partials/shipping-form.php';
	}
	
	/**
	 *
	 * @since    1.0.0
	 */			
	public function display_country_shipping_methods() {
		
		global $wpdb;
		$country = $_POST['country'];
		$zone_id = $echo = '';
		$data = array();		 
		$woocommerce_shipping_zone_locations 	= $wpdb->prefix.'woocommerce_shipping_zone_locations';
		$woocommerce_shipping_zone_methods 		= $wpdb->prefix.'woocommerce_shipping_zone_methods';
		$q = $wpdb->prepare("SELECT zone_id FROM $woocommerce_shipping_zone_locations WHERE location_code=%s LIMIT 1;", $country);
		$data = $wpdb->get_row($q);
		if (!empty($data->zone_id)){
			$zone_id  = $data->zone_id;
			$data  = $wpdb->get_results("SELECT * FROM $woocommerce_shipping_zone_methods WHERE zone_id =".$zone_id."");   			
		}

		if( is_array($data) && !empty($data) ) {

			$custom_text_label = "shipping_info_".$country."";
			$shipping_info = get_option( $custom_text_label );
			$echo .='<tr valign="top" ><th scope="row"><label for="jl-field-name">Description</label></th><td class="middle-align"><textarea name="the-custom-text" id="the-custom-text" required class="timezone_string" rows=5 cols=39 placeholder="Description">'.$shipping_info.'</textarea></td></tr>';

			foreach( $data as $d ){
				$shipping_method = "woocommerce_".$d->method_id."_".$d->instance_id."_settings";
				$shipping_info = "woocommerce_".$d->method_id."_".$d->instance_id."_days";
				$data = get_option( $shipping_method );
				$existing_data = get_option( $shipping_info );
				$echo .= '<tr valign="top"><th scope="row">Shipping Method:</th><td class="middle-align">'.$data["title"].'</td></tr>
				<tr valign="top"><th scope="row">Day(s):<td class="middle-align"><input type="text" required name="woocommerce_'.$d->method_id.'_'.$d->instance_id.'_days" class="timezone_string" placeholder="Shipping Day(s)" value="'.$existing_data.'" /><span class="shipping-method-info">Ex.&nbsp;4-15</span></td></tr>';
			}
		} else {
			$echo .= '<tr valign="top"><th scope="row">Shipping Method:</th><td class="middle-align">Shipping method not found!</td></tr>';			
		}
		wp_send_json($echo);
	}


}
