<?php
/**
 * Localization Tab
 *
 * @package Buy Again\Settings Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'BYA_Localization_Tab' ) ) {
	return new BYA_Localization_Tab();
}

/**
 * BYA_Localization_Tab.
 */
class BYA_Localization_Tab extends BYA_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'localization';
		$this->label = esc_html__( 'Localization', 'buy-again-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 */
	public function get_sections() {
		$sections = array();
		/**
		 * Filter to Buy Again Settings section.
		 *
		 * @since 1.0
		 * */
		return apply_filters( $this->plugin_slug . '_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings for localization section array.
	 */
	public function localization_section_array() {
		$section_fields = array();
		$buy_again_my_account_menu_slug = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );
		// Localization Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Buy Again Page', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_localization_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Buy Again My Account Page Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Buy Again',
			'id'      => $this->get_option_key( 'buy_again_menu_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Search Field Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Search Products',
			'id'      => $this->get_option_key( 'search_field_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Thumbnail Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Product Image',
			'id'      => $this->get_option_key( 'product_image_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Title Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Product Name',
			'id'      => $this->get_option_key( 'product_name_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Previously purchased quantity count & order count label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Purchased quantity & order count',
			'id'      => $this->get_option_key( 'order_count_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Previously purchased quantity count & order count', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => '[qty_count] quantity of [order_count] order(s)',
			'id'      => $this->get_option_key( 'order_count_val' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Last purchased order column label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Last Purchased Order',
			'id'      => $this->get_option_key( 'last_purchased_order_id_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Stock Count Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Stock Count',
			'id'      => $this->get_option_key( 'stock_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Price Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Product Price',
			'id'      => $this->get_option_key( 'product_price_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Quantity Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Product Quantity',
			'id'      => $this->get_option_key( 'product_quantity_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Action Column Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Action',
			'id'      => $this->get_option_key( 'action_label' ),
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Add to Cart Button Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Add to Cart',
			'id'      => $this->get_option_key( 'add_to_cart_label' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Buy Now Button Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Buy Now',
			'id'      => $this->get_option_key( 'buy_again_label' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Filter Button Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Filter',
			'id'      => $this->get_option_key( 'filter_btn_label' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Filter By label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Filter By',
			'id'      => $this->get_option_key( 'filter_by_label' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Search Button Label', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'Search',
			'id'      => $this->get_option_key( 'search_btn_label' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_localization_options',
		);
		// Localization Section End.

		return $section_fields;
	}

}

return new BYA_Localization_Tab();
