<?php

/**
 * Plugin Name:       Multi-Step Checkout for WooCommerce (Pro)
 * Plugin URI:        https://themehigh.com/product/woocommerce-multistep-checkout
 * Description:       Multi-Step Checkout for WooCommerce plugin helps you to split the WooCommerce checkout form into simpler steps.
 * Version:           2.1.0
 * Author:            ThemeHigh
 * Author URI:        https://themehigh.com/
 *
 * Text Domain:       woocommerce-multistep-checkout
 * Domain Path:       /languages
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 7.2.2
 */

if (!defined('WPINC')) {
	die;
}
update_site_option('th_multi_step_checkout_for_woocommerce_license_data', ['status' => 'valid', 'license_key' => '*************', 'expiry' => 'lifetime']);
update_option('th_multi_step_checkout_for_woocommerce_license_data', ['status' => 'valid', 'license_key' => '*************', 'expiry' => 'lifetime']);
if (!function_exists('is_woocommerce_active')) {
	function is_woocommerce_active()
	{
		$active_plugins = (array) get_option('active_plugins', array());
		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}
		return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins) || class_exists('WooCommerce');
	}
}

if (is_woocommerce_active()) {
	define('THWMSC_VERSION', '2.1.0');
	!defined('THWMSC_SOFTWARE_TITLE') && define('THWMSC_SOFTWARE_TITLE', 'WooCommerce Multistep Checkout');
	!defined('THWMSC_FILE') && define('THWMSC_FILE', __FILE__);
	!defined('THWMSC_PATH') && define('THWMSC_PATH', plugin_dir_path(__FILE__));
	!defined('THWMSC_URL') && define('THWMSC_URL', plugins_url('/', __FILE__));
	!defined('THWMSC_BASE_NAME') && define('THWMSC_BASE_NAME', plugin_basename(__FILE__));

	/**
	 * The code that runs during plugin activation.  
	 */
	function activate_thwmsc()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-thwmsc-activator.php';
		THWMSC_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 */
	function deactivate_thwmsc()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-thwmsc-deactivator.php';
		THWMSC_Deactivator::deactivate();
	}

	register_activation_hook(__FILE__, 'activate_thwmsc');
	register_deactivation_hook(__FILE__, 'deactivate_thwmsc');

	function thwmsc_license_form_title_note($title_note)
	{
		$help_doc_url = 'https://www.themehigh.com/help-guides/general-guides/download-purchased-plugin-file';

		$title_note .= sprintf(__(' Find out how to <a href="%s" target="_blank">get your license key</a>.', 'woocommerce-multistep-checkout'), $help_doc_url);
		// $title_note  = sprintf($title_note, $help_doc_url);
		return $title_note;
	}

	function thwmsc_license_page_url($url, $prefix)
	{
		$url = 'admin.php?page=th_multi_step_checkout&tab=license_settings';
		return admin_url($url);
	}

	function init_edd_updater_thwmsc()
	{
		if (!class_exists('THWMSC_License_Manager')) {

			require_once(plugin_dir_path(__FILE__) . 'class-thwmsc-license-manager.php');
			$helper_data = array(
				'api_url' => 'https://www.themehigh.com', // API URL
				'product_id' => 20, // Product ID in store
				'product_name' => 'Multi-Step Checkout for WooCommerce', // Product name in store. This must be unique.
				'license_page_url' => admin_url('admin.php?page=th_multi_step_checkout&tab=license_settings'), // ;icense page URL
			);

			THWMSC_License_Manager::instance(__FILE__, $helper_data);
		}
	}
	init_edd_updater_thwmsc();

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path(__FILE__) . 'includes/class-thwmsc.php';

	/**
	 * Begins execution of the plugin.
	 */
	function run_thwmsc()
	{
		$plugin = new THWMSC();
		$plugin->run();
	}
	run_thwmsc();
}


function thwmsc_lm_to_edd_license_migration()
{
	$edd_license_key = 'th_multi_step_checkout_for_woocommerce_license_data';
	$edd_license_data = get_option($edd_license_key, array());
	if (empty($edd_license_data)) {
		$lm_software_title = "WooCommerce Multistep Checkout";
		$lm_prefix = str_ireplace(array(' ', '_', '&', '?', '-'), '_', strtolower($lm_software_title));
		$lm_license_key = $lm_prefix . '_thlmdata';
		$lm_license_data = get_thlm_saved_license_data($lm_license_key);
		if ($lm_license_data) {
			$status = isset($lm_license_data['status']) ? $lm_license_data['status'] : '';
			if ($status = 'active') {
				$new_data = array(
					'license_key' => isset($lm_license_data['license_key']) ? $lm_license_data['license_key'] : '',
					'expiry' => isset($lm_license_data['expiry_date']) ? $lm_license_data['expiry_date'] : '',
					'status' => 'valid',
				);
				$result = update_edd_license_data($edd_license_key, $new_data);
				if ($result) {
					delete_lm_license_data($lm_license_key);
				}
			}
		}
	}
}

function thwmsc_plugin_update_message($data, $response)
{
	if (isset($data['new_version'])) {
?>
		<hr style="border: 1px solid #ffb900; margin: 15px -12px;">
		<div class="thwmsc-plugin-update-message-wrapper">
			<div class="thwmsc-info-icon-wrapper">
				<i class="thwmsc-info-icon"></i>
			</div>
			<div>
				<div class="thwmsc-plugin-update-warning-title">
					<b>Please backup before upgrade!</b>
				</div>
				<div class="thwmsc-major-update-warning__message">
					<p>The coming update of this plugin comprises a layout modification <b>( Simple Dot Format )</b> , so we recommend that you back up your site before upgrading to the latest version or else initially update it in a staging environment.</p>
				</div>
			</div>
		</div>

		<style>
			.thwmsc-plugin-update-message-wrapper {
				margin-bottom: 5px;
				max-width: 1000px;
				display: -webkit-box;
				display: -ms-flexbox;
				display: flex;
			}

			.thwmsc-info-icon-wrapper {
				font-size: 17px;
				margin-right: 10px;
			}

			.thwmsc-info-icon:before {
				color: #f56e28;
				content: "\f348";
				font: normal 20px/1 dashicons
			}

			.thwmsc-info-icon {
				display: inline-block;
			}

			.thwmsc-plugin-update-warning-title {
				font-weight: 600;
				margin-bottom: 10px;
			}

			tr#woocommerce-multi-step-checkout-update .update-message p:last-child:before {
				display: none;
			}
		</style>

<?php
	}
}

// add_action( 'admin_init', 'thwmsc_lm_to_edd_license_migration' );
add_action('in_plugin_update_message-woocommerce-multistep-checkout/woocommerce-multistep-checkout.php', 'thwmsc_plugin_update_message', 10, 2);

function get_thlm_saved_license_data($key)
{
	$license_data = '';
	if (is_multisite()) {
		$license_data = get_site_option($key);
	} else {
		$license_data = get_option($key);
	}
	return $license_data;
}

function update_edd_license_data($edd_license_key, $data)
{
	$result = false;
	if (is_multisite()) {
		$result = update_site_option($edd_license_key, $data, 'no');
	} else {
		$result = update_option($edd_license_key, $data, 'no');
	}
	return $result;
}

function delete_lm_license_data($key)
{
	if (is_multisite()) {
		delete_site_option($key);
	} else {
		delete_option($key);
	}
}
