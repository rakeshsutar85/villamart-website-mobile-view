<?php
/**
 * Settings: Delivery Day.
 *
 * @package WC_OD/Admin/Settings
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WC_OD_Settings_Delivery_Day', false ) ) {
	return;
}

if ( ! class_exists( 'WC_OD_Settings_API', false ) ) {
	include_once WC_OD_PATH . 'includes/abstracts/abstract-class-wc-od-settings-api.php';
}

if ( ! trait_exists( 'WC_OD_Settings_Lockout' ) ) {
	include_once WC_OD_PATH . 'includes/traits/trait-wc-od-settings-lockout.php';
}

if ( ! trait_exists( 'WC_OD_Settings_Fee' ) ) {
	include_once WC_OD_PATH . 'includes/traits/trait-wc-od-settings-fee.php';
}

/**
 * WC_OD_Settings_Delivery_Day class.
 */
class WC_OD_Settings_Delivery_Day extends WC_OD_Settings_API {

	use WC_OD_Settings_Lockout;
	use WC_OD_Settings_Fee;

	/**
	 * The day ID.
	 *
	 * @var int
	 */
	public $day_id;

	/**
	 * The delivery day object.
	 *
	 * @var WC_OD_Delivery_Day
	 */
	protected $delivery_day;

	/**
	 * Constructor.
	 *
	 * @since 1.5.0
	 *
	 * @param int $day_id The delivery day ID.
	 */
	public function __construct( $day_id ) {
		$this->id     = 'delivery_days';
		$this->day_id = $day_id;
	}

	/**
	 * Gets the delivery day object.
	 *
	 * @since 1.6.0
	 *
	 * @return WC_OD_Delivery_Day
	 */
	public function get_delivery_day() {
		if ( is_null( $this->delivery_day ) ) {
			$this->delivery_day = wc_od_get_delivery_day( $this->day_id );
		}

		return $this->delivery_day;
	}

	/**
	 * Initialise form fields.
	 *
	 * @since 1.5.0
	 */
	public function init_form_fields() {
		$delivery_day = $this->get_delivery_day();

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-order-delivery' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this day for delivery', 'woocommerce-order-delivery' ),
				'default' => $delivery_day->get_enabled(),
			),
		);

		if ( ! $delivery_day->has_time_frames() ) {
			$this->form_fields = array_merge(
				$this->form_fields,
				$this->get_lockout_fields(),
				$this->get_shipping_methods_fields(),
				$this->get_fee_fields()
			);

			$this->form_fields['number_of_orders']['default']            = $delivery_day->get_number_of_orders();
			$this->form_fields['shipping_methods_option']['description'] = __( 'Choose the available shipping methods for this delivery day.', 'woocommerce-order-delivery' );
		}

		$this->form_fields['time_frames'] = array(
			'title'    => __( 'Time frames', 'woocommerce-order-delivery' ),
			'type'     => 'wc_od_table',
			'desc'     => __( 'Define the time frames for this delivery day.', 'woocommerce-order-delivery' ),
			'desc_tip' => true,
		);
	}

	/**
	 * Gets the form title.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_form_title() {
		return sprintf(
			/* translators: %s: weekday name */
			_x( 'Delivery days > %s', 'delivery day settings page title', 'woocommerce-order-delivery' ),
			wc_od_get_weekday( $this->day_id )
		);
	}

	/**
	 * Gets the form description.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_form_description() {
		return _x( 'Edit the delivery day settings.', 'settings page description', 'woocommerce-order-delivery' );
	}

	/**
	 * Outputs the backlink in the heading.
	 *
	 * @since 1.7.0
	 */
	public function output_heading_backlink() {
		wc_back_link( _x( 'Return to the shipping options', 'settings back link label', 'woocommerce-order-delivery' ), wc_od_get_settings_url() );
	}

	/**
	 * Initialise Settings.
	 *
	 * @since 1.5.0
	 */
	public function init_settings() {
		$delivery_day = $this->get_delivery_day();
		$settings     = array_merge( $this->get_form_fields_defaults(), $delivery_day->get_data_without( array( 'id', 'meta_data' ) ) );

		if ( $settings['shipping_methods_option'] ) {
			$setting_key = "{$settings['shipping_methods_option']}_shipping_methods";

			$settings[ $setting_key ] = $settings['shipping_methods'];
		}

		unset( $settings['shipping_methods'] );

		$this->settings = $settings;
	}

	/**
	 * Sanitizes the settings.
	 *
	 * @since 2.0.0
	 *
	 * @param array $settings The settings to sanitize.
	 * @return array
	 */
	public function sanitized_fields( $settings ) {
		$settings = $this->sanitize_fee_fields( $settings );

		return parent::sanitized_fields( $settings );
	}

	/**
	 * Saves the settings.
	 *
	 * @since 1.7.0
	 *
	 * @return bool was anything saved?
	 */
	public function save() {
		$settings = $this->sanitized_fields( $this->settings );

		$delivery_day = $this->get_delivery_day();
		$delivery_day->set_props( $settings );
		$delivery_day->save();

		return true;
	}
}
