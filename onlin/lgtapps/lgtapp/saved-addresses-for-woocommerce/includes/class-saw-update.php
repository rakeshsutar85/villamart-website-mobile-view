<?php
/**
 * Class to update addresses for existing users from checkout/My Account > Address from previous orders
 *
 * @package     saved-addresses-for-woocommerce/includes/
 * @since       2.0.0
 * @version     1.0.3
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SAW_Update' ) ) {

	/**
	 * SA Saved Address For WooCommerce - Main class
	 */
	class SAW_Update {

		/**
		 * Variable to hold instance of Saved Addresses
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Saved Addresses For WooCommerce update.
		 *
		 * @return SAW_Update Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			// Checkout page action.
			add_action( 'woocommerce_checkout_before_customer_details', array( $this, 'import_customers_previous_addresses' ) );

			// My Account > Addresses action.
			add_action( 'woocommerce_before_edit_account_address_form', array( $this, 'import_customers_previous_addresses' ) );
		}

		/**
		 * Update in update usermeta previous address of the user
		 */
		public function import_customers_previous_addresses() {

			if ( ( is_checkout() || is_account_page() ) && is_user_logged_in() ) {

				$current_user_id     = get_current_user_id();
				$is_address_imported = get_user_meta( $current_user_id, 'sa_saved_addresses_imported', true );
				if ( 'yes' !== $is_address_imported ) {

					$sa_saved_formatted_shipping_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_addresses', true );
					$sa_saved_formatted_billing_addresses  = get_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', true );

					if ( empty( $sa_saved_formatted_shipping_addresses ) || empty( $sa_saved_formatted_billing_addresses ) ) {

						$previous_orders_details = get_posts(
							array(
								'numberposts' => -1,
								'meta_key'    => '_customer_user', // phpcs:ignore
								'meta_value'  => $current_user_id, // phpcs:ignore
								'post_type'   => 'shop_order',
								'post_status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
							)
						);

						if ( ! empty( $previous_orders_details ) ) {

							foreach ( $previous_orders_details as $orders_placed ) {
								$order_ids[] = $orders_placed->ID;
							}

							foreach ( $order_ids as $order_id ) {
								$order = new WC_Order( $order_id );

								$formatted_billing_address  = $order->get_formatted_billing_address();
								$formatted_shipping_address = $order->get_formatted_shipping_address();

								// Code for billing addresses.
								if ( ! empty( $formatted_billing_address ) ) {
									$billing_address_html[] = $formatted_billing_address;
									$billing_details[]      = SA_Saved_Addresses_For_WooCommerce::get_instance()->get_billing_details( $order );
								}

								// Code for shipping addresses.
								if ( ! empty( $formatted_shipping_address ) ) {
									$shipping_address_html[] = $formatted_shipping_address;
									$shipping_details[]      = SA_Saved_Addresses_For_WooCommerce::get_instance()->get_shipping_details( $order );
								}
							}

							// Code for billing addresses.
							if ( isset( $billing_address_html ) && isset( $billing_details ) ) {
								$saw_billing_address_html = array_filter( $billing_address_html );
								$saw_billing_details      = array_filter( $billing_details );
							}

							// Code for shipping addresses.
							if ( isset( $shipping_address_html ) && isset( $shipping_details ) ) {
								$saw_shipping_address_html = array_filter( $shipping_address_html );
								$saw_shipping_details      = array_filter( $shipping_details );
							}

							// Code for billing addresses.
							if ( ! empty( $saw_billing_address_html ) && ! empty( $saw_billing_details ) ) {
								$total_formated_billing_addresses = array_map( 'unserialize', array_unique( array_map( 'serialize', $saw_billing_address_html ) ) );
								$total_saved_billing_addresses    = array_map( 'unserialize', array_unique( array_map( 'serialize', $saw_billing_details ) ) );
							}

							// Code for shipping addresses.
							if ( ! empty( $saw_shipping_address_html ) && ! empty( $saw_shipping_details ) ) {
								$total_formated_shipping_addresses = array_map( 'unserialize', array_unique( array_map( 'serialize', $saw_shipping_address_html ) ) );
								$total_saved_shipping_addresses    = array_map( 'unserialize', array_unique( array_map( 'serialize', $saw_shipping_details ) ) );
							}

							$default_address_keys             = array();
							$default_address_keys['billing']  = 0;
							$default_address_keys['shipping'] = 0;

							// Code for billing addresses.
							if ( ! empty( $total_formated_billing_addresses ) && ! empty( $total_saved_billing_addresses ) ) {

								// Can improve following line with array_key_last() when PHP 7.2+.
								$default_address_keys['billing'] = array_keys( $total_formated_billing_addresses )[ count( $total_formated_billing_addresses ) - 1 ];

								if ( empty( $sa_saved_formatted_billing_addresses ) ) {
									update_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', $total_formated_billing_addresses );
									update_user_meta( $current_user_id, 'sa_saved_billing_addresses', $total_saved_billing_addresses );
								}
							}

							// Code for shipping addresses.
							if ( ! empty( $total_formated_shipping_addresses ) && ! empty( $total_saved_shipping_addresses ) ) {

								// Can improve following line with array_key_last() when PHP 7.2+.
								$default_address_keys['shipping'] = array_keys( $total_formated_shipping_addresses )[ count( $total_formated_shipping_addresses ) - 1 ];

								if ( empty( $sa_saved_formatted_shipping_addresses ) ) {
									update_user_meta( $current_user_id, 'sa_saved_formatted_addresses', $total_formated_shipping_addresses );
									update_user_meta( $current_user_id, 'sa_saved_shipping_addresses', $total_saved_shipping_addresses );
								}
							}

							update_user_meta( $current_user_id, 'sa_saved_default_address_keys', $default_address_keys );

						}
					}

					update_user_meta( $current_user_id, 'sa_saved_addresses_imported', 'yes' );

				}
			}

		}

	}

}

SAW_Update::get_instance();
