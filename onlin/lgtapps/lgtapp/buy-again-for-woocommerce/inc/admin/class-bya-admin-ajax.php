<?php
/**
 * Admin Ajax
 *
 * @package Class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'BYA_Admin_Ajax' ) ) {

	/**
	 * Main Class
	 */
	class BYA_Admin_Ajax {

		/**
		 * Class initialization
		 */
		public static function init() {
			$actions = array(
				'json_search_products_and_variations' => false,
				'json_search_products'                => false,
				'json_search_user'                    => false,
				'add_to_cart_product'                 => false,
				'buy_again_product'                   => false,
				'products_pagination'                 => false,
			);

			foreach ( $actions as $action => $nopriv ) {
				add_action( 'wp_ajax_bya_' . $action, array( __CLASS__, $action ) );

				if ( $nopriv ) {
					add_action( 'wp_ajax_nopriv_bya_' . $action, array( __CLASS__, $action ) );
				}
			}
		}

		/**
		 * Search for products
		 *
		 * @since 1.0
		 * @param String  $term term to search.
		 * @param Boolean $include_variations Include Variations.
		 * @throws exception Invalid Data.
		 */
		public static function json_search_products( $term = '', $include_variations = false ) {
			// check_ajax_referer( 'bya-search-nonce' , 'bya_security' ) ;

			try {
				if ( empty( $term ) && isset( $_GET['term'] ) ) {
					$term = isset( $_GET['term'] ) ? wc_clean( wp_unslash( $_GET['term'] ) ) : '';
				}

				if ( empty( $term ) ) {
					throw new exception( esc_html__( 'No Products found', 'buy-again-for-woocommerce' ) );
				}

				if ( ! empty( $_GET['limit'] ) ) {
					$limit = absint( $_GET['limit'] );
				} else {
					/**
					 * Filter to Woocommerce Json Search Limit.
					 *
					 * @since 1.0
					 * */
					$limit = absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );
				}

				$data_store = WC_Data_Store::load( 'product' );
				$ids        = $data_store->search_products( $term, '', (bool) $include_variations );

				$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
				$products        = array();

				$exclude_global_variable = isset($_GET['exclude_global_variable']) ? wc_clean(wp_unslash($_GET['exclude_global_variable'])) : 'no'; // @codingStandardsIgnoreLine.
				foreach ( $product_objects as $product_object ) {
					if ( 'yes' === $exclude_global_variable && $product_object->is_type( 'variable' ) ) {
						continue;
					}

					$products[ $product_object->get_id() ] = rawurldecode( $product_object->get_formatted_name() );
				}

				wp_send_json( $products );
			} catch ( Exception $ex ) {
				wp_die();
			}
		}

		/**
		 * Search for product variations
		 *
		 * @since 1.0
		 * @param String  $term term to search.
		 * @param Boolean $include_variations Include Variations.
		 */
		public static function json_search_products_and_variations( $term = '', $include_variations = false ) {
			self::json_search_products( '', true );
		}

		/**
		 * Customers search
		 *
		 * @since 1.0
		 * @throws exception Invalid Data.
		 */
		public static function json_search_user() {
			check_ajax_referer( 'bya-search-nonce', 'bya_security' );

			try {
				$term = isset($_GET['term']) ? wc_clean(wp_unslash($_GET['term'])) : ''; // @codingStandardsIgnoreLine.

				if ( empty( $term ) ) {
					throw new exception( esc_html__( 'No Users found', 'buy-again-for-woocommerce' ) );
				}

				$exclude = isset($_GET['exclude']) ? wc_clean(wp_unslash($_GET['exclude'])) : ''; // @codingStandardsIgnoreLine.
				$exclude = ! empty( $exclude ) ? array_map( 'intval', explode( ',', $exclude ) ) : array();

				$found_customers = array();
				$customers_query = new WP_User_Query(
					array(
						'fields'         => 'all',
						'orderby'        => 'display_name',
						'search'         => '*' . $term . '*',
						'search_columns' => array( 'ID', 'user_login', 'user_email', 'user_nicename' ),
					)
				);
				$customers       = $customers_query->get_results();

				if ( bya_check_is_array( $customers ) ) {
					foreach ( $customers as $customer ) {
						if ( ! in_array( $customer->ID, $exclude ) ) {
							$found_customers[ $customer->ID ] = $customer->display_name . ' (#' . $customer->ID . ' &ndash; ' . sanitize_email( $customer->user_email ) . ')';
						}
					}
				}

				wp_send_json( $found_customers );
			} catch ( Exception $ex ) {
				wp_die();
			}
		}

		/**
		 * Add to cart
		 *
		 * @since 1.0
		 * @throws exception Invalid Data.
		 */
		public static function add_to_cart_product() {
			check_ajax_referer( 'bya-add-to-cart-nonce', 'bya_security' );

			try {
				ob_start();

				if ( ! isset( $_POST ) || ! isset( $_POST['product_id'] ) || ! isset( $_POST['qty'] ) || empty( absint( $_POST['product_id'] ) ) || empty( absint( $_POST['qty'] ) ) ) {
					throw new exception( esc_html__( 'Invalid Request', 'buy-again-for-woocommerce' ) );
				}

				$user_id        = get_current_user_id();
				$product_id     = absint( $_POST['product_id'] );
				$variation_id   = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
				$quantity       = absint( $_POST['qty'] );
				$product_status = get_post_status( $product_id );

				/**
				 * Filter to add to cart validation.
				 *
				 * @since 1.0
				 * */
				$passed_validation                   = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
				$order_id                            = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : '';
				$cart_item_data                      = bya_prepare_cart_item_data( $order_id, array(), $product_id, $variation_id );
				$cart_item_data['buy_again_product'] = array( 'bya_product' );
				$cart_item_attr                      = bya_prepare_cart_item_attr( $order_id, array(), $product_id, $variation_id );

				if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $cart_item_attr, $cart_item_data ) && 'publish' === $product_status ) {
					/**
					 * Action hook to adjust ajax add to cart product.
					 *
					 * @since 1.0
					 * @param Integer $product_id.
					 */
					do_action( 'woocommerce_ajax_added_to_cart', $product_id );

					if ( 'no' === get_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' ) || 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
						$display_product = empty( $variation_id ) ? $product_id : $variation_id;
						wc_add_to_cart_message( array( $display_product => $quantity ), true );
					}

					WC_AJAX::get_refreshed_fragments();
				} else {
					$data = array( 'error' => true );
					wp_send_json_error( $data );
				}
			} catch ( Exception $e ) {
				return new WP_Error( 'add_to_cart_error', $e->getMessage(), array( 'status' => 500 ) );
			}
		}

		/**
		 * Buy again Product
		 *
		 * @since 1.0
		 * @throws exception Invalid Data.
		 */
		public static function buy_again_product() {
			check_ajax_referer( 'bya-buy-again-nonce', 'bya_security' );

			try {
				if ( ! isset( $_POST ) || ! isset( $_POST['product_id'] ) || ! isset( $_POST['qty'] ) || empty( absint( $_POST['product_id'] ) ) || empty( absint( $_POST['qty'] ) ) ) {
					throw new exception( esc_html__( 'Invalid Request', 'buy-again-for-woocommerce' ) );
				}

				$order_id       = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : '';
				$product_id     = absint( $_POST['product_id'] );
				$variation_id   = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : '';
				$cart_item_data = bya_prepare_cart_item_data( $order_id, array(), $product_id, $variation_id );
				$cart_item_attr = bya_prepare_cart_item_attr( $order_id, array(), $product_id, $variation_id );
				$quantity       = absint( $_POST['qty'] );
				$user_id        = get_current_user_id();

				bya_add_to_cart( $product_id, $quantity, $variation_id, $cart_item_attr, $cart_item_data );

				wp_send_json_success( array( 'page_url' => wc_get_checkout_url() ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) );
			}
		}

		/**
		 * Display Buy Again Products based on pagination
		 *
		 * @since 1.0
		 * @throws exception Invalid Data.
		 */
		public static function products_pagination() {
			check_ajax_referer( 'bya-product-pagination-nonce', 'bya_security' );

			try {
				if (!isset($_POST) || !isset($_POST['page_number'])) { // @codingStandardsIgnoreLine.
					throw new exception( esc_html__( 'Invalid Request', 'buy-again-for-woocommerce' ) );
				}

				// Sanitize post values.
				$current_page = !empty($_POST['page_number']) ? absint($_POST['page_number']) : 0; // @codingStandardsIgnoreLine.
				$page_url = !empty($_POST['page_url']) ? wc_clean(wp_unslash($_POST['page_url'])) : ''; // @codingStandardsIgnoreLine.
				$per_page     = bya_get_buy_again_product_per_page_count();
				$offset       = ( $current_page - 1 ) * $per_page;
				$user_id      = get_current_user_id();
				$term         = isset( $_POST['inp'] ) ? wc_clean( wp_unslash( $_POST['inp'] ) ) : '';

				if ( empty( $term ) ) {
					$product_ids = bya_get_product_ids_from_user_orders( $user_id );
				} else {
					global $wpdb;
					$user_id               = get_current_user_id();
					$bya_product_ids       = bya_get_product_ids_from_user_orders( $user_id );
					$buy_again_product_ids = bya_check_is_array( $bya_product_ids ) ? ( array_keys( $bya_product_ids ) ) : $bya_product_ids;
					$post_query            = new BYA_Query( $wpdb->prefix . 'posts', 'p' );
					$post_query->select( 'DISTINCT `p`.ID' )
							->whereIn( '`p`.post_type', array( 'product_variation', 'product' ) )
							->where( '`p`.post_status', 'publish' )
							->whereIn( '`p`.ID', $buy_again_product_ids )
							->whereLike( '`p`.post_title', '%' . $term . '%' );

					$product_ids  = $post_query->fetchCol( 'ID' );
					$selected_ids = array();

					if ( bya_check_is_array( $product_ids ) ) {
						foreach ( $product_ids as $product_id ) {
							if ( isset( $bya_product_ids[ $product_id ] ) ) {
								$selected_ids[ $product_id ] = $bya_product_ids[ $product_id ];
							}
						}
					}

					$product_ids = $selected_ids;
				}

				$product_ids = bya_sort_product_ids( $product_ids );
				$product_ids = bya_check_is_array( $product_ids ) ? array_slice( $product_ids, $offset, $per_page ) : '';
				// Get buy again products table body content.
				$html = bya_get_template_html(
					'myaccount-buy-again-table.php',
					array(
						'user_id'     => $user_id,
						'product_ids' => $product_ids,
					)
				);

				wp_send_json_success( array( 'html' => $html ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( array( 'error' => $ex->getMessage() ) );
			}
		}
	}

	BYA_Admin_Ajax::init();
}
