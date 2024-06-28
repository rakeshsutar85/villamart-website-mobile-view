<?php
/**
 * Common functions
 *
 * @package Buy Again\Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'bya-layout-functions.php';
require_once 'bya-post-functions.php';

if ( ! function_exists( 'bya_check_is_array' ) ) {

	/**
	 * Function to check given a variable is array and not empty
	 *
	 * @since 1.0
	 * @param Array $args Array.
	 * @return boolean
	 */
	function bya_check_is_array( $args ) {
		return ( is_array( $args ) && ! empty( $args ) ) ? true : false;
	}
}

if ( ! function_exists( 'bya_get_settings_page_url' ) ) {

	/**
	 * Get Settings page URL
	 *
	 * @since 1.0
	 * @param Array $args URL Arguments.
	 * @return Array
	 */
	function bya_get_settings_page_url( $args = array() ) {

		$url = add_query_arg( array( 'page' => 'bya_settings' ), admin_url( 'admin.php' ) );

		if ( bya_check_is_array( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}
}

if ( ! function_exists( 'bya_get_allowed_setting_tabs' ) ) {

	/**
	 * Get setting tabs
	 *
	 * @since 1.0
	 * @return array
	 */
	function bya_get_allowed_setting_tabs() {
		/**
		 * Filter about Settings tab.
		 *
		 * @since 1.0
		 * @return Array.
		 */
		return apply_filters( 'bya_settings_tabs_array', array() );
	}
}

if ( ! function_exists( 'bya_page_screen_ids' ) ) {

	/**
	 * Get page screen IDs
	 *
	 * @since 1.0
	 * @return array
	 */
	function bya_page_screen_ids() {
		$args = array( 'buy-again_page_bya_settings' );

		/**
		 * Filter to Buy Again Page ID.
		 *
		 * @since 1.0
		 * @param Array $args Page Arguments.
		 * @return Array.
		 */
		return apply_filters( 'bya_page_screen_ids', $args );
	}
}

if ( ! function_exists( 'bya_get_order_statuses' ) ) {

	/**
	 * Get to order status
	 *
	 * @since 1.0
	 * @return array
	 * */
	function bya_get_order_statuses() {
		$order_statuses = array();

		if ( function_exists( 'wc_get_order_statuses' ) ) {
			$wc_order_statuses = wc_get_order_statuses();
			$orderstatuses     = str_replace( 'wc-', '', array_keys( $wc_order_statuses ) );
			$orderslugs        = array_values( $wc_order_statuses );
			$order_statuses    = array_combine( (array) $orderstatuses, (array) $orderslugs );
		}

		return $order_statuses;
	}
}

if ( ! function_exists( 'bya_get_categories' ) ) {

	/**
	 * Get categories
	 *
	 * @since 1.0
	 * @return array
	 * */
	function bya_get_categories() {
		$categorylist = array();
		$categories   = get_terms( 'product_cat' );

		if ( is_wp_error( $categories ) || ! bya_check_is_array( $categories ) ) {
			return $categorylist;
		}

		foreach ( $categories as $category ) {
			$categorylist[ $category->term_id ] = $category->name;
		}

		return $categorylist;
	}
}

if ( ! function_exists( 'bya_get_user_roles' ) ) {

	/**
	 * Get User Roles
	 *
	 * @since 1.0
	 * @return array
	 * */
	function bya_get_user_roles() {
		global $wp_roles;
		$user_roles = array();

		if ( ! bya_check_is_array( $wp_roles->roles ) ) {
			return $user_roles;
		}

		foreach ( $wp_roles->roles as $slug => $role ) {
			$user_roles[ $slug ] = $role['name'];
		}

		return $user_roles;
	}
}

if ( ! function_exists( 'bya_format_woo_order_status' ) ) {

	/**
	 * Get woocommerce order status
	 *
	 * @since 1.0
	 * @param Array $order_statuses Order statuses.
	 * @return array
	 * */
	function bya_format_woo_order_status( $order_statuses ) {
		if ( ! bya_check_is_array( $order_statuses ) ) {
			return;
		}

		return preg_filter( '/^/', 'wc-', $order_statuses );
	}
}

if ( ! function_exists( 'bya_allow_buy_again' ) ) {

	/**
	 * Get buy again status
	 *
	 * @since 1.0
	 * @return bool
	 * */
	function bya_allow_buy_again() {
		return ( 'yes' !== get_option( 'bya_general_enable_buy_again', 'no' ) ) ? false : true;
	}
}

if ( ! function_exists( 'bya_user_restriction' ) ) {

	/**
	 * Get buy again user restriction status
	 *
	 * @since 1.0
	 * @param Integer $user_id User ID.
	 * @return bool
	 * */
	function bya_user_restriction( $user_id ) {
		if ( empty( $user_id ) ) {
			return false;
		}

		$user      = new WP_User( $user_id );
		$user_role = '';

		if ( ! empty( $user->roles ) && bya_check_is_array( $user->roles ) ) {
			foreach ( $user->roles as $role ) {
				$user_role = $role;
			}
		}

		$user_option = get_option( 'bya_advanced_allow_users', '1' );

		if ( '2' === $user_option ) {
			$include_user = get_option( 'bya_advanced_include_user', array() );
			return ( in_array( $user_id, $include_user ) ) ? true : false;
		} elseif ( '3' === $user_option ) {
			$exclude_user = get_option( 'bya_advanced_exclude_user', array() );
			return ( in_array( $user_id, $exclude_user ) ) ? false : true;
		} elseif ( '4' === $user_option ) {
			$include_user_role = get_option( 'bya_include_user_role', array() );
			return ( in_array( $user_role, $include_user_role ) ) ? true : false;
		} elseif ( '5' === $user_option ) {
			$exclude_user_role = get_option( 'bya_exclude_user_role', array() );
			return ( in_array( $user_role, $exclude_user_role ) ) ? false : true;
		}

		return true;
	}
}

if ( ! function_exists( 'bya_product_restriction' ) ) {

	/**
	 * Get buy again product restriction status
	 *
	 * @since 1.0
	 * @param Integer $product_id Product ID.
	 * @return bool
	 * */
	function bya_product_restriction( $product_id ) {
		if ( empty( $product_id ) ) {
			return false;
		}

		$product_option = get_option( 'bya_advanced_allow_products', '1' );

		if ( '2' === $product_option ) {
			$include_products = get_option( 'bya_advanced_include_product', array() );
			return ( in_array( $product_id, $include_products ) ) ? true : false;
		} elseif ( '3' === $product_option ) {
			$exclude_products = get_option( 'bya_advanced_exclude_product', array() );
			return ( ! in_array( $product_id, $exclude_products ) ) ? true : false;
		} elseif ( in_array( $product_option, array( '4', '5' ) ) ) {
			$product_obj = wc_get_product( $product_id );
			$parent_id   = $product_obj->get_parent_id();
			$product_id  = ! empty( $parent_id ) ? $parent_id : $product_id;
			$terms       = get_the_terms( $product_id, 'product_cat' );

			if ( '5' === $product_option ) {
				$exclude_categories = get_option( 'bya_advanced_exclude_category', array() );

				if ( bya_check_is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( in_array( $term->term_id, $include_categories ) ) {
							return false;
						}
					}
				}

				return true;
			} else {
				$include_categories = get_option( 'bya_advanced_include_category', array() );

				if ( bya_check_is_array( $terms ) ) {
					foreach ( $terms as $term ) {
						if ( in_array( $term->term_id, $include_categories ) ) {
							return true;
						}
					}
				}

				return false;
			}
		}

		return true;
	}
}

if ( ! function_exists( 'bya_customize_array_position' ) ) {

	/**
	 * Get customize position of my-account menu
	 *
	 * @since 1.0
	 * @param Array  $array Customize need array.
	 * @param String $key Array Key.
	 * @param String $value Array Value.
	 * @return array
	 * */
	function bya_customize_array_position( $array, $key, $value ) {
		$keys  = array_keys( $array );
		$index = array_search( $key, $keys, true );
		$pos   = false === $index ? count( $array ) : $index + 1;
		$value = is_array( $value ) ? $value : array( $value );

		return array_merge( array_slice( $array, 0, $pos ), $value, array_slice( $array, $pos ) );
	}
}

if ( ! function_exists( 'bya_get_product_ids_from_user_orders' ) ) {

	/**
	 * Get product id's from user orders
	 *
	 * @since 1.0.0
	 * @param Integer $user_id User ID.
	 * @return Array
	 * */
	function bya_get_product_ids_from_user_orders( $user_id ) {
		if ( empty( $user_id ) ) {
			return;
		}

		$order_statuses  = bya_format_woo_order_status( get_option( 'bya_general_order_status_to_show', array( 'processing', 'completed' ) ) );
		$args            = array(
			'numberposts' => -1,
			'post_type'   => wc_get_order_types(),
			'post_status' => $order_statuses,
			'fields'      => 'ids',
			'meta_query'  => array(
				array(
					'key'     => '_customer_user',
					'value'   => $user_id,
					'compare' => '==',
				),
			),
		);
		$time_filter_val = isset( $_REQUEST['time_filter'] ) ? wc_clean( wp_unslash( $_REQUEST['time_filter'] ) ) : '';

		if ( ! empty( $time_filter_val ) ) {
			$start_date_val      = isset( $_REQUEST['start_date'] ) ? wc_clean( wp_unslash( $_REQUEST['start_date'] ) ) : '';
			$end_date_val        = isset( $_REQUEST['end_date'] ) ? wc_clean( wp_unslash( $_REQUEST['end_date'] ) ) : '';
			$args ['date_query'] = array(
				array(
					'after'     => $start_date_val,
					'before'    => $end_date_val,
					'inclusive' => true,
				),
			);
		}

		$customer_orders = get_posts( $args );

		if ( ! $customer_orders ) {
			return;
		}

		$product_ids = array();

		foreach ( $customer_orders as $customer_order_id ) {
			$order = wc_get_order( $customer_order_id );
			$items = $order->get_items();

			foreach ( $items as $item_id => $item ) {
				$product_id   = $item->get_product_id();
				$variation_id = $item->get_variation_id();

				if ( ( ! empty( $variation_id ) && ! bya_product_restriction( $variation_id ) ) || ( empty( $variation_id ) && ! bya_product_restriction( $product_id ) ) ) {
					continue;
				}

				$product_obj = wc_get_product( $product_id );

				if ( ! is_object( $product_obj ) ) {
					continue;
				}

				$product_args = array(
					ucfirst( $product_obj->get_title() ),
					'order_id'     => $customer_order_id,
					'product_id'   => $product_id,
					'variation_id' => ! empty( $variation_id ) ? $variation_id : '',
					'item_id'      => $item_id,
					'item'         => $item,
					'order_count'  => 1,
					'qty_count'    => $item->get_quantity(),
				);

				$unique_key = empty( $variation_id ) ? $product_id : $variation_id;

				if ( ! isset( $product_ids[ $unique_key ]['product_id'] ) ||
				( ! empty( $product_id ) && isset( $product_ids[ $unique_key ]['product_id'] ) && $product_id != $product_ids[ $unique_key ]['product_id'] ) ||
				( ! empty( $variation_id ) && isset( $product_ids[ $unique_key ]['variation_id'] ) && $variation_id != $product_ids[ $unique_key ]['variation_id'] )
				) {
					$product_ids[ $unique_key ] = $product_args;
				} else {
					$product_ids[ $unique_key ]['order_count'] = $product_ids[ $unique_key ]['order_count'] + $product_args['order_count'];
					$product_ids[ $unique_key ]['qty_count']   = $product_ids[ $unique_key ]['qty_count'] + $product_args['qty_count'];
				}
			}
		}

		return bya_product_filters( $product_ids );
	}
}

if ( ! function_exists( 'bya_add_to_cart_ajax_enable' ) ) {

	/**
	 * Get add to cart ajax enable
	 *
	 * @since 1.0.0
	 * @return String
	 * */
	function bya_add_to_cart_ajax_enable() {
		return get_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );
	}
}

