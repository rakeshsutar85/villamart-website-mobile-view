<?php
/**
 * Buy Again Page Class.
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Page' ) ) {

	/**
	 * BYA_Page Class.
	 * */
	class BYA_Page {

		/**
		 * Plugin slug.
		 *
		 * @var string
		 * */
		private static $plugin_slug = 'bya';

		/**
		 * Class initialization.
		 * */
		public static function init() {
			// Render User Details content.
			add_action( 'bya_product_details_content', array( __CLASS__, 'render_view_product_details' ) );
			// Render Product Details content.
			add_action( 'bya_order_details_content', array( __CLASS__, 'render_view_order_details' ) );
		}

		/**
		 * Output Auction Page.
		 * */
		public static function output() {
			global $product_id, $bya_post_id, $bya_obj, $bya_product_id, $current_user_id, $current_action;

			$current_action  = ( isset( $_REQUEST['page'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['page'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['page'] ) ) : '';
			$current_user_id = ( isset( $_REQUEST['user_id'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['user_id'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['user_id'] ) ) : '';
			$bya_post_id     = ( isset( $_REQUEST['post'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['post'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['post'] ) ) : '';
			$bya_product_id  = ( isset( $_REQUEST['bya_product_id'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['bya_product_id'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['bya_product_id'] ) ) : '';
			$bya_obj         = ( ! empty( $bya_post_id ) ) ? bya_get_buy_again_log( $bya_post_id ) : '';
			$product_id      = ( isset( $_REQUEST['product_id'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['product_id'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['product_id'] ) ) : '';

			switch ( $current_action ) {
				case 'bya_product_details':
					self::render_view_product_details();
					break;
				case 'bya_order_details':
					self::render_view_order_details();
					break;
			}
		}

		/**
		 * View Product Details
		 * */
		public static function render_view_product_details() {
			global $bya_post_id, $bya_obj, $bya_product_id, $current_user_id, $current_action;

			if ( empty( $current_user_id ) ) {
				return;
			}

			$user      = get_userdata( $current_user_id );
			$user_name = $user->data->display_name . '(' . $user->user_email . ')';

			if ( ! class_exists( 'bya_product_details_List_Table' ) ) {
				require_once BYA_PLUGIN_PATH . '/inc/admin/menu/wp-list-table/class-bya-product-details-list-table.php';
			}

			$post_table = new BYA_Product_Details_List_Table();
			$post_table->prepare_items();

			// Html for view product details
			include_once BYA_PLUGIN_PATH . '/inc/admin/menu/views/html-view-product-details.php';
		}

		/**
		 * View order Details
		 * */
		public static function render_view_order_details() {
			global $product_id, $bya_post_id, $bya_obj, $bya_product_id, $current_user_id, $current_action;

			if ( empty( $current_user_id ) ) {
				return;
			}

			$user         = get_userdata( $current_user_id );
			$user_name    = $user->data->display_name . '(' . $user->user_email . ')';
			$product_name = bya_get_product_title( $product_id );

			if ( ! class_exists( 'BYA_order_details_List_Table' ) ) {
				require_once BYA_PLUGIN_PATH . '/inc/admin/menu/wp-list-table/class-bya-order-details-list-table.php';
			}

			$post_table = new BYA_Order_Details_List_Table();
			$post_table->prepare_items();

			// Html for view auction page.
			include_once BYA_PLUGIN_PATH . '/inc/admin/menu/views/html-view-order-details.php';
		}

	}

}
