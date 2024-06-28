<?php
/**
 * General Tab
 *
 * @package Class
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'BYA_Advanced_Tab' ) ) {
	return new BYA_Advanced_Tab();
}

/**
 * BYA_Advanced_Tab.
 */
class BYA_Advanced_Tab extends BYA_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'advanced';
		$this->label = esc_html__( 'Advanced', 'buy-again-for-woocommerce' );

		add_action( 'woocommerce_admin_field_bya_product_img_size', array( __CLASS__, 'product_img_customize_settings' ) );
		add_filter(
			'woocommerce_admin_settings_sanitize_option_bya_product_img_size',
			function() {
				if ( isset( $_REQUEST['bya_product_img_size'] ) ) {
					update_option( 'bya_product_img_size', wc_clean( wp_unslash( $_REQUEST['bya_product_img_size'] ) ) );
				}
			}
		);
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
	 * Get settings for advanced section array.
	 */
	public function advanced_section_array() {
		$section_fields = array();

		// Advanced Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Restriction Settings', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_restriction_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Buy Again will be Displayed for', 'buy-again-for-woocommerce' ),
			'type'    => 'select',
			'default' => '1',
			'id'      => $this->get_option_key( 'allow_products' ),
			'options' => array(
				'1' => esc_html__( 'All Product(s)', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Include Product(s)', 'buy-again-for-woocommerce' ),
				'3' => esc_html__( 'Exclude Product(s)', 'buy-again-for-woocommerce' ),
				'4' => esc_html__( 'Include Category', 'buy-again-for-woocommerce' ),
				'5' => esc_html__( 'Exclude Category', 'buy-again-for-woocommerce' ),
			),
		);
		$section_fields[] = array(
			'title'                   => esc_html__( 'Include Product(s)', 'buy-again-for-woocommerce' ),
			'action'                  => 'bya_json_search_products_and_variations',
			'type'                    => 'bya_custom_fields',
			'id'                      => $this->get_option_key( 'include_product' ),
			'class'                   => 'bya_allow_product_option wc-product-search',
			'placeholder'             => esc_html__( 'Select a Product', 'buy-again-for-woocommerce' ),
			'list_type'               => 'products',
			'exclude_global_variable' => 'yes',
			'bya_field'               => 'ajaxmultiselect',
			'default'                 => array(),
		);
		$section_fields[] = array(
			'title'                   => esc_html__( 'Exclude Product(s)', 'buy-again-for-woocommerce' ),
			'action'                  => 'bya_json_search_products_and_variations',
			'type'                    => 'bya_custom_fields',
			'id'                      => $this->get_option_key( 'exclude_product' ),
			'class'                   => 'bya_allow_product_option wc-product-search',
			'placeholder'             => esc_html__( 'Select a Product', 'buy-again-for-woocommerce' ),
			'list_type'               => 'products',
			'exclude_global_variable' => 'yes',
			'bya_field'               => 'ajaxmultiselect',
			'default'                 => array(),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Include Categories', 'buy-again-for-woocommerce' ),
			'type'    => 'multiselect',
			'id'      => $this->get_option_key( 'include_category' ),
			'class'   => 'bya_allow_product_option bya_select2',
			'default' => array(),
			'options' => bya_get_categories(),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Exclude Categories', 'buy-again-for-woocommerce' ),
			'type'    => 'multiselect',
			'id'      => $this->get_option_key( 'exclude_category' ),
			'class'   => 'bya_allow_product_option bya_select2',
			'default' => array(),
			'options' => bya_get_categories(),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Buy Again can be accessed by', 'buy-again-for-woocommerce' ),
			'type'    => 'select',
			'default' => '1',
			'id'      => $this->get_option_key( 'allow_users' ),
			'options' => array(
				'1' => esc_html__( 'All User(s)', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Include User(s)', 'buy-again-for-woocommerce' ),
				'3' => esc_html__( 'Exclude User(s)', 'buy-again-for-woocommerce' ),
				'4' => esc_html__( 'Include User Role(s)', 'buy-again-for-woocommerce' ),
				'5' => esc_html__( 'Exclude User Role(s)', 'buy-again-for-woocommerce' ),
			),
		);
		$section_fields[] = array(
			'title'       => esc_html__( 'Include User(s)', 'buy-again-for-woocommerce' ),
			'id'          => $this->get_option_key( 'include_user' ),
			'class'       => 'bya_allow_user_option',
			'type'        => 'bya_custom_fields',
			'bya_field'   => 'ajaxmultiselect',
			'list_type'   => 'customers',
			'placeholder' => esc_html__( 'Select a user', 'buy-again-for-woocommerce' ),
			'action'      => 'bya_json_search_user',
			'default'     => array(),
		);
		$section_fields[] = array(
			'title'       => esc_html__( 'Exclude User(s)', 'buy-again-for-woocommerce' ),
			'id'          => $this->get_option_key( 'exclude_user' ),
			'class'       => 'bya_allow_user_option',
			'type'        => 'bya_custom_fields',
			'bya_field'   => 'ajaxmultiselect',
			'list_type'   => 'customers',
			'placeholder' => esc_html__( 'Select a user', 'buy-again-for-woocommerce' ),
			'action'      => 'bya_json_search_user',
			'default'     => array(),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Include User Role', 'buy-again-for-woocommerce' ),
			'id'      => 'bya_include_user_role',
			'type'    => 'multiselect',
			'default' => '',
			'class'   => 'bya_allow_user_option bya_select2',
			'options' => bya_get_user_roles(),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Exclude User Role', 'buy-again-for-woocommerce' ),
			'id'      => 'bya_exclude_user_role',
			'type'    => 'multiselect',
			'default' => '',
			'class'   => 'bya_allow_user_option bya_select2',
			'options' => bya_get_user_roles(),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_restriction_options',
		);
		// Advanced Section End.
		// Buy Again Table Sort Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Buy Again Table Settings', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_table_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Buy Again table Sort By', 'buy-again-for-woocommerce' ),
			'type'    => 'select',
			'options' => array(
				'1' => esc_html__( 'Based on Last order', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Based on Product Name', 'buy-again-for-woocommerce' ),
			),
			'default' => '1',
			'id'      => $this->get_option_key( 'buy_again_table_sort' ),
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Product Image', 'buy-again-for-woocommerce' ),
			'type'    => 'select',
			'options' => array(
				'1' => esc_html__( 'Default', 'buy-again-for-woocommerce' ),
				'2' => esc_html__( 'Customize', 'buy-again-for-woocommerce' ),
			),
			'default' => '1',
			'id'      => $this->get_option_key( 'buy_again_table_product_img_disp' ),
		);
		$section_fields[] = array(
			'id'   => 'bya_product_img_size',
			'type' => 'bya_product_img_size',
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_table_options',
		);
		// Buy Again Table Sort Section End.
		// Slug Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Slug Settings', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_slug_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'My Account Page Menu Slug', 'buy-again-for-woocommerce' ),
			'type'    => 'text',
			'default' => 'buy-again',
			'id'      => $this->get_option_key( 'my_account_menu_slug' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_slug_options',
		);
		// slug Section End
		// Custom CSS Section Start
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Custom CSS Settings', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_custom_css_options',
		);
		$section_fields[] = array(
			'title'   => esc_html__( 'Custom CSS', 'buy-again-for-woocommerce' ),
			'type'    => 'textarea',
			'default' => '',
			'id'      => $this->get_option_key( 'custom_css' ),
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_custom_css_options',
		);
		// Custom CSS Section End

		return $section_fields;
	}

	public static function product_img_customize_settings() {
		$image_size = get_option(
			'bya_product_img_size',
			array(
				'width'  => 75,
				'height' => 50,
			)
		);
		?>
		<tr valign = "top">
			<th scope = "row" class = "titledesc">
				<label for = "bya_product_img_size"><?php esc_html_e( 'Width X Height (Values in Pixel)', 'buy-again-for-woocommerce' ); ?></label>
			</th>
			<td class = "forminp forminp-text">
				<input class="bya_product_img_size" name = "bya_product_img_size[width]" type = "text" value = "<?php echo esc_attr( $image_size['width'] ); ?>">
				<span><b>x</b></span>
				<input class="bya_product_img_size" name = "bya_product_img_size[height]" type = "text" value = "<?php echo esc_attr( $image_size['height'] ); ?>">
			</td>
		</tr>
		<?php
	}

}

return new BYA_Advanced_Tab();
