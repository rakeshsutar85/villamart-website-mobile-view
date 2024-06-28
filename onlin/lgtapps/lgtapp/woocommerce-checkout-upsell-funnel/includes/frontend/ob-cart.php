<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Ob_Cart {
	protected $settings, $cache;
	public static $rules;

	public function __construct() {
		$this->settings = new  VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		if ( ! $this->settings->enable( 'ob_' ) ) {
			return;
		}
		// check for existing item in cart.
		add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', array( __CLASS__, 'viwcuf_woocommerce_add_to_cart_sold_individually_found_in_cart' ), PHP_INT_MAX, 5 );
		//remove product ob in cart
		add_filter( 'woocommerce_after_calculate_totals', array( $this, 'viwcuf_ob_woocommerce_after_calculate_totals' ) );

		// change product quantity on cart page, wcaio sidebar cart
		add_filter( 'woocommerce_cart_item_quantity', array( $this, 'viwcuf_ob_woocommerce_cart_item_quantity' ), PHP_INT_MAX, 3 );
		add_filter( 'vi_wcaio_mini_cart_pd_qty', array( $this, 'viwcuf_ob_wcaio_mini_cart_pd_qty' ), PHP_INT_MAX, 3 );

		//set new price
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'viwcuf_ob_woocommerce_add_cart_item_data' ), PHP_INT_MAX, 1 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'viwcuf_ob_mark_as_cart_item' ), 10, 1 );
		add_filter( 'woocommerce_product_get_price', array( $this, 'viwcuf_ob_product_get_price' ), PHP_INT_MAX, 2 );
		add_filter( 'woocommerce_product_variation_get_price', array( $this, 'viwcuf_ob_product_get_price' ), PHP_INT_MAX, 2 );
		add_filter( 'viredis_get_price', array( $this, 'viredis_get_price' ), PHP_INT_MAX, 5 );

		if ( ! $this->settings->get_params( 'ob_cart_coupon_enable' ) ) {
			add_filter( 'woocommerce_coupon_get_discount_amount', array( $this, 'viwcuf_ob_woocommerce_coupon_get_discount_amount' ), PHP_INT_MAX, 5 );
		}
	}
	public static function viwcuf_woocommerce_add_to_cart_sold_individually_found_in_cart($result, $product_id, $variation_id, $cart_item_data, $cart_id ){
		if (empty($cart_item_data['viwcuf_ob_product'])&& !$result){
			$result = VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Frontend::get_pd_qty_in_cart($product_id,'viwcuf_ob_product');
		}
		return $result;
	}

	public function viwcuf_ob_woocommerce_after_calculate_totals( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return $cart;
		}
		if ( $cart->is_empty() ) {
			return $cart;
		}
		if ( ! wp_doing_ajax() ) {
			return $cart;
		}
		$rule_ids    = $this->settings->get_params( 'ob_ids' );
		self::$rules = VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Frontend::get_rules( 'ob_' );
		$cart_items  = $cart->get_cart();
		foreach ( $cart_items as $key => $cart_item ) {
			if ( ! empty( $cart_item['viwcuf_ob_product'] ) ) {
				$rule_id = $cart_item['viwcuf_ob_product']['rule_id'] ?? '';
				if ( ! $rule_id || empty( self::$rules ) || ! in_array( $rule_id, self::$rules ) ) {
					$cart->remove_cart_item( $key );
					continue;
				}
				$index = array_search( $rule_id, $rule_ids );
				if ( $index === false || ! $this->settings->get_current_setting( 'ob_active', $index, '' ) ) {
					$cart->remove_cart_item( $key );
					continue;
				}
				$product_id = $this->settings->get_current_setting( 'ob_product', $index, '' );
				if ( ! $product_id || ( $product_id != $cart_item['product_id'] && $product_id != $cart_item['variation_id'] ) ) {
					$cart->remove_cart_item( $key );
				}
			}
		}
	}

	public function viwcuf_ob_woocommerce_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
		if ( empty( $cart_item['viwcuf_ob_product'] ) ) {
			return $product_quantity;
		}
		$product_quantity = sprintf( '%s <input type="hidden" name="cart[%s][qty]" value="%s" />', $cart_item['quantity'], $cart_item_key, $cart_item['quantity'] );

		return $product_quantity = apply_filters( 'viwcuf_ob_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
	}

	public function viwcuf_ob_wcaio_mini_cart_pd_qty( $product_quantity, $cart_item_key, $cart_item ) {
		if ( empty( $cart_item['viwcuf_ob_product'] ) ) {
			return $product_quantity;
		}
		$product_quantity = sprintf( '<div class="vi-wcaio-sidebar-cart-pd-quantity vi-wcaio-hidden"><input type="hidden" name="viwcaio_cart[%s][qty]" value="%s"></div>', $cart_item_key, $cart_item['quantity'] );

		return $product_quantity = apply_filters( 'viwcuf_ob_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
	}

	public function viwcuf_ob_woocommerce_add_cart_item_data( $cart_item_data ) {
		if ( isset( $_REQUEST['viwcuf_ob_product_id'], $_REQUEST['viwcuf_ob_info'] ) ) {
			$cart_item_data['viwcuf_ob_product'] = viwcuf_sanitize_fields( $_REQUEST['viwcuf_ob_info'] );
		}

		return $cart_item_data;
	}

	public function viwcuf_ob_mark_as_cart_item( $cart_item_data ) {
		if ( isset( $cart_item_data['viwcuf_ob_product'] ) ) {
			$cart_item_data['data']->viwcuf_ob_product = $cart_item_data['key'];
			$cart_item_data['data']->viwcuf_ob_info    = $cart_item_data['viwcuf_ob_product'];
		}

		return $cart_item_data;
	}

	public function viredis_get_price( $current_price, $price, $product, $rules, $product_qty ) {
		if ( ! $product ) {
			return $current_price;
		}
		$viwcuf_ob_product = $product->viwcuf_ob_product ?? '';
		$viwcuf_ob_info    = $product->viwcuf_ob_info ?? '';
		if ( ! $viwcuf_ob_product || empty( $viwcuf_ob_info ) ) {
			return $current_price;
		}
		$this->cache[ 'viredis_get_price_' . ( $product_id = $product->get_id() ) ] = true;
		$current_price                                                              = $this->viwcuf_ob_product_get_price( $current_price, $product );
		unset( $this->cache[ 'viredis_get_price_' . $product_id ] );
		return $current_price;
	}
	public function viwcuf_ob_product_get_price( $price, $product ) {
		if ( ! $price || ! $product ) {
			return $price;
		}
		if ( ! did_action( 'woocommerce_cart_loaded_from_session' ) ) {
			return $price;
		}
		if ( ! empty( $product->viredis_cart_item ) && ! isset( $this->cache[ 'viredis_get_price_' . $product->get_id() ] ) ) {
			return $price;
		}
		$viwcuf_ob_product = $product->viwcuf_ob_product ?? '';
		$viwcuf_ob_info    = $product->viwcuf_ob_info ?? '';
		if ( ! $viwcuf_ob_product || empty( $viwcuf_ob_info ) ) {
			return $price;
		}
		if ( isset( $this->cache[ $viwcuf_ob_product ][ $price ] ) ) {
			return $this->cache[ $viwcuf_ob_product ][ $price ];
		}
		if ( $viwcuf_ob_info && is_array( $viwcuf_ob_info ) && count( $viwcuf_ob_info ) ) {
			$discount_type   = $viwcuf_ob_info['discount_type'] ?? '';
			$discount_amount = $viwcuf_ob_info['discount_amount'] ?? 0;
			$regular_price   = in_array( $discount_type, [ '1', '2' ] ) ? (float) $product->get_regular_price() : $price;
			$discount_amount = $discount_amount ?: 0;
			$new_price       = VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Frontend::set_new_price_pd( $price, $regular_price, $discount_type, $discount_amount );
		}

		return $this->cache[ $viwcuf_ob_product ][ $price ] = apply_filters( 'viwcuf_ob_product_get_price', $new_price ?? $price, $product );
	}

	public function viwcuf_ob_woocommerce_coupon_get_discount_amount( $result, $number_precision, $item, $bool, $coupon ) {
		if ( isset( $item['viwcuf_ob_product'] ) ) {
			$result = 0;
		}

		return $result;
	}
}