if ( ! function_exists( 'bya_get_product_url' ) ) {

	/**
	 * Get product URL
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 * @return Boolean
	 * */
	function bya_get_product_url( $product_id ) {
		return ! empty( $product_id ) ? get_permalink( $product_id ) : '';
	}
}

if ( ! function_exists( 'bya_get_buy_again_product_per_page_count' ) ) {

	/**
	 * Get Buy Again Product Per Page Column Count
	 *
	 * @since 1.0.0
	 * @return Integer
	 */
	function bya_get_buy_again_product_per_page_count() {
		return (int) get_option( 'bya_localization_myaccount_per_page_product_count', 5 );
	}
}

if ( ! function_exists( 'bya_get_buy_again_product_table_heading' ) ) {

	/**
	 * Get Buy Again Product Per table heading
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function bya_get_buy_again_product_table_heading() {
		$args = array(
			'image_label'         => array(
				'display' => ( 'yes' === get_option( 'bya_localization_allow_product_image_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_product_image_label', esc_html__( 'Product Image', 'buy-again-for-woocommerce' ) ),
			),
			'name_label'          => array(
				'display' => ( 'yes' === get_option( 'bya_localization_allow_product_name_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_product_name_label', esc_html__( 'Product Name', 'buy-again-for-woocommerce' ) ),
			),
			'product_description' => array(
				'display' => ( '1' === get_option( 'bya_localization_buy_again_table_product_desc', '2' ) ) ? true : false,
				'value'   => '',
			),
			'order_count_label'   => array(
				'display' => ( 'yes' == get_option( 'bya_localization_allow_order_count_col', 'no' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_order_count_label', esc_html__( 'Product Price', 'buy-again-for-woocommerce' ) ),
			),
			'order_id_label'      => array(
				'display' => ( 'yes' == get_option( 'bya_localization_allow_last_purchased_order_id_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_last_purchased_order_id_label', 'Last Purchased Order ID' ),
			),
			'stock_label'         => array(
				'display' => ( 'yes' === get_option( 'bya_localization_allow_stock_col', 'no' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_stock_label', 'Stock Count' ),
			),
			'price_label'         => array(
				'display' => ( 'yes' == get_option( 'bya_localization_allow_product_price_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_product_price_label', esc_html__( 'Product Price', 'buy-again-for-woocommerce' ) ),
			),
			'quantity_label'      => array(
				'display' => ( 'yes' == get_option( 'bya_localization_allow_product_quantity_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_product_quantity_label', esc_html__( 'Product Quantity', 'buy-again-for-woocommerce' ) ),
			),
			'action_label'        => array(
				'display' => ( 'yes' == get_option( 'bya_localization_allow_action_col', 'yes' ) ) ? true : false,
				'value'   => get_option( 'bya_localization_action_label', esc_html__( 'Action', 'buy-again-for-woocommerce' ) ),
			),
		);

		$display_label_count = 0;

		foreach ( $args as $key => $arg ) {
			if ( isset( $arg['display'] ) && $arg['display'] ) {
				$display_label_count = ++$display_label_count;
			}
		}

		$args['display_label_count'] = $display_label_count;

		return $args;
	}
}

if ( ! function_exists( 'bya_add_to_cart' ) ) {
	/**
	 * Buy Again Add to cart
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 * @param Integer $quantity Quantity.
	 * @param Integer $variation_id Product variant ID.
	 * @param Array   $variation_array Product variant.
	 * @param Array   $cart_item_data Cart item data.
	 */
	function bya_add_to_cart( $product_id, $quantity, $variation_id = 0, $variation_array = array(), $cart_item_data = array() ) {
		$cart_item_data['buy_again_product'] = array( 'bya_product' );

		WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation_array, $cart_item_data );
	}
}

