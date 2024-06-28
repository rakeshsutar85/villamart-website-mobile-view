<?php
/**
 * Handles the Order Item.
 *
 * @package Buy Again/ Order Item.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Order_Page_Item_Handler' ) ) {
	/**
	 * Main Class
	 */
	class BYA_Order_Page_Item_Handler {

		/**
		 * Class Initialization.
		 */
		public static function init() {
			add_action( 'woocommerce_order_item_meta_end', array( __CLASS__, 'order_item' ), 10, 3 );
		}

		/**
		 * Each Order Item Controls.
		 *
		 * @since 1.0.0
		 * @param Integer $item_id Order Item ID.
		 * @param Object  $item Order Item.
		 * @param Object  $order Order Object.
		 */
		public static function order_item( $item_id, $item, $order ) {
			$endpoint                 = WC()->query->get_current_endpoint();
			$order_received_menu_slug = get_option( 'woocommerce_checkout_order_received_endpoint', 'order-received' );
			$view_order_menu_slug     = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );

			if ( empty( $endpoint ) || is_checkout() || ! in_array( $endpoint, array( $order_received_menu_slug, $view_order_menu_slug ), true ) ) {
				return;
			}

			// Buy again enable check.
			$allow_buy_again = bya_allow_buy_again();

			if ( ! $allow_buy_again ) {
				return;
			}

			$user_id = get_current_user_id();

			// User enable check.
			$user_restriction = bya_user_restriction( $user_id );

			if ( ! $user_restriction ) {
				return;
			}

			$order_statuses = get_option( 'bya_general_order_status_to_show', array( 'processing', 'completed' ) );

			if ( ! in_array( $order->get_status(), $order_statuses, true ) ) {
				return;
			}

			$product_id                 = $item->get_product_id();
			$variation_id               = $item->get_variation_id();
			$id_for_product_restriction = ! empty( $variation_id ) ? $variation_id : $product_id;
			$product_restriction        = bya_product_restriction( $id_for_product_restriction );

			if ( ! $product_restriction ) {
				return;
			}

			$product = wc_get_product( $product_id );

			if ( ! is_a( $product, 'WC_Product' ) ) {
				return;
			}

			$quantity_id = ( $variation_id ) ? 'bya_qty_' . $variation_id : 'bya_qty_' . $product_id;
			$args        = array(
				'product_id'              => $product_id,
				'variation_id'            => $variation_id,
				'product_obj'             => $product,
				'quantity_id'             => $quantity_id,
				'cartlink'                => '?add-to-cart=' . $product_id,
				'add_to_cart_class'       => 'bya_add_to_cart_' . $product_id,
				'buy_again_id'            => 'bya_buy_again_' . $product_id,
				'add_to_cart_ajax_enable' => bya_add_to_cart_ajax_enable(),
				'product_url'             => bya_get_product_url( $product_id ),
				'bya_order_id'            => $order->get_id(),
			);

			bya_get_template( 'each-order-item-layout.php', $args );
		}

	}

	BYA_Order_Page_Item_Handler::init();
}
