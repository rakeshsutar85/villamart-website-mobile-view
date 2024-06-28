<?php

/**
 * Custom Post Type.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Register_Post_Typess' ) ) {

	/**
	 * BYA_Register_Post_Types Class.
	 */
	class BYA_Register_Post_Types {
		/*
		 * Master Log Post Type.
		 */

		const BUY_AGAIN_LIST_POSTTYPE = 'buy_again_list';

		/**
		 * BYA_Register_Post_Types Class initialization.
		 */
		public static function init() {
			add_action( 'init', array( __CLASS__, 'register_post_types' ) );
		}

		/**
		 * Register Custom Post types.
		 */
		public static function register_post_types() {

			$custom_post_types = array(
				self::BUY_AGAIN_LIST_POSTTYPE => array( __CLASS__, 'buy_again_list_post_type' ),
			);

			/**
			 * Filter to Buy Again Custom Post Type Arguments.
			 *
			 * @since 1.0
			 * @param Array $custom_post_types Arguments.
			 * */
			$custom_post_types = apply_filters( 'buy_again_add_custom_post_type', $custom_post_types );

			if ( ! bya_check_is_array( $custom_post_types ) ) {
				return;
			}

			foreach ( $custom_post_types as $post_name => $args_function ) {
				$args = array();
				if ( $args_function ) {
					$args = call_user_func_array( $args_function, array() );
				}
				register_post_type( $post_name, $args );
			}
		}

		/**
		 * Register Buy Again Post Types.
		 */
		public static function buy_again_list_post_type() {
			$args = array(
				'labels'              => array(
					'name'          => esc_html__( 'Buy Again Purchase History', 'buy-again-for-woocommerce' ),
					'singular_name' => esc_html__( 'Purchase History', 'buy-again-for-woocommerce' ),
					'menu_name'     => esc_html__( 'Purchase History', 'buy-again-for-woocommerce' ),
					'search_items'  => esc_html__( 'Search User', 'buy-again-for-woocommerce' ),
				),
				'public'              => true,
				'show_ui'             => true,
				'capability_type'     => 'post',
				'show_in_menu'        => 'buy_again_list',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false, // Hierarchical causes memory issues - WP loads all records!
				'show_in_nav_menus'   => true,
				'capabilities'        => array(
					'publish_posts'      => 'publish_posts',
					'read_private_posts' => 'read_private_posts',
					'read_post'          => 'read_post',
					'create_posts'       => 'do_not_allow',
				),
				'map_meta_cap'        => true,
			);

			/**
			 * Filter to Buy Again Post Type.
			 *
			 * @since 1.0
			 * @param Array $args Arguments.
			 * */
			return apply_filters( 'buy_again_list_post_type', $args );
		}

	}

	BYA_Register_Post_Types::init();
}
