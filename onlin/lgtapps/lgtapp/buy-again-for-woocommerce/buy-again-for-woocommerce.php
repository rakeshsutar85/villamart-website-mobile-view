<?php
/**
 * Plugin Name: Buy Again for WooCommerce
 * Description: Your customers can quickly purchase the products which they had purchased earlier in your site.
 * Version: 3.9.0
 * Author: Flintop
 * Author URI: https://flintop.com
 * Text Domain: buy-again-for-woocommerce
 * Domain Path: /languages
 * Woo: 5134099:587b5729b4b3c9533ee01be25419d1f8
 * Tested up to: 6.2.2
 * WC tested up to: 7.8.0
 * WC requires at least: 3.5
 * Copyright: © 2019 Flintop
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Buy Again for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* Include once will help to avoid fatal error by load the files when you call init hook */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Include main class file.
if ( ! class_exists( 'Buy_Again' ) ) {
	include_once 'inc/class-buy-again.php';
}

if ( ! function_exists( 'bya_is_valid_wp' ) ) {
	/**
	 * Is valid WordPress version?
	 *
	 * @return bool
	 */
	function bya_is_valid_wp() {
		return ( version_compare( get_bloginfo( 'version' ), Buy_Again::$wp_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'bya_is_valid_wc' ) ) {
	/**
	 * Is valid WooCommerce version?
	 *
	 * @return bool
	 */
	function bya_is_valid_wc() {
		return ( version_compare( get_option( 'woocommerce_version' ), Buy_Again::$wc_requires, '<' ) ) ? false : true;
	}
}

if ( ! function_exists( 'bya_is_wc_active' ) ) {
	/**
	 * Function to check whether WooCommerce is active or not.
	 *
	 * @return bool
	 */
	function bya_is_wc_active() {
		// This condition is for multi site installation.
		if ( is_multisite() && ! is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
			// This condition is for single site installation.
		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return false;
		}

		return true;
	}
}

if ( ! function_exists( 'bya_is_plugin_active' ) ) {
	/**
	 * Is plugin active?
	 *
	 * @return bool
	 */
	function bya_is_plugin_active() {
		if ( bya_is_valid_wp() && bya_is_wc_active() && bya_is_valid_wc() ) {
			return true;
		}

		add_action(
			'admin_notices',
			function() {
				$notice = '';

				if ( ! bya_is_valid_wp() ) {
					$notice = sprintf( 'This version of Buy Again for WooCommerce requires WordPress %1s or newer.', Buy_Again::$wp_requires );
				} elseif ( ! bya_is_wc_active() ) {
					$notice = 'Buy Again for WooCommerce Plugin will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin.';
				} elseif ( ! bya_is_valid_wc() ) {
					$notice = sprintf( 'This version of Buy Again for WooCommerce requires WooCommerce %1s or newer.', Buy_Again::$wc_requires );
				}

				if ( $notice ) {
					echo '<div class="error">';
					echo '<p>' . wp_kses_post( $notice ) . '</p>';
					echo '</div>';
				}
			}
		);

		return false;
	}
}

// Return if the plugin is not active.
if ( ! bya_is_plugin_active() ) {
	return;
}

// Define constant.
if ( ! defined( 'BYA_PLUGIN_FILE' ) ) {
	define( 'BYA_PLUGIN_FILE', __FILE__ );
}

if ( ! function_exists( 'bya' ) ) {
	/**
	 * Return Buy Again class object
	 */
	function bya() {
		return Buy_Again::instance();
	}
}

// initialize the plugin.
bya();
