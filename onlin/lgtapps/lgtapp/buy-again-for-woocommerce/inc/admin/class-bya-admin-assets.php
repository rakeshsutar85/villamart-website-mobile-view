<?php

/**
 * Admin Assets
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'BYA_Admin_Assets' ) ) {

	/**
	 * Class.
	 */
	class BYA_Admin_Assets {

		/**
		 * Class Initialization.
		 */
		public static function init() {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'external_files' ) );
		}

		/**
		 * Enqueue external JS files.
		 * */
		public static function external_files() {
			self::external_css_files();
			self::external_js_files();
		}

		/**
		 * Enqueue external JS files.
		 * */
		public static function external_css_files() {
			$screen_ids   = bya_page_screen_ids();
			$newscreenids = get_current_screen();
			$screenid     = str_replace( 'edit-', '', $newscreenids->id );

			if ( ! in_array( $screenid, $screen_ids ) ) {
				return;
			}

			wp_enqueue_style( 'bya-admin', BYA_PLUGIN_URL . '/assets/css/admin.css', array(), BYA_VERSION );
		}

		/**
		 * Enqueue external JS files
		 */
		public static function external_js_files() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			$screen_ids   = bya_page_screen_ids();
			$newscreenids = get_current_screen();
			$screenid     = str_replace( 'edit-', '', $newscreenids->id );

			$enqueue_array = array(
				'bya-admin'   => array(
					'callable' => array( 'BYA_Admin_Assets', 'admin' ),
					'restrict' => in_array( $screenid, $screen_ids ),
				),
				'bya-select2' => array(
					'callable' => array( 'BYA_Admin_Assets', 'select2' ),
					'restrict' => in_array( $screenid, $screen_ids ),
				),
			);

			/**
			 * Filter to Buy Again Admin Assets.
			 *
			 * @since 1.0
			 * */
			$enqueue_array = apply_filters( 'bya_admin_assets', $enqueue_array );
			if ( ! bya_check_is_array( $enqueue_array ) ) {
				return;
			}

			foreach ( $enqueue_array as $key => $enqueue ) {
				if ( ! bya_check_is_array( $enqueue ) ) {
					continue;
				}

				if ( $enqueue['restrict'] ) {
					call_user_func_array( $enqueue['callable'], array( $suffix ) );
				}
			}
		}

		/**
		 * Enqueue Admin end required JS files
		 */
		public static function admin( $suffix ) {
			// admin
			wp_enqueue_script( 'bya-admin', BYA_PLUGIN_URL . '/assets/js/admin.js', array( 'jquery', 'jquery-blockui' ), BYA_VERSION );
			wp_localize_script( 'bya-admin', 'bya_admin_params', array() );
		}

		/**
		 * Enqueue select2 scripts and CSS
		 */
		public static function select2( $suffix ) {
			wp_enqueue_script( 'bya-enhanced', BYA_PLUGIN_URL . '/assets/js/bya-enhanced.js', array( 'jquery', 'select2', 'jquery-ui-datepicker' ), BYA_VERSION );
			wp_localize_script(
				'bya-enhanced',
				'bya_enhanced_select_params',
				array(
					'search_nonce' => wp_create_nonce( 'bya-search-nonce' ),
					'ajaxurl'      => BYA_ADMIN_AJAX_URL,
				)
			);
		}

	}

	BYA_Admin_Assets::init();
}
