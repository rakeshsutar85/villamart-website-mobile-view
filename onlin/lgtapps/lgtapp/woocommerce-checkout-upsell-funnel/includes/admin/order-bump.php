<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Order_Bump {
	protected $settings, $error;
	protected $default_language, $languages, $languages_data;

	public function __construct() {
		$this->settings         = new VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		$this->languages        = array();
		$this->languages_data   = array();
		$this->default_language = '';
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_init', array( $this, 'save_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), PHP_INT_MAX );
	}

	public function admin_menu() {
		add_submenu_page(
			'woocommerce-checkout-upsell-funnel',
			esc_html__( 'Order Bump', 'woocommerce-checkout-upsell-funnel' ),
			esc_html__( 'Order Bump', 'woocommerce-checkout-upsell-funnel' ),
			'manage_options',
			'woocommerce-checkout-upsell-funnel-ob',
			array( $this, 'settings_callback' )
		);
	}

	public function save_settings() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page !== 'woocommerce-checkout-upsell-funnel-ob' ) {
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
		if ( ! isset( $_POST['_viwcuf_settings_ob'] ) || ! wp_verify_nonce( $_POST['_viwcuf_settings_ob'], '_viwcuf_settings_ob_action' ) ) {
			return;
		}
		global $viwcuf_params;
		if ( isset( $_POST['vi-wcuf-save'] ) ) {
			$map_args_1 = array(
				'ob_enable',
				'ob_mobile_enable',
				'ob_cart_coupon_enable',
				'ob_apply_rule',
				'ob_position',
				'ob_vicaio_enable',
			);
			$map_args_2 = array(
				'ob_ids',
				'ob_names',
				'ob_active',
				'ob_discount_amount',
				'ob_discount_type',
				'ob_days_show',
				'ob_times_show',
				'ob_product',
				'ob_product_qty',
				'ob_padding',
				'ob_border_style',
				'ob_border_width',
				'ob_border_radius',
				'ob_border_color',
				'ob_bg_color',
				'ob_title_bg_color',
				'ob_title_color',
				'ob_title_padding',
				'ob_title_font_size',
				'ob_image',
				'ob_content_bg_color',
				'ob_content_color',
				'ob_content_padding',
				'ob_content_font_size',
				'ob_content_max_length',
				'ob_cart_rule_type',
				'ob_cart_total',
				'ob_cart_subtotal',
				'ob_cart_item_include_all',
				'ob_cart_item_include',
				'ob_cart_item_exclude',
				'ob_cart_cats_include',
				'ob_cart_cats_exclude',
				'ob_cart_tags_include',
				'ob_cart_tags_exclude',
				'ob_cart_coupon_include',
				'ob_cart_coupon_exclude',
				'ob_billing_countries_include',
				'ob_billing_countries_exclude',
				'ob_shipping_countries_include',
				'ob_shipping_countries_exclude',
				'ob_user_rule_type',
				'ob_limit_per_day',
				'ob_user_logged',
				'ob_user_include',
				'ob_user_exclude',
				'ob_user_role_include',
				'ob_user_role_exclude',
			);
			$map_args_3 = array(
				'ob_title',
				'ob_content',
			);
			if ( count( $this->languages ) ) {
				foreach ( $this->languages as $key => $value ) {
					$value        = '_' . $value;
					$map_args_3[] = 'ob_title' . $value;
					$map_args_3[] = 'ob_content' . $value;
				}
			}
			$args = array();
			foreach ( $map_args_1 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( stripslashes( $_POST[ $item ] ) ) : '';
			}
			foreach ( $map_args_2 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? viwcuf_sanitize_fields( $_POST[ $item ] ) : array();
			}
			foreach ( $map_args_3 as $item ) {
				$args[ $item ] = isset( $_POST[ $item ] ) ? viwcuf_sanitize_kses( $_POST[ $item ] ) : array();
			}
			$args          = wp_parse_args( $args, get_option( 'viwcuf_woo_checkout_upsell_funnel', $viwcuf_params ) );
			$viwcuf_params = $args;
			update_option( 'viwcuf_woo_checkout_upsell_funnel', $args );
		}
	}

	public function settings_callback() {
		$this->settings = new  VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		$admin          = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Settings';
		?>
        <div class="wrap<?php echo esc_attr( is_rtl() ? ' viwcuf-rtl-wrap' : '' ); ?>">
            <h2 class=""><?php esc_html_e( 'Order Bumps', 'woocommerce-checkout-upsell-funnel' ) ?></h2>
            <div id="vi-wcuf-message-error" class="error <?php echo $this->error ? '' : esc_attr( 'hidden' ); ?>">
                <p><?php echo esc_html( $this->error ); ?></p>
            </div>
            <div class="vi-ui raised">
                <form class="vi-ui form" method="post">
					<?php wp_nonce_field( '_viwcuf_settings_ob_action', '_viwcuf_settings_ob' ); ?>
                    <div class="vi-ui vi-ui-main tabular attached menu">
                        <a class="item active" data-tab="general"><?php esc_html_e( 'General Settings', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                        <a class="item" data-tab="rule"><?php esc_html_e( 'Order Bumps', 'woocommerce-checkout-upsell-funnel' ); ?></a>
                    </div>
                    <div class="vi-ui bottom attached tab segment active" data-tab="general">
						<?php
						$ob_enable             = $this->settings->get_params( 'ob_enable' );
						$ob_mobile_enable      = $this->settings->get_params( 'ob_mobile_enable' );
						$ob_cart_coupon_enable = $this->settings->get_params( 'ob_cart_coupon_enable' );
						$ob_apply_rule         = $this->settings->get_params( 'ob_apply_rule' );
						$ob_position           = $this->settings->get_params( 'ob_position' );
						$ob_vicaio_enable      = $this->settings->get_params( 'ob_vicaio_enable' );
						?>
                        <table class="form-table">
                            <tr>
                                <th>
                                    <label for="vi-wcuf-ob_enable-checkbox"><?php esc_html_e( 'Enable', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-ob_enable" name="ob_enable" value="<?php echo esc_attr( $ob_enable ); ?>">
                                        <input type="checkbox" id="vi-wcuf-ob_enable-checkbox" <?php checked( $ob_enable, '1' ) ?>><label for="vi-wcuf-ob_enable-checkbox"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-ob_mobile_enable-checkbox"><?php esc_html_e( 'Mobile enable', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-ob_mobile_enable" name="ob_mobile_enable" value="<?php echo esc_attr( $ob_mobile_enable ); ?>">
                                        <input type="checkbox" id="vi-wcuf-ob_mobile_enable-checkbox" <?php checked( $ob_mobile_enable, '1' ) ?>><label for="vi-wcuf-ob_mobile_enable-checkbox"></label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-ob_cart_coupon_enable-checkbox"><?php esc_html_e( 'Apply coupon', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <div class="vi-ui toggle checkbox">
                                        <input type="hidden" id="vi-wcuf-ob_cart_coupon_enable" name="ob_cart_coupon_enable" value="<?php echo esc_attr( $ob_cart_coupon_enable ); ?>">
                                        <input type="checkbox" id="vi-wcuf-ob_cart_coupon_enable-checkbox" <?php checked( $ob_cart_coupon_enable, '1' ) ?>><label></label>
                                    </div>
                                    <p class="description">
										<?php esc_html_e( 'Apply coupon to Order Bump products in Cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
	                        <?php
	                        if ( class_exists( 'VIWCAIO_CART_ALL_IN_ONE' ) ) {
		                        ?>
                                <tr>
                                    <th>
                                        <label for="vi-wcuf-ob_vicaio_enable-checkbox"><?php esc_html_e( 'Enable on Sidebar cart', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui toggle checkbox">
                                            <input type="hidden" id="vi-wcuf-ob_vicaio_enable" name="ob_vicaio_enable" value="<?php echo esc_attr( $ob_vicaio_enable ); ?>">
                                            <input type="checkbox" id="vi-wcuf-ob_vicaio_enable-checkbox" <?php checked( $ob_vicaio_enable, '1' ) ?>><label></label>
                                        </div>
                                        <p class="description">
					                        <?php esc_html_e( 'Display Order Bump for Checkout form on the Sidebar Cart', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </p>
                                    </td>
                                </tr>
		                        <?php
	                        }
	                        ?>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-ob_apply_rule-checkbox"><?php esc_html_e( 'Application of rules', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <select name="ob_apply_rule" id="vi-wcuf-ob_apply_rule" class="vi-ui fluid dropdown vi-wcuf-ob_apply_rule">
                                        <option value="0" <?php selected( $ob_apply_rule, 0 ) ?>>
											<?php esc_html_e( 'All matched rules', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                        <option value="1" <?php selected( $ob_apply_rule, 1 ) ?>>
											<?php esc_html_e( 'The first matched rule', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php
										esc_html_e( 'If choose \'Apply all rules\', all matched rule will be applied', 'woocommerce-checkout-upsell-funnel' );
										?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="vi-wcuf-ob_position"><?php esc_html_e( 'Position on checkout page', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                </th>
                                <td>
                                    <select name="ob_position" id="vi-wcuf-ob_position" class="vi-ui fluid dropdown vi-wcuf-ob_position">
                                        <option value="1" <?php selected( $ob_position, '1' ) ?>>
											<?php esc_html_e( 'Before billing details', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                        <option value="2" <?php selected( $ob_position, '2' ) ?>>
											<?php esc_html_e( 'After billing details', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                        <option value="3" <?php selected( $ob_position, '3' ) ?>>
											<?php esc_html_e( 'Before order details', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                        <option value="4" <?php selected( $ob_position, '4' ) ?>>
											<?php esc_html_e( 'Before payment gateways', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                        <option value="5" <?php selected( $ob_position, '5' ) ?>>
											<?php esc_html_e( 'After payment gateways', 'woocommerce-checkout-upsell-funnel' ); ?>
                                        </option>
                                    </select>
                                    <p class="description">
										<?php esc_html_e( 'Choose the position for Order Bump on checkout page', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="vi-ui bottom attached tab segment vi-wcuf-tab-rule" data-tab="rule">
                        <div class="vi-wcuf-rules-wrap">
							<?php
							$ob_ids              = $this->settings->get_params( 'ob_ids' );
							$woo_currency_symbol = get_woocommerce_currency_symbol();
							$woo_countries       = new WC_Countries();
							$woo_countries       = $woo_countries->__get( 'countries' );
							$woo_users_role      = wp_roles()->roles;
							foreach ( $ob_ids as $i => $id ) {
								$ob_name            = $this->settings->get_current_setting( 'ob_names', $i );
								$ob_active          = $this->settings->get_current_setting( 'ob_active', $i );
								$ob_discount_amount = $this->settings->get_current_setting( 'ob_discount_amount', $i ) ?: 0;
								$ob_discount_type   = $this->settings->get_current_setting( 'ob_discount_type', $i );
								$ob_days_show       = $this->settings->get_current_setting( 'ob_days_show', $id, array() );
								$ob_times_show       = $this->settings->get_current_setting( 'ob_times_show', $id, array() );
								$ob_product         = $this->settings->get_current_setting( 'ob_product', $i, '' );
								$ob_product_qty     = $this->settings->get_current_setting( 'ob_product_qty', $i, 1 );
								?>
                                <div class="vi-ui fluid styled accordion active vi-wcuf-accordion-rule-wrap  vi-wcuf-accordion-wrap" data-rule_id="<?php echo esc_attr( $id ); ?>">
                                    <div class="vi-wcuf-accordion-info">
                                        <i class="expand arrows alternate icon vi-wcuf-accordion-move"></i>
                                        <div class="vi-ui toggle checkbox checked vi-wcuf-active-wrap" data-tooltip="<?php esc_attr_e( 'Active', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                            <input type="hidden" name="ob_active[]" id="vi-wcuf-active-<?php echo esc_attr( $id ); ?>" class="vi-wcuf-ob_active"
                                                   value="<?php echo esc_attr( $ob_active ); ?>"/>
                                            <input type="checkbox" class="vi-wcuf-active-checkbox" <?php checked( $ob_active, 1 ) ?>><label></label>
                                        </div>
                                        <h4><span class="vi-wcuf-accordion-name"><?php echo esc_html( $ob_name ); ?></span></h4>
                                        <span class="vi-wcuf-accordion-action">
                                                <span class="vi-wcuf-accordion-clone" data-tooltip="<?php esc_attr_e( 'Clone', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                                    <i class="clone icon"></i>
                                                </span>
                                                <span class="vi-wcuf-accordion-remove" data-tooltip="<?php esc_attr_e( 'Remove', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                                    <i class="times icon"></i>
                                                </span>
                                        </span>
                                    </div>
                                    <div class="title <?php echo $ob_active ? esc_attr( 'active' ) : ''; ?>">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'General settings', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content <?php echo $ob_active ? esc_attr( 'active' ) : ''; ?>">
                                        <div class="field vi-wcuf-accordion-general-wrap">
                                            <div class="field">
                                                <label><?php esc_html_e( 'Name', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                <input type="hidden" class="vi-wcuf-rule-id vi-wcuf-ob_ids" name="ob_ids[]" value="<?php echo esc_attr( $id ); ?>">
                                                <input type="text" class="vi-wcuf-ob_names" name="ob_names[]" value="<?php echo esc_attr( $ob_name ); ?>">
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Days', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <select name="ob_days_show[<?php echo esc_attr( $id ) ?>][]" data-wcuf_name_default="ob_days_show[{index_default}][]"
                                                            class="vi-ui fluid dropdown vi-wcuf-ob_days_show" multiple>
                                                        <option value="0" <?php selected( in_array( '0', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Sunday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="1" <?php selected( in_array( '1', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Monday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="2" <?php selected( in_array( '2', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Tuesday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="3" <?php selected( in_array( '3', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Wednesday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="4" <?php selected( in_array( '4', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Thursday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="5" <?php selected( in_array( '5', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Friday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="6" <?php selected( in_array( '6', $ob_days_show ), true ) ?>>
				                                            <?php esc_html_e( 'Saturday', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Timer per day', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="equal width fields">
                                                        <div class="field">
                                                            <div class="vi-ui left labeled input">
                                                                <label class="vi-ui label vi-wcuf-basic-label">
						                                            <?php esc_html_e( 'From', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </label>
                                                                <input type="time"
                                                                       name="ob_times_show[<?php echo esc_attr( $id ) ?>][from]"
                                                                       class="vi-wcuf-ob_times_show-from"
                                                                       value="<?php echo esc_attr( $ob_times_show['from'] ?? '00:00' ) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="field">
                                                            <div class="vi-ui left labeled input">
                                                                <label class="vi-ui label vi-wcuf-basic-label">
						                                            <?php esc_html_e( 'To', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </label>
                                                                <input type="time"
                                                                       name="ob_times_show[<?php echo esc_attr( $id ) ?>][to]"
                                                                       class="vi-wcuf-ob_times_show-to"
                                                                       value="<?php echo esc_attr( $ob_times_show['to'] ?? '23:59' ) ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <div class="equal width fields">
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Discount amount', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <div class="vi-ui right action labeled input">
                                                            <input type="number" min="0" step="1" max="<?php echo esc_attr(in_array($ob_discount_type,['1','3'])? '100': ''); ?>"
                                                                   class="vi-wcuf-ob_discount_amount<?php echo $ob_discount_type ? '' : esc_attr( ' vi-wcuf-hidden' ); ?>"
                                                                   name="ob_discount_amount[]" value="<?php echo esc_attr( $ob_discount_amount ); ?>">
                                                            <select name="ob_discount_type[]" id="vi-wcuf-ob_discount_type" class="vi-ui fluid dropdown vi-wcuf-ob_discount_type">
                                                                <option value="0" <?php selected( $ob_discount_type, '0' ) ?>>
																	<?php esc_html_e( 'None', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </option>
                                                                <option value="1" <?php selected( $ob_discount_type, '1' ) ?>>
																	<?php esc_html_e( 'Percentage(%) regular price', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </option>
                                                                <option value="2" <?php selected( $ob_discount_type, '2' ) ?>>
																	<?php echo sprintf( esc_html__( 'Fixed(%s) regular price', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ); ?>
                                                                </option>
                                                                <option value="3" <?php selected( $ob_discount_type, '3' ) ?>>
																	<?php esc_html_e( 'Percentage(%) price', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                                </option>
                                                                <option value="4" <?php selected( $ob_discount_type, '4' ) ?>>
																	<?php echo sprintf( esc_html__( 'Fixed(%s) price', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ); ?>
                                                                </option>
                                                                <option value="5" <?php selected( $ob_discount_type, '5' ) ?>>
																	<?php echo sprintf( esc_html__( 'New fixed(%s) price', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ); ?>
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <p class="description">
															<?php esc_html_e( 'The amount discounted on recommended product', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                        <p class="description vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-1<?php echo esc_attr( $ob_discount_type =='1' ?  '': ' vi-wcuf-hidden'); ?>">
															<?php esc_html_e( 'The price displayed on order bump will be the difference between the regular price and the discount (based on percentage)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                        <p class="description vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-2<?php echo esc_attr( $ob_discount_type =='2' ?  '': ' vi-wcuf-hidden'); ?>">
															<?php esc_html_e( 'The price displayed on order bump will be the difference between the regular price and the discount (based on fixed amount)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                        <p class="description vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-3<?php echo esc_attr( $ob_discount_type =='3' ?  '': ' vi-wcuf-hidden'); ?>">
															<?php esc_html_e( 'The price displayed on order bump will be the difference between the sale price and the discount (based on percentage). If your product is not on sale, it will take the regular price.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                        <p class="description vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-4<?php echo esc_attr( $ob_discount_type =='4' ?  '': ' vi-wcuf-hidden'); ?>">
															<?php esc_html_e( 'The price displayed on order bump will be the difference between the sale price and the discount (based on fixed amount). If your product is not on sale, it will take the regular price.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                        <p class="description vi-wcuf-discount-amount-notice vi-wcuf-discount-amount-notice-5<?php echo esc_attr( $ob_discount_type == '5' ?  '': ' vi-wcuf-hidden'); ?>">
															<?php esc_html_e( 'The price displayed on order bump will the new price.', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </p>
                                                    </div>
                                                    <div class="field">
                                                        <label><?php esc_html_e( 'Product quantity', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                        <input type="number" min="1" step="1" name="ob_product_qty[]" class="vi-wcuf-ob_product_qty" value="<?php echo esc_attr( $ob_product_qty ); ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Product', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                <select class="vi-wcuf-search-select2 vi-wcuf-search-product vi-wcuf-pd-condition-ob_product vi-wcuf-condition-value"
                                                        data-type_select2="product"
                                                        data-pd_include="1"
                                                        name="ob_product[]">
													<?php
													if ( $ob_product && ( $product = wc_get_product( $ob_product ) ) ) {
														echo sprintf( '<option value="%s" selected>%s</option>', $ob_product, $product->get_formatted_name() );
													}
													?>
                                                </select>
                                                <p class="description">
													<?php esc_html_e( 'Choose the product to appear', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="title">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Design', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content">
										<?php
										$ob_bg_color          = $this->settings->get_current_setting( 'ob_bg_color', $i, '' );
										$ob_padding           = $this->settings->get_current_setting( 'ob_padding', $i, '' );
										$ob_border_style      = $this->settings->get_current_setting( 'ob_border_style', $i, '' );
										$ob_border_color      = $this->settings->get_current_setting( 'ob_border_color', $i, '' );
										$ob_border_width      = $this->settings->get_current_setting( 'ob_border_width', $i, 0 );
										$ob_border_radius     = $this->settings->get_current_setting( 'ob_border_radius', $i, 0 );
										$ob_title             = $this->settings->get_current_setting( 'ob_title', $i, '' );
										$ob_title_bg_color    = $this->settings->get_current_setting( 'ob_title_bg_color', $i, '' );
										$ob_title_color       = $this->settings->get_current_setting( 'ob_title_color', $i, '' );
										$ob_title_padding     = $this->settings->get_current_setting( 'ob_title_padding', $i, '' );
										$ob_title_font_size   = $this->settings->get_current_setting( 'ob_title_font_size', $i, 0 );
										$ob_content           = $this->settings->get_current_setting( 'ob_content', $i, '' );
										$ob_image             = $this->settings->get_current_setting( 'ob_image', $i, '' );
										$ob_content_bg_color  = $this->settings->get_current_setting( 'ob_content_bg_color', $i, '' );
										$ob_content_color     = $this->settings->get_current_setting( 'ob_content_color', $i, '' );
										$ob_content_padding   = $this->settings->get_current_setting( 'ob_content_padding', $i, '' );
										$ob_content_font_size = $this->settings->get_current_setting( 'ob_content_font_size', $i, 0 );
										$ob_content_max_length = $this->settings->get_current_setting( 'ob_content_max_length', $i, 150 );
										?>
                                        <div class="field">
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_bg_color"
                                                           name="ob_bg_color[]"
                                                           value="<?php echo esc_attr( $ob_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-ob_padding"
                                                           name="ob_padding[]" value="<?php echo esc_attr( $ob_padding ); ?>"
                                                           placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Border style', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <select name="ob_border_style[]" class="vi-ui fluid dropdown vi-wcuf-ob_border_style">
                                                        <option value="none" <?php selected( $ob_border_style, 'none' ) ?>>
															<?php esc_html_e( 'None', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="dashed" <?php selected( $ob_border_style, 'dashed' ) ?>>
															<?php esc_html_e( 'Dashed', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="double" <?php selected( $ob_border_style, 'double' ) ?>>
															<?php esc_html_e( 'Double', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="dotted" <?php selected( $ob_border_style, 'dotted' ) ?>>
															<?php esc_html_e( 'Dotted', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                        <option value="solid" <?php selected( $ob_border_style, 'solid' ) ?>>
															<?php esc_html_e( 'Solid', 'woocommerce-checkout-upsell-funnel' ); ?>
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Border color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_border_color"
                                                           name="ob_border_color[]"
                                                           value="<?php echo esc_attr( $ob_border_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Border width', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui right labeled input">
                                                        <input type="number" name="ob_border_width[]" class="vi-wcuf-ob_border_width"
                                                               min="0" step="1" value="<?php echo esc_attr( $ob_border_width ); ?>">
                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Border radius', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui right labeled input">
                                                        <input type="number" name="ob_border_radius[]" class="vi-wcuf-ob_border_radius"
                                                               min="0" step="1" value="<?php echo esc_attr( $ob_border_radius ); ?>">
                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="vi-ui header dividing vi-wcuf-pd_rule-title">
												<?php esc_html_e( 'Title', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </h5>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Message', 'woocommerce-checkout-upsell-funnel' ); ?></label>
												<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                <textarea name="ob_title[]" class="vi-wcuf-ob_title" rows="3"
                                                          placeholder="<?php esc_attr_e( 'Yes! I want it!', 'woocommerce-checkout-upsell-funnel' ); ?>"><?php echo wp_kses_post( $ob_title ); ?></textarea>
                                                <p class="description">
													<?php echo sprintf( '{product_name} - %s', esc_html__( 'Product name', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                </p>
												<?php
												if ( count( $this->languages ) ) {
													foreach ( $this->languages as $key => $value ) {
														$admin::get_language_flag_html( $value, $this->languages_data );
														echo sprintf(
															'<textarea name="ob_title_%s[]" class="vi-wcuf-ob_title" rows="3" placeholder="%s">%s</textarea>',
															$value, esc_html__( 'Yes! I want it!', 'woocommerce-checkout-upsell-funnel' ), $this->settings->get_current_setting( 'ob_title', $i, '', '_' . $value ) );
													}
												}
												?>
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_title_bg_color"
                                                           name="ob_title_bg_color[]"
                                                           value="<?php echo esc_attr( $ob_title_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_title_color"
                                                           name="ob_title_color[]"
                                                           value="<?php echo esc_attr( $ob_title_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-ob_title_padding"
                                                           name="ob_title_padding[]" value="<?php echo esc_attr( $ob_title_padding ); ?>"
                                                           placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui right labeled input">
                                                        <input type="number" name="ob_title_font_size[]" class="vi-wcuf-ob_title_font_size"
                                                               min="0" step="1" value="<?php echo esc_attr( $ob_title_font_size ); ?>">
                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <h5 class="vi-ui header dividing vi-wcuf-pd_rule-title">
												<?php esc_html_e( 'Content', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </h5>
                                            <div class="field">
                                                <label><?php esc_html_e( 'Message', 'woocommerce-checkout-upsell-funnel' ); ?></label>
												<?php $admin::get_language_flag_html( $this->default_language, $this->languages_data ); ?>
                                                <textarea name="ob_content[]" class="vi-wcuf-ob_content" rows="3"><?php echo wp_kses_post( $ob_content ); ?></textarea>
                                                <p class="description">
													<?php echo sprintf( '{product_name} - %s', esc_html__( 'Product name', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                </p>
                                                <p class="description">
													<?php echo sprintf( '{product_short_desc} - %s', esc_html__( 'The short description of product', 'woocommerce-checkout-upsell-funnel' ) ) ?>
                                                </p>
												<?php
												if ( count( $this->languages ) ) {
													foreach ( $this->languages as $key => $value ) {
														$admin::get_language_flag_html( $value, $this->languages_data );
														echo sprintf(
															'<textarea name="ob_content_%s[]" class="vi-wcuf-ob_content" rows="3" >%s</textarea>',
															$value, $this->settings->get_current_setting( 'ob_content', $i, '', '_' . $value ) );
													}
												}
												?>
                                            </div>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Enable product image', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui toggle checkbox">
                                                        <input type="hidden" name="ob_image[]" class="vi-wcuf-ob_image" value="<?php echo esc_attr( $ob_image ); ?>">
                                                        <input type="checkbox" class="vi-wcuf-ob_image-checkbox" <?php checked( $ob_image, 1 ); ?>>
                                                    </div>
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Background', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_content_bg_color"
                                                           name="ob_content_bg_color[]"
                                                           value="<?php echo esc_attr( $ob_content_bg_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Color', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-color vi-wcuf-ob_content_color"
                                                           name="ob_content_color[]"
                                                           value="<?php echo esc_attr( $ob_content_color ) ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Padding', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="text" class="vi-wcuf-ob_content_padding"
                                                           name="ob_content_padding[]" value="<?php echo esc_attr( $ob_content_padding ); ?>"
                                                           placeholder="<?php echo esc_attr( '10px 15px' ); ?>">
                                                </div>
                                                <div class="field">
                                                    <label><?php esc_html_e( 'Font size', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <div class="vi-ui right labeled input">
                                                        <input type="number" name="ob_content_font_size[]" class="vi-wcuf-ob_content_font_size"
                                                               min="0" step="1" value="<?php echo esc_attr( $ob_content_font_size ); ?>">
                                                        <div class="vi-ui label vi-wcuf-basic-label"><?php echo esc_html( 'Px' ); ?></div>
                                                    </div>
                                                </div>
                                                <div class="field"
                                                     data-tooltip="<?php esc_attr_e('Button "More" will show if a message content length is greater than this value so that customers can click the button to load full message content. Leave blank to not limit this.', 'woocommerce-checkout-upsell-funnel'); ?>">
                                                    <label><?php esc_html_e( 'Max content length', 'woocommerce-checkout-upsell-funnel' ); ?></label>
                                                    <input type="number" name="ob_content_max_length[]" class="vi-wcuf-ob_content_max_length" data-wcuf_allow_empty="1"
                                                           min="0" step="1" value="<?php echo esc_attr( $ob_content_max_length ); ?>">
                                                </div>
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
												$ob_cart_rule_type = $this->settings->get_current_setting( 'ob_cart_rule_type', $id, array() );
												if ( is_array( $ob_cart_rule_type ) && count( $ob_cart_rule_type ) ) {
													foreach ( $ob_cart_rule_type as $item_type ) {
														wc_get_template( 'admin-cart-rule.php',
															array(
																'index'               => $id,
																'woo_currency_symbol' => $woo_currency_symbol,
																'woo_countries'       => $woo_countries,
																'prefix'              => 'ob_',
																'type'                => $item_type,
																$item_type            => $this->settings->get_current_setting( 'ob_' . $item_type, $id, array() ),
															),
															'',
															VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
													}
												}
												?>
                                            </div>
                                            <span class="vi-ui positive mini button vi-wcuf-add-condition-btn vi-wcuf-cart_rule-add-condition"
                                                  data-rule_type="cart" data-rule_prefix="ob_">
                                                <?php esc_html_e( 'Add Conditions(AND)', 'woocommerce-checkout-upsell-funnel' ); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="title"
                                         data-tooltip="<?php esc_attr_e( 'Choose the customers who can see the recommended products', 'woocommerce-checkout-upsell-funnel' ); ?>">
                                        <i class="dropdown icon"></i>
										<?php esc_html_e( 'Customer conditions', 'woocommerce-checkout-upsell-funnel' ); ?>
                                    </div>
                                    <div class="content">
                                        <div class="field vi-wcuf-rule-wrap-wrap vi-wcuf-user_rule-wrap-wrap">
                                            <div class="field vi-wcuf-rule-wrap vi-wcuf-user-rule-wrap vi-wcuf-user_rule-wrap">
												<?php
												$ob_user_rule_type = $this->settings->get_current_setting( 'ob_user_rule_type', $id, array() );
												if ( is_array( $ob_user_rule_type ) && count( $ob_user_rule_type ) ) {
													foreach ( $ob_user_rule_type as $item_type ) {
														wc_get_template( 'admin-user-rule.php',
															array(
																'index'               => $id,
																'woo_currency_symbol' => $woo_currency_symbol,
																'woo_users_role'      => $woo_users_role,
																'prefix'              => 'ob_',
																'type'                => $item_type,
																$item_type            => $this->settings->get_current_setting( 'ob_' . $item_type, $id, in_array( $item_type, [
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
                                                  data-rule_type="user" data-rule_prefix="ob_">
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
                    <p class="vi-wcuf-save-wrap">
                        <button type="button" class="vi-wcuf-save vi-ui primary button" name="vi-wcuf-save">
							<?php esc_html_e( 'Save', 'woocommerce-checkout-upsell-funnel' ); ?>
                        </button>
                    </p>
                </form>
            </div>
        </div>
		<?php
	}

	public function admin_enqueue_scripts() {
		$page  = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
		$admin = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Settings';
		if ( $page === 'woocommerce-checkout-upsell-funnel-ob' ) {
			$admin::remove_other_script();
			$admin::enqueue_style(
				array( 'semantic-ui-accordion', 'semantic-ui-button', 'semantic-ui-checkbox', 'semantic-ui-dropdown', 'semantic-ui-form', 'semantic-ui-header', 'semantic-ui-icon' ),
				array( 'accordion.min.css', 'button.min.css', 'checkbox.min.css', 'dropdown.min.css', 'form.min.css', 'header.min.css', 'icon.min.css' )
			);
			$admin::enqueue_style(
				array( 'semantic-ui-input', 'semantic-ui-label', 'semantic-ui-menu', 'semantic-ui-message', 'semantic-ui-popup', 'semantic-ui-segment', 'semantic-ui-tab' ),
				array( 'input.min.css', 'label.min.css', 'menu.min.css', 'message.min.css', 'popup.min.css', 'segment.min.css', 'tab.css' )
			);
			$admin::enqueue_style(
				array( 'vi-wcuf-admin-settings', 'select2', 'transition', 'minicolors' ),
				array( 'admin-settings.css', 'select2.min.css', 'transition.min.css', 'minicolors.css' )
			);
			$admin::enqueue_script(
				array( 'semantic-ui-accordion', 'semantic-ui-address', 'semantic-ui-checkbox', 'semantic-ui-dropdown', 'semantic-ui-form', 'semantic-ui-tab', 'transition' ),
				array( 'accordion.min.js', 'address.min.js', 'checkbox.min.js', 'dropdown.min.js', 'form.min.js', 'tab.js', 'transition.min.js' )
			);
			$admin::enqueue_script(
				array( 'vi-wcuf-admin-settings', 'vi-wcuf-admin-order', 'minicolors', 'select2' ),
				array( 'admin-settings.js', 'admin-order.js', 'minicolors.min.js', 'select2.js', ),
				array( array( 'jquery' ), array( 'jquery', 'jquery-ui-sortable' ) )
			);
		}
	}
}