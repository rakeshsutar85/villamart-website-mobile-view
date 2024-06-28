<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Order_Bump {
	protected $settings;
	public static $frontend, $rules, $hook_action;

	public function __construct() {
		$this->settings = new  VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data();
		if ( ! $this->settings->enable( 'ob_' ) ) {
			return;
		}
		self::$frontend    = 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Frontend';
		$position          = $this->settings->get_params( 'ob_position' ) ?: 5;
		$position_args     = array(
			'1' => 'woocommerce_before_checkout_billing_form',
			'2' => 'woocommerce_after_checkout_billing_form',
			'3' => 'woocommerce_review_order_before_cart_contents',
			'4' => 'woocommerce_review_order_before_payment',
			'5' => 'woocommerce_review_order_after_payment',
		);
		if (is_plugin_active('klarna-checkout-for-woocommerce/klarna-checkout-for-woocommerce.php')){
			$position_args     = array(
				'1'      => 'kco_wc_before_wrapper',
				'2'      => 'kco_wc_before_order_review',
				'3'      => 'kco_wc_before_order_review',
				'4'      => 'kco_wc_after_order_review',
				'5'      => 'kco_wc_after_wrapper',
			);
		}
		self::$hook_action = $position_args[ $position ] ?? 'woocommerce_review_order_before_payment';
		self::$hook_action = apply_filters( 'viwcuf_ob_get_action', self::$hook_action );
		if ( ! self::$hook_action ) {
			return;
		}
		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'viwcuf_ob_woocommerce_update_order_review_fragments' ), PHP_INT_MAX, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'viwcuf_wp_enqueue_scripts' ) );
		//don't show order bump on wcaio checkout form
		add_action( 'vi_wcaio_before_checkout_form', array( $this, 'viwcaio_before_checkout_form' ) );
	}

	public function frontend_html() {
		if ( ! apply_filters( 'viwcuf_ob_enable', true ) ) {
			return;
		}
		?>
        <div class="viwcuf-checkout-ob-container"></div>
		<?php
	}

	public function viwcuf_ob_woocommerce_update_order_review_fragments( $result ) {
		if ( ! apply_filters( 'viwcuf_ob_enable', true ) ) {
			return $result;
		}
		self::$rules = VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_Ob_Cart::$rules ?? self::$frontend::get_rules( 'ob_' );
		$language    = self::$frontend::get_language();
		if ( self::$rules ) {
			ob_start();
			printf( '<div class="viwcuf-checkout-ob-container">' );
			foreach ( self::$rules as $rule_id ) {
				$shortcode_html = do_shortcode( '[viwcuf_checkout_order_bump id="' . $rule_id . '" language="' . $language . '" ]' );
				if ( ! $shortcode_html ) {
					continue;
				}
				wc_get_template( 'checkout-order-bump.php',
					array(
						'shortcode_html' => $shortcode_html,
						'rule_id'        => $rule_id
					),
					'woocommerce-checkout-upsell-funnel' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
                    VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES );
			}
			printf( '</div>' );
			$html                                    = ob_get_clean();
		}
		$result['.viwcuf-checkout-ob-container'] = $html ?? '<div class="viwcuf-checkout-ob-container"></div>';
		return $result;
	}

	public function viwcuf_wp_enqueue_scripts() {
		if ( ! is_checkout() || ! apply_filters( 'viwcuf_ob_enable', true ) || ! isset( WC()->session ) ) {
			return;
		}
		if ( ! isset( WC()->cart ) || WC()->cart->is_empty() ) {
			if ( ! class_exists( 'VIWCAIO_CART_ALL_IN_ONE_Frontend_Sidebar_Cart_Content' ) || ! VIWCAIO_CART_ALL_IN_ONE_Frontend_Sidebar_Cart_Content::$sc_checkout ) {
				return;
			}
		}
		add_action( self::$hook_action, array( $this, 'frontend_html' ) );
		$suffix = WP_DEBUG ? '' : 'min.';
		wp_enqueue_style( 'viwcuf-frontend', VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS . 'frontend.' . $suffix . 'css', array(), VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION );
		wp_enqueue_style( 'viwcuf-frontend-order', VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS . 'frontend-order.' . $suffix . 'css', array(), VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION );
		wp_enqueue_script( 'viwcuf-frontend-order', VIWCUF_CHECKOUT_UPSELL_FUNNEL_JS . 'frontend-order.' . $suffix . 'js', array( 'jquery','wc-checkout' ), VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION );
		wp_enqueue_script( 'viwcuf-frontend-swatches', VIWCUF_CHECKOUT_UPSELL_FUNNEL_JS . 'frontend-swatches.' . $suffix . 'js', array( 'jquery' ), VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION );
		wp_enqueue_style( 'viwcuf-frontend-checked_icons', VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS . 'checked-icon.min.css', array(), VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION );
		$language = '';
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default_lang     = apply_filters( 'wpml_default_language', null );
			$current_language = apply_filters( 'wpml_current_language', null );
			if ( $current_language && $current_language !== $default_lang ) {
				$language = '_' . $current_language;
			}
		} else if ( class_exists( 'Polylang' ) ) {
			$default_lang     = pll_default_language( 'slug' );
			$current_language = pll_current_language( 'slug' );
			if ( $current_language && $current_language !== $default_lang ) {
				$language = '_' . $current_language;
			}
		}
		$args = array(
			'wc_ajax_url'                => WC_AJAX::get_endpoint( "%%endpoint%%" ),
			'nonce' => wp_create_nonce('viwcuf_nonce'),
			'language'                   => $language,
			'i18n_unavailable_text'      => apply_filters( 'vi-wcuf-i18n_unavailable_text', __( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce-checkout-upsell-funnel' ) ),
			'i18n_make_a_selection_text' => apply_filters( 'vi-wcuf-i18n_make_a_selection_text', __( 'Please select some product options before adding {product_name} to your cart.', 'woocommerce-checkout-upsell-funnel' ) ),
		);
		wp_localize_script( 'viwcuf-frontend-order', 'viwcuf_frontend_ob_params', $args );
		$css = $this->get_inline_css();
		wp_add_inline_style( 'viwcuf-frontend', $css );
	}

	public function get_inline_css() {
		$css = '';
		if ( ! $this->settings->enable( 'us_' ) ) {
			$css .= str_replace( array('\n','&gt;'), array(' ','>'), wp_unslash($this->settings->get_params( 'custom_css' )) );
		}
		$ids = $this->settings->get_params( 'ob_ids' );
		if ( $ids && is_array( $ids ) && count( $ids ) ) {
			foreach ( $ids as $i => $id ) {
				$css .= $this->add_inline_style(
					array(
						'.viwcuf-checkout-ob-shortcode-' . $id . ' .vi-wcuf-ob-product-wrap',
					),
					array( 'ob_bg_color', 'ob_padding', 'ob_border_style', 'ob_border_color', 'ob_border_width', 'ob_border_radius' ),
					array( 'background', 'padding', 'border-style', 'border-color', 'border-width', 'border-radius' ),
					array( '', '', '', '', 'px', 'px' ),
					$i
				);
				$css .= $this->add_inline_style(
					array(
						'.viwcuf-checkout-ob-shortcode-' . $id . ' .vi-wcuf-ob-product-wrap .vi-wcuf-ob-product-top',
					),
					array( 'ob_title_bg_color', 'ob_title_color', 'ob_title_padding', 'ob_title_font_size' ),
					array( 'background', 'color', 'padding', 'font-size' ),
					array( '', '', '', 'px' ),
					$i
				);
				$css .= $this->add_inline_style(
					array(
						'.viwcuf-checkout-ob-shortcode-' . $id . ' .vi-wcuf-ob-product-wrap .vi-wcuf-ob-product-content',
					),
					array( 'ob_content_bg_color', 'ob_content_color', 'ob_content_padding', 'ob_content_font_size' ),
					array( 'background', 'color', 'padding', 'font-size' ),
					array( '', '', '', 'px' ),
					$i
				);
			}
		}

		return $css;
	}

	public function viwcaio_before_checkout_form() {
		if ( ! $this->settings->get_params( 'ob_vicaio_enable' ) ) {
			remove_action( self::$hook_action, array( $this, 'frontend_html' ) );
			remove_filter( 'woocommerce_update_order_review_fragments', array( $this, 'viwcuf_ob_woocommerce_update_order_review_fragments' ), PHP_INT_MAX );
		} elseif ( wp_doing_ajax()  ) {
			add_action( self::$hook_action, array( $this, 'frontend_html' ) );
		}
	}

	private function add_inline_style( $element, $name, $style, $suffix = '', $index = 0 ) {
		if ( ! $element || ! is_array( $element ) ) {
			return '';
		}
		$element = implode( ',', $element );
		$return  = $element . '{';
		if ( is_array( $name ) && count( $name ) ) {
			foreach ( $name as $key => $value ) {
				$get_value  = $this->settings->get_current_setting( $value, $index );
				$get_suffix = $suffix[ $key ] ?? '';
				$return     .= $style[ $key ] . ':' . $get_value . $get_suffix . ';';
			}
		}
		$return .= '}';

		return $return;
	}
}