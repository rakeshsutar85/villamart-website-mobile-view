<?php
/**
 * User Details List Table.
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'BYA_Order_Details_List_Table' ) ) {

	/**
	 * BYA_Order_Details_List_Table Class.
	 * */
	class BYA_Order_Details_List_Table extends WP_List_Table {

		/**
		 * Total Count of Table
		 *
		 * @var $total_items
		 * */
		private $total_items;

		/**
		 * Per page count
		 *
		 * @var $perpage
		 * */
		private $perpage;

		/**
		 * Database
		 *
		 * @var $database
		 * */
		private $database;

		/**
		 * Offset
		 *
		 * @var $offset
		 * */
		private $offset;

		/**
		 * Order BY
		 *
		 * @var $orderby
		 * */
		private $orderby = 'ID';

		/**
		 * Order
		 *
		 * @var $order
		 * */
		private $order = 'DESC';

		/**
		 * Base URL
		 *
		 * @var $base_url
		 * */
		private $base_url;

		/**
		 * Current URL
		 *
		 * @var $current_url
		 * */
		private $current_url;

		/**
		 * Current URL
		 *
		 * @var $order_post_ids
		 * */
		private $order_post_ids;

		/**
		 * Prepare the table Data to display table based on pagination.
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;
			$this->base_url = add_query_arg( array( 'post_type' => 'buy_again_list' ), admin_url( 'edit.php' ) );
			$this->prepare_current_url();
			$this->get_perpage_count();
			$this->get_current_pagenum();
			$this->get_current_page_items();
			$this->prepare_pagination_args();
			$this->prepare_column_headers();
		}

		/**
		 * Get per page count
		 * */
		private function get_perpage_count() {
			$this->perpage = 10;
		}

		/**
		 * Prepare pagination
		 * */
		private function prepare_pagination_args() {
			$this->set_pagination_args(
				array(
					'total_items' => $this->total_items,
					'per_page'    => $this->perpage,
				)
			);
		}

		/**
		 * Get current page number
		 * */
		private function get_current_pagenum() {
			$this->offset = 10 * ( $this->get_pagenum() - 1 );
		}

		/**
		 * Prepare header columns
		 * */
		private function prepare_column_headers() {
			$columns  = $this->get_columns();
			$hidden   = $this->get_hidden_columns();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
		}

		/**
		 * Initialize the columns.
		 * */
		public function get_columns() {
			$columns = array(
				'order_id'      => esc_html__( 'Order ID', 'buy-again-for-woocommerce' ),
				'product_price' => esc_html__( 'Product Price', 'buy-again-for-woocommerce' ),
				'quantity'      => esc_html__( 'Quantity', 'buy-again-for-woocommerce' ),
				'date'          => esc_html__( 'Date', 'buy-again-for-woocommerce' ),
			);

			return $columns;
		}

		/**
		 * Initialize the hidden columns
		 * */
		protected function get_hidden_columns() {
			return array();
		}

		/**
		 * Initialize the sortable columns
		 * */
		protected function get_sortable_columns() {
			return array();
		}

		/**
		 * Get current url
		 * */
		private function prepare_current_url() {
			$pagenum       = $this->get_pagenum();
			$args['paged'] = $pagenum;
			$url           = add_query_arg( $args, $this->base_url );

			$this->current_url = $url;
		}

		/**
		 * Prepare each column data.
		 * */
		protected function column_default( $bya_order, $column_name ) {
			global $product_id;

			if ( is_object( $bya_order ) ) {
				foreach ( $bya_order->get_items() as $item_id => $item_data ) {
					$product = $item_data->get_product();

					if ( $product_id == $product->get_id() ) {
						$quantity = $item_data->get_quantity();
						$price    = $bya_order->get_item_total( $item_data, false, true );
					}
				}
			}

			switch ( $column_name ) {
				case 'order_id':
					/* translators: %s: Order ID */
					$label = sprintf( esc_html__( 'Order #%s', 'buy-again-for-woocommerce' ), esc_attr( $bya_order->get_id() ) );
					$url   = $bya_order->get_edit_order_url();

					return '<a class="row-title" href="' . esc_url( $url ) . '">' . esc_attr( $label ) . '</a>';
					break;

				case 'product_price':
					bya_price( $price );
					break;

				case 'quantity':
					echo esc_attr($quantity);
					break;

				case 'date':
					echo esc_html( $bya_order->get_date_created()->format( BYA_Date_Time::get_wp_datetime_format() ) );
					break;
			}
		}

		/**
		 * Initialize the columns
		 * */
		private function get_current_page_items() {
			global $product_id, $bya_post_id, $bya_obj, $bya_product_id, $current_user_id, $current_action;

			$current_user_id   = ( isset( $_REQUEST['user_id'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['user_id'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['user_id'] ) ) : '';
			$bya_post_id       = ( isset( $_REQUEST['post'] ) && ! empty( wc_clean( wp_unslash( $_REQUEST['post'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['post'] ) ) : '';
			$items             = bya_get_order_ids_from_product_id( $product_id );
			$this->total_items = count( $items );

			$from  = $this->offset;
			$to    = ( $this->total_items > ( $this->get_pagenum() * $this->perpage ) ) ? ( $this->get_pagenum() * $this->perpage ) : $this->total_items;
			$items = array_slice( $items, $from, $to );

			$this->prepare_item_object( $items );
		}

		/**
		 * Prepare item Object
		 * */
		private function prepare_item_object( $items ) {
			$prepare_items = array();
			if ( bya_check_is_array( $items ) ) {
				foreach ( $items as $item_id ) {
					$prepare_items[] = wc_get_order( $item_id );
				}
			}

			$this->items = $prepare_items;
		}
	}
}
