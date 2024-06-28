<?php
/**
 * GDPR Privacy
 *
 * @package     saved-addresses-for-woocommerce/includes/admin/
 * @since       2.0.0
 * @version     1.0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Abstract_Privacy' ) ) {
	return;
}

if ( ! class_exists( 'SAW_Privacy' ) ) {

	/**
	 * Main class for handling privacy for Saved Addresses For WooCommerce plugin
	 */
	class SAW_Privacy extends WC_Abstract_Privacy {

		/**
		 * Variable to hold instance of WC_SC_Privacy
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			// To show this plugin's privacy message in Privacy Policy Guide page on your admin dashboard.
			parent::__construct( __( 'Saved Addresses For WooCommerce', 'saved-addresses-for-woocommerce' ) );

			// GDPR - Register Data Exporter.
			add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'saw_register_exporter' ), 10 );

			// GDPR - Register Data Eraser.
			add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'saw_register_eraser' ), 10 );
		}

		/**
		 * Get single instance of WC_SC_Privacy
		 *
		 * @return WC_SC_Privacy Singleton object of WC_SC_Privacy
		 */
		public static function get_instance() {
			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Gets the message of the privacy to display.
		 */
		public function saw_add_privacy_message() {
			if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
				$content = $this->get_privacy_message();

				if ( $content ) {
					wp_add_privacy_policy_content( __( 'Saved Addresses For WooCommerce', 'saved-addresses-for-woocommerce' ), $content );
				}
			}
		}

		/**
		 * Gets the message of the privacy to display.
		 */
		public function get_privacy_message() {
			return '<div contenteditable="false">' .
						'<p class="wp-policy-help">' .
						__( 'For registered users, we save your every unique billing and shipping address. Those addresses will be avaiable to you during the checkout process. User can also modify the addresses from their My Account > Address page.', 'saved-addresses-for-woocommerce' ) .
						'</p>' .
					'</div>';
		}

		/**
		 * Function to register callback for data exporter
		 *
		 * @param  array $exporters Exporters.
		 * @return array $exporters Exporters with SAW Privacy data exporter.
		 */
		public function saw_register_exporter( $exporters = array() ) {
			$exporters['saw-customer-addresses'] = array(
				'exporter_friendly_name' => __( 'Saved Shipping Addresses', 'saved-addresses-for-woocommerce' ),
				'callback'               => array( 'SAW_Privacy', 'saved_addresses_data_exporter' ),
			);

			return $exporters;
		}

		/**
		 * Function to register callback for data eraser
		 *
		 * @param  array $exporters Exporters.
		 * @return array $exporters Exporters with SAW Privacy data exporter.
		 */
		public function saw_register_eraser( $exporters = array() ) {
			$exporters['saw-customer-addresses'] = array(
				'eraser_friendly_name' => __( 'Saved Shipping Addresses', 'saved-addresses-for-woocommerce' ),
				'callback'             => array( 'SAW_Privacy', 'saved_addresses_data_eraser' ),
			);

			return $exporters;
		}

		/**
		 * Finds and exports saved addresses by email address.
		 *
		 * @param string $email_address The user email address.
		 * @param int    $page  Page.
		 * @return array An array of personal data in name value pairs
		 */
		public static function saved_addresses_data_exporter( $email_address, $page ) {
			$user           = get_user_by( 'email', $email_address );
			$data_to_export = array();

			if ( $user instanceof WP_User ) {
				$saved_addresses = self::get_saved_address( $user->ID );

				if ( ! empty( $saved_addresses ) ) {
					foreach ( $saved_addresses as $index => $saved_address ) {
						$data_to_export[] = array(
							'group_id'    => 'saw_data',
							'group_label' => __( 'Saved Shipping Addresses', 'saved-addresses-for-woocommerce' ),
							'item_id'     => 'saved_addresses_' . $index,
							'data'        => self::saw_get_personal_data_to_export( $saved_address ),
						);
					}
				}
			}

			return array(
				'data' => $data_to_export,
				'done' => true,
			);
		}

		/**
		 * Get all saved addresses for a user
		 *
		 * @param int $user_id ID of the registered user.
		 * @return array $saved_addresses Saved addresses of a user
		 */
		public static function get_saved_address( $user_id ) {
			$saved_addresses = array();
			$saved_addresses = get_user_meta( $user_id, 'sa_saved_shipping_addresses', true );

			return $saved_addresses;
		}

		/**
		 * Get personal data to export
		 *
		 * @param array $saved_address Saved addresses of a user.
		 * @return array $personal_data Personal data to export
		 */
		public static function saw_get_personal_data_to_export( $saved_address ) {
			$personal_data = array();

			$data_to_export = array(
				'first_name' => __( 'First Name', 'saved-addresses-for-woocommerce' ),
				'last_name'  => __( 'Last Name', 'saved-addresses-for-woocommerce' ),
				'company'    => __( 'Company', 'saved-addresses-for-woocommerce' ),
				'address_1'  => __( 'Address 1', 'saved-addresses-for-woocommerce' ),
				'address_2'  => __( 'Address 2', 'saved-addresses-for-woocommerce' ),
				'city'       => __( 'City', 'saved-addresses-for-woocommerce' ),
				'state'      => __( 'State', 'saved-addresses-for-woocommerce' ),
				'postcode'   => __( 'Postcode', 'saved-addresses-for-woocommerce' ),
				'country'    => __( 'Country', 'saved-addresses-for-woocommerce' ),
			);

			if ( ! empty( $saved_address ) ) {
				foreach ( $data_to_export as $key => $name ) {
					$value = '';

					if ( array_key_exists( $key, $saved_address ) ) {
						$value = $saved_address[ $key ];
					}

					if ( $value ) {
						$personal_data[] = array(
							'name'  => $name,
							'value' => $value,
						);
					}
				}
			}

			return $personal_data;
		}

		/**
		 * Find and delete saved addresses data by email address
		 *
		 * @param string $email_address The user email address.
		 * @param int    $page  Page.
		 * @return array An array of personal data in name value pairs
		 */
		public static function saved_addresses_data_eraser( $email_address, $page ) {
			global $wpdb;

			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			// Check if user has an ID in the DB to load stored personal data.
			$user = get_user_by( 'email', $email_address );

			if ( ! $user instanceof WP_User ) {
				return $response;
			}

			$customer = new WC_Customer( $user->ID );

			if ( ! $customer ) {
				return $response;
			}

			$user_id = $user->ID;
			$result  = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}usermeta WHERE user_id = %s AND meta_key IN ( 'sa_saved_formatted_addresses', 'sa_saved_shipping_addresses' )", $user_id ) ); // phpcs:ignore

			if ( $result > 0 ) {
				$response['items_removed'] = true;
				/* translators: Email address. */
				$response['messages'][] = sprintf( __( 'Removed all saved Shipping Addresses for user "%s"', 'saved-addresses-for-woocommerce' ), $email_address );
			}

			return $response;
		}

	}

}

SAW_Privacy::get_instance();
