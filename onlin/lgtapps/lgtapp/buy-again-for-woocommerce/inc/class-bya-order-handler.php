<?php
/**
 * Handles the Order.
 *
 * @package Buy Again/ Order Handler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Order_Handler' ) ) {
	/**
	 * Class BYA_Order_Handler
	 */
	class BYA_Order_Handler {

		/**
		 * Class Initialization.
		 */
		public static function init() {
			// update order meta.
			add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'adjust_order_item' ), 10, 4 );
			// create buy again log.
			add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'create_buy_again_list' ) );
			// Remove Order Item Meta key.
			add_action( 'woocommerce_hidden_order_itemmeta', array( __CLASS__, 'hide_order_item_meta_key' ), 10, 2 );
		}

		/**
		 * Adjust order item meta
		 *
		 * @since 1.0.0
		 * @param WC_Order_Item $item Order Item.
		 * @param String        $cart_item_key Cart item Key.
		 * @param Array         $values Values.
		 * @param WC_Order      $order Order Object.
		 */
		public static function adjust_order_item( $item, $cart_item_key, $values, $order ) {
			if ( ! isset( $values['buy_again_product'] ) ) {
				return;
			}

			$product_ids  = array();
			$product_id   = $item->get_product_id();
			$variation_id = $item->get_variation_id();

			if ( $variation_id ) {
				$product_ids[] = $variation_id;
			} else {
				$product_ids[] = $product_id;
			}

			// update order item meta.
			$item->add_meta_data( '_buy_again_product', 'yes' );
			$item->add_meta_data( '_buy_again_product_ids', $product_ids );

			$custom_metas = bya_get_display_custom_metas();

			if ( bya_check_is_array( $custom_metas ) ) {
				foreach ( $custom_metas as $custom_meta ) {

					if ( ! isset( $values[ $custom_meta ] ) || ! isset( $values[ $custom_meta ][ $custom_meta ] ) ) {
						continue;
					}

					$item->add_meta_data( $custom_meta, wp_strip_all_tags( $values[ $custom_meta ][ $custom_meta ] ) );
				}
			}
		}

		/**
		 * Create Buy Again List
		 *
		 * @since 1.0.0
		 * @param Integer $order_id Order ID.
		 */
		public static function create_buy_again_list( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				return;
			}

			$user_id         = $order->get_meta( '_customer_user', true );
			$product_details = array();
			$bya_parent_id   = false;

			foreach ( $order->get_items() as $key => $value ) {
				if ( ! isset( $value['buy_again_product'] ) ) {
					continue;
				}

				$product_id    = ! empty( $value['variation_id'] ) ? $value['variation_id'] : $value['product_id'];
				$product       = wc_get_product( $product_id );
				$product_price = $value->get_quantity() * $product->get_price();

				// meta data.
				$meta_data = array(
					'bya_product_id'       => $product_id,
					'bya_product_name'     => $product->get_name(),
					'bya_product_quantity' => $value->get_quantity(),
					'bya_product_price'    => $product_price,
					'bya_order_id'         => array( $order_id ),
					'bya_customer_id'      => $user_id,
				);

				$bya_parent_id = bya_is_user_having_buy_again_list( $user_id );

				if ( $bya_parent_id ) {
					$bya_product_id = bya_is_prodcut_already_in_buy_again_list( $user_id, $product_id );

					if ( ! $bya_product_id ) {
						$post_args        = array(
							'post_parent' => $bya_parent_id,
							'post_author' => $user_id,
							'post_status' => 'bya_products',
						);
						$buy_again_log_id = bya_create_new_buy_again_log( $meta_data, $post_args );
					} else {
						$bya_obj     = bya_get_buy_again_log( $bya_product_id );
						$order_ids   = $bya_obj->get_order_id();
						$order_ids[] = $order_id;

						$meta_data = array(
							'bya_product_id'       => $product_id,
							'bya_product_name'     => $product->get_name(),
							'bya_product_quantity' => $value->get_quantity() + $bya_obj->get_product_quantity(),
							'bya_product_price'    => $product_price + $bya_obj->get_product_price(),
							'bya_order_id'         => $order_ids,
							'bya_customer_id'      => $user_id,
						);

						bya_update_buy_again_log( $bya_product_id, $meta_data );
					}
				} else {
					// Parent Post Entry.
					$post_args = array(
						'post_author' => $user_id,
						'post_status' => 'publish',
					);

					$bya_parent_id = bya_create_new_buy_again_log( array(), $post_args );

					// Child Post Entry.
					$post_args ['post_parent'] = $bya_parent_id;
					$post_args ['post_status'] = 'bya_products';

					$buy_again_log_id = bya_create_new_buy_again_log( $meta_data, $post_args );
				}
				// Update Parent Post Count.
				$product_quantity = $value->get_quantity();
				$product_price    = $product_quantity * $product->get_price();
				$bya_data         = bya_get_buy_again_log( $bya_parent_id );

				bya_update_buy_again_log(
					$bya_parent_id,
					array(
						'bya_total_products' => ( $bya_data->get_total_products() + 1 ),
						'bya_total_earnings' => ( $bya_data->get_total_earnings() + $product_price ),
						'bya_activity_date'  => current_time( 'mysql', true ),
					)
				);
			}

			if ( $bya_parent_id ) {
				// Update Parent Post Count.
				$bya_data = bya_get_buy_again_log( $bya_parent_id );

				bya_update_buy_again_log(
					$bya_parent_id,
					array(
						'bya_total_orders'  => ( $bya_data->get_total_orders() + 1 ),
						'bya_activity_date' => current_time( 'mysql', true ),
					)
				);
			}
		}

		/**
		 * Hidden Custom Order item meta
		 *
		 * @since 1.0.0
		 * @param Array $hidden_order_itemmeta Hidden order item meta.
		 */
		public static function hide_order_item_meta_key( $hidden_order_itemmeta ) {
			$custom_order_itemmeta = array( '_buy_again_product' );

			return array_merge( $hidden_order_itemmeta, $custom_order_itemmeta );
		}

	}

	BYA_Order_Handler::init();
}
