<?php
/**
 * Pages
 *
 * @package Buy Again
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Pages' ) ) {

	/**
	 * Main Class.
	 */
	class BYA_Pages {
		/**
		 * Plugin Slug
		 *
		 * @var String
		 */
		protected static $plugin_slug = 'bya';

		/**
		 * Class Initialization
		 *
		 * @since 3.7.0
		 */
		public static function init() {
			add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 10, 2 );
		}

		/**
		 * Create pages
		 *
		 * @since 3.7.0
		 */
		public static function create_pages() {
			/**
			 * Create Custom pages filter
			 *
			 * @since 3.7.0
			 */
			$pages = apply_filters(
				self::$plugin_slug . '_create_pages',
				array(
					'register' => array(
						'name'    => _x( 'bya-buy-again', 'Page slug', 'buy-again-for-woocommerce' ),
						'title'   => _x( 'Buy Again', 'Page title', 'buy-again-for-woocommerce' ),
						'content' => '[bya_buy_again_table]',
						'option'  => self::$plugin_slug . '_buy_again_page_id',
					),
				)
			);

			foreach ( $pages as $page_args ) {
				self::create( $page_args );
			}
		}

		/**
		 * Create page
		 *
		 * @since 3.7.0
		 * @param Array $page_args Page arguments.
		 */
		public static function create( $page_args = array() ) {
			$page_args = wp_parse_args(
				$page_args,
				array(
					'name'    => '',
					'title'   => '',
					'content' => '',
					'option'  => '',
				)
			);

			$option_value = get_option( $page_args['option'] );

			if ( ! empty( $page_args['option'] ) && ! empty( $option_value ) ) {
				$page_object = get_post( $option_value );

				if ( 'page' === get_post_type( $page_object ) ) {
					if ( ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
						return $page_object->ID;
					}
				}
			}

			$page_id = wp_insert_post(
				array(
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'post_author'    => 1,
					'post_name'      => esc_sql( $page_args['name'] ),
					'post_title'     => $page_args['title'],
					'post_content'   => $page_args['content'],
					'comment_status' => 'closed',
				)
			);

			if ( $page_args['option'] ) {
				update_option( $page_args['option'], $page_id );
			}

			return $page_id;
		}

		/**
		 * Page Description
		 *
		 * @since 3.7.0
		 * @param Array  $pages Page Arguments.
		 * @param Object $post Post object.
		 * @return Array
		 */
		public static function post_states( $pages, $post ) {
			if ( (int) get_option( self::$plugin_slug . '_buy_again_page_id' ) === $post->ID ) {
				$pages[ self::$plugin_slug . '_buy_again_page' ] = esc_html__( 'Buy Again Page', 'buy-again-for-woocommerce' );
			}

			return $pages;
		}

	}

	BYA_Pages::init();
}
