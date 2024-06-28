<?php
/**
 * Frontend Assets
 *
 * @package Buy Again for Woocommerce/Frontend Assets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if ( ! class_exists( 'BYA_Fronend_Assets' ) ) {

	/**
	 * Class.
	 */
	class BYA_Fronend_Assets {

		/**
		 * Suffix.
		 *
		 * @var String.
		 * */
		private static $suffix;

		/**
		 * Class Initialization.
		 */
		public static function init() {
			self::$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'external_js_css_files' ) );
		}

		/**
		 * Enqueue external JS CSS files.
		 *
		 * @since 1.0
		 * */
		public static function external_js_css_files() {
			self::external_js_files();
			self::external_css_files();
			self::enhanced_assests();
		}

		/**
		 * Enqueue external JS files
		 */
		public static function external_js_files() {
			// Frontend.
			wp_enqueue_script( 'bya-frontend', BYA_PLUGIN_URL . '/assets/js/frontend.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-core', 'jquery-ui-dialog', 'jquery-ui-datepicker', 'moment' ), BYA_VERSION );
			wp_localize_script(
				'bya-frontend',
				'bya_frontend_params',
				array(
					'add_to_cart_nonce'                => wp_create_nonce( 'bya-add-to-cart-nonce' ),
					'redirect_add_to_cart'             => get_option( 'woocommerce_cart_redirect_after_add', 'no' ),
					'ajax_add_to_cart'                 => get_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' ),
					'buy_again_nonce'                  => wp_create_nonce( 'bya-buy-again-nonce' ),
					'pagination_nonce'                 => wp_create_nonce( 'bya-product-pagination-nonce' ),
					'search_nonce'                     => wp_create_nonce( 'bya-product-search-nonce' ),
					'ajaxurl'                          => BYA_ADMIN_AJAX_URL,
					'current_page_url'                 => get_permalink(),
					'cart_url'                         => wc_get_cart_url(),
					'view_cart_label'                  => esc_html__( 'view cart', 'buy-again-for-woocommerce' ),
					'user_id'                          => get_current_user_id(),
					'orderby'                          => isset( $_REQUEST['orderby'] ) ? wc_clean( wp_unslash( $_REQUEST['orderby'] ) ) : '',
					'order'                            => isset( $_REQUEST['order'] ) ? wc_clean( wp_unslash( $_REQUEST['order'] ) ) : '',
					'current_url'                      => bya_get_current_page_url(),
					'min_qty_msg'                      => esc_html__( 'Please select the value that is not less than {min_qty}.', 'buy-again-for-woocommerce' ),
					'max_qty_msg'                      => esc_html__( 'Please select the value that is not more than  {max_qty}.', 'buy-again-for-woocommerce' ),
					'step_qty_msg'                     => esc_html__( 'Please select a valid value. The two nearest valid values are {from_qty} and {to_qty}.', 'buy-again-for-woocommerce' ),
				)
			);
		}

		/**
		 * Enqueue scripts and CSS.
		 *
		 * @since 1.0
		 * */
		public static function enhanced_assests() {
			wp_enqueue_script( 'bya-enhanced', BYA_PLUGIN_URL . '/assets/js/bya-enhanced.js', array( 'jquery', 'select2', 'jquery-ui-datepicker' ), BYA_VERSION );
			wp_localize_script(
				'bya-enhanced',
				'bya_enhanced_params',
				array(
					'i18n_no_matches'           => esc_html_x( 'No matches found', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_input_too_short_1'    => esc_html_x( 'Please enter 1 or more characters', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_input_too_short_n'    => esc_html_x( 'Please enter %qty% or more characters', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_input_too_long_1'     => esc_html_x( 'Please delete 1 character', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_input_too_long_n'     => esc_html_x( 'Please delete %qty% characters', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_selection_too_long_1' => esc_html_x( 'You can only select 1 item', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_selection_too_long_n' => esc_html_x( 'You can only select %qty% items', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_load_more'            => esc_html_x( 'Loading more results&hellip;', 'enhanced select', 'buy-again-for-woocommerce' ),
					'i18n_searching'            => esc_html_x( 'Searching&hellip;', 'enhanced select', 'buy-again-for-woocommerce' ),
					'search_nonce'              => wp_create_nonce( 'bya-search-nonce' ),
					'calendar_image'            => WC()->plugin_url() . '/assets/images/calendar.png',
					'ajaxurl'                   => BYA_ADMIN_AJAX_URL,
					'wc_version'                => WC()->version,
				)
			);
		}

		/**
		 * Enqueue external CSS files
		 */
		public static function external_css_files() {
			// Frontend.
			wp_enqueue_style( 'bya-frontend-css', BYA_PLUGIN_URL . '/assets/css/frontend.css', array(), BYA_VERSION );
			wp_enqueue_style( 'jquery-ui-datepicker-addon', BYA_PLUGIN_URL . '/assets/css/jquery-ui-timepicker-addon' . self::$suffix . '.css', array(), BYA_VERSION );
			wp_enqueue_style( 'jquery-ui', BYA_PLUGIN_URL . '/assets/css/jquery-ui' . self::$suffix . '.css', array(), BYA_VERSION );
			// Add custom css.
			self::add_inline_style();
		}

		/**
		 * Add Inline style
		 */
		public static function add_inline_style() {
			$contents = get_option( 'bya_advanced_custom_css', '' );

			if ( '2' === get_option( 'bya_advanced_buy_again_table_product_img_disp', '1' ) ) {
				$product_img_size = get_option(
					'bya_product_img_size',
					array(
						'width'  => 75,
						'height' => 50,
					)
				);
				$contents        .= '.bya_product_table_container .bya_buy_again_product_table tbody td img.bya_product_img {
                    width:' . $product_img_size['width'] . 'px ;
                    height: ' . $product_img_size['height'] . 'px ;
                    max-width: none;
                    }';
			}

			if ( ! $contents ) {
				return;
			}

			// Add custom css as inline style.
			wp_add_inline_style( 'bya-frontend-css', $contents );
		}

	}

	BYA_Fronend_Assets::init();
}
