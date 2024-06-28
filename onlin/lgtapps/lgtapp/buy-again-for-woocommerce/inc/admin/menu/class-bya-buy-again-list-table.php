<?php
/**
 * Admin Cart List Custom Post Type.
 *
 * @package Buy Again/ List Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Buy_Again_List_Table' ) ) {

	/**
	 * BYA_Buy_Again_List_Table Class.
	 */
	class BYA_Buy_Again_List_Table {
		/**
		 * Object
		 */
		private static $object;

		/**
		 * Post type
		 */
		private static $post_type = BYA_Register_Post_Types::BUY_AGAIN_LIST_POSTTYPE;

		/**
		 * BYA_Buy_Again_List_Table Class initialization.
		 */
		public static function init() {
			// post row action.
			add_filter( 'post_row_actions', array( __CLASS__, 'handle_post_row_actions' ), 10, 2 );
			// Add custom column.
			add_filter( 'manage_' . self::$post_type . '_posts_columns', array( __CLASS__, 'custom_columns' ) );
			// display column value.
			add_action( 'manage_' . self::$post_type . '_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 );
			// bulk action.
			add_filter( 'bulk_actions-edit-' . self::$post_type, array( __CLASS__, 'remove_bulk_action' ) );
			// remove post type views.
			add_action( 'views_edit-' . self::$post_type, array( __CLASS__, 'remove_post_type_views' ) );
			// remove month filter.
			add_filter( 'months_dropdown_results', array( __CLASS__, 'remove_bulk_action' ) );
			// search action.
			add_filter( 'posts_search', array( __CLASS__, 'search_action' ) );
		}

		/**
		 * Initialization of columns
		 */
		public static function custom_columns( $columns ) {
			$columns = array(
				'bya_user_name'      => esc_html__( 'Username', 'buy-again-for-woocommerce' ),
				'bya_no_of_orders'   => esc_html__( 'Orders Placed using Buy Again', 'buy-again-for-woocommerce' ),
				'bya_no_of_products' => esc_html__( 'Products Purchased using Buy Again', 'buy-again-for-woocommerce' ),
				'bya_total_amount'   => esc_html__( 'Amount Spent Using Buy Again', 'buy-again-for-woocommerce' ),
			);

			return $columns;
		}

		/**
		 * Handle Row Actions
		 **/
		public static function handle_post_row_actions( $actions, $post ) {
			if ( $post->post_type === self::$post_type ) {
				return array();
			}

			return $actions;
		}

		/**
		 * Remove bulk action
		 */
		public static function remove_bulk_action( $columns ) {
			return array();
		}

		/**
		 * Remove Custom Post Type Views
		 */
		public static function remove_post_type_views( $views ) {
			unset( $views['mine'] );
			unset( $views['publish'] );

			return $views;
		}

		/**
		 * Search Action
		 */
		public static function search_action( $where ) {
			global $wpdb, $wp_query;

			if ( ! is_search() || ! isset( $_REQUEST['s'] ) || $wp_query->query_vars['post_type'] != self::$post_type ) {
				return $where;
			}

			$search_ids = array();
			$terms      = explode( ',', sanitize_title( wp_unslash( $_REQUEST['s'] ) ) );

			foreach ( $terms as $term ) {
				$term       = $wpdb->esc_like( wc_clean( $term ) );
				$post_query = new BYA_Query( $wpdb->posts, 'p' );
				$post_query->select( 'DISTINCT `p`.post_author' )
						->where( '`p`.post_type', self::$post_type )
						->where( '`p`.post_status', 'publish' );

				$search_ids = $post_query->fetchCol( 'post_author' );

				$post_query = new BYA_Query( $wpdb->posts, 'p' );
				$post_query->select( '`um`.user_id' )
						->leftJoin( $wpdb->users, 'u', '`u`.ID = `p`.ID' )
						->leftJoin( $wpdb->usermeta, 'um', '`p`.ID = `um`.user_id' )
						->whereIn( '`u`.ID', $search_ids )
						->whereIn( '`um`.meta_key', array( 'first_name', 'last_name', 'billing_email', 'nickname' ) )
						->wherelike( '`um`.meta_value', '%' . $term . '%' )
						->orderBy( '`p`.ID' );

				$search_ids = $post_query->fetchCol( 'user_id' );
			}
			$search_ids = array_filter( array_unique( $search_ids ) );

			if ( count( $search_ids ) > 0 ) {
				$where = str_replace( 'AND (((', "AND ( ({$wpdb->posts}.post_author IN (" . implode( ',', $search_ids ) . ')) OR ((', $where );
			}

			return $where;
		}

		/**
		 * Render each column
		 */
		public static function render_columns( $column, $postid ) {
			self::prepare_row_data( $postid );
			$function = 'render_' . $column . '_cloumn';

			if ( method_exists( __CLASS__, $function ) ) {
				self::$function();
			}
		}

		/**
		 * Remove views
		 */
		public static function prepare_row_data( $postid ) {
			if ( empty( self::$object ) || self::$object->get_id() != $postid ) {
				self::$object = bya_get_buy_again_log( $postid );
			}

			return self::$object;
		}

		/**
		 * Render User name column
		 */
		public static function render_bya_user_name_cloumn() {
			$url_args = array(
				'page'    => 'bya_product_details',
				'post'    => self::$object->get_id(),
				'user_id' => self::$object->get_user_id(),
			);

			$url       = add_query_arg( $url_args, admin_url( 'admin.php' ) );
			$user_name = self::$object->get_user()->display_name . ' (' . self::$object->get_user()->user_email . ')';
			$views     = '<a class="row-title" href="' . esc_url( $url ) . '">' . esc_attr( $user_name ) . '</a> <br/> <div class="row-actions">';
			/* translators: %s: Campaign ID */
			$actions ['id']       = sprintf( esc_html__( 'ID: %s', 'buy-again-for-woocommerce' ), self::$object->get_id() );
			$actions['view_more'] = '<a href=' . esc_url( $url ) . '>' . esc_html__( 'View More', 'buy-again-for-woocommerce' ) . '</a>';
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

			echo wp_kses_post( $views ) . '</div>';
		}

		/**
		 * Render total no of orders column
		 */
		public static function render_bya_no_of_orders_cloumn() {
			echo esc_html( self::$object->get_total_orders() );
		}

		/**
		 * Render total no of products column
		 */
		public static function render_bya_no_of_products_cloumn() {
			echo esc_html( self::$object->get_total_products() );
		}

		/**
		 * Render total amount column
		 */
		public static function render_bya_total_amount_cloumn() {
			bya_price( self::$object->get_total_earnings() );
		}
	}

	BYA_Buy_Again_List_Table::init();
}
