<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$item_index          = $item_index ?? '';
$item_index          = $item_index ?: '{item_index}';
$index               = $index ?? '';
$index               = $index ?: '{index}';
$prefix              = $prefix ?? '';
$prefix              = $prefix ?: '{prefix}';
$params              = isset( $params ) && is_array( $params ) ? $params : array();
$type                = $type ?? 'cart_subtotal';
$woo_currency_symbol = $woo_currency_symbol ?? get_woocommerce_currency_symbol();
if ( empty( $woo_countries ) ) {
	$woo_countries = new WC_Countries();
	$woo_countries = $woo_countries->__get( 'countries' );
}
$conditions                      = array(
	'Cart Total'       => array(
		'cart_subtotal' => __( 'Cart Subtotal( total of products)', 'woocommerce-checkout-upsell-funnel' ),
		'cart_total'    => __( 'Cart Total', 'woocommerce-checkout-upsell-funnel' ),
	),
	'Cart Items'       => array(
		'cart_item_include_all' => __( 'Include all Products', 'woocommerce-checkout-upsell-funnel' ),
		'cart_item_include' => __( 'Include any Products', 'woocommerce-checkout-upsell-funnel' ),
		'cart_item_exclude' => __( 'Exclude any Products', 'woocommerce-checkout-upsell-funnel' ),
		'cart_cats_include' => __( 'Include Cart Items by Categories', 'woocommerce-checkout-upsell-funnel' ),
		'cart_cats_exclude' => __( 'Exclude Cart Items by Categories', 'woocommerce-checkout-upsell-funnel' ),
		'cart_tags_include' => __( 'Include Cart Items by Tags', 'woocommerce-checkout-upsell-funnel' ),
		'cart_tags_exclude' => __( 'Exclude Cart Items by Tags', 'woocommerce-checkout-upsell-funnel' ),
	),
	'Applied Coupon'   => array(
		'cart_coupon_include' => __( 'Include Coupon', 'woocommerce-checkout-upsell-funnel' ),
		'cart_coupon_exclude' => __( 'Exclude Coupon', 'woocommerce-checkout-upsell-funnel' ),
	),
	'Billing Address'  => array(
		'billing_countries_include' => __( 'Include Billing Countries', 'woocommerce-checkout-upsell-funnel' ),
		'billing_countries_exclude' => __( 'Exclude Billing Countries', 'woocommerce-checkout-upsell-funnel' ),
	),
	'Shipping Address' => array(
		'shipping_countries_include' => __( 'Include Shipping Countries', 'woocommerce-checkout-upsell-funnel' ),
		'shipping_countries_exclude' => __( 'Exclude Shipping Countries', 'woocommerce-checkout-upsell-funnel' ),
	),
);
$cart_subtotal_min               = $cart_subtotal['min'] ?? 0;
$cart_subtotal_max               = $cart_subtotal['max'] ?? '';
$cart_total_min                  = $cart_total['min'] ?? 0;
$cart_total_max                  = $cart_total['max'] ?? '';
$cart_item_include_all               = isset( $cart_item_include_all ) && is_array( $cart_item_include_all ) ? $cart_item_include_all : array();
$cart_item_include               = isset( $cart_item_include ) && is_array( $cart_item_include ) ? $cart_item_include : array();
$cart_item_exclude               = isset( $cart_item_exclude ) && is_array( $cart_item_exclude ) ? $cart_item_exclude : array();
$cart_cats_include               = isset( $cart_cats_include ) && is_array( $cart_cats_include ) ? $cart_cats_include : array();
$cart_cats_exclude               = isset( $cart_cats_exclude ) && is_array( $cart_cats_exclude ) ? $cart_cats_exclude : array();
$cart_tags_include               = isset( $cart_tags_include ) && is_array( $cart_tags_include ) ? $cart_tags_include : array();
$cart_tags_exclude               = isset( $cart_tags_exclude ) && is_array( $cart_tags_exclude ) ? $cart_tags_exclude : array();
$cart_coupon_include             = isset( $cart_coupon_include ) && is_array( $cart_coupon_include ) ? $cart_coupon_include : array();
$cart_coupon_exclude             = isset( $cart_coupon_exclude ) && is_array( $cart_coupon_exclude ) ? $cart_coupon_exclude : array();
$billing_countries_include       = isset( $billing_countries_include ) && is_array( $billing_countries_include ) ? $billing_countries_include : array();
$billing_countries_exclude       = isset( $billing_countries_exclude ) && is_array( $billing_countries_exclude ) ? $billing_countries_exclude : array();
$shipping_countries_include      = isset( $shipping_countries_include ) && is_array( $shipping_countries_include ) ? $shipping_countries_include : array();
$shipping_countries_exclude      = isset( $shipping_countries_exclude ) && is_array( $shipping_countries_exclude ) ? $shipping_countries_exclude : array();
$name_condition_type             = $prefix . 'cart_rule_type[' . $index . '][]';
$name_cart_subtotal_min          = $prefix . 'cart_subtotal[' . $index . '][min]';
$name_cart_subtotal_max          = $prefix . 'cart_subtotal[' . $index . '][max]';
$name_cart_total_min             = $prefix . 'cart_total[' . $index . '][min]';
$name_cart_total_max             = $prefix . 'cart_total[' . $index . '][max]';
$name_cart_item_include_all         = $prefix . 'cart_item_include_all[' . $index . '][]';
$name_cart_item_include          = $prefix . 'cart_item_include[' . $index . '][]';
$name_cart_item_exclude          = $prefix . 'cart_item_exclude[' . $index . '][]';
$name_cart_cats_include          = $prefix . 'cart_cats_include[' . $index . '][]';
$name_cart_cats_exclude          = $prefix . 'cart_cats_exclude[' . $index . '][]';
$name_cart_tags_include          = $prefix . 'cart_tags_include[' . $index . '][]';
$name_cart_tags_exclude          = $prefix . 'cart_tags_exclude[' . $index . '][]';
$name_cart_coupon_include        = $prefix . 'cart_coupon_include[' . $index . '][]';
$name_cart_coupon_exclude        = $prefix . 'cart_coupon_exclude[' . $index . '][]';
$name_billing_countries_include  = $prefix . 'billing_countries_include[' . $index . '][]';
$name_billing_countries_exclude  = $prefix . 'billing_countries_exclude[' . $index . '][]';
$name_shipping_countries_include = $prefix . 'shipping_countries_include[' . $index . '][]';
$name_shipping_countries_exclude = $prefix . 'shipping_countries_exclude[' . $index . '][]';
?>
<div class="vi-ui placeholder segment vi-wcuf-condition-wrap-wrap vi-wcuf-cart-condition-wrap-wrap">
    <div class="fields">
        <div class="four wide field">
            <select class="vi-ui fluid dropdown vi-wcuf-condition-type vi-wcuf-cart-condition-cart_rule_type"
                    data-wcuf_name="<?php echo esc_attr( $name_condition_type ) ?>"
                    data-wcuf_name_default="{prefix_default}cart_rule_type[{index_default}][]"
                    data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                    name="<?php echo esc_attr( $name_condition_type ) ?>">
				<?php
				foreach ( $conditions as $condition_group => $condition_arg ) {
					?>
                    <optgroup label="<?php esc_attr_e( $condition_group ) ?>">
						<?php
						foreach ( $condition_arg as $condition_k => $condition_v ) {
							$check = '';
							if ( $type == $condition_k ) {
								$check = 'selected';
							}
							echo sprintf( '<option value="%s" %s >%s</option>', $condition_k, $check, esc_html( $condition_v ) );
						}
						?>
                    </optgroup>
					<?php
				}
				?>
            </select>
        </div>
        <div class="thirteen wide field vi-wcuf-condition-value-wrap-wrap">
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_subtotal-wrap <?php echo $type === 'cart_subtotal' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <div class="equal width fields">
                    <div class="field">
                        <div class="vi-ui  left labeled input">
                            <div class="vi-ui label vi-wcuf-basic-label">
								<?php echo sprintf( __( 'Min(%s)', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ) ?>
                            </div>
                            <input type="number" min="0" step="0.01"
                                   name="<?php echo $type === 'cart_subtotal' ? esc_attr( $name_cart_subtotal_min ) : ''; ?>"
                                   data-wcuf_name="<?php echo esc_attr( $name_cart_subtotal_min ) ?>"
                                   data-wcuf_name_default="{prefix_default}cart_subtotal[{index_default}][min]"
                                   data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                                   class="vi-wcuf-cart-condition-cart_subtotal_min vi-wcuf-condition-value" value="<?php echo esc_attr( $cart_subtotal_min ?: 0 ) ?>">
                        </div>
                    </div>
                    <div class="field">
                        <div class="vi-ui  left labeled input">
                            <div class="vi-ui label vi-wcuf-basic-label">
								<?php echo sprintf( __( 'Max(%s)', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ) ?>
                            </div>
                            <input type="number" min="0" step="0.01"
                                   name="<?php echo $type === 'cart_subtotal' ? esc_attr( $name_cart_subtotal_max ) : ''; ?>"
                                   data-wcuf_allow_empty="1"
                                   data-wcuf_name="<?php echo esc_attr( $name_cart_subtotal_max ) ?>"
                                   data-wcuf_name_default="{prefix_default}cart_subtotal[{index_default}][max]"
                                   data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                                   placeholder="<?php esc_attr_e( 'Leave blank to not limit this', 'woocommerce-checkout-upsell-funnel' ); ?>"
                                   class="vi-wcuf-cart-condition-cart_subtotal_max vi-wcuf-condition-value" value="<?php echo esc_attr( $cart_subtotal_max ) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_total-wrap <?php echo $type === 'cart_total' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <div class="equal width fields">
                    <div class="field">
                        <div class="vi-ui  left labeled input">
                            <div class="vi-ui label vi-wcuf-basic-label">
								<?php echo sprintf( __( 'Min(%s)', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ) ?>
                            </div>
                            <input type="number" min="0" step="0.01"
                                   name="<?php echo $type === 'cart_total' ? esc_attr( $name_cart_total_min ) : ''; ?>"
                                   data-wcuf_name="<?php echo esc_attr( $name_cart_total_min ) ?>"
                                   data-wcuf_name_default="{prefix_default}cart_total[{index_default}][min]"
                                   data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                                   class="vi-wcuf-cart-condition-cart_total_min vi-wcuf-condition-value" value="<?php echo esc_attr( $cart_total_min ?: 0 ) ?>">
                        </div>
                    </div>
                    <div class="field">
                        <div class="vi-ui  left labeled input">
                            <div class="vi-ui label vi-wcuf-basic-label">
								<?php echo sprintf( __( 'Max(%s)', 'woocommerce-checkout-upsell-funnel' ), $woo_currency_symbol ) ?>
                            </div>
                            <input type="number" min="0" step="0.01"
                                   name="<?php echo $type === 'cart_total' ? esc_attr( $name_cart_total_max ) : ''; ?>"
                                   data-wcuf_allow_empty="1"
                                   data-wcuf_name="<?php echo esc_attr( $name_cart_total_max ) ?>"
                                   data-wcuf_name_default="{prefix_default}cart_total[{index_default}][max]"
                                   data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                                   placeholder="<?php esc_attr_e( 'Leave blank to not limit this', 'woocommerce-checkout-upsell-funnel' ); ?>"
                                   class="vi-wcuf-cart-condition-cart_total_max vi-wcuf-condition-value" value="<?php echo esc_attr( $cart_total_max ) ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_item_include_all-wrap <?php echo $type === 'cart_item_include_all' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select class="vi-wcuf-search-select2 vi-wcuf-search-product vi-wcuf-cart-condition-cart_item_include vi-wcuf-condition-value"
                        data-type_select2="product"
                        name="<?php echo $type === 'cart_item_include_all' ? esc_attr( $name_cart_item_include_all ) : ''; ?>"
                        data-wcuf_name_default="{prefix_default}cart_item_include_all[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_item_include_all ) ?>" multiple>
					<?php
					if ( $cart_item_include_all && is_array( $cart_item_include_all ) && count( $cart_item_include_all ) ) {
						foreach ( $cart_item_include_all as $pd_id ) {
							$product = wc_get_product( $pd_id );
							if ( $product ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $pd_id, $product->get_formatted_name() );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_item_include-wrap <?php echo $type === 'cart_item_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select class="vi-wcuf-search-select2 vi-wcuf-search-product vi-wcuf-cart-condition-cart_item_include vi-wcuf-condition-value"
                        data-type_select2="product"
                        name="<?php echo $type === 'cart_item_include' ? esc_attr( $name_cart_item_include ) : ''; ?>"
                        data-wcuf_name_default="{prefix_default}cart_item_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_item_include ) ?>" multiple>
					<?php
					if ( $cart_item_include && is_array( $cart_item_include ) && count( $cart_item_include ) ) {
						foreach ( $cart_item_include as $pd_id ) {
							$product = wc_get_product( $pd_id );
							if ( $product ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $pd_id, $product->get_formatted_name() );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_item_exclude-wrap <?php echo $type === 'cart_item_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_item_exclude' ? esc_attr( $name_cart_item_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_item_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_item_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="product"
                        class="vi-wcuf-search-select2 vi-wcuf-search-product vi-wcuf-cart-condition-cart_item_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_item_exclude && is_array( $cart_item_exclude ) && count( $cart_item_exclude ) ) {
						foreach ( $cart_item_exclude as $pd_id ) {
							$product = wc_get_product( $pd_id );
							if ( $product ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $pd_id, $product->get_formatted_name() );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_cats_include-wrap <?php echo $type === 'cart_cats_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_cats_include' ? esc_attr( $name_cart_cats_include ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_cats_include ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_cats_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="category"
                        class="vi-wcuf-search-select2 vi-wcuf-search-category vi-wcuf-cart-condition-cart_cats_include vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_cats_include && is_array( $cart_cats_include ) && count( $cart_cats_include ) ) {
						foreach ( $cart_cats_include as $cart_id ) {
							$term = get_term( $cart_id );
							if ( $term ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $cart_id, $term->name );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_cats_exclude-wrap <?php echo $type === 'cart_cats_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_cats_exclude' ? esc_attr( $name_cart_cats_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_cats_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_cats_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="category"
                        class="vi-wcuf-search-select2 vi-wcuf-search-category vi-wcuf-cart-condition-cart_cats_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_cats_exclude && is_array( $cart_cats_exclude ) && count( $cart_cats_exclude ) ) {
						foreach ( $cart_cats_exclude as $cart_id ) {
							$term = get_term( $cart_id );
							if ( $term ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $cart_id, $term->name );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_tags_include-wrap <?php echo $type === 'cart_tags_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_tags_include' ? esc_attr( $name_cart_tags_include ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_tags_include ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_tags_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="tag"
                        class="vi-wcuf-search-select2 vi-wcuf-search-category vi-wcuf-cart-condition-cart_tags_include vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_tags_include && is_array( $cart_tags_include ) && count( $cart_tags_include ) ) {
						foreach ( $cart_tags_include as $cart_id ) {
							$term = get_term( $cart_id );
							if ( $term ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $cart_id, $term->name );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_tags_exclude-wrap <?php echo $type === 'cart_tags_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_tags_exclude' ? esc_attr( $name_cart_tags_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_tags_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_tags_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="tag"
                        class="vi-wcuf-search-select2 vi-wcuf-search-category vi-wcuf-cart-condition-cart_tags_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_tags_exclude && is_array( $cart_tags_exclude ) && count( $cart_tags_exclude ) ) {
						foreach ( $cart_tags_exclude as $cart_id ) {
							$term = get_term( $cart_id );
							if ( $term ) {
								echo sprintf( '<option value="%s" selected>%s</option>', $cart_id, $term->name );
							}
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_coupon_include-wrap <?php echo $type === 'cart_coupon_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_coupon_include' ? esc_attr( $name_cart_coupon_include ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_coupon_include ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_coupon_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="coupon"
                        class="vi-wcuf-search-select2 vi-wcuf-search-coupon vi-wcuf-cart-condition-cart_coupon_include vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_coupon_include && is_array( $cart_coupon_include ) && count( $cart_coupon_include ) ) {
						foreach ( $cart_coupon_include as $coupon_code ) {
							echo sprintf( '<option value="%s" selected>%s</option>', $coupon_code, esc_html( strtoupper( $coupon_code ) ) );
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-cart_coupon_exclude-wrap <?php echo $type === 'cart_coupon_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'cart_coupon_exclude' ? esc_attr( $name_cart_coupon_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_cart_coupon_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}cart_coupon_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="coupon"
                        class="vi-wcuf-search-select2 vi-wcuf-search-coupon vi-wcuf-cart-condition-cart_coupon_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $cart_coupon_exclude && is_array( $cart_coupon_exclude ) && count( $cart_coupon_exclude ) ) {
						foreach ( $cart_coupon_exclude as $coupon_code ) {
							echo sprintf( '<option value="%s" selected>%s</option>', $coupon_code, esc_html( strtoupper( $coupon_code ) ) );
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-billing_countries_include-wrap <?php echo $type === 'billing_countries_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'billing_countries_include' ? esc_attr( $name_billing_countries_include ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_billing_countries_include ) ?>"
                        data-wcuf_name_default="{prefix_default}billing_countries_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="country"
                        class="vi-wcuf-search-select2 vi-wcuf-search-country vi-wcuf-cart-condition-billing_countries_include vi-wcuf-condition-value" multiple>
					<?php
					if ( $woo_countries && is_array( $woo_countries ) && count( $woo_countries ) ) {
						foreach ( $woo_countries as $country_id => $country_name ) {
							echo sprintf( '<option value="%s" %s>%s</option>', $country_id, in_array( $country_id, $billing_countries_include ) ? esc_attr( 'selected' ) : '', esc_html( $country_name ) );
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-billing_countries_exclude-wrap <?php echo $type === 'billing_countries_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'billing_countries_exclude' ? esc_attr( $name_billing_countries_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_billing_countries_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}billing_countries_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="country"
                        class="vi-wcuf-search-select2 vi-wcuf-search-country vi-wcuf-cart-condition-billing_countries_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $woo_countries && is_array( $woo_countries ) && count( $woo_countries ) ) {
						foreach ( $woo_countries as $country_id => $country_name ) {
							echo sprintf( '<option value="%s" %s>%s</option>', $country_id, in_array( $country_id, $billing_countries_exclude ) ? esc_attr( 'selected' ) : '', esc_html( $country_name ) );
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-shipping_countries_include-wrap <?php echo $type === 'shipping_countries_include' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'shipping_countries_include' ? esc_attr( $name_shipping_countries_include ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_shipping_countries_include ) ?>"
                        data-wcuf_name_default="{prefix_default}shipping_countries_include[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="country"
                        class="vi-wcuf-search-select2 vi-wcuf-search-country vi-wcuf-cart-condition-shipping_countries_include vi-wcuf-condition-value" multiple>
					<?php
					if ( $woo_countries && is_array( $woo_countries ) && count( $woo_countries ) ) {
						foreach ( $woo_countries as $country_id => $country_name ) {
							echo sprintf( '<option value="%s" %s>%s</option>', $country_id, in_array( $country_id, $shipping_countries_include ) ? esc_attr( 'selected' ) : '', esc_html( $country_name ) );
						}
					}
					?>
                </select>
            </div>
            <div class="field vi-wcuf-condition-wrap vi-wcuf-cart-condition-wrap vi-wcuf-condition-shipping_countries_exclude-wrap <?php echo $type === 'shipping_countries_exclude' ? '' : esc_attr( 'vi-wcuf-hidden' ); ?>">
                <select name="<?php echo $type === 'shipping_countries_exclude' ? esc_attr( $name_shipping_countries_exclude ) : ''; ?>"
                        data-wcuf_name="<?php echo esc_attr( $name_shipping_countries_exclude ) ?>"
                        data-wcuf_name_default="{prefix_default}shipping_countries_exclude[{index_default}][]"
                        data-wcuf_prefix="<?php echo esc_attr( $prefix ); ?>"
                        data-type_select2="country"
                        class="vi-wcuf-search-select2 vi-wcuf-search-country vi-wcuf-cart-condition-shipping_countries_exclude vi-wcuf-condition-value" multiple>
					<?php
					if ( $woo_countries && is_array( $woo_countries ) && count( $woo_countries ) ) {
						foreach ( $woo_countries as $country_id => $country_name ) {
							echo sprintf( '<option value="%s" %s>%s</option>', $country_id, selected( in_array( $country_id, $shipping_countries_exclude ), true ), esc_html( $country_name ) );
						}
					}
					?>
                </select>
            </div>
        </div>
        <div class="field vi-wcuf-revmove-condition-btn-wrap">
             <span class="vi-wcuf-revmove-condition-btn vi-wcuf-pd_cart_rule-revmove-condition"
                   data-tooltip="<?php esc_html_e( 'Remove', 'woocommerce-checkout-upsell-funnel' ); ?>">
                 <i class="times icon"></i>
             </span>
        </div>
    </div>
</div>
