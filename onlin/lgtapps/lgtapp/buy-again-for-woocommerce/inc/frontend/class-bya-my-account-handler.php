<?php
/**
 * Handles the Order Item.
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_My_Account_Handler' ) ) {
	/**
	 * Class
	 **/
	class BYA_My_Account_Handler {

		/**
		 * Buy Again endpoint.
		 *
		 * @var String
		 */
		public static $buy_again_endpoint;

		/**
		 * Class Initialization.
		 **/
		public static function init() {
			self::$buy_again_endpoint = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );

			if ( bya_allow_buy_again() ) {
				add_action( 'init', array( __CLASS__, 'custom_rewrite_endpoint' ) ); // Add custom rewrite endpoint.
				add_action( 'wp_loaded', array( __CLASS__, 'flush_rewrite_rules' ) ); // flush rewrite rules.
				add_filter( 'query_vars', array( __CLASS__, 'custom_query_vars' ), 0 ); // Add custom query vars.
				add_filter( 'the_title', array( __CLASS__, 'customize_menu_title' ) ); // Customize the myaccount menu title.
				add_filter( 'woocommerce_account_menu_items', array( __CLASS__, 'add_custom_myaccount_menu' ), 10, 1 ); // add custom menu to my account.
				add_action( 'woocommerce_account_' . self::$buy_again_endpoint . '_endpoint', array( __CLASS__, 'display_buy_again_menu_content' ), 10 ); // add custom menu to my account content.
				add_filter( 'bya_table_item_name', array( __CLASS__, 'add_custom_info_to_bya_item_name' ), 10, 2 ); // Booster for woocommerce - Custom buy again title compatibility.
				add_action( 'init', array( __CLASS__, 'buy_again_table_filter' ) );
			}
		}

		/**
		 * Custom rewrite endpoint
		 *
		 * @since 1.0
		 */
		public static function custom_rewrite_endpoint() {
			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( $user_restriction ) {
				add_rewrite_endpoint( self::$buy_again_endpoint, EP_ROOT | EP_PAGES );
			}
		}

		/**
		 * Flush Rewrite Rules
		 *
		 * @since 1.0
		 */
		public static function flush_rewrite_rules() {
			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( $user_restriction ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Add custom Query variable
		 *
		 * @since 1.0
		 * @param Array $vars Custom Query.
		 * @return Array
		 */
		public static function custom_query_vars( $vars ) {
			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( $user_restriction ) {
				$vars[] = self::$buy_again_endpoint;
			}

			return $vars;
		}

		/**
		 * Customize the My account menu title
		 *
		 * @since 1.0
		 * @param String $title Menus.
		 * @return String
		 */
		public static function customize_menu_title( $title ) {
			global $wp_query;
			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( is_main_query() && in_the_loop() && is_account_page() && $user_restriction ) {
				if ( isset( $wp_query->query_vars[ self::$buy_again_endpoint ] ) ) {
					$title = get_option( 'bya_localization_buy_again_menu_label', esc_html__( 'Buy Again', 'buy-again-for-woocommerce' ) );
				}

				remove_filter( 'the_title', array( __CLASS__, 'customize_menu_title' ) );
			}

			return $title;
		}

		/**
		 * Customize the My account menu title
		 *
		 * @since 1.0
		 * @param Array $menus Menus.
		 * @return Array
		 */
		public static function add_custom_myaccount_menu( $menus ) {
			if ( ! is_user_logged_in() ) {
				return $menus;
			}

			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( $user_restriction ) {
				$buy_again_menu = array( self::$buy_again_endpoint => get_option( 'bya_localization_buy_again_menu_label', esc_html__( 'Buy Again', 'buy-again-for-woocommerce' ) ) );
				$menus          = bya_customize_array_position( $menus, 'orders', $buy_again_menu );
			}

			return $menus;
		}

		/**
		 * Display the buy again menu content
		 *
		 * @since 1.0
		 */
		public static function display_buy_again_menu_content() {
			$user_id          = get_current_user_id();
			$user_restriction = bya_user_restriction( $user_id );

			if ( ! $user_restriction ) {
				return esc_html__( 'No Product Found', 'buy-again-for-woocommerce' );
			}

			$product_ids    = bya_get_product_ids_from_user_orders( $user_id );
			$product_ids    = bya_check_is_array( $product_ids ) ? $product_ids : array();
			$per_page_count = bya_get_buy_again_product_per_page_count();
			$current_page   = isset( $_REQUEST['paged'] ) ? wc_clean( wp_unslash( absint( $_REQUEST['paged'] ) ) ) : 1;

			/* Calculate Page Count */
			$default_args['posts_per_page'] = $per_page_count;
			$default_args['offset']         = ( $current_page - 1 ) * $per_page_count;
			$page_count                     = ceil( count( $product_ids ) / $per_page_count );
			$args                           = array(
				'user_id'       => $user_id,
				'product_ids'   => array_slice( $product_ids, $default_args['offset'], $per_page_count ),
				'product_count' => bya_check_is_array( $product_ids ) ? count( $product_ids ) : 0,
				'pagination'    => array(
					'page_count'      => $page_count,
					'current_page'    => $current_page,
					'prev_page_count' => ( ( $current_page - 1 ) == 0 ) ? ( $current_page ) : ( $current_page - 1 ),
					'next_page_count' => ( ( $current_page + 1 ) > $page_count ) ? ( $current_page ) : ( $current_page + 1 ),
				),
			);

			bya_get_template( 'myaccount-buy-again.php', $args );
		}

		/**
		 * Display Product title based on Booster for WooCommerce custom cart info
		 *
		 * @param String $product_title Product Title.
		 * @param Object $bya_item Buy Again Item.
		 * @return String.
		 **/
		public static function add_custom_info_to_bya_item_name( $product_title, $bya_item ) {
			if ( ! class_exists( 'WC_Jetpack' ) || ! function_exists( 'wcj_is_module_enabled' ) || ! wcj_is_module_enabled( 'cart' ) || ! class_exists( 'WCJ_Cart' ) || ! method_exists( 'WCJ_Cart', 'add_custom_info_to_cart_item_name' ) ) {
				return $product_title;
			}

			$wcj_cart_obj = new WCJ_Cart();

			if ( ! is_object( $wcj_cart_obj ) ) {
				return $product_title;
			}

			$product_title = $wcj_cart_obj->add_custom_info_to_cart_item_name( $product_title, $bya_item, '' );

			echo wp_kses_post( $product_title );
		}


		/**
		 * Display Product title based on Booster for WooCommerce custom cart info
		 *
		 * @since 2.0.
		 **/
		public static function buy_again_table_filter() {
			if ( ! isset( $_POST['_bya_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_bya_nonce'] ) ), 'bya_filter_submit' ) ) {
				return;
			}

			if ( isset( $_POST['bya_product_list_filter_form'] ) ) {
				$keys        = array( 'search', 'time_filter', 'start_date', 'end_date' );
				$arguments   = array();
				$current_url = bya_get_current_page_url();

				foreach ( $keys as $key ) {
					$val = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( $_POST[ $key ] ) ) : '';

					if ( ! empty( $val ) ) {
						$arguments[ $key ] = $val;
					} else {
						$current_url = remove_query_arg( $key, $current_url );
					}
				}

				$url = ( bya_check_is_array( $arguments ) ) ? add_query_arg( $arguments, $current_url ) : $current_url;

				wp_redirect( $url );
				exit();
			}
		}

	}

	BYA_My_Account_Handler::init();
}