if ( ! function_exists( 'bya_get_product_title' ) ) {
	/**
	 * Get product title
	 *
	 * @since 1.0.0
	 * @param Integer $product_id Product ID.
	 * @return String
	 */
	function bya_get_product_title( $product_id ) {
		$product = wc_get_product( $product_id );
		return ( is_a( $product, 'WC_Product' ) ) ? $product->get_title() : esc_html__( 'Product not found', 'buy-again-for-woocommerce' );
	}
}

if ( ! function_exists( 'bya_get_purchase_history_url' ) ) {

	/**
	 * Get Buy again Product Purchase history url
	 *
	 * @since 1.0.0
	 * @return Array
	 */
	function bya_get_purchase_history_url() {
		return add_query_arg( array( 'post_type' => 'buy_again_list' ), admin_url( 'edit.php' ) );
	}
}

if ( ! function_exists( 'bya_get_product_details_url' ) ) {

	/**
	 * Get Buy again Product Purchase history url
	 *
	 * @since 1.0.0
	 * @param Integer $post_id Post ID.
	 * @param Integer $user_id User ID.
	 * @return string
	 */
	function bya_get_product_details_url( $post_id, $user_id ) {
		return add_query_arg(
			array(
				'page'    => 'bya_product_details',
				'post'    => $post_id,
				'user_id' => $user_id,
			),
			admin_url( 'admin.php' )
		);
	}
}

