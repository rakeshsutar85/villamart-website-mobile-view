<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Upsell_Funnel {
	protected $settings, $error;
	protected $default_language, $languages, $languages_data;

	public function __construct() {
		$this->settings         = new VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10 );
		add_action( 'admin_init', array( $this, 'check_update' ) );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
	}

	public function admin_menu() {
		add_menu_page(
			esc_html__( 'Checkout Funnel', 'woocommerce-checkout-upsell-funnel' ),
			esc_html__( 'Checkout Funnel', 'woocommerce-checkout-upsell-funnel' ),
			'manage_options',
			'woocommerce-checkout-upsell-funnel',
			array( $this, 'settings_callback' ),
			'dashicons-filter',
			2 );
		add_submenu_page(
			'woocommerce-checkout-upsell-funnel',
			esc_html__( 'Upsell Funnel', 'woocommerce-checkout-upsell-funnel' ),
			esc_html__( 'Upsell Funnel', 'woocommerce-checkout-upsell-funnel' ),
			'manage_options',
			'woocommerce-checkout-upsell-funnel',
			array( $this, 'settings_callback' )
		);
	}

	public function check_update() {
		/**
		 * Check update
		 */
		if ( class_exists( 'VillaTheme_Plugin_Check_Update' ) ) {
			$setting_url = admin_url( 'admin.php?page=woocommerce-checkout-upsell-funnel' );
			$key         = $this->settings->get_params( 'purchased_code' );
			new VillaTheme_Plugin_Check_Update (
				VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION,                    // current version
				'https://villatheme.com/wp-json/downloads/v3',  // update path
				'woocommerce-checkout-upsell-funnel/woocommerce-checkout-upsell-funnel.php',                  // plugin file slug
				'woocommerce-checkout-upsell-funnel', '33397', $key, $setting_url
			);
			new VillaTheme_Plugin_Updater( 'woocommerce-checkout-upsell-funnel/woocommerce-checkout-upsell-funnel.php', 'woocommerce-checkout-upsell-funnel', $setting_url );
		}
	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page !== 'woocommerce-checkout-upsell-funnel' ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			/*wpml*/
			global $sitepress;
			$this->default_language = $sitepress->get_default_language();
			$languages              = icl_get_languages( 'skip_missing=N&orderby=KEY&order=DIR&link_empty_to=str' );
			$this->languages_data   = $languages;
			if ( count( $languages ) ) {
				foreach ( $languages as $key => $language ) {
					if ( $key != $this->default_language ) {
						$this->languages[] = $key;
					}
				}
			}
		} elseif ( class_exists( 'Polylang' ) ) {
			/*Polylang*/
			$languages              = pll_languages_list();
			$this->default_language = pll_default_language( 'slug' );
			foreach ( $languages as $language ) {
				if ( $language == $this->default_language ) {
					continue;
				}
				$this->languages[] = $language;
			}
		}
		if ( ! isset( $_POST['_viwcuf_settings_us'] ) || ! wp_verify_nonce( $_POST['_viwcuf_settings_us'], '_viwcuf_settings_us_action' ) ) {
			return;
		}
		global $viwcuf_params;
		if ( isset( $_POST['vi-wcuf-save'] ) || isset( $_POST['vi-wcuf-check_key'] ) ) {
			$map_args_1 = array(
				'purchased_code',
				'us_enable',
				'us_mobile_enable',
				'us_cart_coupon_enable',
				'us_pd_redirect',
				'us_pd_hide_after_atc',
				'us_pd_exclude_cart_items',
				'us_vicaio_enable',
				'us_desktop_style',
				'us_mobile_style',
				'us_desktop_position',
				'us_mobile_position',
				'us_redirect_page_endpoint',
				'us_border_color',
				'us_border_style',
				'us_border_width',
				'us_border_radius',
				'us_header_bg_color',
				'us_header_padding',
				'us_container_bg_color',
				'us_container_padding',
				'us_footer_bg_color',
				'us_footer_padding',
				'us_title',
				'us_title_color',
				'us_title_font_size',
				'us_bt_continue_title',
				'us_bt_continue_bg_color',
				'us_bt_continue_color',
				'us_bt_continue_border_color',
				'us_bt_continue_border_width',
				'us_bt_continue_border_radius',
				'us_bt_continue_font_size',
				'us_skip_icon',
				'us_skip_icon_color',
				'us_skip_icon_font_size',
				'us_bt_alltc_title',
				'us_bt_alltc_bg_color',
				'us_bt_alltc_color',
				'us_bt_alltc_border_color',
				'us_bt_alltc_border_width',
				'us_bt_alltc_border_radius',
				'us_bt_alltc_font_size',
				'us_alltc_icon',
				'us_alltc_icon_color',
				'us_alltc_icon_font_size',
				'us_time_checkout',
				'us_time',
				'us_time_reset',
				'us_countdown_message',
				'us_countdown_color',
				'us_countdown_font_size',
				'us_progress_bar_bt_pause',
				'us_progress_bar_border_width',
				'us_progress_bar_diameter',
				'us_progress_bar_bg_color',
				'us_progress_bar_border_color1',
				'us_progress_bar_border_color2',
				'us_bt_pause_title',
				'us_bt_pause_bg_color',
				'us_bt_pause_color',
				'us_bt_pause_border_color',
				'us_bt_pause_border_width',
				'us_bt_pause_border_radius',
				'us_bt_pause_font_size',
				'us_pause_icon',
				'us_pause_icon_color',
				'us_pause_icon_font_size',
				'us_desktop_display_type',
				'us_mobile_display_type',
				'us_desktop_item_per_row',
				'us_mobile_item_per_row',
				'us_desktop_scroll_limit_rows',
				'us_mobile_scroll_limit_rows',
				'us_pd_template',
				'us_pd_bg_color',
				'us_pd_box_shadow_color',
				'us_pd_border_color',
				'us_pd_border_radius',
				'us_pd_img_padding',
				'us_pd_img_border_color',
				'us_pd_img_border_width',
				'us_pd_img_border_radius',
				'us_pd_details_padding',
				'us_pd_details_font_size',
				'us_pd_details_color',
				'us_pd_details_text_align',
				'us_pd_qty_bg_color',
				'us_pd_qty_color',
				'us_pd_qty_border_color',
				'us_pd_qty_border_radius',
				'us_pd_atc_title',
				'us_pd_atc_bg_color',
				'us_pd_atc_color',
				'us_pd_atc_border_color',
				'us_pd_atc_border_width',
				'us_pd_atc_border_radius',
				'us_pd_atc_font_size',
				'us_pd_atc_icon',
				'us_pd_atc_icon_color',
				'us_pd_atc_icon_font_size',
			);
			$map_args_2 = array(
				'custom_css',
				'us_content',
				'us_header_content',
				'us_container_content',
				'us_footer_content',
			);
			$map_args_3 = array(
				'us_ids',
				'us_names',
				'us_active',
				'us_discount_amount',
				'us_discount_type',
				'us_days_show',
				'us_product_type',
				'us_product_limit',
				'us_product_order_by',
				'us_product_order',
				'us_product_qty',
				'us_product_rule_type',
				'us_product_show_variation',
				'us_product_visibility',
				'us_product_include',
				'us_product_exclude',
				'us_cats_include',
				'us_cats_exclude',
				'us_product_price',
				'us_cart_rule_type',
				'us_cart_total',
				'us_cart_subtotal',
				'us_cart_item_include_all',
				'us_cart_item_include',
				'us_cart_item_exclude',
				'us_cart_cats_include',
				'us_cart_cats_exclude',
				'us_cart_tags_include',
				'us_cart_tags_exclude',
				'us_cart_coupon_include',
				'us_cart_coupon_exclude',
				'us_billing_countries_include',
				'us_billing_countries_exclude',
				'us_shipping_countries_include',
				'us_shipping_countries_exclude',
				'us_user_rule_type',
				'us_limit_per_day',
				'us_user_logged',
				'us_user_include',
				'us_user_exclude',
				'us_user_role_include',
				'us_user_role_exclude',
			);
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$value        = '_' . $value;
					$map_args_1[] = 'us_title' . $value;
					$map_args_1[] = 'us_bt_continue_title' . $value;
					$map_args_1[] = 'us_bt_alltc_title' . $value;
					$map_args_1[] = 'us_countdown_message' . $value;
					$map_args_1[] = 'us_pd_atc_title' . $value;
					$map_args_1[] = 'us_bt_pause_title' . $value;
					$map_args_2[] = 'us_content' . $value;
					$map_args_2[] = 'us_header_content' . $value;
					$map_args_2[] = 'us_container_content' . $value;
					$map_args_2[] = 'us_footer_content' . $value;
				}
			}
			$old_args = get_option( 'viwcuf_woo_checkout_upsell_funnel', $viwcuf_params );
			$args     = array();
			foreach ( $map_args_1 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( wp_unslash( $_POST[ $item ] ) ) : '';
			}
			foreach ( $map_args_2 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? wp_kses_post( wp_unslash( $_POST[ $item ] ) ) : '';
			}
			foreach ( $map_args_3 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? viwcuf_sanitize_fields( $_POST[ $item ] ) : array();
			}
			if ( ! empty( $args['us_product_type'] ) ) {
				$key                          = array_search( '4', $args['us_product_type'] );
				$args['recent_viewed_cookie'] = $key === false ? '' : 1;
			}
			$args          = wp_parse_args( $args, $old_args );
			if ( isset( $_POST['vi-wcuf-check_key'] ) ) {
				delete_site_transient( 'update_plugins' );
				delete_transient( 'villatheme_item_33397' );
				delete_option( 'woocommerce-checkout-upsell-funnel_messages' );
				do_action( 'villatheme_save_and_check_key_woocommerce-checkout-upsell-funnel', $args['purchased_code'] );
			}
			$viwcuf_params = $args;
			if ( ( $args['us_desktop_style'] == '3' ) || ( ! empty( $args['us_mobile_enable'] ) && $args['us_mobile_style'] == '3' ) ) {
				if ( ! empty( $args['us_redirect_page_endpoint'] ) ) {
					update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
				} else {
					$this->error = esc_html__( 'Suggest page cannot be empty!', 'woocommerce-checkout-upsell-funnel' );

					return;
				}
			}
			$update_prefix = false;
			if ( ! get_option( 'viwcuf_upsell_funnel_prefix', '' ) || $args['us_desktop_style'] != $old_args['us_desktop_style'] || ( ! empty( $args['us_mobile_enable'] ) && $args['us_mobile_style'] != $old_args['us_mobile_style'] ) ) {
				$update_prefix = true;
			}
			if ( ! $update_prefix ) {
				foreach ( $map_args_3 as $item ) {
					if ( $args[ $item ] !== $old_args[ $item ] ) {
						$update_prefix = true;
						break;
					}
				}
			}
			if ( $update_prefix ) {
				update_option( 'viwcuf_upsell_funnel_prefix', substr( md5( date( "YmdHis" ) ), 0, 10 ) );
			}
			update_option( 'viwcuf_woo_checkout_upsell_funnel', $args );
		}
	}

	public function settings_callback() {
		$this->settings = new  VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		$admin          = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Settings';
		?>
        <div class="wrap<?php echo esc_attr( is_rtl() ? ' viwcuf-rtl-wrap' : '' ); ?>">
            <h2 class=""><?php esc_html_e( 'WooCommerce Checkout Upsell Funnel', 'woocommerce-checkout-upsell-funnel' ) ?></h2>
            <div id="vi-wcuf-message-error" class="error <?php echo $this->error ? '' : esc_attr( 'hidden' ); ?>">
                <p><?php echo esc_html( $this->error ); ?></p>
            </div>
            <div class="vi-ui raised">
                <form class="vi-ui form" method="post">
					<?php wp_nonce_field( '_viwcuf_settings_us_action', '_viwcuf_settings_us' ); ?>
                    <div class="vi-ui vi-ui-main tabular attached menu">
                        <a class="item active"
                           data-tab="general"><?php esc_html_e( 'General Settings', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                        <a class="item"
                           data-tab="rule"><?php esc_html_e( 'Rules & Products', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                        <a class="item"
                           data-tab="design"><?php esc_html_e( 'Design', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                        <a class="item"
                           data-tab="custom_css"><?php esc_html_e( 'Custom CSS', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                        <a class="item"
                           data-tab="update"><?php esc_html_e( 'Update', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                    </div>
                    <div class="vi-ui bottom attached tab segment active" data-tab="general">
						<?php
						$us_enable                 = $this->settings->get_params( 'us_enable' );
						$us_mobile_enable          = $this->settings->get_params( 'us_mobile_enable' );
						$us_cart_coupon_enable     = $this->settings->get_params( 'us_cart_coupon_enable' );
						$us_pd_redirect            = $this->settings->get_params( 'us_pd_redirect' );
						$us_pd_hide_after_atc      = $this->settings->get_params( 'us_pd_hide_after_atc' );
						$us_pd_exclude_cart_items  = $this->settings->get_params( 'us_pd_exclude_cart_items' );
						$us_vicaio_enable          = $this->settings->get_params( 'us_vicaio_enable' );
						$us_desktop_style          = $this->settings->get_params( 'us_desktop_style' );
						$us_mobile_style           = $this->settings->get_params( 'us_mobile_style' );
						$us_desktop_position       = $this->settings->get_params( 'us_desktop_position' );
						$us_mobile_position        = $this->settings->get_params( 'us_mobile_position' );
						$us_redirect_page_endpoint = $this->settings->get_params( 'us_redirect_page_endpoint' ) ?: 'upsell-funnel';
						$checkout_pos              = array(
							'1' => esc_html__( 'Before checkout form', 'woocommerce-checkout-upsell-funnel' ),
							'2' => esc_html__( 'Before billing details', 'woocommerce-checkout-upsell-funnel' ),
							'3' => esc_html__( 'After billing details', 'woocommerce-checkout-upsell-funnel' ),
							'4' => esc_html__( 'Before order details', 'woocommerce-checkout-upsell-funnel' ),
							'5' => esc_html__( 'Before payment gateways', 'woocommerce-checkout-upsell-funnel' ),
							'6' => esc_html__( 'After payment gateways', 'woocommerce-checkout-upsell-funnel' ),
							'7' => esc_html__( 'After checkout form', 'woocommerce-checkout-upsell-funnel' ),
						);
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_enable-checkbox"><?php esc_html_e( 'Enable', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_enable" name="us_enable"
                                               value="<?php echo esc_attr( $us_enable ); ?>">
                                        <input type="checkbox"
                                               id="vi-wcuf-us_enable-checkbox" <?php checked( $us_enable, '1' ) ?>><label
                                                for="vi-wcuf-us_enable-checkbox"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_cart_coupon_enable-checkbox"><?php esc_html_e( 'Apply coupon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_cart_coupon_enable"
                                               name="us_cart_coupon_enable"
                                               value="<?php echo esc_attr( $us_cart_coupon_enable ); ?>">
                                        <input type="checkbox"
                                               id="vi-wcuf-us_cart_coupon_enable-checkbox" <?php checked( $us_cart_coupon_enable, '1' ) ?>><label
                                                for="vi-wcuf-us_cart_coupon_enable-checkbox"></label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Apply coupon to recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_pd_redirect-checkbox"><?php esc_html_e( 'Redirect to single product page', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_pd_redirect" name="us_pd_redirect"
                                               value="<?php echo esc_attr( $us_pd_redirect ); ?>">
                                        <input type="checkbox"
                                               id="vi-wcuf-us_pd_redirect-checkbox" <?php checked( $us_pd_redirect, '1' ) ?>><label></label>
                                    </div>
                                    <p class="description">
										<?php
										esc_html_e( 'Redirect to single product page when click to product image or product title', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_pd_hide_after_atc-checkbox"><?php esc_html_e( 'Remove after adding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_pd_hide_after_atc"
                                               name="us_pd_hide_after_atc"
                                               value="<?php echo esc_attr( $us_pd_hide_after_atc ); ?>">
                                        <input type="checkbox"
                                               id="vi-wcuf-us_pd_hide_after_atc-checkbox" <?php checked( $us_pd_hide_after_atc, '1' ) ?>><label></label>
                                    </div>
                                    <p class="description">
										<?php
										esc_html_e( 'Remove a product from the upsells list right after being added to cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_pd_exclude_cart_items-checkbox"><?php esc_html_e( 'Exclude added products', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_pd_exclude_cart_items"
                                               name="us_pd_exclude_cart_items"
                                               value="<?php echo esc_attr( $us_pd_exclude_cart_items ); ?>">
                                        <input type="checkbox"
                                               id="vi-wcuf-us_pd_exclude_cart_items-checkbox" <?php checked( $us_pd_exclude_cart_items, '1' ) ?>><label></label>
                                    </div>
                                    <p class="description">
										<?php
										esc_html_e( 'Exclude products which are already in a customer\'s cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
	                        <?php
	                        if ( class_exists( 'VIWCAIO_CART_ALL_IN_ONE' ) ) {
		                        ?>
                                <tr>
                                    <th>
                                        <label for="vi-wcuf-us_vicaio_enable-checkbox"><?php esc_html_e( 'Enable on Sidebar cart', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="hidden" id="vi-wcuf-us_vicaio_enable" name="us_vicaio_enable" value="<?php echo esc_attr( $us_vicaio_enable ); ?>">
                                            <input type="checkbox" id="vi-wcuf-us_vicaio_enable-checkbox" <?php checked( $us_vicaio_enable, '1' ) ?>><label></label>
                                        </div>
                                        <p class="description">
					                        <?php esc_html_e( 'Display Upsell Funnel for Checkout form on the Sidebar Cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </p>
                                    </td>
                                </tr>
		                        <?php
	                        }
	                        ?>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_desktop_style"><?php esc_html_e( 'Style to display recommended products', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
									<?php
									$us_style = array(
										'1' => esc_html__( 'On Checkout page', 'woocommerce-checkout-upsell-funnel' ),
										'2' => esc_html__( 'On popup after clicking \'Place Order\' button', 'woocommerce-checkout-upsell-funnel' ),
										'3' => esc_html__( 'Redirect to another page after clicking \'Place Order\' button', 'woocommerce-checkout-upsell-funnel' ),
									);
									if ( class_exists( 'WC_Gateway_Twocheckout_Inline' ) ) {
										unset( $us_style['2'] );
										$us_desktop_style = $us_desktop_style == 2 ? 1 : $us_desktop_style;
										$us_mobile_style  = $us_mobile_style == 2 ? 1 : $us_mobile_style;
									}
									?>
                                    <select name="us_desktop_style"
                                            class="vi-ui fluid dropdown vi-wcuf-us_desktop_style"
                                            id="vi-wcuf-us_desktop_style">
										<?php
										foreach ( $us_style as $k => $v ) {
											echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $us_desktop_style, $k ), $v );
										}
										?>
                                    </select>
                                    <p class="description">
										<?php
										esc_html_e( 'Set the style to display recommended products', 'woocommerce-checkout-upsell-funnel' );
										?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wcuf-us_desktop_position-wrap <?php echo $us_desktop_style == 1 ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                                <th>
                                    <label for="vi-wcuf-us_desktop_position"><?php esc_html_e( 'Products position on checkout page', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <select name="us_desktop_position" id="vi-wcuf-us_desktop_position"
                                            class="vi-ui fluid dropdown vi-wcuf-us_desktop_position">
										<?php
										foreach ( $checkout_pos as $k => $v ) {
											echo sprintf( '<option value="%s" %s>%s</option>', $k, selected( $us_desktop_position, $k ), $v );
										}
										?>
                                    </select>
                                    <p class="description">
										<?php
										esc_html_e( 'Choose the position for recommended products on checkout page', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wcuf-us_redirect_page_endpoint-wrap <?php echo $us_desktop_style == 3 || ( $us_mobile_enable && $us_mobile_style == 3 ) ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                                <th>
                                    <label for="vi-wcuf-us_redirect_page_endpoint"><?php esc_html_e( 'URL of endpoint page', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="vi-wcuf-us_redirect_page_endpoint"
                                           name="us_redirect_page_endpoint"
                                           class="vi-wcuf-us_redirect_page_endpoint"
                                           value="<?php echo esc_attr( $us_redirect_page_endpoint ); ?>">
                                    <p class="description">
										<?php esc_html_e( 'Endpoints are appended to your checkout page to display recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-us_mobile_enable-checkbox"><?php esc_html_e( 'Mobile enable', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-us_mobile_enable" name="us_mobile_enable"
                                               value="<?php echo esc_attr( $us_mobile_enable ); ?>">
                                        <input type="checkbox" id="vi-wcuf-us_mobile_enable-checkbox"
                                               class="vi-wcuf-us_mobile_enable-checkbox" <?php checked( $us_mobile_enable, '1' ) ?>><label
                                                for="vi-wcuf-us_mobile_enable-checkbox"></label>
                                    </div>
                                    <p class="description">
										<?php
										esc_html_e( 'Enable to display recommended products on mobile', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wcuf-us_mobile_enable-enable <?php echo $us_mobile_enable ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                                <th>
                                    <label for="vi-wcuf-us_mobile_style"><?php esc_html_e( 'Style to display recommended products on mobile', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <select name="us_mobile_style" class="vi-ui fluid dropdown vi-wcuf-us_mobile_style"
                                            id="vi-wcuf-us_mobile_style">
										<?php
										foreach ( $us_style as $k => $v ) {
											echo sprintf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $us_mobile_style, $k ), $v );
										}
										?>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'Set the style to display recommended products on mobile', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr class="vi-wcuf-us_mobile_enable-enable vi-wcuf-us_mobile_position-wrap <?php echo $us_mobile_enable && $us_mobile_style === '1' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                                <th>
                                    <label for="vi-wcuf-us_mobile_position"><?php esc_html_e( 'Products position on checkout page on mobile', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <select name="us_mobile_position" id="vi-wcuf-us_mobile_position"
                                            class="vi-ui fluid dropdown vi-wcuf-us_mobile_position">
										<?php
										foreach ( $checkout_pos as $k => $v ) {
											echo sprintf( '<option value="%s" %s>%s</option>', $k, selected( $us_mobile_position, $k ), $v );
										}
										?>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'Choose the position for recommended products on checkout page on mobile', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wcuf-tab-rule" data-tab="rule">
                        <div class="vi-ui blue message">
							<?php
							esc_html_e( 'Check rules from top to bottom and apply the first one found', 'woocommerce-checkout-upsell-funnel' );
							?>
                        </div>
                        <div class="vi-wcuf-rules-wrap">
							<?php
							$us_ids              = $this->settings->get_params( 'us_ids' );
							$woo_currency_symbol = get_woocommerce_currency_symbol();
							$woo_countries       = new WC_Countries();
							$woo_countries       = $woo_countries->__get( 'countries' );
							$woo_users_role      = wp_roles()->roles;
							foreach ( $us_ids as $i => $id ) {
								$us_name            = $this->settings->get_current_setting( 'us_names', $i );
								$us_active          = $this->settings->get_current_setting( 'us_active', $i );
								$us_discount_amount = $this->settings->get_current_setting( 'us_discount_amount', $i ) ?: 0;
								$us_discount_type   = $this->settings->get_current_setting( 'us_discount_type', $i );
								$us_days_show       = $this->settings->get_current_setting( 'us_days_show', $id, array() );
								?>
                                <div class="vi-ui fluid styled accordion active vi-wcuf-accordion-rule-wrap  vi-wcuf-accordion-wrap vi-wcuf-accordion-<?php echo esc_attr( $id ); ?>"
                                     data-rule_id="<?php echo esc_attr( $id ); ?>">
                                    <div class="vi-wcuf-accordion-info">
                                        <i class="expand arrows alternate icon vi-wcuf-accordion-move"></i>
                                        <div class="vi-ui toggle checkbox checked vi-wcuf-active-wrap"
                                             data-tooltip="<?php esc_attr_e( 'Active', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                            <input type="hidden" name="us_active[]"
                                                   id="vi-wcuf-active-<?php echo esc_attr( $id ); ?>"
                                                   class="vi-wcuf-us_active"
                                                   value="<?php echo esc_attr( $us_active ); ?>"/>
                                            <input type="checkbox"
                                                   class="vi-wcuf-active-checkbox" <?php checked( $us_active, 1 ) ?>><label></label>
                                        </div>
                                        <h4>
                                            <span class="vi-wcuf-accordion-name"><?php echo esc_html( $us_name ); ?></span>
                                        </h4>
                                        <span class="vi-wcuf-accordion-action">
                                                <span class="vi-wcuf-accordion-clone"
                                                      data-tooltip="<?php esc_attr_e( 'Clone', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                                    <i class="clone icon"></i>
                                                </span>
                                                <span class="vi-wcuf-accordion-remove"
                                                      data-tooltip="<?php esc_attr_e( 'Remove', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                                    <i class="times icon"></i>
                                                </span>
                                        </span>
                                    </div>
                                    <div class="title <?php echo $us_active ? esc_attr( 'active' ) : ''; ?>">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'General settings', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content <?php echo $us_active ? esc_attr( 'active' ) : ''; ?>">
                                        <div class="field vi-wcuf-accordion-general-wrap">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Name', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="hidden" class="vi-wcuf-rule-id vi-wcuf-us_ids"
                                                           name="us_ids[]" value="<?php echo esc_attr( $id ); ?>">
                                                    <input type="text" class="vi-wcuf-us_names" name="us_names[]"
                                                           value="<?php echo esc_attr( $us_name ); ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Days', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <select name="us_days_show[<?php echo esc_attr( $id ) ?>][]"
                                                            data-wcuf_name_default="us_days_show[{index_default}][]"
                                                            class="vi-ui fluid dropdown vi-wcuf-us_days_show" multiple>
                                                        <option value="0" <?php selected( in_array( '0', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Sunday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="1" <?php selected( in_array( '1', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Monday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="2" <?php selected( in_array( '2', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Tuesday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="3" <?php selected( in_array( '3', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Wednesday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="4" <?php selected( in_array( '4', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Thursday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="5" <?php selected( in_array( '5', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Friday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="6" <?php selected( in_array( '6', $us_days_show ), true ) ?>>
															<?php esc_html_e( 'Saturday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content">
										<?php
										$us_product_type     = $this->settings->get_current_setting( 'us_product_type', $i );
										$us_product_limit    = $this->settings->get_current_setting( 'us_product_limit', $i, 4 );
										$us_product_order_by = $this->settings->get_current_setting( 'us_product_order_by', $i, 'date' );
										$us_product_order    = $this->settings->get_current_setting( 'us_product_order', $i, 'desc' );
										$us_product_qty      = $this->settings->get_current_setting( 'us_product_qty', $i, 1 );
										?>
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Type', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <select name="us_product_type[]"
                                                            class="vi-ui fluid dropdown vi-wcuf-us_product_type">
                                                        <option value="0" <?php selected( $us_product_type, '0' ); ?>>
															<?php esc_html_e( 'Featured products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="1" <?php selected( $us_product_type, '1' ); ?>>
															<?php esc_html_e( 'Best selling products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="2" <?php selected( $us_product_type, '2' ); ?>>
															<?php esc_html_e( 'On sale', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="3" <?php selected( $us_product_type, '3' ); ?>>
															<?php esc_html_e( 'Recently published products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="4" <?php selected( $us_product_type, '4' ); ?>>
															<?php esc_html_e( 'Recently viewed products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="5" <?php selected( $us_product_type, '5' ); ?>>
															<?php esc_html_e( 'Related products of products in the cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="6" <?php selected( $us_product_type, '6' ); ?>>
															<?php esc_html_e( 'Up-sells of products in the cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="7" <?php selected( $us_product_type, '7' ); ?>>
															<?php esc_html_e( 'Cross-sells of products in the cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="8" <?php selected( $us_product_type, '8' ); ?>>
															<?php esc_html_e( 'Products in the same categories of products in the cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="9" <?php selected( $us_product_type, '9' ); ?>>
															<?php esc_html_e( 'Products from Billing', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="10" <?php selected( $us_product_type, '10' ); ?>>
															<?php esc_html_e( 'Most purchased products from Billing', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="11" <?php selected( $us_product_type, '11' ); ?>>
															<?php esc_html_e( 'Most expensive products from Billing', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="12" <?php selected( $us_product_type, '12' ); ?>>
															<?php esc_html_e( 'Recently purchased products from Billing', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="13" <?php selected( $us_product_type, '13' ); ?>>
															<?php esc_html_e( 'Selected products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="14" <?php selected( $us_product_type, '14' ); ?>>
															<?php esc_html_e( 'Products in the cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                    </select>
                                                    <p class="description">
														<?php esc_html_e( 'The type of products will appear', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                    </p>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Discount amount', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui right action labeled input">
                                                        <input type="number" min="0" step="1"
                                                               max="<?php echo esc_attr( in_array( $us_discount_type, [
															       '1',
															       '3'
														       ] ) ? '100' : '' ); ?>"
                                                               class="vi-wcuf-us_discount_amount<?php echo $us_discount_type ? '' : esc_attr( ' vi-wcuf-hidden' ); ?>"
                                                               name="us_discount_amount[]"
                                                               value="<?php echo esc_attr( $us_discount_amount ); ?>">
                                                        <select name="us_discount_type[]" id="vi-wcuf-us_discount_type"
                                                                class="vi-ui fluid dropdown vi-wcuf-us_discount_type">
                                                            <option value="0" <?php selected( $us_discount_type, '0' ) ?>>
																<?php esc_html_e( 'None', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="1" <?php selected( $us_discount_type, '1' ) ?>>
																<?php esc_html_e( 'Percentage(%) regular price', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="2" <?php selected( $us_discount_type, '2' ) ?>>
																<?php echo sprintf( esc_html__( 'Fixed(%s) regular price', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ); ?>
                                                            </option>
                                                            <option value="3" <?php selected( $us_discount_type, '3' ) ?>>
																<?php esc_html_e( 'Percentage(%) price', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="4" <?php selected( $us_discount_type, '4' ) ?>>
																<?php echo sprintf( esc_html__( 'Fixed(%s) price', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ); ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <p class="description">
														<?php
														esc_html_e( 'The amount discounted on recommended products', 'woocommerce-checkout-upsell-funnel' );
														?>
                                                    </p>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Products limit', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="number" min="0" max="1000" step="1"
                                                           name="us_product_limit[]"
                                                           value="<?php echo esc_attr( $us_product_limit ); ?>">
                                                    <p class="description">
														<?php esc_html_e( 'The maximum number of recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                    </p>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Products quantity limit', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="number" min="1" data-wcuf_allow_empty="1" step="1"
                                                           name="us_product_qty[]"
                                                           value="<?php echo esc_attr( $us_product_qty ); ?>">
                                                    <p class="description">
														<?php esc_html_e( 'The maximum number of products quantity. Leave blank to not limit this.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                    </p>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Order products by', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui left action input">
                                                        <select name="us_product_order_by[]"
                                                                class="vi-ui fluid dropdown vi-wcuf-us_product_order_by left">
                                                            <option value="date" <?php selected( $us_product_order_by, 'date' ) ?>>
																<?php esc_html_e( 'Date', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="id" <?php selected( $us_product_order_by, 'id' ) ?>>
																<?php esc_html_e( 'ID', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="menu_order" <?php selected( $us_product_order_by, 'menu_order' ) ?>>
																<?php esc_html_e( 'Menu order', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="popularity" <?php selected( $us_product_order_by, 'popularity' ) ?>>
																<?php esc_html_e( 'Popularity', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="price" <?php selected( $us_product_order_by, 'price' ) ?>>
																<?php esc_html_e( 'Price', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="rand" <?php selected( $us_product_order_by, 'rand' ) ?>>
																<?php esc_html_e( 'Random', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="rating" <?php selected( $us_product_order_by, 'rating' ) ?>>
																<?php esc_html_e( 'Rating', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="title" <?php selected( $us_product_order_by, 'title' ) ?>>
																<?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                        </select>
                                                        <select name="us_product_order[]"
                                                                class="vi-ui fluid dropdown vi-wcuf-us_product_order">
                                                            <option value="asc" <?php selected( $us_product_order, 'asc' ) ?>>
																<?php esc_html_e( 'ASC', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                            <option value="desc" <?php selected( $us_product_order, 'desc' ) ?>>
																<?php esc_html_e( 'DESC', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="vi-ui header dividing vi-wcuf-pd_rule-title">
												<?php esc_html_e( 'Conditions of Product', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                <span class="vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-1<?php echo esc_attr( $us_discount_type == '1' ? '' : ' vi-wcuf-hidden' ); ?>">
                                                    <?php esc_html_e( ' - The price displayed on recommended products will be  the difference between the regular price and the discount (based on percentage)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </span>
                                                <span class="vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-2<?php echo esc_attr( $us_discount_type == '2' ? '' : ' vi-wcuf-hidden' ); ?>">
                                                    <?php esc_html_e( ' - The price displayed on recommended products will be  the difference between the regular price and the discount (based on fixed amount)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </span>
                                                <span class="vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-3<?php echo esc_attr( $us_discount_type == '3' ? '' : ' vi-wcuf-hidden' ); ?>">
                                                    <?php esc_html_e( ' - The price displayed on recommended products will be the difference between the sale price and the discount (based on percentage). If your product is not on sale, it will take the regular price.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </span>
                                                <span class="vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-4<?php echo esc_attr( $us_discount_type == '4' ? '' : ' vi-wcuf-hidden' ); ?>">
                                                    <?php esc_html_e( ' - The price displayed on recommended products will be the difference between the sale price and the discount (based on fixed amount). If your product is not on sale, it will take the regular price.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </span>
                                            </h5>
                                            <div class="field vi-wcuf-rule-wrap-wrap vi-wcuf-pd_rule-wrap-wrap">
                                                <div class="field vi-wcuf-rule-wrap vi-wcuf-pd-rule-wrap vi-wcuf-pd_rule-condition-wrap">
													<?php
													$us_product_rule_type = $this->settings->get_current_setting( 'us_product_rule_type', $id, array() );
													if ( is_array( $us_product_rule_type ) && count( $us_product_rule_type ) ) {
														foreach ( $us_product_rule_type as $item_type ) {
															wc_get_template( 'admin-product-rule.php',
																array(
																	'index'               => $id,
																	'woo_currency_symbol' => $woo_currency_symbol,
																	'prefix'              => 'us_',
																	'type'                => $item_type,
																	$item_type            => $this->settings->get_current_setting( 'us_' . $item_type, $id, $item_type === 'product_price' ? array() : '' ),
																),
																'',
																VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
														}
													}
													?>
                                                </div>
                                                <span class="vi-ui positive mini button vi-wcuf-add-condition-btn vi-wcuf-pd_rule-add-condition"
                                                      data-rule_type="pd" data-rule_prefix="us_">
                                                    <?php esc_html_e( 'Add Conditions(AND)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title"
                                         data-tooltip="<?php esc_attr_e( 'Choose the conditions Carts which will display recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Cart Conditions', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field vi-wcuf-rule-wrap-wrap vi-wcuf-cart_rule-wrap-wrap">
                                            <div class="field vi-wcuf-rule-wrap  vi-wcuf-cart-rule-wrap vi-wcuf-cart_rule-wrap">
												<?php
												$us_cart_rule_type = $this->settings->get_current_setting( 'us_cart_rule_type', $id, array() );
												if ( is_array( $us_cart_rule_type ) && count( $us_cart_rule_type ) ) {
													foreach ( $us_cart_rule_type as $item_type ) {
														wc_get_template( 'admin-cart-rule.php',
															array(
																'index'               => $id,
																'woo_currency_symbol' => $woo_currency_symbol,
																'woo_countries'       => $woo_countries,
																'prefix'              => 'us_',
																'type'                => $item_type,
																$item_type            => $this->settings->get_current_setting( 'us_' . $item_type, $id, array() ),
															),
															'',
															VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
													}
												}
												?>
                                            </div>
                                            <span class="vi-ui positive mini button vi-wcuf-add-condition-btn vi-wcuf-cart_rule-add-condition"
                                                  data-rule_type="cart" data-rule_prefix="us_">
                                                <?php esc_html_e( 'Add Conditions(AND)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="title"
                                         data-tooltip="<?php esc_attr_e( 'Choose the customers who can see the recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Customer Conditions', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field vi-wcuf-rule-wrap-wrap vi-wcuf-user_rule-wrap-wrap">
                                            <div class="field vi-wcuf-rule-wrap vi-wcuf-user-rule-wrap vi-wcuf-user_rule-wrap">
												<?php
												$us_user_rule_type = $this->settings->get_current_setting( 'us_user_rule_type', $id, array() );
												if ( is_array( $us_user_rule_type ) && count( $us_user_rule_type ) ) {
													foreach ( $us_user_rule_type as $item_type ) {
														wc_get_template( 'admin-user-rule.php',
															array(
																'index'               => $id,
																'woo_currency_symbol' => $woo_currency_symbol,
																'woo_users_role'      => $woo_users_role,
																'prefix'              => 'us_',
																'type'                => $item_type,
																$item_type            => $this->settings->get_current_setting( 'us_' . $item_type, $id, in_array( $item_type, [
																	'limit_per_day',
																	'user_logged'
																] ) ? '' : array() ),
															),
															'',
															VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
													}
												}
												?>
                                            </div>
                                            <span class="vi-ui positive mini button vi-wcuf-add-condition-btn vi-wcuf-user_rule-add-condition"
                                                  data-rule_type="user" data-rule_prefix="us_">
                                                <?php esc_html_e( 'Add Conditions(AND)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
								<?php
							}
							?>
                        </div>
                        <div class="field vi-wcuf-rule-new-wrap vi-wcuf-pricing-rule-new-wrap vi-wcuf-hidden">
                            <div class="vi-wcuf-pd-condition-new-wrap">
								<?php
								wc_get_template( 'admin-product-rule.php',
									array(
										'woo_currency_symbol' => $woo_currency_symbol,
									),
									'',
									VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
								?>
                            </div>
                            <div class="vi-wcuf-cart-condition-new-wrap">
								<?php
								wc_get_template( 'admin-cart-rule.php',
									array(
										'woo_currency_symbol' => $woo_currency_symbol,
										'woo_countries'       => $woo_countries,
									),
									'',
									VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
								?>
                            </div>
                            <div class="vi-wcuf-user-condition-new-wrap">
								<?php
								wc_get_template( 'admin-user-rule.php',
									array(
										'woo_currency_symbol' => $woo_currency_symbol,
										'woo_users_role'      => $woo_users_role,
									),
									'',
									VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
								?>
                            </div>
                        </div>
                    </div>
                    <div class="vi-ui bottom attached tab segment" data-tab="design">
                        <div class="vi-ui fluid styled accordion active vi-wcuf-accordion-wrap">
                            <div class="title active">
                                <i class="dropdown icon"></i>
								<?php esc_html_e( 'Layout', 'woocommerce-checkout-upsell-funnel' ); ?>
                            </div>
                            <div class="content active">
								<?php
								$us_content       = $this->settings->get_params( 'us_content' );
								$us_border_color  = $this->settings->get_params( 'us_border_color' );
								$us_border_style  = $this->settings->get_params( 'us_border_style' );
								$us_border_width  = $this->settings->get_params( 'us_border_width' ) ?: 0;
								$us_border_radius = $this->settings->get_params( 'us_border_radius' ) ?: 0;
								?>
                                <div class="field">
                                    <div class="field">
                                        <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
										<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                        <input type="text" class="vi-wcuf-us_content" name="us_content"
                                               value="<?php echo esc_attr( $us_content ); ?>"
                                               placeholder="<?php echo esc_attr( '{countdown_timer}{content}' ); ?>">
                                        <p class="description">
											<?php echo sprintf( '{content} - %s', esc_html__( 'Go to content tab to customize it', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                        </p>
                                        <p class="description">
											<?php echo sprintf( '{countdown_timer} - %s', esc_html__( 'Go to Countdown timer tab to customize it', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                        </p>
                                        <p class="description vi-wcuf-warning-message <?php echo strpos( $us_content, '{content}' ) === false ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
											<?php esc_html_e( 'The recommended products will not show if content does not include {content}', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </p>
										<?php
										if ( count( $this->languages ) ) {
											foreach ( $this->languages as $key => $value ) {
												$admin::get_language_flag_html( $value, $this->languages_data );
												echo sprintf(
													'<input type="text" name="us_content_%s" class="vi-wcuf-us_content" placeholder="%s" value="%s">',
													$value, esc_attr( '{countdown_timer}{content}' ), $this->settings->get_params( 'us_content', '_' . $value ) );
											}
										}
										?>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                            <input type="text"
                                                   class="vi-wcuf-color vi-wcuf-us_border_color"
                                                   name="us_border_color"
                                                   value="<?php echo esc_attr( $us_border_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border style', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                            <select name="us_border_style"
                                                    class="vi-ui fluid dropdown vi-wcuf-us_border_style">
                                                <option value="none" <?php selected( $us_border_style, 'none' ) ?>>
													<?php esc_html_e( 'None', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </option>
                                                <option value="dashed" <?php selected( $us_border_style, 'dashed' ) ?>>
													<?php esc_html_e( 'Dashed', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </option>
                                                <option value="double" <?php selected( $us_border_style, 'double' ) ?>>
													<?php esc_html_e( 'Double', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </option>
                                                <option value="dotted" <?php selected( $us_border_style, 'dotted' ) ?>>
													<?php esc_html_e( 'Dotted', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </option>
                                                <option value="solid" <?php selected( $us_border_style, 'solid' ) ?>>
													<?php esc_html_e( 'Solid', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number"
                                                       class="vi-wcuf-us_border_width"
                                                       name="us_border_width"
                                                       step="1"
                                                       min="0"
                                                       value="<?php echo esc_attr( $us_border_width ) ?>">
                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                            </div>
                                        </div>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                            <div class="vi-ui right labeled input">
                                                <input type="number"
                                                       class="vi-wcuf-us_border_radius"
                                                       name="us_border_radius"
                                                       step="1"
                                                       min="0"
                                                       value="<?php echo esc_attr( $us_border_radius ) ?>">
                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="title">
                                <i class="dropdown icon"></i>
								<?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?>
                            </div>
                            <div class="content">
								<?php
								$us_pd_template        = $this->settings->get_params( 'us_pd_template' );
								$us_header_content     = $this->settings->get_params( 'us_header_content' );
								$us_header_bg_color    = $this->settings->get_params( 'us_header_bg_color' );
								$us_header_padding     = $this->settings->get_params( 'us_header_padding' );
								$us_container_content  = $this->settings->get_params( 'us_container_content' );
								$us_container_bg_color = $this->settings->get_params( 'us_container_bg_color' );
								$us_container_padding  = $this->settings->get_params( 'us_container_padding' );
								$us_footer_content     = $this->settings->get_params( 'us_footer_content' );
								$us_footer_bg_color    = $this->settings->get_params( 'us_footer_bg_color' );
								$us_footer_padding     = $this->settings->get_params( 'us_footer_padding' );
								?>
                                <div class="field">
                                    <h5 class="vi-ui header dividing"><?php esc_html_e( 'Header', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
									<?php
									if ( count( $this->languages ) ) {
									?>
                                    <div class="field">
                                        <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
										<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                        <input type="text" name="us_header_content"
                                               value="<?php echo esc_attr( $us_header_content ); ?>"
                                               placeholder="<?php echo esc_attr( '{title}{continue_button}' ); ?>">
										<?php
										foreach ( $this->languages as $key => $value ) {
											$admin::get_language_flag_html( $value, $this->languages_data );
											echo sprintf(
												'<input type="text" name="us_header_content_%s" class="vi-wcuf-us_header_content" placeholder="%s" value="%s">',
												$value, esc_attr( '{title}{continue_button}' ), $this->settings->get_params( 'us_header_content', '_' . $value ) );
										}
										?>
                                    </div>
                                    <div class="equal width fields">
										<?php
										}else{
										?>
                                        <div class="equal width fields">
                                            <div class="field">
                                                <label>
													<?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </label>
                                                <input type="text" name="us_header_content"
                                                       value="<?php echo esc_attr( $us_header_content ); ?>"
                                                       placeholder="<?php echo esc_attr( '{title}{continue_button}' ); ?>">
                                            </div>
											<?php
											}
											?>
                                            <div class="field">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <input type="text"
                                                               class="vi-wcuf-color vi-wcuf-us_header_bg_color"
                                                               name="us_header_bg_color"
                                                               value="<?php echo esc_attr( $us_header_bg_color ) ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <input type="text" class="vi-wcuf-us_header_padding"
                                                               name="us_header_padding"
                                                               value="<?php echo esc_attr( $us_header_padding ); ?>"
                                                               placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <h5 class="vi-ui header dividing"><?php esc_html_e( 'Container', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
										<?php
										if ( count( $this->languages ) ) {
										?>
                                        <div class="field">
                                            <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
											<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                            <input type="text" name="us_container_content"
                                                   value="<?php echo esc_attr( $us_container_content ); ?>"
                                                   placeholder="<?php echo esc_attr( '{product_list}' ); ?>">
											<?php
											foreach ( $this->languages as $key => $value ) {
												$admin::get_language_flag_html( $value, $this->languages_data );
												echo sprintf(
													'<input type="text" name="us_container_content_%s" class="vi-wcuf-us_container_content" placeholder="{product_list}" value="%s">',
													$value, $this->settings->get_params( 'us_container_content', '_' . $value ) );
											}
											?>
                                        </div>
                                        <div class="equal width fields">
											<?php
											}else{
											?>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" name="us_container_content"
                                                           value="<?php echo esc_attr( $us_container_content ); ?>"
                                                           placeholder="<?php echo esc_attr( '{product_list}' ); ?>">
                                                </div>
												<?php
												}
												?>
                                                <div class="field">
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <input type="text"
                                                                   class="vi-wcuf-color vi-wcuf-us_container_bg_color"
                                                                   name="us_container_bg_color"
                                                                   value="<?php echo esc_attr( $us_container_bg_color ); ?>">
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <input type="text" class="vi-wcuf-us_container_padding"
                                                                   name="us_container_padding"
                                                                   value="<?php echo esc_attr( $us_container_padding ); ?>"
                                                                   placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="vi-ui header dividing"><?php esc_html_e( 'Footer', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
											<?php
											if ( count( $this->languages ) ) {
											?>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
												<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                <input type="text" name="us_footer_content"
                                                       value="<?php echo esc_attr( $us_footer_content ); ?>"
                                                       placeholder="<?php echo esc_attr( '{add_all_to_cart}' ); ?>">
												<?php
												foreach ( $this->languages as $key => $value ) {
													$admin::get_language_flag_html( $value, $this->languages_data );
													echo sprintf(
														'<input type="text" name="us_footer_content_%s" class="vi-wcuf-us_footer_content" placeholder="{add_all_to_cart}" value="%s">',
														$value, $this->settings->get_params( 'us_footer_content', '_' . $value ) );
												}
												?>
                                            </div>
                                            <div class="equal width fields">
												<?php
												}else{
												?>
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <input type="text" name="us_footer_content"
                                                               value="<?php echo esc_attr( $us_footer_content ); ?>"
                                                               placeholder="<?php echo esc_attr( '{add_all_to_cart}' ); ?>">
                                                    </div>
													<?php
													}
													?>
                                                    <div class="field">
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <input type="text"
                                                                       class="vi-wcuf-color vi-wcuf-us_footer_bg_color"
                                                                       name="us_footer_bg_color"
                                                                       value="<?php echo esc_attr( $us_footer_bg_color ); ?>">
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <input type="text" class="vi-wcuf-us_footer_padding"
                                                                       name="us_footer_padding"
                                                                       value="<?php echo esc_attr( $us_footer_padding ); ?>"
                                                                       placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Shortcode', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                <div class="field">
                                                    <p class="description">
														<?php echo sprintf( '{title} - %s', esc_html__( 'The display of title popup, it is customized in the title tab', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                    <p class="description">
														<?php echo sprintf( '{product_list} - %s',
															esc_html__( 'Display the list of recommended products, this shortcode is only used in container of pop up and designed in Product list tab and customized in product list tab', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                    <p class="description">
														<?php echo sprintf( '{continue_button} - %s', esc_html__( 'Display the button for continuing checkout and it was customized in Continue button tab', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                    <p class="description">
														<?php echo sprintf( '{countdown_timer} - %s',
															esc_html__( 'Display Countdown timer for watching and buying recommend products by customers, and it was customized in Countdown timer tab', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                    <p class="description">
														<?php echo sprintf( '{add_all_to_cart} - %s',
															esc_html__( 'Display the button which can add all recommended products to Cart. If the recommended products are appeared after clicking \'place order\' and the customers click on \'add all to Cart\' button, the customers can checkout all in one time.It is not working if Product Template of Product list tab is \'Add to cart with checkbox. This shortcode was customized in \'Add all to Cart button\' tab', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="title">
                                            <i class="dropdown icon"></i>
											<?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </div>
                                        <div class="content">
											<?php
											$us_title           = $this->settings->get_params( 'us_title' );
											$us_title_color     = $this->settings->get_params( 'us_title_color' );
											$us_title_font_size = $this->settings->get_params( 'us_title_font_size' ) ?: 0;
											?>
                                            <div class="field">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Message', 'woocommerce-checkout-upsell-funnel' ); ?></label>
													<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                    <textarea name="us_title" id="vi-wpvs-us_title"
                                                              class="vi-wpvs-us_title"
                                                              rows="5"><?php echo esc_textarea( $us_title ) ?></textarea>
                                                    <p class="description">
														<?php echo sprintf( '{discount_amount} - %s ', esc_html__( 'The discount amount for one product', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
                                                    <p class="description">
														<?php echo sprintf( '{discount_type} - %s', esc_html__( 'The discount amount in regular or current price', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                    </p>
													<?php
													if ( count( $this->languages ) ) {
														foreach ( $this->languages as $key => $value ) {
															$admin::get_language_flag_html( $value, $this->languages_data );
															echo sprintf(
																'<textarea name="us_title_%s"  class="vi-wpvs-us_title" rows="5">%s</textarea>',
																$value, $this->settings->get_params( 'us_title', '_' . $value ) );
														}
													}
													?>
                                                </div>
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <input type="text" class="vi-wcuf-color vi-wcuf-us_title_color"
                                                               name="us_title_color"
                                                               value="<?php echo esc_attr( $us_title_color ) ?>">
                                                    </div>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <div class="vi-ui right labeled input">
                                                            <input type="number" name="us_title_font_size"
                                                                   class="vi-wcuf-us_title_font_size"
                                                                   min="0" step="1"
                                                                   value="<?php echo esc_attr( $us_title_font_size ); ?>">
                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="title">
                                            <i class="dropdown icon"></i>
											<?php esc_html_e( 'Continue button', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </div>
                                        <div class="content">
											<?php
											$us_bt_continue_title         = $this->settings->get_params( 'us_bt_continue_title' );
											$us_bt_continue_bg_color      = $this->settings->get_params( 'us_bt_continue_bg_color' );
											$us_bt_continue_color         = $this->settings->get_params( 'us_bt_continue_color' );
											$us_bt_continue_border_color  = $this->settings->get_params( 'us_bt_continue_border_color' );
											$us_bt_continue_border_width  = $this->settings->get_params( 'us_bt_continue_border_width' ) ?: 0;
											$us_bt_continue_border_radius = $this->settings->get_params( 'us_bt_continue_border_radius' ) ?: 0;
											$us_bt_continue_font_size     = $this->settings->get_params( 'us_bt_continue_font_size' ) ?: 0;

											$skip_icons             = $this->settings->get_class_icons( 'skip_icons' );
											$us_skip_icon           = $this->settings->get_params( 'us_skip_icon' ) ?: '7';
											$us_skip_icon_color     = $this->settings->get_params( 'us_skip_icon_color' );
											$us_skip_icon_font_size = $this->settings->get_params( 'us_skip_icon_font_size' ) ?: 0;
											?>
                                            <div class="field">
												<?php
												if ( count( $this->languages ) ) {
												?>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
													<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                    <input type="text" name="us_bt_continue_title"
                                                           class="vi-wcuf-us_bt_continue_title"
                                                           value="<?php echo esc_attr( $us_bt_continue_title ); ?>">
                                                    <p class="description">
														<?php echo sprintf( '{skip_icon} - %s', esc_html__( 'The skip icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                    </p>
													<?php
													foreach ( $this->languages as $key => $value ) {
														$admin::get_language_flag_html( $value, $this->languages_data );
														echo sprintf(
															'<input type="text" name="us_bt_continue_title_%s"  class="vi-wpvs-us_bt_continue_title" value="%s">',
															$value, $this->settings->get_params( 'us_bt_continue_title', '_' . $value ) );
													}
													echo sprintf( '</div><div class="equal width fields">' );
													} else{
													?>
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <input type="text" name="us_bt_continue_title"
                                                                   class="vi-wcuf-us_bt_continue_title"
                                                                   value="<?php echo esc_attr( $us_bt_continue_title ); ?>">
                                                            <p class="description">
																<?php echo sprintf( '{skip_icon} - %s', esc_html__( 'The skip icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                            </p>
                                                        </div>
														<?php
														}
														?>
                                                        <div class="field">
                                                            <div class="equal width fields">
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_bt_continue_bg_color"
                                                                           name="us_bt_continue_bg_color"
                                                                           value="<?php echo esc_attr( $us_bt_continue_bg_color ); ?>">
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_bt_continue_color"
                                                                           name="us_bt_continue_color"
                                                                           value="<?php echo esc_attr( $us_bt_continue_color ); ?>">
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_bt_continue_border_color"
                                                                           name="us_bt_continue_border_color"
                                                                           value="<?php echo esc_attr( $us_bt_continue_border_color ); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <div class="vi-ui right labeled input">
                                                                <input type="number"
                                                                       class="vi-wcuf-us_bt_continue_border_width"
                                                                       name="us_bt_continue_border_width" min="0"
                                                                       step="1"
                                                                       value="<?php echo esc_attr( $us_bt_continue_border_width ); ?>">
                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <div class="vi-ui right labeled input">
                                                                <input type="number"
                                                                       class="vi-wcuf-us_bt_continue_border_radius"
                                                                       name="us_bt_continue_border_radius" min="0"
                                                                       step="1"
                                                                       value="<?php echo esc_attr( $us_bt_continue_border_radius ); ?>">
                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <div class="vi-ui right labeled input">
                                                                <input type="number"
                                                                       class="vi-wcuf-us_bt_continue_font_size"
                                                                       name="us_bt_continue_font_size" min="0" step="1"
                                                                       value="<?php echo esc_attr( $us_bt_continue_font_size ); ?>">
                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <h5 class="vi-ui header dividing"><?php esc_html_e( 'Skip icon', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Icon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <div class="fields vi-wcuf-fields-icons">
															<?php
															foreach ( $skip_icons as $k => $icon ) {
																?>
                                                                <div class="field ">
                                                                    <div class="vi-ui center aligned segment radio checked checkbox">
                                                                        <input type="radio" name="us_skip_icon"
                                                                               class="vi-wcuf-us_skip_icon"
                                                                               value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $us_skip_icon ) ?>>
                                                                        <label><i class="viwcuf-icon <?php echo esc_attr( $icon ); ?>"></i></label>
                                                                    </div>
                                                                </div>
																<?php
															}
															?>
                                                        </div>
                                                    </div>
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <input type="text"
                                                                   class="vi-wcuf-color vi-wcuf-us_skip_icon_color"
                                                                   name="us_skip_icon_color"
                                                                   value="<?php echo esc_attr( $us_skip_icon_color ); ?>">
                                                        </div>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <div class="vi-ui right labeled input">
                                                                <input type="number"
                                                                       class="vi-wcuf-us_skip_icon_font_size" min="0"
                                                                       step="1"
                                                                       name="us_skip_icon_font_size"
                                                                       value="<?php echo esc_attr( $us_skip_icon_font_size ); ?>">
                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="title vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>">
                                                <i class="dropdown icon"></i>
												<?php esc_html_e( 'Add all to cart button', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </div>
                                            <div class="content vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>"
                                                 data-tooltip="<?php esc_attr_e( 'The add all to cart button will not work if Product Template of Product list tab is \'Add to cart with checkbox\'', 'woocommerce-checkout-upsell-funnel' ); ?>">
												<?php
												$us_bt_alltc_title         = $this->settings->get_params( 'us_bt_alltc_title' );
												$us_bt_alltc_bg_color      = $this->settings->get_params( 'us_bt_alltc_bg_color' );
												$us_bt_alltc_color         = $this->settings->get_params( 'us_bt_alltc_color' );
												$us_bt_alltc_border_color  = $this->settings->get_params( 'us_bt_alltc_border_color' );
												$us_bt_alltc_border_width  = $this->settings->get_params( 'us_bt_alltc_border_width' ) ?: 0;
												$us_bt_alltc_border_radius = $this->settings->get_params( 'us_bt_alltc_border_radius' ) ?: 0;
												$us_bt_alltc_font_size     = $this->settings->get_params( 'us_bt_alltc_font_size' ) ?: 0;

												$cart_icons              = $this->settings->get_class_icons( 'cart_icons' );
												$us_alltc_icon           = $this->settings->get_params( 'us_alltc_icon' );
												$us_alltc_icon_color     = $this->settings->get_params( 'us_alltc_icon_color' );
												$us_alltc_icon_font_size = $this->settings->get_params( 'us_alltc_icon_font_size' ) ?: 0;
												?>
                                                <div class="field">
													<?php
													if ( count( $this->languages ) ) {
													?>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
														<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                        <input type="text" name="us_bt_alltc_title"
                                                               class="vi-wcuf-us_bt_alltc_title"
                                                               value="<?php echo esc_attr( $us_bt_alltc_title ); ?>">
                                                        <p class="description">
															<?php echo sprintf( '{skip_icon} - %s', esc_html__( 'The skip icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                        </p>
														<?php
														foreach ( $this->languages as $key => $value ) {
															$admin::get_language_flag_html( $value, $this->languages_data );
															echo sprintf(
																'<input type="text" name="us_bt_alltc_title_%s"  class="vi-wpvs-us_bt_alltc_title" value="%s">',
																$value, $this->settings->get_params( 'us_bt_alltc_title', '_' . $value ) );
														}
														echo sprintf( '</div><div class="equal width fields">' );
														} else{
														?>
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <input type="text" name="us_bt_alltc_title"
                                                                       class="vi-wcuf-us_bt_alltc_title"
                                                                       value="<?php echo esc_attr( $us_bt_alltc_title ); ?>">
                                                                <p class="description">
																	<?php echo sprintf( '{cart_icon} - %s', esc_html__( 'The cart icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                </p>
                                                            </div>
															<?php
															}
															?>
                                                            <div class="field">
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-color vi-wcuf-us_bt_alltc_bg_color"
                                                                               name="us_bt_alltc_bg_color"
                                                                               value="<?php echo esc_attr( $us_bt_alltc_bg_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-color vi-wcuf-us_bt_alltc_color"
                                                                               name="us_bt_alltc_color"
                                                                               value="<?php echo esc_attr( $us_bt_alltc_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-color vi-wcuf-us_bt_alltc_border_color"
                                                                               name="us_bt_alltc_border_color"
                                                                               value="<?php echo esc_attr( $us_bt_alltc_border_color ); ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number"
                                                                           class="vi-wcuf-us_bt_alltc_border_width"
                                                                           name="us_bt_alltc_border_width" min="0"
                                                                           step="1"
                                                                           value="<?php echo esc_attr( $us_bt_alltc_border_width ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number"
                                                                           class="vi-wcuf-us_bt_alltc_border_radius"
                                                                           name="us_bt_alltc_border_radius" min="0"
                                                                           step="1"
                                                                           value="<?php echo esc_attr( $us_bt_alltc_border_radius ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number"
                                                                           class="vi-wcuf-us_bt_alltc_font_size"
                                                                           name="us_bt_alltc_font_size" min="0" step="1"
                                                                           value="<?php echo esc_attr( $us_bt_alltc_font_size ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <h5 class="vi-ui header dividing">
															<?php esc_html_e( 'Cart icon', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </h5>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Icon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                            <div class="fields vi-wcuf-fields-icons">
																<?php
																foreach ( $cart_icons as $k => $icon ) {
																	?>
                                                                    <div class="field">
                                                                        <div class="vi-ui radio checkbox center aligned segment">
                                                                            <input type="radio" name="us_alltc_icon"
                                                                                   class="vi-wcuf-us_alltc_icon"
                                                                                   value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $us_alltc_icon ) ?>>
                                                                            <label><i class="viwcuf-icon <?php echo esc_attr( $icon ); ?>"></i></label>
                                                                        </div>
                                                                    </div>
																	<?php
																}
																?>
                                                            </div>
                                                        </div>
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <input type="text"
                                                                       class="vi-wcuf-color vi-wcuf-us_alltc_icon_color"
                                                                       name="us_alltc_icon_color"
                                                                       value="<?php echo esc_attr( $us_alltc_icon_color ); ?>">
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number"
                                                                           class="vi-wcuf-us_alltc_icon_font_size"
                                                                           min="0" step="1"
                                                                           name="us_alltc_icon_font_size"
                                                                           value="<?php echo esc_attr( $us_alltc_icon_font_size ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="title">
                                                    <i class="dropdown icon"></i>
													<?php esc_html_e( 'Countdown timer', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </div>
                                                <div class="content">
													<?php
													$us_time_checkout       = $this->settings->get_params( 'us_time_checkout' );
													$us_time                = $this->settings->get_params( 'us_time' );
													$us_time_reset          = $this->settings->get_params( 'us_time_reset' );
													$us_countdown_message   = $this->settings->get_params( 'us_countdown_message' );
													$us_countdown_color     = $this->settings->get_params( 'us_countdown_color' );
													$us_countdown_font_size = $this->settings->get_params( 'us_countdown_font_size' );

													$us_progress_bar_bt_pause      = $this->settings->get_params( 'us_progress_bar_bt_pause' );
													$us_progress_bar_border_width  = $this->settings->get_params( 'us_progress_bar_border_width' );
													$us_progress_bar_diameter      = $this->settings->get_params( 'us_progress_bar_diameter' );
													$us_progress_bar_bg_color      = $this->settings->get_params( 'us_progress_bar_bg_color' );
													$us_progress_bar_border_color1 = $this->settings->get_params( 'us_progress_bar_border_color1' );
													$us_progress_bar_border_color2 = $this->settings->get_params( 'us_progress_bar_border_color2' );

													$us_bt_pause_title         = $this->settings->get_params( 'us_bt_pause_title' );
													$us_bt_pause_bg_color      = $this->settings->get_params( 'us_bt_pause_bg_color' );
													$us_bt_pause_color         = $this->settings->get_params( 'us_bt_pause_color' );
													$us_bt_pause_border_color  = $this->settings->get_params( 'us_bt_pause_border_color' );
													$us_bt_pause_border_width  = $this->settings->get_params( 'us_bt_pause_border_width' ) ?: 0;
													$us_bt_pause_border_radius = $this->settings->get_params( 'us_bt_pause_border_radius' ) ?: 0;
													$us_bt_pause_font_size     = $this->settings->get_params( 'us_bt_pause_font_size' ) ?: 0;

													$pause_icons             = $this->settings->get_class_icons( 'pause_icons' );
													$us_pause_icon           = $this->settings->get_params( 'us_pause_icon' );
													$us_pause_icon_color     = $this->settings->get_params( 'us_pause_icon_color' );
													$us_pause_icon_font_size = $this->settings->get_params( 'us_pause_icon_font_size' ) ?: 0;
													?>
                                                    <div class="field">
                                                        <div class="equal width fields">
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Checkout page enable', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui toggle checkbox">
                                                                    <input type="hidden" id="vi-wcuf-us_time_checkout"
                                                                           name="us_time_checkout"
                                                                           value="<?php echo esc_attr( $us_time_checkout ); ?>">
                                                                    <input type="checkbox"
                                                                           id="vi-wcuf-us_time_checkout-checkbox"
                                                                           class="vi-wcuf-us_time_checkout-checkbox"
																		<?php checked( $us_time_checkout, '1' ) ?>><label
                                                                            for="vi-wcuf-us_time_checkout-checkbox"></label>
                                                                </div>
                                                                <p class="description">
																	<?php esc_html_e( 'Enable it to display countdown timer for buying recommended products on checkout page', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </p>
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Display time', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number" min="5" id="vi-wcuf-us_time"
                                                                           max="300" name="us_time"
                                                                           value="<?php echo esc_attr( $us_time ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php esc_html_e( 'Seconds', 'woocommerce-checkout-upsell-funnel' ); ?></div>
                                                                </div>
                                                                <p class="description">
																	<?php esc_html_e( 'The time for countdown', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </p>
                                                            </div>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Reset time', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                <div class="vi-ui right labeled input">
                                                                    <input type="number" min="1" step="1"
                                                                           name="us_time_reset"
                                                                           id="vi-wcuf-us_time_reset"
                                                                           value="<?php echo esc_attr( $us_time_reset ); ?>">
                                                                    <div class="vi-ui label vi-wcuf-basic-label"><?php esc_html_e( 'Days', 'woocommerce-checkout-upsell-funnel' ); ?></div>
                                                                </div>
                                                                <p class="description">
																	<?php esc_html_e( 'Set time for reappear recommend popup when the Cart is not checkout and popup is timed out', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </p>
                                                            </div>
                                                        </div>
														<?php
														if ( count( $this->languages ) ) {
														?>
                                                        <div class="field">
                                                            <label><?php esc_html_e( 'Message', 'woocommerce-checkout-upsell-funnel' ); ?></label>
															<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                            <input type="text" name="us_countdown_message"
                                                                   class="vi-wcuf-us_countdown_message"
                                                                   value="<?php echo esc_attr( $us_countdown_message ); ?>">
                                                            <p class="description">
																<?php echo sprintf( '{time} - %s', esc_html__( 'The time to continue checkout', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                            </p>
                                                            <p class="description">
																<?php echo sprintf( '{progress_bar} - %s',
																	esc_html__( 'The bar used to visualization the remaining time of recommended popup', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                            </p>
                                                            <p class="description">
																<?php echo sprintf( '{pause_button} - %s',
																	esc_html__( 'The button use to stop countdown and is only go with continue button', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                            </p>
															<?php
															foreach ( $this->languages as $key => $value ) {
																$admin::get_language_flag_html( $value, $this->languages_data );
																echo sprintf(
																	'<input type="text" name="us_countdown_message_%s"  class="vi-wpvs-us_countdown_message" value="%s">',
																	$value, $this->settings->get_params( 'us_countdown_message', '_' . $value ) );
															}
															echo sprintf( '</div><div class="equal width fields">' );
															} else{
															?>
                                                            <div class="equal width fields">
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Message', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text" name="us_countdown_message"
                                                                           class="vi-wcuf-us_countdown_message"
                                                                           value="<?php echo esc_attr( $us_countdown_message ); ?>">
                                                                    <p class="description">
																		<?php echo sprintf( '{time} - %s', esc_html__( 'The time to continue checkout', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                    </p>
                                                                    <p class="description">
																		<?php echo sprintf( '{progress_bar} - %s',
																			esc_html__( 'The bar used to visualization the remaining time of recommended popup', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                    </p>
                                                                    <p class="description">
																		<?php echo sprintf( '{pause_button} - %s',
																			esc_html__( 'The button use to stop countdown and is only go with continue button', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                    </p>
                                                                </div>
																<?php
																}
																?>
                                                                <div class="field">
                                                                    <div class="equal width fields">
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <input type="text"
                                                                                   class="vi-wcuf-color vi-wcuf-us_countdown_color"
                                                                                   name="us_countdown_color"
                                                                                   value="<?php echo esc_attr( $us_countdown_color ); ?>">
                                                                        </div>
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <div class="vi-ui right labeled input">
                                                                                <input type="number"
                                                                                       class="vi-wcuf-us_countdown_font_size"
                                                                                       min="0" step="1"
                                                                                       name="us_countdown_font_size"
                                                                                       value="<?php echo esc_attr( $us_countdown_font_size ); ?>">
                                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <h5 class="vi-ui header dividing"><?php esc_html_e( 'Progress bar', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                            <div class="equal width fields">
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Enable pause button', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="vi-ui toggle checkbox">
                                                                        <input type="hidden"
                                                                               class="vi-wcuf-us_progress_bar_bt_pause"
                                                                               name="us_progress_bar_bt_pause"
                                                                               value="<?php echo esc_attr( $us_progress_bar_bt_pause ); ?>">
                                                                        <input type="checkbox"
                                                                               class="vi-wcuf-us_progress_bar_bt_pause-checkbox"
																			<?php checked( $us_progress_bar_bt_pause, 1 ); ?>>
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="vi-ui right labeled input">
                                                                        <input type="number"
                                                                               class="vi-wcuf-us_progress_bar_border_width"
                                                                               name="us_progress_bar_border_width"
                                                                               min="0" step="1"
                                                                               value="<?php echo esc_attr( $us_progress_bar_border_width ); ?>">
                                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Diameter', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="vi-ui right labeled input">
                                                                        <input type="number"
                                                                               class="vi-wcuf-us_progress_bar_diameter"
                                                                               name="us_progress_bar_diameter" min="0"
                                                                               step="1"
                                                                               value="<?php echo esc_attr( $us_progress_bar_diameter ); ?>">
                                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="equal width fields">
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_progress_bar_bg_color"
                                                                           name="us_progress_bar_bg_color"
                                                                           value="<?php echo esc_attr( $us_progress_bar_bg_color ); ?>">
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Border color 1', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_progress_bar_border_color1"
                                                                           name="us_progress_bar_border_color1"
                                                                           value="<?php echo esc_attr( $us_progress_bar_border_color1 ); ?>">
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Border color 2', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <input type="text"
                                                                           class="vi-wcuf-color vi-wcuf-us_progress_bar_border_color2"
                                                                           name="us_progress_bar_border_color2"
                                                                           value="<?php echo esc_attr( $us_progress_bar_border_color2 ); ?>">
                                                                </div>
                                                            </div>
                                                            <h5 class="vi-ui header dividing"><?php esc_html_e( 'Pause button', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
															<?php
															if ( count( $this->languages ) ) {
															?>
                                                            <div class="field">
                                                                <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
																<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                                <input type="text" name="us_bt_pause_title"
                                                                       class="vi-wcuf-us_bt_pause_title"
                                                                       value="<?php echo esc_attr( $us_bt_pause_title ); ?>">
                                                                <p class="description">
																	<?php echo sprintf( '{pause_icon} - %s', esc_html__( 'The cart icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                </p>
																<?php
																foreach ( $this->languages as $key => $value ) {
																	$admin::get_language_flag_html( $value, $this->languages_data );
																	echo sprintf(
																		'<input type="text" name="us_bt_pause_title_%s"  class="vi-wpvs-us_bt_pause_title" value="%s">',
																		$value, $this->settings->get_params( 'us_bt_pause_title', '_' . $value ) );
																}
																echo sprintf( '</div><div class="equal width fields">' );
																} else{
																?>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_bt_pause_title"
                                                                               class="vi-wcuf-us_bt_pause_title"
                                                                               value="<?php echo esc_attr( $us_bt_pause_title ); ?>">
                                                                        <p class="description">
																			<?php echo sprintf( '{pause_icon} - %s', esc_html__( 'The cart icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                        </p>
                                                                    </div>
																	<?php
																	}
																	?>
                                                                    <div class="field">
                                                                        <div class="equal width fields">
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       class="vi-wcuf-color vi-wcuf-us_bt_pause_bg_color"
                                                                                       name="us_bt_pause_bg_color"
                                                                                       value="<?php echo esc_attr( $us_bt_pause_bg_color ); ?>">
                                                                            </div>
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       class="vi-wcuf-color vi-wcuf-us_bt_pause_color"
                                                                                       name="us_bt_pause_color"
                                                                                       value="<?php echo esc_attr( $us_bt_pause_color ); ?>">
                                                                            </div>
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       class="vi-wcuf-color vi-wcuf-us_bt_pause_border_color"
                                                                                       name="us_bt_pause_border_color"
                                                                                       value="<?php echo esc_attr( $us_bt_pause_border_color ); ?>">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number"
                                                                                   class="vi-wcuf-us_bt_pause_border_width"
                                                                                   name="us_bt_pause_border_width"
                                                                                   min="0" step="1"
                                                                                   value="<?php echo esc_attr( $us_bt_pause_border_width ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number"
                                                                                   class="vi-wcuf-us_bt_pause_border_radius"
                                                                                   name="us_bt_pause_border_radius"
                                                                                   min="0" step="1"
                                                                                   value="<?php echo esc_attr( $us_bt_pause_border_radius ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number"
                                                                                   class="vi-wcuf-us_bt_pause_font_size"
                                                                                   name="us_bt_pause_font_size" min="0"
                                                                                   step="1"
                                                                                   value="<?php echo esc_attr( $us_bt_pause_font_size ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Pause icon', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Icon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="fields vi-wcuf-fields-icons">
																		<?php
																		foreach ( $pause_icons as $k => $icon ) {
																			?>
                                                                            <div class="field">
                                                                                <div class="vi-ui radio checkbox center aligned segment">
                                                                                    <input type="radio"
                                                                                           name="us_pause_icon"
                                                                                           class="vi-wcuf-us_pause_icon"
                                                                                           value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $us_pause_icon ) ?>>
                                                                                    <label><i class="viwcuf-icon <?php echo esc_attr( $icon ); ?>"></i></label>
                                                                                </div>
                                                                            </div>
																			<?php
																		}
																		?>
                                                                    </div>
                                                                </div>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-color vi-wcuf-us_pause_icon_color"
                                                                               name="us_pause_icon_color"
                                                                               value="<?php echo esc_attr( $us_pause_icon_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number"
                                                                                   class="vi-wcuf-us_pause_icon_font_size"
                                                                                   min="0" step="1"
                                                                                   name="us_pause_icon_font_size"
                                                                                   value="<?php echo esc_attr( $us_pause_icon_font_size ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="title">
                                                            <i class="dropdown icon"></i>
															<?php esc_html_e( 'Product list', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </div>
                                                        <div class="content">
															<?php
															$us_desktop_display_type      = $this->settings->get_params( 'us_desktop_display_type' );
															$us_mobile_display_type       = $this->settings->get_params( 'us_mobile_display_type' );
															$us_desktop_item_per_row      = $this->settings->get_params( 'us_desktop_item_per_row' ) ?: 4;
															$us_mobile_item_per_row       = $this->settings->get_params( 'us_mobile_item_per_row' ) ?: 1;
															$us_desktop_scroll_limit_rows = $this->settings->get_params( 'us_desktop_scroll_limit_rows' );
															$us_mobile_scroll_limit_rows  = $this->settings->get_params( 'us_mobile_scroll_limit_rows' );
															$us_pd_bg_color               = $this->settings->get_params( 'us_pd_bg_color' );
															$us_pd_box_shadow_color       = $this->settings->get_params( 'us_pd_box_shadow_color' );
															$us_pd_border_color           = $this->settings->get_params( 'us_pd_border_color' );
															$us_pd_border_radius          = $this->settings->get_params( 'us_pd_border_radius' ) ?: 0;
															$us_pd_img_padding            = $this->settings->get_params( 'us_pd_img_padding' );
															$us_pd_img_border_color       = $this->settings->get_params( 'us_pd_img_border_color' );
															$us_pd_img_border_width       = $this->settings->get_params( 'us_pd_img_border_width' ) ?: 0;
															$us_pd_img_border_radius      = $this->settings->get_params( 'us_pd_img_border_radius' ) ?: 0;
															$us_pd_details_padding        = $this->settings->get_params( 'us_pd_details_padding' );
															$us_pd_details_font_size      = $this->settings->get_params( 'us_pd_details_font_size' ) ?: 0;
															$us_pd_details_color          = $this->settings->get_params( 'us_pd_details_color' );
															$us_pd_details_text_align     = $this->settings->get_params( 'us_pd_details_text_align' );
															$us_pd_qty_bg_color           = $this->settings->get_params( 'us_pd_qty_bg_color' );
															$us_pd_qty_color              = $this->settings->get_params( 'us_pd_qty_color' );
															$us_pd_qty_border_color       = $this->settings->get_params( 'us_pd_qty_border_color' );
															$us_pd_qty_border_radius      = $this->settings->get_params( 'us_pd_qty_border_radius' ) ?: 0;
															$us_pd_atc_title              = $this->settings->get_params( 'us_pd_atc_title' );
															$us_pd_atc_bg_color           = $this->settings->get_params( 'us_pd_atc_bg_color' );
															$us_pd_atc_color              = $this->settings->get_params( 'us_pd_atc_color' );
															$us_pd_atc_border_color       = $this->settings->get_params( 'us_pd_atc_border_color' );
															$us_pd_atc_border_width       = $this->settings->get_params( 'us_pd_atc_border_width' ) ?: 0;
															$us_pd_atc_border_radius      = $this->settings->get_params( 'us_pd_atc_border_radius' ) ?: 0;
															$us_pd_atc_font_size          = $this->settings->get_params( 'us_pd_atc_font_size' ) ?: 0;
															$us_pd_atc_icon               = $this->settings->get_params( 'us_pd_atc_icon' );
															$us_pd_atc_icon_color         = $this->settings->get_params( 'us_pd_atc_icon_color' );
															$us_pd_atc_icon_font_size     = $this->settings->get_params( 'us_pd_atc_icon_font_size' ) ?: 0;
															?>
                                                            <div class="field">
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Desktop', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="equal width fields">
                                                                        <div class="field">
                                                                            <div class="vi-ui right action labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Display type', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <select name="us_desktop_display_type"
                                                                                        id="vi-wcuf-us_desktop_display_type"
                                                                                        class="vi-ui fluid dropdown vi-wcuf-us_desktop_display_type">
                                                                                    <option value="slider" <?php selected( $us_desktop_display_type, 'slider' ); ?> >
																						<?php esc_html_e( 'Slider', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                    </option>
                                                                                    <option value="scroll" <?php selected( $us_desktop_display_type, 'scroll' ); ?> >
																						<?php esc_html_e( 'Scroll', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <div class="vi-ui left labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Item per row', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <input type="number" min="1" step="1"
                                                                                       max="6"
                                                                                       name="us_desktop_item_per_row"
                                                                                       class="vi-wcuf-us_desktop_item_per_row"
                                                                                       value="<?php echo esc_attr( $us_desktop_item_per_row ); ?>">
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <div class="vi-ui left labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Row on Scroll', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <input type="number" step="1" min="1"
                                                                                       name="us_desktop_scroll_limit_rows"
                                                                                       data-wcuf_allow_empty="1"
                                                                                       placeholder="<?php esc_attr_e( 'Leave blank to not limit this', 'woocommerce-checkout-upsell-funnel' ); ?>"
                                                                                       class="vi-wcuf-us_desktop_scroll_limit_rows"
                                                                                       value="<?php echo esc_attr( $us_desktop_scroll_limit_rows ); ?>">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label><?php esc_html_e( 'Mobile', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                    <div class="equal width fields">
                                                                        <div class="field">
                                                                            <div class="vi-ui right action labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Display type', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <select name="us_mobile_display_type"
                                                                                        id="vi-wcuf-us_mobile_display_type"
                                                                                        class="vi-ui fluid dropdown vi-wcuf-us_mobile_display_type">
                                                                                    <option value="slider" <?php selected( $us_mobile_display_type, 'slider' ); ?> >
																						<?php esc_html_e( 'Slider', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                    </option>
                                                                                    <option value="scroll" <?php selected( $us_mobile_display_type, 'scroll' ); ?> >
																						<?php esc_html_e( 'Scroll', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <div class="vi-ui left labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Item per row', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <input type="number" min="1" step="1"
                                                                                       max="6"
                                                                                       name="us_mobile_item_per_row"
                                                                                       class="vi-wcuf-us_mobile_item_per_row"
                                                                                       value="<?php echo esc_attr( $us_mobile_item_per_row ); ?>">
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <div class="vi-ui left labeled input">
                                                                                <div class="vi-ui label vi-wcuf-basic-label">
																					<?php esc_html_e( 'Row on Scroll', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                                </div>
                                                                                <input type="number" step="1" min="1"
                                                                                       name="us_mobile_scroll_limit_rows"
                                                                                       data-wcuf_allow_empty="1"
                                                                                       placeholder="<?php esc_attr_e( 'Leave blank to not limit this', 'woocommerce-checkout-upsell-funnel' ); ?>"
                                                                                       class="vi-wcuf-us_mobile_scroll_limit_rows"
                                                                                       value="<?php echo esc_attr( $us_mobile_scroll_limit_rows ); ?>">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Product', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Template', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <select name="us_pd_template"
                                                                                class="vi-ui fluid dropdown vi-wcuf-us_pd_template">
                                                                            <option value="1" <?php selected( $us_pd_template, 1 ); ?>>
																				<?php esc_html_e( 'Basic template', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                            </option>
                                                                            <option value="2" <?php selected( $us_pd_template, 2 ); ?>>
																				<?php esc_html_e( 'Add to cart with checkbox', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_bg_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_bg_color"
                                                                               value="<?php echo esc_attr( $us_pd_bg_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Box shadow color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_box_shadow_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_box_shadow_color"
                                                                               value="<?php echo esc_attr( $us_pd_box_shadow_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_border_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_border_color"
                                                                               value="<?php echo esc_attr( $us_pd_border_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number"
                                                                                   name="us_pd_border_radius" min="0"
                                                                                   step="1"
                                                                                   class="vi-wcuf-us_pd_border_radius"
                                                                                   value="<?php echo esc_attr( $us_pd_border_radius ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Product image', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-us_pd_img_padding"
                                                                               name="us_pd_img_padding"
                                                                               value="<?php echo esc_attr( $us_pd_img_padding ); ?>"
                                                                               placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Image border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_img_border_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_img_border_color"
                                                                               value="<?php echo esc_attr( $us_pd_img_border_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Image border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number" min="0" step="1"
                                                                                   name="us_pd_img_border_width"
                                                                                   class="vi-wcuf-us_pd_img_border_width"
                                                                                   value="<?php echo esc_attr( $us_pd_img_border_width ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Image border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number" min="0" step="1"
                                                                                   name="us_pd_img_border_radius"
                                                                                   class="vi-wcuf-us_pd_img_border_radius"
                                                                                   value="<?php echo esc_attr( $us_pd_img_border_radius ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Product details', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text"
                                                                               class="vi-wcuf-us_pd_details_padding"
                                                                               name="us_pd_details_padding"
                                                                               value="<?php echo esc_attr( $us_pd_details_padding ); ?>"
                                                                               placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number" min="0" step="1"
                                                                                   name="us_pd_details_font_size"
                                                                                   class="vi-wcuf-us_pd_details_font_size"
                                                                                   value="<?php echo esc_attr( $us_pd_details_font_size ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_details_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_details_color"
                                                                               value="<?php echo esc_attr( $us_pd_details_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Text align', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <select name="us_pd_details_text_align"
                                                                                id="vi-wcuf-us_pd_details_text_align"
                                                                                class="vi-ui fluid dropdown vi-wcuf-us_pd_details_text_align">
                                                                            <option value="center" <?php selected( $us_pd_details_text_align, 'center' ) ?>>
																				<?php esc_html_e( 'Center', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                            </option>
                                                                            <option value="left" <?php selected( $us_pd_details_text_align, 'left' ) ?>>
																				<?php esc_html_e( 'Left', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                            </option>
                                                                            <option value="right" <?php selected( $us_pd_details_text_align, 'right' ) ?>>
																				<?php esc_html_e( 'Right', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                            </option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing"><?php esc_html_e( 'Product quantity', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <div class="equal width fields">
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_qty_bg_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_qty_bg_color"
                                                                               value="<?php echo esc_attr( $us_pd_qty_bg_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_qty_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_qty_color"
                                                                               value="<?php echo esc_attr( $us_pd_qty_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <input type="text" name="us_pd_qty_border_color"
                                                                               class="vi-wcuf-color vi-wcuf-us_pd_qty_border_color"
                                                                               value="<?php echo esc_attr( $us_pd_qty_border_color ); ?>">
                                                                    </div>
                                                                    <div class="field">
                                                                        <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="vi-ui right labeled input">
                                                                            <input type="number" min="0" step="1"
                                                                                   name="us_pd_qty_border_radius"
                                                                                   class="vi-wcuf-us_pd_qty_border_radius"
                                                                                   value="<?php echo esc_attr( $us_pd_qty_border_radius ); ?>">
                                                                            <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <h5 class="vi-ui header dividing vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>"><?php esc_html_e( 'Product \'Add To Cart\' button', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                <h5 class="vi-ui header dividing vi-wcuf-us_pd_atc_checkbox<?php echo in_array( $us_pd_template, [ '2' ] ) ? '' : esc_attr( ' vi-wcuf-hidden' ); ?>"><?php esc_html_e( 'Product checkbox button', 'woocommerce-checkout-upsell-funnel' ); ?></h5>

																<?php
																if ( count( $this->languages ) ) {
																?>
                                                                <div class="field vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>">
                                                                    <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
																	<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                                    <input type="text" name="us_pd_atc_title"
                                                                           class="vi-wcuf-us_pd_atc_title"
                                                                           value="<?php echo esc_attr( $us_pd_atc_title ); ?>">
                                                                    <p class="description">
																		<?php echo sprintf( '{cart_icon} - %s', esc_html__( 'The cart icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                    </p>
																	<?php
																	foreach ( $this->languages as $key => $value ) {
																		$admin::get_language_flag_html( $value, $this->languages_data );
																		echo sprintf(
																			'<input type="text" name="us_pd_atc_title_%s"  class="vi-wpvs-us_pd_atc_title" value="%s">',
																			$value, $this->settings->get_params( 'us_pd_atc_title', '_' . $value ) );
																	}
																	echo sprintf( '</div><div class="equal width fields">' );
																	} else{
																	?>
                                                                    <div class="equal width fields">
                                                                        <div class="field vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>">
                                                                            <label><?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <input type="text" name="us_pd_atc_title"
                                                                                   class="vi-wcuf-us_pd_atc_title"
                                                                                   value="<?php echo esc_attr( $us_pd_atc_title ); ?>">
                                                                            <p class="description">
																				<?php echo sprintf( '{cart_icon} - %s', esc_html__( 'The cart icon', 'woocommerce-checkout-upsell-funnel' ) ); ?>
                                                                            </p>
                                                                        </div>
																		<?php
																		}
																		?>
                                                                        <div class="equal width fields">
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       name="us_pd_atc_bg_color"
                                                                                       class="vi-wcuf-color vi-wcuf-us_pd_atc_bg_color"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_bg_color ); ?>">
                                                                            </div>
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       name="us_pd_atc_color"
                                                                                       class="vi-wcuf-color vi-wcuf-us_pd_atc_color"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_color ); ?>">
                                                                            </div>
                                                                            <div class="field">
                                                                                <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                                <input type="text"
                                                                                       name="us_pd_atc_border_color"
                                                                                       class="vi-wcuf-color vi-wcuf-us_pd_atc_border_color"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_border_color ); ?>">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="equal width fields">
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <div class="vi-ui right labeled input">
                                                                                <input type="number" min="0" step="1"
                                                                                       name="us_pd_atc_border_width"
                                                                                       class="vi-wcuf-us_pd_atc_border_width"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_border_width ); ?>">
                                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <div class="vi-ui right labeled input">
                                                                                <input type="number" min="0" step="1"
                                                                                       name="us_pd_atc_border_radius"
                                                                                       class="vi-wcuf-us_pd_atc_border_radius"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_border_radius ); ?>">
                                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <div class="vi-ui right labeled input">
                                                                                <input type="number" min="0" step="1"
                                                                                       name="us_pd_atc_font_size"
                                                                                       class="vi-wcuf-us_pd_atc_font_size"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_font_size ); ?>">
                                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <h5 class="vi-ui header dividing vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>"><?php esc_html_e( 'Cart icon', 'woocommerce-checkout-upsell-funnel' ); ?></h5>
                                                                    <div class="field vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>">
                                                                        <label><?php esc_html_e( 'Icon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                        <div class="fields vi-wcuf-fields-icons">
																			<?php
																			foreach ( $cart_icons as $k => $icon ) {
																				?>
                                                                                <div class="field">
                                                                                    <div class="vi-ui radio checkbox center aligned segment">
                                                                                        <input type="radio"
                                                                                               name="us_pd_atc_icon"
                                                                                               class="vi-wcuf-us_pd_atc_icon"
                                                                                               value="<?php echo esc_attr( $k ); ?>" <?php checked( $k, $us_pd_atc_icon ) ?>>
                                                                                        <label><i class="viwcuf-icon <?php echo esc_attr( $icon ); ?>"></i></label>
                                                                                    </div>
                                                                                </div>
																				<?php
																			}
																			?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="equal width fields vi-wcuf-us_pd_atc<?php echo in_array( $us_pd_template, [ '2' ] ) ? esc_attr( ' vi-wcuf-hidden' ) : ''; ?>">
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <input type="text"
                                                                                   class="vi-wcuf-color vi-wcuf-us_pd_atc_icon_color"
                                                                                   name="us_pd_atc_icon_color"
                                                                                   value="<?php echo esc_attr( $us_pd_atc_icon_color ); ?>">
                                                                        </div>
                                                                        <div class="field">
                                                                            <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                                            <div class="vi-ui right labeled input">
                                                                                <input type="number"
                                                                                       class="vi-wcuf-us_pd_atc_icon_font_size"
                                                                                       min="0" step="1"
                                                                                       name="us_pd_atc_icon_font_size"
                                                                                       value="<?php echo esc_attr( $us_pd_atc_icon_font_size ); ?>">
                                                                                <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="vi-ui bottom attached tab segment"
                                                         data-tab="custom_css">
                                                        <table class="form-table">
                                                            <tr>
                                                                <th>
                                                                    <label for="vi-wcuf-custom_css"><?php esc_html_e( 'Custom CSS', 'woocommerce-checkout-upsell-funnel' ) ?></label>
                                                                </th>
                                                                <td>
																	<?php
																	$custom_css = $this->settings->get_params( 'custom_css' );
																	?>
                                                                    <textarea name="custom_css" id="vi-wcuf-custom_css"
                                                                              class="vi-wcuf-custom_css"
                                                                              rows="10"><?php echo wp_kses_post(wp_unslash( $custom_css) ) ?></textarea>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <div class="vi-ui bottom attached tab segment" data-tab="update">
                                                        <table class="form-table">
                                                            <tr>
                                                                <th>
                                                                    <label for="auto-update-key"><?php esc_html_e( 'Auto Update Key', 'woocommerce-checkout-upsell-funnel' ) ?></label>
                                                                </th>
                                                                <td>
                                                                    <div class="fields">
                                                                        <div class="ten wide field">
                                                                            <input type="text" name="purchased_code"
                                                                                   id="auto-update-key"
                                                                                   class="villatheme-autoupdate-key-field"
                                                                                   value="<?php echo esc_attr( htmlentities( $this->settings->get_params( 'purchased_code' ) ) ); ?>">
                                                                        </div>
                                                                        <div class="six wide field">
                                        <span class="vi-ui button green small villatheme-get-key-button"
                                              data-href="https://api.envato.com/authorization?response_type=code&client_id=villatheme-download-keys-6wzzaeue&redirect_uri=https://villatheme.com/update-key"
                                              data-id="31397052"><?php echo esc_html__( 'Get Key', 'woocommerce-checkout-upsell-funnel' ) ?></span>
                                                                        </div>
                                                                    </div>
																	<?php do_action( 'woocommerce-checkout-upsell-funnel_key' ) ?>
                                                                    <p class="description"><?php echo wp_kses_post( __( 'Please fill your key what you get from <a target="_blank" href="https://villatheme.com/my-download">https://villatheme.com/my-download</a>. You can auto update WooCommerce Checkout Upsell Funnel plugin. See <a target="_blank" href="https://villatheme.com/knowledge-base/how-to-use-auto-update-feature/">guide</a>', 'woocommerce-checkout-upsell-funnel' ) ); ?></p>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <p class="vi-wcuf-save-wrap">
                                                        <button type="button" class="vi-wcuf-save vi-ui primary button"
                                                                name="vi-wcuf-save">
															<?php esc_html_e( 'Save', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </button>
                                                        <button type="button"
                                                                class="vi-ui button labeled icon vi-wcuf-save"
                                                                name="vi-wcuf-check_key">
                                                            <i class="send icon"></i> <?php esc_html_e( 'Save & Check Key', 'woocommerce-checkout-upsell-funnel' ) ?>
                                                        </button>
                                                    </p>
                </form>
				<?php do_action( 'villatheme_support_woocommerce-checkout-upsell-funnel' ); ?>
            </div>
        </div>
		<?php
	}

	public function admin_enqueue_scripts() {
		$page  = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$admin = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Settings';
		if ( $page === 'woocommerce-checkout-upsell-funnel' ) {
			$admin::remove_other_script();
			$admin::enqueue_style(
				array(
					'semantic-ui-accordion',
					'semantic-ui-button',
					'semantic-ui-checkbox',
					'semantic-ui-dropdown',
					'semantic-ui-form',
					'semantic-ui-header',
					'semantic-ui-icon'
				),
				array(
					'accordion.min.css',
					'button.min.css',
					'checkbox.min.css',
					'dropdown.min.css',
					'form.min.css',
					'header.min.css',
					'icon.min.css'
				)
			);
			$admin::enqueue_style(
				array(
					'semantic-ui-input',
					'semantic-ui-label',
					'semantic-ui-menu',
					'semantic-ui-message',
					'semantic-ui-popup',
					'semantic-ui-segment',
					'semantic-ui-tab'
				),
				array(
					'input.min.css',
					'label.min.css',
					'menu.min.css',
					'message.min.css',
					'popup.min.css',
					'segment.min.css',
					'tab.css'
				)
			);
			$admin::enqueue_style(
				array(
					'vi-wcuf-admin-settings',
					'vi-wcuf-cart_icons',
					'vi-wcuf-skip_icons',
					'vi-wcuf-pause_icons',
					'select2',
					'transition',
					'minicolors'
				),
				array(
					'admin-settings.css',
					'cart-icons.min.css',
					'skip-icons.min.css',
					'pause-icons.min.css',
					'select2.min.css',
					'transition.min.css',
					'minicolors.css'
				)
			);
			$admin::enqueue_script(
				array(
					'semantic-ui-accordion',
					'semantic-ui-address',
					'semantic-ui-checkbox',
					'semantic-ui-dropdown',
					'semantic-ui-form',
					'semantic-ui-tab',
					'transition'
				),
				array(
					'accordion.min.js',
					'address.min.js',
					'checkbox.min.js',
					'dropdown.min.js',
					'form.min.js',
					'tab.js',
					'transition.min.js'
				)
			);
			$admin::enqueue_script(
				array( 'vi-wcuf-admin-settings', 'vi-wcuf-admin-upsell', 'minicolors', 'select2' ),
				array( 'admin-settings.js', 'admin-upsell.js', 'minicolors.min.js', 'select2.js', ),
				array( array( 'jquery' ), array( 'jquery', 'jquery-ui-sortable' ) )
			);
		}
	}

}
