<?php
/**
 * General Tab
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'BYA_General_Tab' ) ) {
	return new BYA_General_Tab();
}

/**
 * BYA_General_Tab.
 */
class BYA_General_Tab extends BYA_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'general';
		$this->label = esc_html__( 'General', 'buy-again-for-woocommerce' );

		parent::__construct();
	}

	/**
	 * Get sections.
	 */
	public function get_sections() {
		$sections = array(
			'general' => esc_html__( 'General Settings', 'buy-again-for-woocommerce' ),
			'display' => esc_html__( 'Display Settings', 'buy-again-for-woocommerce' ),
		);
		/**
		 * Filter to Buy Again Settings section.
		 *
		 * @since 1.0
		 * */
		return apply_filters( $this->plugin_slug . '_get_sections_' . $this->id, $sections );
	}

	/**
	 * Get settings for general section array.
	 */
	public function general_section_array() {
		$section_fields = array();
		// General Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'General Settings', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_general_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Enable Buy Again', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => $this->get_option_key( 'enable_buy_again' ),
		);
		$section_fields[] = array(
			'title'    => esc_html__( 'Order Status for Purchasing Products through Buy Again', 'buy-again-for-woocommerce' ),
			'type'     => 'multiselect',
			'class'    => 'bya_select2',
			'default'  => array( 'processing', 'completed' ),
			'desc_tip' => true,
			'desc'     => esc_html__( 'Please select the order status to display buy again in user(s) order details & buy again products menu on frontend', 'buy-again-for-woocommerce' ),
			'options'  => bya_get_order_statuses(),
			'id'       => $this->get_option_key( 'order_status_to_show' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Same product Quantity display in Cart page (when Added using Normal Add  to Cart and Buy Again Add to Cart)', 'buy-again-for-woocommerce' ),
			'type'    => 'select',
			'default' => '1',
			'options' => array(
				'1' => esc_html__( 'Update only Quantity', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Display separate', 'buy-again-for-woocommerce' ),
			),
			'id'      => $this->get_option_key( 'cart_same_entry' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_general_options',
		);
		// General Section End.
		// Message Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Messages', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_general_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Previously Purchased Notice on Single Product Page', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => $this->get_option_key( 'show_buy_again_notice' ),
			'options' => array(
				'1' => esc_html__( 'Show', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Hide', 'buy-again-for-woocommerce' ),
			),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Previously Purchased Message', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'id'      => $this->get_option_key( 'buy_again_message' ),
			'default' => esc_html__( 'You have previously purchased this product [order_details]', 'buy-again-for-woocommerce' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Order Details Link Caption ', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'id'      => $this->get_option_key( 'order_detail_link_caption' ),
			'default' => esc_html__( 'Order Details', 'buy-again-for-woocommerce' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_general_options',
		);
		// Message Section End.

		return $section_fields;
	}

	/**
	 * Get settings for display section array.
	 *
	 * @since 1.0.0
	 */
	public function display_section_array() {
		$section_fields                 = array();
		$buy_again_my_account_menu_slug = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );
		$view_order_menu_slug           = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );

		// Display Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Buy Again Table', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_display_options',
		);
		$section_fields[] = array(
			'title'             => esc_html__( 'Number of Products to Display Per Page in Buy Again Table', 'buy-again-for-woocommerce' ),
			'type'              => 'number',
			'default'           => '5',
			'custom_attributes' => array( 'min' => '1' ),
			'id'                => 'bya_localization_myaccount_per_page_product_count',
			'class'             => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'    => esc_html__( 'Display Add to Cart Button on', 'buy-again-for-woocommerce' ),
			'type'     => 'multiselect',
			'class'    => 'bya_select2',
			'default'  => array( $view_order_menu_slug, $buy_again_my_account_menu_slug ),
			'desc_tip' => true,
			'desc'     => esc_html__( 'Select the page(s) to display Add to Cart Button', 'buy-again-for-woocommerce' ),
			'options'  => array(
				$view_order_menu_slug           => esc_html__( 'Order Details', 'buy-again-for-woocommerce' ),
				$buy_again_my_account_menu_slug => esc_html__( 'Buy Again Menu', 'buy-again-for-woocommerce' ),
			),
			'id'       => $this->get_option_key( 'buy_again_add_to_cart_to_show' ),
		);
		$section_fields[] = array(
			'title'    => esc_html__( 'Display Buy Again Button on', 'buy-again-for-woocommerce' ),
			'type'     => 'multiselect',
			'class'    => 'bya_select2',
			'default'  => array( $view_order_menu_slug, $buy_again_my_account_menu_slug ),
			'desc_tip' => true,
			'desc'     => esc_html__( 'Select the page(s) to display Buy Again Button', 'buy-again-for-woocommerce' ),
			'options'  => array(
				$view_order_menu_slug           => esc_html__( 'Order Details', 'buy-again-for-woocommerce' ),
				$buy_again_my_account_menu_slug => esc_html__( 'Buy Again Menu', 'buy-again-for-woocommerce' ),
			),
			'id'       => $this->get_option_key( 'buy_again_buy_now_to_show' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Enable Product Filter option', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_advanced_allow_filter_btn',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Search Box in Buy Again Table', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_search_box',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Filter By date', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => 'bya_advanced_allow_filter_by',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Product Thumbnail in Buy Again Table', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_product_image_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Product Title Column', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_product_name_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Description', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => 'bya_localization_buy_again_table_product_desc',
			'value'   => in_array( get_option( 'bya_localization_buy_again_table_product_desc', '2' ), array( '1', 'yes' ), true ) ? 'yes' : 'no',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Previously purchased quantity count & order count Column', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => 'bya_localization_allow_order_count_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Last purchased order column in Buy Again Table', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_last_purchased_order_id_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Price Column', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_product_price_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Stock Count in Buy Again Table', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => 'bya_localization_allow_stock_col',
			'class'   => 'bya_myaccount_fields',
			'desc'    => esc_html__( 'Stock Count display for each product in Buy Again table.  Note, stock count display only if you are enabled Manage stock for the  product.', 'pay-your-price-for-woocommerce' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Quantity Column', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_product_quantity_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Action Column', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_action_col',
			'class'   => 'bya_myaccount_fields',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Display Add to Cart Button', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
			'id'      => 'bya_localization_allow_add_to_cart_col',
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_display_options',
		);
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Order Details', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_order_details_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Hide Quantity Field in Order details page', 'buy-again-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'no',
			'id'      => $this->get_option_key( 'hide_qty_field_in_order_detaile_page' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_order_details_options',
		);
		// Message Section End.

		return $section_fields;
	}

}

return new BYA_General_Tab();
