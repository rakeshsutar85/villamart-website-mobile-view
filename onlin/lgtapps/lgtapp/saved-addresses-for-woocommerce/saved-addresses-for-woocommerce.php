<?php
/**
 * Plugin Name: Saved Addresses For WooCommerce
 * Plugin URI: https://woocommerce.com/products/saved-addresses-for-woocommerce/
 * Description: Save billing and shipping addresses for later use and do quick checkout. Easily manage address from My Account.
 * Version: 2.5.2
 * Author: StoreApps
 * Author URI: https://www.storeapps.org/
 * Developer: StoreApps
 * Developer URI: https://www.storeapps.org/
 * Requires at least: 5.0.0
 * Tested up to: 5.8.2
 * WC requires at least: 3.7.0
 * WC tested up to: 5.9.0
 * Text Domain: saved-addresses-for-woocommerce
 * Domain Path: /languages/
 * Woo: 5240922:52f50878974c09d8f7baadb261b952d7
 * Copyright (c) 2016-2021 StoreApps. All rights reserved.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package saved-addresses-for-woocommerce
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * On activation
 */
register_activation_hook( __FILE__, 'saw_activate' );

/**
 * Function to set transient on plugin activation.
 */
function saw_activate() {
	// Redirect to welcome/landing page.
	if ( ! is_network_admin() && ! isset( $_GET['activate-multi'] ) ) { // phpcs:ignore
		set_transient( '_saw_activation_redirect', 1, 30 );
	}
}

/**
 * Load Saved Addresses For WooCommerce only if woocommerce is activated
 */
function initialize_sa_saved_addresses_wc() {

	if ( ! defined( 'SAW_PLUGIN_DIRNAME' ) ) {
		define( 'SAW_PLUGIN_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );
	}
	if ( ! defined( 'SAW_PLUGIN_DIR_PATH' ) ) {
		define( 'SAW_PLUGIN_DIR_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}
	if ( ! defined( 'SAW_PLUGIN_FILE' ) ) {
		define( 'SAW_PLUGIN_FILE', __FILE__ );
	}
	if ( ! defined( 'SAW_PLUGIN_URL' ) ) {
		define( 'SAW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
	}
	if ( ! defined( 'SAW_PLUGIN_DIRPATH' ) ) {
		define( 'SAW_PLUGIN_DIRPATH', dirname( __FILE__ ) );
	}

	if ( is_admin() ) {
		include_once 'includes/admin/class-saw-admin-welcome.php';
		include_once 'includes/admin/class-saw-admin-notices.php';
		include_once 'includes/admin/class-saw-privacy.php';
	}

	include_once 'includes/saw-functions.php';
	include_once 'includes/class-sa-saved-addresses-for-woocommerce.php';
	include_once 'includes/class-saw-update.php';
	include_once 'includes/compat/class-sa-wc-compatibility-3-6.php';
	include_once 'includes/compat/class-sa-wc-compatibility-3-7.php';
	include_once 'includes/compat/class-sa-wc-compatibility-3-8.php';
	include_once 'includes/compat/class-sa-wc-compatibility-3-9.php';
	include_once 'includes/compat/class-sa-wc-compatibility-4-0.php';

	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	if ( ( in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) ) {
		require_once 'includes/class-sa-saved-addresses-for-woocommerce.php';
		$GLOBALS['sa_saved_addresses_wc'] = SA_Saved_Addresses_For_WooCommerce::get_instance();
	}

}

add_action( 'plugins_loaded', 'initialize_sa_saved_addresses_wc' );
