<?php
/**
 * Handles the Product Notice.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'BYA_Product_Notice_Handler' ) ) {
	/**
	 * Main Class
	 */
	class BYA_Product_Notice_Handler {

		/**
		 * Class Initialization.
		 */
		public static function init() {
			// buy again enable check.
			$enable_by_again = bya_allow_buy_again();

			if ( $enable_by_again ) {
				add_action( 'wp_head', array( __CLASS__, 'get_product_notice' ) );
			}
		}

		public static function get_product_notice() {
			if ( is_product() ) {
				$notice_enable       = get_option( 'bya_general_show_buy_again_notice', 'yes' );
				$user_id             = get_current_user_id();
				$user_restriction    = bya_user_restriction( $user_id );
				$product             = wc_get_product();
				$product_id          = $product->get_id();
				$product_restriction = bya_product_restriction( $product_id );

				if ( 'no' === $notice_enable ) {
					return;
				}

				if ( ! $user_restriction ) {
					return;
				}

				if ( ! $product_restriction ) {
					return;
				}

				$order = self::get_product_contain_order_id();

				if ( ! bya_check_is_array( $order ) ) {
					return;
				}
				$link_caption = get_option( 'bya_general_order_detail_link_caption', esc_html__( 'Order_details', 'buy-again-for-woocommerce' ) );
				$link         = '<a href="' . $order['url'] . '" >' . esc_html( $link_caption ) . '</a>';
				$message      = get_option( 'bya_general_buy_again_message', esc_html__( 'Previously you have puchased this product [order_details]', 'buy-again-for-woocommerce' ) );
				$message      = str_replace( array( '[order_details]' ), array( $link ), $message );

				wc_add_notice( $message, 'notice' );
			}
		}

		/*
		 * get product contain order id.
		 */

		public static function get_product_contain_order_id() {
			if ( ! is_product() ) {
				return;
			}

			$product        = wc_get_product();
			$product_id     = $product->get_id();
			$order_statuses = bya_format_woo_order_status( get_option( 'bya_general_order_status_to_show', array( 'processing', 'completed' ) ) );

			if ( ! bya_check_is_array( $order_statuses ) ) {
				return;
			}

			// Getting current customer orders
			$customer_orders = wc_get_orders(
				array(
					'meta_key'    => '_customer_user',
					'meta_value'  => get_current_user_id(),
					'post_status' => $order_statuses,
					'numberposts' => -1,
				)
			);

			if ( ! bya_check_is_array( $customer_orders ) ) {
				return;
			}

			foreach ( $customer_orders as $order ) {
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				$url      = $order->get_view_order_url();

				if ( ! bya_check_is_array( $order->get_items() ) ) {
					return;
				}

				foreach ( $order->get_items() as $item_id => $item ) {
					$item_id = method_exists( $item, 'get_product_id' ) ? $item->get_product_id() : $item['product_id'];

					if ( $product_id === $item_id ) {
						return array(
							'order_id' => $order_id,
							'url'      => $url,
						);
					}
				}
			}

			return;
		}

	}

	BYA_Product_Notice_Handler::init();
}
