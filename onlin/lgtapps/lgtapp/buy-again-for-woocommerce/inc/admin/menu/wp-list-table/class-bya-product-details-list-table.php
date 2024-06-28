<?php

/**
 * User Details List Table.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'BYA_Product_Details_List_Table' ) ) {

	/**
	 * BYA_Product_Details_List_Table Class.
	 * */
	class BYA_Product_Details_List_Table extends WP_List_Table {

		/**
		 * Total Count of Table
		 * */
		private $total_items;

		/**
		 * Per page count
		 * */
		private $perpage;

		/**
		 * Database
		 * */
		private $database;

		/**
		 * Offset
		 * */
		private $offset;

		/**
		 * Order BY
		 * */
		private $orderby = 'ID';

		/**
		 * Order.
		 * */
		private $order = 'DESC';

		/**
		 * Base URL
		 * */
		private $base_url;

		/**
		 * Current URL
		 * */
		private $current_url;

		/**
		 * Current URL
		 * */
		private $product_post_ids;

		/**
		 * Prepare the table Data to display table based on pagination.
		 * */
		public function prepare_items() {
			global $wpdb;
			$this->database = $wpdb;

			$this->base_url = add_query_arg( array( 'post_type' => 'buy_again_list' ), admin_url( 'edit.php' ) );

			$this->prepare_bya_post_ids();
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
				'product_name' => esc_html__( 'Product Name', 'buy-again-for-woocommerce' ),
				'total_orders' => esc_html__( 'No.Of Orders', 'buy-again-for-woocommerce' ),
				'quantity'     => esc_html__( 'Total Quantity Purchased', 'buy-again-for-woocommerce' ),
				'total_amount' => esc_html__( 'Total Amount Spent for this Product through Buy Again', 'buy-again-for-woocommerce' ),
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
		protected function column_default( $bya_obj, $column_name ) {
			global $bya_post_id, $current_user_id;

			switch ( $column_name ) {
				case 'product_name':
					$product_id = $bya_obj->get_product_id();
					$product_obj    = wc_get_product( $product_id );

					if ( ! is_object( $product_obj ) ) {
						/* translators: %s: number of product id */
						$views = sprintf( esc_html__( '#%s Product was deleted', 'buy-again-for-woocommerce' ), absint( $product_id ) );
					} else {
						global $bya_post_id, $current_user_id;
						$actions = array();
						$views   = '<a class="row-title" href="' . esc_url( get_edit_post_link( $product_id ) ) . '">' . esc_html( $product_obj->get_name() ) . '</a> <br/> <div class="row-actions">';
						/* translators: %s: Campaign ID */
						$actions ['id']           = sprintf( esc_html__( 'ID: %s', 'buy-again-for-woocommerce' ), $bya_obj->get_id() );
						$url_args = array(
							'page'       => 'bya_order_details',
							'post'       => $bya_post_id,
							'user_id'    => $current_user_id,
							'product_id' => $bya_obj->get_product_id(),
							'bya_product_id' => $bya_obj->get_id()
						);

						$url = add_query_arg( $url_args, admin_url( 'admin.php' ) );

						$actions ['view_more'] = '<a href=' . esc_url( $url ) . '>' . esc_html__( 'View More', 'buy-again-for-woocommerce' ) . '</a>';
						end( $actions );

						$last_key = key( $actions );

						foreach ( $actions as $key => $action ) {
							$views .= '<span class="' . $key . '">' . $action;

							if ( $last_key == $key ) {
								$views .= '</span>';
								break;
							}

							$views .= ' | </span>';
						}

						$views = $views . '</div>';
					}

					return $views;
					break;

				case 'total_orders':
					echo esc_html( count( $bya_obj->get_order_id() ) );
					break;

				case 'quantity':
					echo esc_html( $bya_obj->get_product_quantity() );
					break;

				case 'total_amount':
					bya_price( $bya_obj->get_product_price() );
					break;
			}
		}

		/**
		 * Initialize the columns
		 * */
		private function get_current_page_items() {
			if ( isset( $_GET['post'] ) && isset( $_GET['user_id'] ) ) {
				$user_id             = absint( $_GET['user_id'] );
				$bya_post_id         = absint( $_GET['post'] );
				$bya_product_post_id = array();

				$args = array(
					'author'         => $user_id,
					'parent'         => $bya_post_id,
					'post_status'    => 'bya_products',
					'post_type'      => BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE,
					'fields'         => 'ids',
					'posts_per_page' => '-1',
					'post__in'       => $this->product_post_ids,
				);

				$count_items       = get_posts( $args );
				$this->total_items = count( $count_items );

				$args['orderby']        = isset( $_GET['orderby'] ) ? sanitize_title( wp_unslash( $_GET['orderby'] ) ) : $this->orderby;
				$args['order']          = isset( $_GET['order'] ) ? sanitize_title( wp_unslash( $_GET['order'] ) ) : $this->order;
				$args['posts_per_page'] = $this->perpage;
				$args['offset']         = $this->offset;
				$search_term            = ( isset( $_REQUEST['s'] ) && strlen( wc_clean( wp_unslash( $_REQUEST['s'] ) ) ) ) ? wc_clean( wp_unslash( $_REQUEST['s'] ) ) : '';

				if ( $search_term ) {
					$args['s'] = $search_term;
				}

				// get product post ids.
				$items = get_posts( $args );
				$this->prepare_item_object( $items );
			}
		}

		/**
		 * Prepare item Object
		 * */
		private function prepare_item_object( $items ) {
			$prepare_items = array();
			if ( bya_check_is_array( $items ) ) {
				foreach ( $items as $item_id ) {
					$prepare_items[] = bya_get_buy_again_log( $item_id );
				}
			}

			$this->items = $prepare_items;
		}

		/**
		 * Prepare Auction IDs.
		 * */
		private function prepare_bya_post_ids() {
			if ( isset( $_GET['post'] ) && isset( $_GET['user_id'] ) ) {
				$user_id     = absint( $_GET['user_id'] );
				$bya_post_id = absint( $_GET['post'] );

				// get product post ids
				$this->product_post_ids = bya_bya_product_post_id( $user_id, $bya_post_id );
			}
			return $this->product_post_ids;
		}

	}

}