if ( ! function_exists( 'bya_array_key_last' ) ) {

	/**
	 * Get array key last
	 *
	 * @since 1.0.0
	 * @param Array $array Array.
	 * @return string
	 */
	function bya_array_key_last( $array ) {
		return key( array_slice( $array, -1 ) );
	}
}

if ( ! function_exists( 'bya_render_product_image' ) ) {

	/**
	 * Get Product image
	 *
	 * @since 1.0.0
	 * @param Object  $product Product Object.
	 * @param Boolean $echo Print Type.
	 * @return string
	 */
	function bya_render_product_image( $product, $echo = true ) {

		$allowed_html = array(
			'a'   => array(
				'href' => array(),
			),
			'img' => array(
				'class'  => array(),
				'src'    => array(),
				'alt'    => array(),
				'srcset' => array(),
				'sizes'  => array(),
				'width'  => array(),
				'height' => array(),
				'data'   => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( $product->get_image( array( 50, 50 ) ), $allowed_html );
		}

		return $product->get_image();
	}
}

if ( ! function_exists( 'bya_get_hidden_order_itemmeta' ) ) {

	/**
	 * Get hidden order item meta
	 *
	 * @return Array
	 */
	function bya_get_hidden_order_itemmeta() {
		$args = array(
			'_qty',
			'_tax_class',
			'_product_id',
			'_variation_id',
			'_line_subtotal',
			'_line_subtotal_tax',
			'_line_total',
			'_line_tax',
			'method_id',
			'cost',
			'_reduced_stock',
			'_buy_again_product',
			'_qc_buy_now_product',
		);

		/**
		 * Filter to Hidden order itemmeta.
		 *
		 * @since 1.0
		 * @param Array $args Hidden Arguments.
		 * @return Array.
		 */
		return apply_filters( 'bya_hidden_order_itemmeta', $args );
	}
}

if ( ! function_exists( 'bya_prepare_cart_item_data' ) ) {

	/**
	 * Prepare cart item data for buy again
	 *
	 * @return Array
	 */
	function bya_prepare_cart_item_data( $order_id, $cart_item_data, $product_id, $variation_id ) {
		if ( empty( $order_id ) ) {
			return $cart_item_data;
		}

		$order_obj = wc_get_order( $order_id );

		if ( ! is_object( $order_obj ) ) {
			return $cart_item_data;
		}

		$items = $order_obj->get_items();

		if ( ! bya_check_is_array( $items ) ) {
			return $cart_item_data;
		}

		foreach ( $items as $item_id => $item ) {
			$bya_product_id   = $item->get_product_id();
			$bya_variation_id = $item->get_variation_id();

			if ( $bya_product_id != $product_id ) {
				continue;
			}

			$hidden_order_itemmeta = bya_get_hidden_order_itemmeta();
			$meta_data             = $item->get_all_formatted_meta_data( '' );

			if ( ! bya_check_is_array( $meta_data ) ) {
				continue;
			}

			foreach ( $meta_data as $meta_id => $meta ) {
				if ( in_array( $meta->key, $hidden_order_itemmeta ) ) {
					continue;
				}

				$cart_item_data[ $meta->key ] = array( $meta->display_key => $meta->display_value );
			}
		}

		return $cart_item_data;
	}
}

if ( ! function_exists( 'bya_prepare_cart_item_attr' ) ) {

	/**
	 * Prepare cart item data for buy again
	 *
	 * @return Array
	 */
	function bya_prepare_cart_item_attr( $order_id, $cart_item_attr, $product_id, $variation_id ) {
		if ( empty( $order_id ) ) {
			return $cart_item_attr;
		}

		$order_obj = wc_get_order( $order_id );

		if ( ! is_object( $order_obj ) ) {
			return $cart_item_attr;
		}

		$items = $order_obj->get_items();

		if ( ! bya_check_is_array( $items ) ) {
			return $cart_item_attr;
		}

		foreach ( $items as $item_id => $item ) {
			$bya_product_id   = $item->get_product_id();
			$bya_variation_id = $item->get_variation_id();

			if ( $bya_product_id != $product_id ) {
				continue;
			}

			$hidden_order_itemmeta = bya_get_hidden_order_itemmeta();
			$meta_data             = $item->get_formatted_meta_data( '' );

			if ( ! bya_check_is_array( $meta_data ) ) {
				continue;
			}

			foreach ( $meta_data as $meta_id => $meta ) {
				if ( in_array( $meta->key, $hidden_order_itemmeta ) ) {
					continue;
				}

				$cart_item_attr[ 'attribute_' . $meta->key ] = $meta->value;
			}
		}

		return $cart_item_attr;
	}
}

if ( ! function_exists( 'bya_get_display_custom_metas' ) ) {

	/**
	 * Prepare cart item data for buy again
	 *
	 * @return Array
	 */
	function bya_get_display_custom_metas() {
		$args = array();
		/**
		 * Filter to display custom item meta.
		 *
		 * @since 1.0
		 * @param Array $args Custom Item Meta Arguments.
		 * @return Array.
		 */
		return apply_filters( 'bya_display_custom_item_meta', $args );
	}
}

if ( ! function_exists( 'bya_product_filters' ) ) {

	/**
	 * Prepare Product based on product filters
	 *
	 * @since 2.0
	 * @param Array $product_ids Product ID's.
	 * @return Array
	 */
	function bya_product_filters( $product_ids ) {
		if ( ! bya_check_is_array( $product_ids ) ) {
			return $product_ids;
		}

		$product_ids = bya_search_product_ids( $product_ids );
		$product_ids = bya_sort_product_ids( $product_ids );

		return $product_ids;
	}
}

if ( ! function_exists( 'bya_search_product_ids' ) ) {

	/**
	 * Prepare Product ids based on Terms
	 *
	 * @since 2.0
	 * @param Array $product_ids Product ID's.
	 * @return Array
	 */
	function bya_search_product_ids( $product_ids ) {
		if ( ! bya_check_is_array( $product_ids ) ) {
			return $product_ids;
		}

		$search_val = isset( $_REQUEST['search'] ) ? wc_clean( wp_unslash( $_REQUEST['search'] ) ) : '';

		if ( empty( $search_val ) ) {
			return $product_ids;
		}

		global $wpdb;
		$post_query = new BYA_Query( $wpdb->prefix . 'posts', 'p' );
		$post_query->select( 'DISTINCT `p`.ID' )
				->whereIn( '`p`.post_type', array( 'product_variation', 'product' ) )
				->where( '`p`.post_status', 'publish' )
				->whereIn( '`p`.ID', array_keys( $product_ids ) )
				->whereLike( '`p`.post_title', '%' . $search_val . '%' );

		$result       = $post_query->fetchCol( 'ID' );
		$result       = bya_check_is_array( $result ) ? $result : array();
		$selected_ids = array();

		if ( bya_check_is_array( $result ) ) {
			foreach ( $result as $matched ) {
				if ( isset( $product_ids[ $matched ] ) ) {
					$selected_ids[ $matched ] = $product_ids[ $matched ];
				}
			}
		}

		$product_ids = $selected_ids;

		return $product_ids;
	}
}

if ( ! function_exists( 'bya_sort_product_ids' ) ) {

	/**
	 * Prepare cart item data for buy again
	 *
	 * @return Array
	 */
	function bya_sort_product_ids( $product_ids ) {
		if ( ! bya_check_is_array( $product_ids ) ) {
			return $product_ids;
		}

		$order_by = isset( $_REQUEST['orderby'] ) ? wc_clean( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$_order   = isset( $_REQUEST['order'] ) ? wc_clean( wp_unslash( $_REQUEST['order'] ) ) : '';

		if ( 'recent' == $order_by ) {
			return $product_ids;
		} elseif ( 'title' == $order_by ) {
			if ( 'desc' == $_order ) {
				arsort( $product_ids );
			} else {
				asort( $product_ids );
			}
		} else {
			if ( '2' == get_option( 'bya_advanced_buy_again_table_sort', '1' ) ) {
				asort( $product_ids );
			}
		}

		return $product_ids;
	}
}

if ( ! function_exists( 'bya_get_buy_again_product_sort_options' ) ) {

	/**
	 * Prepare cart item data for buy again
	 *
	 * @return Array
	 */
	function bya_get_buy_again_product_sort_options() {
		return array(
			'1' => esc_html__( 'Most Recent', 'buy-again-for-woocommerce' ),
			'2' => esc_html__( 'Name Ascending', 'buy-again-for-woocommerce' ),
			'3' => esc_html__( 'Name Descending', 'buy-again-for-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'bya_get_order_ids_from_product_id' ) ) {

	/**
	 * Get Order id's based on the product id.
	 *
	 * @param Integer $product_id Product ID.
	 * @return Array
	 */
	function bya_get_order_ids_from_product_id( $product_id ) {
		global $bya_product_id;
		$items = array();

		if ( empty( $product_id ) ) {
			return $items;
		}

		$bya_obj       = bya_get_buy_again_log( $bya_product_id );
		$bya_order_ids = $bya_obj->get_order_id();

		if ( ! bya_check_is_array( $bya_order_ids ) ) {
			return $items;
		}

		foreach ( $bya_order_ids as $bya_order_id ) {
			$bya_order = wc_get_order( $bya_order_id );

			if ( ! is_object( $bya_order ) ) {
				continue;
			}

			foreach ( $bya_order->get_items() as $item_id => $item_data ) {
				if ( $item_data->get_product()->get_id() != $product_id ) {
					continue;
				}

				$items[] = $bya_order_id;
			}
		}

		return array_filter( array_unique( $items ) );
	}
}

if ( ! function_exists( 'bya_get_time_filter_options' ) ) {

	/**
	 * Get Time Filter Option
	 *
	 * @since 2.0
	 * @return Array
	 */
	function bya_get_time_filter_options() {
		return array(
			''  => esc_html__( 'All', 'buy-again-for-woocommerce' ),
			'2' => esc_html__( 'Last 30 Days', 'buy-again-for-woocommerce' ),
			'3' => esc_html__( 'Last 60 Days', 'buy-again-for-woocommerce' ),
			'4' => esc_html__( 'Last 3 Months', 'buy-again-for-woocommerce' ),
			'5' => esc_html__( 'Last 6 Months', 'buy-again-for-woocommerce' ),
			'6' => esc_html__( 'Custom', 'buy-again-for-woocommerce' ),
		);
	}
}

if ( ! function_exists( 'bya_get_current_page_url' ) ) {

	/**
	 * Get Current Page URL
	 *
	 * @since 2.0
	 * @return String
	 */
	function bya_get_current_page_url() {
		global $wp;

		$current_url = '';

		if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			$current_url = BYA_PROTOCOL . wc_clean( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		return empty( $current_url ) ? home_url( $wp->request ) : $current_url;
	}
}


if ( ! function_exists( 'bya_check_filter_btn_display' ) ) {

	/**
	 * Check Filter Button Display or not
	 *
	 * @since 3.0
	 * @return Boolean
	 */
	function bya_check_filter_btn_display() {
		if ( 'yes' !== get_option( 'bya_advanced_allow_filter_btn', 'yes' ) ) {
			return false;
		}

		if ( 'yes' !== get_option( 'bya_localization_allow_search_box', 'yes' ) && 'yes' !== get_option( 'bya_advanced_allow_filter_by', 'yes' ) ) {
			return false;
		}

		return true;
	}
}
