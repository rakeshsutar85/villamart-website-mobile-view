<?php
/**
 * Plugin Name: WooCommerce Checkout Upsell Funnel Premium
 * Plugin URI: https://villatheme.com/
 * Description: WooCommerce Checkout Upsell Funnel displays product suggestion and smart order bump on checkout page with the attractive discounts
 * Version: 1.0.7
 * Author: VillaTheme
 * Author URI: https://villatheme.com
 * Text Domain: woocommerce-checkout-upsell-funnel
 * Domain Path: /languages
 * Copyright 2021-2023 VillaTheme.com. All rights reserved.
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.1
 * WC requires at least: 5.0
 * WC tested up to: 7.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION', '1.0.7' );
/**
 * Detect plugin. For use on Front End only.
 */
$viwcuf_errors = array();
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! version_compare( phpversion(), '7.0', '>=' ) ) {
	$viwcuf_errors[] = __( 'Please update PHP version at least 7.0 to use WooCommerce Checkout Upsell Funnel.', 'woocommerce-checkout-upsell-funnel' );
}
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$viwcuf_errors[] = __( 'Please install and activate WooCommerce to use WooCommerce Checkout Upsell Funnel.', 'woocommerce-checkout-upsell-funnel' );
}
if ( empty( $viwcuf_errors ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-checkout-upsell-funnel" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
}

/**
 * Class VIWCUF_CHECKOUT_UPSELL_FUNNEL
 */
class VIWCUF_CHECKOUT_UPSELL_FUNNEL {
	protected $errors;

	public function __construct( $errors = array() ) {
		$this->errors = $errors;
		//compatible with 'High-Performance order storage (COT)'
		add_action( 'before_woocommerce_init', array( $this, 'before_woocommerce_init' ) );
		if ( ! empty( $errors ) ) {
			add_action( 'admin_notices', array( $this, 'global_note' ) );

			return;
		}
		add_action( 'activated_plugin', array( $this, 'activated_plugin' ) );
	}

	/**
	 * Notify if found error
	 */
	function global_note() {
		if ( count( $this->errors ) ) {
			foreach ( $this->errors as $error ) {
				echo sprintf( '<div id="message" class="error"><p>%s</p></div>', esc_html( $error ) );
			}
		}
	}

	/*
	 * Create table to save log
	 */
	function activated_plugin( $plugin ) {
		if ( $plugin === 'woocommerce-checkout-upsell-funnel/woocommerce-checkout-upsell-funnel.php' ) {
			VIWCUF_CHECKOUT_UPSELL_FUNNEL_Report_Table::create_table();
			$viwcuf_params = get_option( 'viwcuf_woo_checkout_upsell_funnel', array() );
			if ( ! empty( $viwcuf_params['us_redirect_page_endpoint'] ) ) {
				update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
			}
		}
	}
	public function before_woocommerce_init() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
}

new VIWCUF_CHECKOUT_UPSELL_FUNNEL( $viwcuf_errors );