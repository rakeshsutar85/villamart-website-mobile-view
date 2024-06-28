<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_Admin {
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter(
			'plugin_action_links_woocommerce-checkout-upsell-funnel/woocommerce-checkout-upsell-funnel.php', array(
				$this,
				'settings_link'
			)
		);
	}

	public function settings_link( $links ) {
		$settings_link = sprintf( '<a href="%s?page=woocommerce-checkout-upsell-funnel" title="%s">%s</a>', esc_attr( admin_url( 'admin.php' ) ),
			esc_attr__( 'Settings', 'woocommerce-checkout-upsell-funnel' ),
			esc_html__( 'Settings', 'woocommerce-checkout-upsell-funnel' )
		);
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce-checkout-upsell-funnel' );
		load_textdomain( 'woocommerce-checkout-upsell-funnel', VIWCUF_CHECKOUT_UPSELL_FUNNEL_LANGUAGES . "woocommerce-checkout-upsell-funnel-$locale.mo" );
		load_plugin_textdomain( 'woocommerce-checkout-upsell-funnel', false, VIWCUF_CHECKOUT_UPSELL_FUNNEL_LANGUAGES );

	}

	public function init() {
		load_plugin_textdomain( 'woocommerce-checkout-upsell-funnel' );
		$this->load_plugin_textdomain();
		if ( class_exists( 'VillaTheme_Support_Pro' ) ) {
			new VillaTheme_Support_Pro(
				array(
					'support'   => 'https://villatheme.com/supports/forum/plugins/woocommerce-checkout-upsell-funnel/',
					'docs'      => 'http://docs.villatheme.com/?item=woocommerce-checkout-upsell-funnel',
					'review'    => 'https://codecanyon.net/downloads',
					'css'       => VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS,
					'image'     => VIWCUF_CHECKOUT_UPSELL_FUNNEL_IMAGES,
					'slug'      => 'woocommerce-checkout-upsell-funnel',
					'menu_slug' => 'woocommerce-checkout-upsell-funnel',
					'version'   => VIWCUF_CHECKOUT_UPSELL_FUNNEL_VERSION
				)
			);
		}
	}
}