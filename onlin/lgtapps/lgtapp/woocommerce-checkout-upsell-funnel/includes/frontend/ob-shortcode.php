<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Ob_Shortcode {
	protected $settings, $frontend;

	public function __construct() {
		$this->settings = new  VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		if ( ! $this->settings->enable( 'ob_' ) ) {
			return;
		}
		$this->frontend = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Frontend';
		add_action( 'init', array( $this, 'shortcode_init' ) );
		add_action( 'viwcuf_ob_simple_add_to_cart', array( $this, 'viwcuf_ob_simple_add_to_cart' ), 10, 5 );
		add_action( 'viwcuf_ob_variable_add_to_cart', array( $this, 'viwcuf_ob_variable_add_to_cart' ), 10, 5 );
		add_action( 'viwcuf_ob_variation_add_to_cart', array( $this, 'viwcuf_ob_variation_add_to_cart' ), 10, 5 );
	}

	public function shortcode_init() {
		add_shortcode( 'viwcuf_checkout_order_bump', array( $this, 'viwcuf_checkout_order_bump' ) );
	}

	public function viwcuf_checkout_order_bump( $atts ) {
		extract( shortcode_atts( array(
			'id'       => '',
			'language' => '',
		), $atts ) );
		if ( ! $id ) {
			return false;
		}
		$ids   = $this->settings->get_params( 'ob_ids' ) ?? array();
        $ids = (array)$ids;
		$index = array_search( $id, $ids );
		if ( $index === false || ! $this->settings->get_current_setting( 'ob_active', $index, '' ) ) {
			return false;
		}
		$product_id = $this->settings->get_current_setting( 'ob_product', $index, '' );
		if ( ! $product_id || ! ( $product = wc_get_product( $product_id ) ) ) {
			return false;
		}
		if ( ! $product->is_in_stock() ) {
			return false;
		}
		if (  class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
			return false;
		}
		$ob_in_cart = $this->frontend::get_pd_qty_in_cart( $product_id, 'viwcuf_ob_product', $id );
		if ( $ob_in_cart ) {
			$product_qty    = $ob_in_cart;
			$cart_item_info = $this->frontend::get_cart_item( $product_id, 'viwcuf_ob_product', $id );
			$cart_item_data = '';
			if ( ! empty( $cart_item_info['product_id'] ) ) {
				$cart_item_data .= 'data-added_id=' . $cart_item_info['product_id'] . ' ';
			}
			if ( ! empty( $cart_item_info['cart_item_key'] ) ) {
				$cart_item_data .= 'data-cart_item_key=' . $cart_item_info['cart_item_key'] . ' ';
			}
			if ( ! empty( $cart_item_info['variation'] ) ) {
				$variations_json = wp_json_encode( $cart_item_info['variation'] );
				$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
			}
		} else {
			$in_cart     = $this->frontend::get_pd_qty_in_cart( $product_id );
			$product_qty = intval( $this->settings->get_current_setting( 'ob_product_qty', $index, 1 ) );
			if ( $product->is_sold_individually() ) {
				if ( $in_cart ) {
					return false;
				}
				$product_qty = $product_qty ? 1 : 0;
			} else {
				$max_qty     = $product->get_max_purchase_quantity() - $in_cart;
				$product_qty = $max_qty < 0 || $max_qty > $product_qty ? $product_qty : $max_qty;
			}
		}
		if ( ! $product_qty ) {
			return false;
		}
		$discount_type   = $this->settings->get_current_setting( 'ob_discount_type', $index, '3' );
		$discount_amount = $this->settings->get_current_setting( 'ob_discount_amount', $index, 10 );
		$ob_title        = $this->settings->get_current_setting( 'ob_title', $index, '', $language );
		$ob_image        = $this->settings->get_current_setting( 'ob_image', $index, '' );
		$ob_content      = $this->settings->get_current_setting( 'ob_content', $index, '', $language );
		$ob_pd_class     = array( 'vi-wcuf-product vi-wcuf-ob-product-wrap' );
		$ob_pd_class[]   = $ob_in_cart ? 'vi-wcuf-ob-product-wrap-checked vi-wcuf-product-wrap-checked' : '';
		$ob_pd_class[]   = is_rtl() ? 'vi-wcuf-ob-product-wrap-rtl' : '';
		$ob_pd_class     = trim( implode( ' ', $ob_pd_class ) );
		ob_start();
		?>
        <div class="<?php echo esc_attr( $ob_pd_class ); ?>"
             data-product_id="<?php echo esc_attr( $product_id ); ?>"
             data-variation="<?php echo esc_attr( !empty($variations_attr) ?$variations_attr :'' ); ?>"
            <?php echo ! empty( $cart_item_data ) ? esc_attr( $cart_item_data ) : ''; ?>>
            <div class="vi-wcuf-ob-product-top">
                <div class="vi-wcuf-ob-title-wrap">
					<?php
					echo wp_kses_post( apply_filters( 'vi_wcuf_ob_checkbox_html', '<span class="vi-wcuf-ob-checkbox"></span>' ) );
					if ( $ob_title ) {
						?>
                        <div class="vi-wcuf-ob-title">
							<?php echo wp_kses_post( $ob_title ); ?>
                        </div>
						<?php
					}
					?>
                </div>
                <div class="vi-wcuf-ob-price">
					<?php
					$this->frontend::product_price_html( $product, $discount_type, $discount_amount );
					?>
                </div>
            </div>
            <div class="vi-wcuf-ob-product-content">
                <div class="vi-wcuf-ob-product-desc-wrap">
			        <?php
			        if ( $ob_image ) {
                        if ( is_plugin_active('litespeed-cache/litespeed-cache.php')){
	                        if ( function_exists( 'wp_calculate_image_srcset' ) ) {
		                        remove_all_filters('wp_calculate_image_srcset');
	                        }
	                        remove_all_filters('wp_get_attachment_image_src');
	                        remove_all_filters('wp_get_attachment_url');
                        }
				        ?>
                        <div class="vi-wcuf-ob-product-image">
					        <?php
					        $product_img = $product->get_image( 'woocommerce_thumbnail' );
					        echo wp_kses_post( $product_img );
					        ?>
                        </div>
				        <?php
			        }
			        if ( $ob_content ) {
				        ?>
                        <div class="vi-wcuf-ob-product-desc">{ob_content}</div>
				        <?php
			        }
			        do_action( 'viwcuf_ob_' . $product->get_type() . '_add_to_cart', $product, $product_qty, $id, $discount_type, $discount_amount );
			        ?>
                </div>
            </div>
        </div>
		<?php
		$html = ob_get_clean();
		$html = str_replace( '{product_name}', $product->get_name(), $html );
		$html = str_replace( '{ob_content}',$this->get_ob_content($index,$ob_content, $product), $html );
		return $html;
	}
	public function get_ob_content($index,$ob_content,$product){
	    if (!$ob_content || !$product){
	        return '';
        }
		$ob_content = str_replace( "\n",'<br>', $ob_content );
	    ob_start();
		echo wp_kses_post( $ob_content );
		$html = ob_get_clean();
		$html = str_replace( '{product_name}', $product->get_name(), $html );
		$html = str_replace( '{product_short_desc}', $product->get_short_description(), $html );
		$ob_content_max_length = $this->settings->get_current_setting('ob_content_max_length',$index, 150);
		if (!is_numeric($ob_content_max_length)){
			return $html;
        }
		$ob_content_max_length = intval($this->settings->get_current_setting('ob_content_max_length',$index, 150));
		$html_length   = function_exists( 'mb_strlen' ) ? mb_strlen( $html ) : strlen( $html );
		if ( $html_length >  $ob_content_max_length){
			$html_t = function_exists( 'mb_substr' ) ? mb_substr( $html, 0, $ob_content_max_length ) : substr( $html, 0, $ob_content_max_length );
			$html = '<div class="vi-wcuf-ob-product-desc-short">' . $html_t . '<span class="vi-wcuf-ob-product-desc-read vi-wcuf-ob-product-desc-read-more" title="' . esc_html__( 'Read more', 'woocommerce-checkout-upsell-funnel' ) . '">' . esc_html__( '...More',  'woocommerce-checkout-upsell-funnel' ) . '</span></div><div class="vi-wcuf-ob-product-desc-full vi-wcuf-disable">' . $html. '<span class="vi-wcuf-ob-product-desc-read vi-wcuf-ob-product-desc-read-short" title="' . esc_html__( 'Hidden', 'woocommerce-checkout-upsell-funnel' ) . '">' . esc_html__( '(Short)',  'woocommerce-checkout-upsell-funnel' ) . '</span></div>';
        }
		return $html;
    }

	public function viwcuf_ob_simple_add_to_cart( $product, $product_qty, $rule_id, $discount_type, $discount_amount ) {
		$product_id = $product->get_id();
		?>
        <div class="vi-wcuf-ob-cart-form" data-product_id="<?php echo esc_attr( $product_id ); ?>">
            <input type="hidden" name="quantity" value="<?php echo esc_attr( $product_qty ); ?>"/>
            <input type="hidden" name="add-to-cart" class="vi-wcuf-add-to-cart" value=""/>
            <input type="hidden" name="product_id" class="vi-wcuf-product_id" value=""/>
            <input type="hidden" name="variation_id" class="variation_id" value="0"/>
            <input type="hidden" name="viwcuf_ob_product_id" class="viwcuf_ob_product_id" value="1"/>
            <input type="hidden" name="viwcuf_ob_info[rule_id]" class="viwcuf_ob_rule_id" value="<?php echo esc_attr( $rule_id ); ?>"/>
            <input type="hidden" name="viwcuf_ob_info[discount_type]" class="viwcuf_ob_discount_type" value="<?php echo esc_attr( $discount_type ); ?>"/>
            <input type="hidden" name="viwcuf_ob_info[discount_amount]" class="viwcuf_ob_discount_amount" value="<?php echo esc_attr( $discount_amount ); ?>"/>
        </div>
		<?php
	}

	public function viwcuf_ob_variable_add_to_cart( $product, $product_qty, $rule_id, $discount_type, $discount_amount ) {
		$attributes = $product->get_variation_attributes();
		if ( empty( $attributes ) ) {
			return;
		}
		$product_id          = $product->get_id();
		$product_name        = $product->get_name();
		$variation_count     = count( $product->get_children() );
		$get_variations      = $variation_count <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
		$selected_attributes = $product->get_default_attributes();
		if ( $get_variations ) {
			$available_variations = $product->get_available_variations();
			if ( empty( $available_variations ) ) {
				return;
			}
			$available_variations_t = array();
			foreach ( $available_variations as $k => $variation ) {
				$variation_id     = $variation['variation_id'] ?? 0;
				$variation_object = wc_get_product( absint( $variation_id ) );
				if ( ! $variation_object || ! $variation_object->is_in_stock() ) {
					continue;
				}
				if ( $variation_object->managing_stock() && $variation_object->get_stock_quantity() <= get_option( 'woocommerce_notify_no_stock_amount', 0 ) && 'no' === $variation_object->get_backorders() ) {
					continue;
				}
				ob_start();
				$this->frontend::product_price_html( $variation_object, $discount_type, $discount_amount );
				$price_html                     = ob_get_clean();
				$variation['viwcuf_price_html'] = $price_html;
				$available_variations_t[]       = $variation;
			}
			$variations_json = wp_json_encode( $available_variations_t );
			$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );
		} else {
			$variations_attr = false;
		}
		?>
        <div class="vi-wcuf-ob-cart-form vi-wcuf-cart-form-swatches vi-wcuf-cart-form-variable" data-product_id="<?php echo esc_attr( $product_id ); ?>"
             data-product_name="<?php echo esc_attr( $product_name ); ?>"
             data-variation_count="<?php echo esc_attr( $variation_count ); ?>"
             data-product_variations="<?php echo esc_attr( $variations_attr ); ?>">
            <div class="vi-wcuf-swatches-wrap-wrap">
				<?php
				foreach ( $attributes as $attribute_name => $options ) {
					$selected = $selected_attributes[ $attribute_name ] ?? $product->get_variation_default_attribute( $attribute_name );
					echo sprintf( '<div class="vi-wcuf-swatches-wrap"><div class="vi-wcuf-swatches-value value" data-selected="%s">' ,esc_attr($selected));
					wc_dropdown_variation_attribute_options( apply_filters( 'vi_wcuf_ob_dropdown_variation_attribute_options', array(
						'options'                 => $options,
						'attribute'               => $attribute_name,
						'product'                 => $product,
						'selected'                => $selected,
						'class'                   => 'viwcuf-attribute-options',
						'viwpvs_swatches_disable' => 0,
					), $attribute_name, $product ) );
					echo sprintf( '</div></div>' );
				}
				?>
            </div>
            <div class="single_variation_wrap">
                <div class="woocommerce-variation single_variation"></div>
                <div class="woocommerce-variation-add-to-cart variations_button">
                    <input type="hidden" name="quantity" value="<?php echo esc_attr( $product_qty ); ?>"/>
                    <input type="hidden" name="add-to-cart" class="vi-wcuf-add-to-cart" value=""/>
                    <input type="hidden" name="product_id" class="vi-wcuf-product_id" value=""/>
                    <input type="hidden" name="variation_id" class="variation_id" value="0"/>
                    <input type="hidden" name="viwcuf_ob_product_id" class="viwcuf_ob_product_id" value="1"/>
                    <input type="hidden" name="viwcuf_ob_info[rule_id]" class="viwcuf_ob_rule_id" value="<?php echo esc_attr( $rule_id ); ?>"/>
                    <input type="hidden" name="viwcuf_ob_info[discount_type]" class="viwcuf_ob_discount_type" value="<?php echo esc_attr( $discount_type ); ?>"/>
                    <input type="hidden" name="viwcuf_ob_info[discount_amount]" class="viwcuf_ob_discount_amount" value="<?php echo esc_attr( $discount_amount ); ?>"/>
                </div>
            </div>
        </div>
		<?php
	}

	public function viwcuf_ob_variation_add_to_cart( $product, $product_qty, $rule_id, $discount_type, $discount_amount ) {
		$product_id    = $product->get_id();
		$product_name  = $product->get_name();
		$pd_parent_ids = $product->get_parent_id();
		$attributes    = $product->get_attributes();
		if ( empty( $attributes ) ) {
			return;
		}
		$count_value = 0;
		foreach ( $attributes as $attribute_name => $options ) {
			if ( $options ) {
				$count_value ++;
			}
		}
		$div_class = array( 'vi-wcuf-swatches-wrap-wrap' );
		if ( $count_value < count( $attributes ) ) {
			$product_parent = wc_get_product( $pd_parent_ids );
			$parent_attr    = $product_parent->get_variation_attributes();
		} else {
			$div_class[] = 'vi-wcuf-disable';
		}
		$div_class = implode( ' ', $div_class );
		?>
        <div class="vi-wcuf-ob-cart-form vi-wcuf-cart-form-swatches" data-product_id="<?php echo esc_attr( $product_id ); ?>"
             data-product_name="<?php echo esc_attr( $product_name ); ?>">
            <div class="<?php echo esc_attr( $div_class ) ?>">
				<?php
				foreach ( $attributes as $attribute_name => $options ) {
					if ( $options ) {
						$name = 'attribute_' . sanitize_title( $attribute_name );
						?>
                        <div class="vi-wcuf-swatches-wrap vi-wcuf-disable">
                            <input type="hidden" id="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" class="viwcuf-attribute-options"
                                   name="<?php echo esc_attr( $name ) ?>" data-attribute_name="<?php echo esc_attr( $name ); ?>"
                                   value="<?php echo esc_attr( $options ); ?>">
                        </div>
						<?php
					} else {
						$attribute   = wc_attribute_label( $attribute_name, $product_parent ?? $product );
						$options     = $parent_attr[ $attribute_name ] ?? $parent_attr[ $attribute ] ?? $options;
						$attribute_t = isset( $parent_attr[ $attribute_name ] ) ? $attribute_name : $attribute;
						echo sprintf( '<div class="vi-wcuf-swatches-wrap"><div class="vi-wcuf-swatches-value value">' );
						wc_dropdown_variation_attribute_options( apply_filters( 'vi_wcuf_ob_dropdown_variation_attribute_options', array(
							'options'                 => $options,
							'attribute'               => $attribute_t,
							'product'                 => $product_parent ?? '',
							'class'                   => 'viwcuf-attribute-options',
							'viwpvs_swatches_disable' => 0,
						), $attribute_name, $product ) );
						echo sprintf( '</div></div>' );
					}
				}
				?>
            </div>
            <div class="single_variation_wrap">
                <div class="woocommerce-variation single_variation"></div>
                <div class="woocommerce-variation-add-to-cart variations_button">
                    <input type="hidden" name="quantity" value="<?php echo esc_attr( $product_qty ); ?>"/>
                    <input type="hidden" name="add-to-cart" class="vi-wcuf-add-to-cart" value=""/>
                    <input type="hidden" name="product_id" class="vi-wcuf-product_id" value=""/>
                    <input type="hidden" name="variation_id" class="variation_id" value="<?php echo esc_attr( $product_id ); ?>"/>
                    <input type="hidden" name="viwcuf_ob_product_id" class="viwcuf_ob_product_id" value="1"/>
                    <input type="hidden" name="viwcuf_ob_info[rule_id]" class="viwcuf_ob_rule_id" value="<?php echo esc_attr( $rule_id ); ?>"/>
                    <input type="hidden" name="viwcuf_ob_info[discount_type]" class="viwcuf_ob_discount_type" value="<?php echo esc_attr( $discount_type ); ?>"/>
                    <input type="hidden" name="viwcuf_ob_info[discount_amount]" class="viwcuf_ob_discount_amount" value="<?php echo esc_attr( $discount_amount ); ?>"/>
                </div>
            </div>
        </div>
		<?php
	}
}
