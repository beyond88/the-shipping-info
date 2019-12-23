<?php

/**
 *
 * @link              https://www.fiverr.com/abdullahalawal
 * @since             1.0.0
 * @package           The_Shipping_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       The Shipping Manager
 * Plugin URI:        https://www.canchema.com/
 * Description:       The shipping manager is an WooCommerce plugin.
 * Version:           1.0.0
 * Author:            Abdullah Al Awal
 * Author URI:        https://www.fiverr.com/abdullahalawal
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       the-shipping-manager
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function wc_missing_wc_notice() {
	/* translators: 1. URL link. */
	echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'The Shipping Manager requires WooCommerce to be installed and active.', 'the-shipping-manager' ), '' ) . '</strong></p></div>';
}

add_action( 'plugins_loaded', 'wc_install_init' );
function wc_install_init() {

	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', 'wc_missing_wc_notice' );
		return;
	}
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TSM_VERSION', '1.0.0' );
define( 'TSM_URL', plugins_url( '/', __FILE__ ) );
define( 'TSM_ADMIN_URL', TSM_URL . 'admin/' );
define( 'TSM_PUBLIC_URL', TSM_URL . 'public/' );

define( 'TSM_FILE', __FILE__ );
define( 'TSM_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'TSM_ADMIN_DIR_PATH', TSM_ROOT_DIR_PATH . 'admin/' );
define( 'TSM_PUBLIC_PATH', TSM_ROOT_DIR_PATH . 'public/' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-the-shipping-manager-activator.php
 */
function activate_the_shipping_manager() {
	require_once TSM_ROOT_DIR_PATH . 'includes/class-the-shipping-manager-activator.php';
	The_Shipping_Manager_Activator::activate();
}

function deactivate_the_shipping_manager() {
	require_once TSM_ROOT_DIR_PATH . 'includes/class-the-shipping-manager-deactivator.php';
	The_Shipping_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_the_shipping_manager' );
register_deactivation_hook( __FILE__, 'deactivate_the_shipping_manager' );
require TSM_ROOT_DIR_PATH . 'includes/class-the-shipping-manager.php';

/**
 *
 * @since    1.0.0
 */
function run_the_shipping_manager() {

	$plugin = new The_Shipping_Manager();
	$plugin->run();

}
run_the_shipping_manager();