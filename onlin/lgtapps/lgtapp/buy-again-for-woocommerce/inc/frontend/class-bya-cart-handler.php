<?php
/**
 * Handles the Cart.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Cart_Handler' ) ) {

	/**
	 * Class BYA_Cart_Handler
	 **/
	class BYA_Cart_Handler {

		/**
		 * Class Initialization.
		 */
		public static function init() {
			// Add tickets data in the cart item.
			add_action( 'woocommerce_get_item_data', array( __CLASS__, 'maybe_add_custom_item_data' ), 10, 2 );
			// Add Cart item data.
			add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'add_cart_item_data' ), 10, 3 );
			// Change Cart ID.
			add_filter( 'woocommerce_cart_id', array( __CLASS__, 'find_cart_id_in_cart' ), 10, 4 );
		}

		/**
		 * Add custom data in cart items.
		 *
		 * @since 1.0
		 * @param Integer $cart_id Cart Item Key.
		 * @param Integer $product_id Product ID.
		 * @param Integer $variation_id Variation ID.
		 * @param Array   $cart_item_data Cart Item Data.
		 * @return String
		 * */
		public static function find_cart_id_in_cart( $cart_id, $product_id, $variation_id, $cart_item_data ) {
			if ( '2' === get_option( 'bya_general_cart_same_entry', '1' ) ) {
				return $cart_id;
			}

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				if ( $product_id !== $cart_item['product_id'] ) {
					continue;
				}

				if ( ! empty( $variation_id ) && $variation_id !== $cart_item['variation_id'] ) {
					continue;
				}

				if ( bya_check_is_array( $cart_item_data ) ) {
					foreach ( $cart_item_data as $cart_item_data_key => $cart_item_data_value ) {
						if ( ! isset( $cart_item[ $cart_item_data_key ] ) ) {
							continue 2;
						}

						if ( $cart_item[ $cart_item_data_key ] !== $cart_item_data_value ) {
							continue 2;
						}
					}
				}

				return $cart_item_key;
			}

			return $cart_id;
		}

		/**
		 * Add custom data in cart items.
		 *
		 * @param Array $item_data Cart Item Data.
		 * @param Array $cart_item Cart Item.
		 * @return Array
		 * */
		public static function maybe_add_custom_item_data( $item_data, $cart_item ) {
			if ( ! isset( $cart_item['product_id'] ) || empty( $cart_item['product_id'] ) ) {
				return $item_data;
			}

			$get_display_metas = bya_get_display_custom_metas();

			if ( ! bya_check_is_array( $get_display_metas ) ) {
				return $item_data;
			}

			foreach ( $get_display_metas as $meta_key ) {
				if ( ! isset( $cart_item[ $meta_key ] ) || ! bya_check_is_array( $cart_item[ $meta_key ] ) ) {
					continue;
				}

				foreach ( $cart_item[ $meta_key ] as $display_key => $display_value ) {
					$item_data[ $meta_key ] = array(
						'key'   => $display_key,
						'value' => $display_value,
					);
				}
			}

			return $item_data;
		}

		/**
		 * Display Product title based on Booster for WooCommerce custom cart info
		 *
		 * @since 1.0
		 * @param Array   $cart_item_data Cart item data.
		 * @param Integer $product_id Product ID.
		 * @param Integer $variation_id Product ID.
		 * @return Array
		 */
		public static function add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
			$order_id = isset( $_REQUEST['bya_order_id'] ) ? wc_clean( wp_unslash( $_REQUEST['bya_order_id'] ) ) : ''; // @codingStandardsIgnoreLine.

			if ( empty( $order_id ) ) {
				return $cart_item_data;
			}

			$cart_item_data = bya_prepare_cart_item_data( $order_id, $cart_item_data, $product_id, $variation_id );

			return $cart_item_data;
		}

	}

	BYA_Cart_Handler::init();
}
