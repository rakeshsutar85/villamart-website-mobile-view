<?php
/**
 * Shortcodes Tab.
 * */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'BYA_Shortcode_Tab' ) ) {
	return new BYA_Shortcode_Tab();
}

/**
 * BYA_Shortcode_Tab.
 * */
class BYA_Shortcode_Tab extends BYA_Settings_Page {

	/**
	 * Constructor.
	 * */
	public function __construct() {

		$this->id          = 'shortcodes';
		$this->label       = esc_html__( 'Shortcodes', 'buy-again-for-woocommerce' );
		$this->show_button = false;

		// Display shortcode information.
		add_action( 'woocommerce_admin_field_bya_display_shortcodes_information', array( $this, 'display_shortcodes_information' ) );
		parent::__construct();
	}

	/**
	 * Get settings for shortcodes section array.
	 * */
	public function shortcodes_section_array() {

		$section_fields = array();

		// Shortcodes Section Start.
		$section_fields[] = array(
			'type'  => 'title',
			'title' => esc_html__( 'Shortcodes', 'buy-again-for-woocommerce' ),
			'id'    => 'bya_shortcodes_options',
		);
		$section_fields[] = array(
			'type' => 'bya_display_shortcodes_information',
		);
		$section_fields[] = array(
			'type' => 'sectionend',
			'id'   => 'bya_shortcodes_options',
		);
		// Shortcodes Section End.

		return $section_fields;
	}

	/**
	 * Display shortcode information.
	 * */
	public function display_shortcodes_information() {

		$shortcodes_info = array(
			'[bya_buy_again_table]' => array(
				'supported_parameters' => 'No',
				'usage'                => esc_html__( 'Displays Buy Again Table', 'buy-again-for-woocommerce' ),
			),
		);

		include_once BYA_ABSPATH . 'inc/admin/menu/views/html-shortcodes-info.php';
	}

}

return new BYA_Shortcode_Tab();
