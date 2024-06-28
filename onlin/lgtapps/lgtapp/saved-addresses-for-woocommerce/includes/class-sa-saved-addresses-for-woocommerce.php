<?php
/**
 * Main class
 *
 * @package     saved-addresses-for-woocommerce/includes/
 * @since       1.0.0
 * @version     2.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SA_Saved_Addresses_For_WooCommerce' ) ) {

	/**
	 * SA Saved Address For WooCommerce - Main class
	 */
	class SA_Saved_Addresses_For_WooCommerce {

		/**
		 * Variable to hold instance of Saved Addresses
		 *
		 * @var $instance
		 */
		private static $instance = null;

		/**
		 * Get single instance of Saved Addresses For WooCommerce.
		 *
		 * @return SA_Saved_Addresses_For_WooCommerce Singleton object of this class
		 */
		public static function get_instance() {

			// Check if instance is already exists.
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __clone() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'saved-addresses-for-woocommerce' ), '3.3.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 3.3.0
		 */
		private function __wakeup() {
			wc_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'saved-addresses-for-woocommerce' ), '3.3.0' );
		}

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'register_styles_and_scripts' ) );

			add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'get_billing_addresses' ) );
			add_action( 'woocommerce_after_checkout_billing_form', array( $this, 'enclose_the_billing_form' ) );
			add_action( 'woocommerce_before_checkout_shipping_form', array( $this, 'get_shipping_addresses' ) );
			add_action( 'woocommerce_after_checkout_shipping_form', array( $this, 'enclose_the_shipping_form' ) );

			add_action( 'wp_ajax_select_billing_address', array( $this, 'select_address' ) );
			add_action( 'wp_ajax_select_shipping_address', array( $this, 'select_address' ) );
			add_action( 'wp_ajax_delete_billing_address', array( $this, 'delete_address' ) );
			add_action( 'wp_ajax_delete_shipping_address', array( $this, 'delete_address' ) );

			// Add two hidden the fields for billing & shipping form at the checkout.
			add_action( 'woocommerce_checkout_fields', array( $this, 'custom_checkout_field_for_addresses' ) );

			// Update details in the order.
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'check_update_address_details' ), 10, 2 );

			// Override My Account > Address template.
			add_filter( 'woocommerce_locate_template', array( $this, 'override_account_address_template' ), 10, 3 );

			// My account.
			add_filter( 'woocommerce_address_to_edit', array( $this, 'addresses_to_edit' ), 11, 2 );
			add_action( 'woocommerce_customer_save_address', array( $this, 'save_address' ), 11, 2 );

			// My account - filter to change title of edit address page based on type of address & action.
			add_filter( 'woocommerce_my_account_edit_address_title', array( $this, 'change_edit_address_title' ), 11, 2 );

			// Actions to set default address from My Account.
			add_action( 'wp_ajax_default_billing_address', array( $this, 'default_address' ) );
			add_action( 'wp_ajax_default_shipping_address', array( $this, 'default_address' ) );

			// Actions to set load address from select2 on checkout.
			add_action( 'wp_ajax_update_last_billing_address', array( $this, 'update_last_address' ) );
			add_action( 'wp_ajax_update_last_shipping_address', array( $this, 'update_last_address' ) );

		}

		/**
		 * Handle call to functions which is not available in this class
		 *
		 * @param string $function_name The function name.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 *
		 * @return result of function call
		 */
		public function __call( $function_name, $arguments = array() ) {
			if ( ! is_callable( 'SA_WC_Compatibility_4_0', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_4_0::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_4_0::' . $function_name );
			}
		}

		/**
		 * Function to return shipping details for a order
		 *
		 * @param object $order Object of WC_Order.
		 *
		 * @return array $shipping_address Shipping Address for the order.
		 */
		public function get_shipping_details( $order ) {
			$shipping_address = $order->get_address( 'shipping' );

			return $shipping_address;
		}

		/**
		 * Function to return billing details for a order
		 *
		 * @param object $order Object of WC_Order.
		 *
		 * @return array $billing_address Billing Address for the order.
		 */
		public function get_billing_details( $order ) {
			$billing_address = $order->get_address( 'billing' );

			return $billing_address;
		}

		/**
		 * Function to find if current address key is default address
		 *
		 * @param integer $key Current address key.
		 * @param string  $address_type Billing or Shipping address.
		 *
		 * @return boolean
		 */
		public function is_default_address( $key, $address_type ) {

			$is_default = false;
			if ( is_user_logged_in() ) {
				$current_user_id      = get_current_user_id();
				$default_address_keys = get_user_meta( $current_user_id, 'sa_saved_default_address_keys', true );
				if ( ! empty( $default_address_keys ) && isset( $default_address_keys[ $address_type ] ) ) {
					// Ignoring strict checking below because type is not same.
					if ( $key == $default_address_keys[ $address_type ] ) { // phpcs:ignore
						$is_default = true;
					}
				}
			}

			return $is_default;

		}

		/**
		 * Function to get template base directory for Saved Addresses templates
		 *
		 * @param  string $template_name Template name.
		 * @return string $template_base_dir Base directory for Saved Addresses templates.
		 */
		public function get_template_base_dir( $template_name = '' ) {

			$template_base_dir = '';
			$plugin_base_dir   = substr( plugin_basename( SAW_PLUGIN_FILE ), 0, strpos( plugin_basename( SAW_PLUGIN_FILE ), '/' ) + 1 );
			$saw_base_dir      = 'woocommerce/' . $plugin_base_dir;

			// First locate the template in woocommerce/saved-addresses-for-woocommerce folder of active theme.
			$template = locate_template(
				array(
					$saw_base_dir . $template_name,
				)
			);

			if ( ! empty( $template ) ) {
				$template_base_dir = $saw_base_dir;
			} else {
				// If not found then locate the template in saved-addresses-for-woocommerce folder of active theme.
				$template = locate_template(
					array(
						$plugin_base_dir . $template_name,
					)
				);

				if ( ! empty( $template ) ) {
					$template_base_dir = $plugin_base_dir;
				}
			}

			$template_base_dir = apply_filters( 'saw_template_base_dir', $template_base_dir, $template_name );

			return $template_base_dir;
		}

		/**
		 * Function to include Saved Addresses template to load on the checkout page
		 *
		 * @param string $formatted_address Formatted billing and/or shipping address.
		 * @param string $saved_address     Raw billing and/or shipping address.
		 * @param string $address_type      Billing or shipping address.
		 */
		public function saw_include_template( $formatted_address, $saved_address, $address_type ) {
			$template_name = 'saved-addresses.php';
			$default_path  = SAW_PLUGIN_DIR_PATH . '/templates/';
			$template_path = $this->get_template_base_dir( $template_name );

			wc_get_template(
				$template_name,
				array(
					'sa_saved_formatted_addresses' => $formatted_address,
					'sa_saved_addresses'           => $saved_address,
					'address_type'                 => $address_type,
				),
				$template_path,
				$default_path
			);
		}

		/**
		 * Replace WooCommerce template with customized template on My Account > Address
		 *
		 * @param string $template Template name.
		 * @param string $template_name Template path.
		 * @param string $template_path Default path.
		 *
		 * @return string
		 */
		public function override_account_address_template( $template, $template_name, $template_path ) {

			if ( 'myaccount/my-address.php' === $template_name ) {
				// Check if template is overriden in active theme else pick from plugin.
				$template_path = $this->get_template_base_dir( 'my-address.php' );
				if ( ! empty( $template_path ) ) {
					$template = get_template_directory() . '/' . $template_path . 'my-address.php';
				} else {
					$template = SAW_PLUGIN_DIRPATH . '/templates/myaccount/my-address.php';
				}
			}
			return $template;

		}

		/**
		 * Function to load/register styles and scripts
		 */
		public function register_styles_and_scripts() {
			if ( is_user_logged_in() ) {
				$plugin_data = get_saw_plugin_data();
				$version     = $plugin_data['Version'];

				$user_id      = get_current_user_id();
				$address_keys = get_user_meta( $user_id, 'sa_saved_default_address_keys', true );

				if ( is_checkout() && ( ! is_wc_endpoint_url( 'order-received' ) ) ) {
					$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
					$assets_path = str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() ) . '/assets/';
					if ( ! wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
						wp_register_script( 'wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array( 'jquery', 'select2' ), WC_VERSION, true );
					}
					$saw_select_params = array(
						'i18n_matches_1'            => _x( 'One result is available, press enter to select it.', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_matches_n'            => _x( '%qty% results are available, use up and down arrow keys to navigate.', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'saved-addresses-for-woocommerce' ),
						'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					);
					wp_localize_script( 'select2', 'wc_enhanced_select_params', $saw_select_params );
					wp_enqueue_script( 'select2' );
					wp_enqueue_script( 'wc-enhanced-select' );
					wp_enqueue_style( 'select2', $assets_path . 'css/select2.css', array(), WC_VERSION, 'all' );

					wp_register_script( 'wc-saw-search', SAW_PLUGIN_URL . 'assets/js/saved-addresses-select.js', array( 'jquery' ), $version, true );
					wp_enqueue_script( 'wc-saw-search' );
					wp_localize_script(
						'wc-saw-search',
						'saved_addresses_select_params',
						array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
						)
					);

					$wc_shipping_destination_option = get_option( 'woocommerce_ship_to_destination' );

					wp_register_script( 'saved-addresses-js', SAW_PLUGIN_URL . 'assets/js/saved-addresses.js', array( 'jquery' ), $version, true );
					if ( ! wp_script_is( 'saved-addresses-js' ) ) {
						wp_enqueue_script( 'saved-addresses-js' );
					}
					wp_localize_script(
						'saved-addresses-js',
						'saved_addresses_params',
						array(
							'default_billing_key'  => ( ! empty( $address_keys ) && ! empty( $address_keys['billing'] ) ) ? $address_keys['billing'] : 0,
							'default_shipping_key' => ( ! empty( $address_keys ) && ! empty( $address_keys['shipping'] ) ) ? $address_keys['shipping'] : 0,
							'ajax_url'             => admin_url( 'admin-ajax.php' ),
							'is_user_logged_in'    => (int) is_user_logged_in(),
							'billing_available'    => 1,
							'wc_shipping_option'   => $wc_shipping_destination_option,
							'saw_select_address'   => wp_create_nonce( 'saw-select-address-nonce' ),
							'saw_delete_address'   => wp_create_nonce( 'saw-delete-address-nonce' ),
							'saw_update_address'   => wp_create_nonce( 'saw-search-address-nonce' ),
							'confirm_delete_text'  => __( 'Are you sure you want to delete this address?', 'saved-addresses-for-woocommerce' ),
						)
					);
				}

				if ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) {
					wp_register_script( 'saved-addresses-myaccount-js', SAW_PLUGIN_URL . 'assets/js/saved-addresses-myaccount.js', array( 'jquery' ), $version, true );
					if ( ! wp_script_is( 'saved-addresses-myaccount-js' ) ) {
						wp_enqueue_script( 'saved-addresses-myaccount-js' );
					}
					wp_localize_script(
						'saved-addresses-myaccount-js',
						'save_addresses_myaccount_params',
						array(
							'ajax_url'             => admin_url( 'admin-ajax.php' ),
							'default_billing_key'  => ( ! empty( $address_keys ) && ! empty( $address_keys['billing'] ) ) ? $address_keys['billing'] : 0,
							'default_shipping_key' => ( ! empty( $address_keys ) && ! empty( $address_keys['shipping'] ) ) ? $address_keys['shipping'] : 0,
							'saw_delete_address'   => wp_create_nonce( 'saw-delete-address-nonce' ),
							'saw_default_address'  => wp_create_nonce( 'saw-default-address-nonce' ),
							'confirm_delete_text'  => __( 'Are you sure you want to delete this address?', 'saved-addresses-for-woocommerce' ),
							'set_default_address'  => __( 'Set this as default address', 'saved-addresses-for-woocommerce' ),
							'set_default'          => __( 'Set default', 'saved-addresses-for-woocommerce' ),
							'default_address'      => __( 'Default address', 'saved-addresses-for-woocommerce' ),
							'default'              => __( 'Default', 'saved-addresses-for-woocommerce' ),

						)
					);
				}

				if ( is_checkout() || ( is_account_page() && is_wc_endpoint_url( 'edit-address' ) ) ) {
					wp_register_style( 'saved-addresses-css', SAW_PLUGIN_URL . 'assets/css/saved-addresses.css', '', $version, false );
					if ( ! wp_style_is( 'saved-addresses-css' ) ) {
						wp_enqueue_style( 'saved-addresses-css' );
					}

					if ( ! wp_style_is( 'dashicons' ) ) {
						wp_enqueue_style( 'dashicons' );
					}
				}
			}
		}

		/**
		 * Function to get billing addresses for a logged in user who has previously placed order
		 */
		public function get_billing_addresses() {
			if ( is_checkout() && is_user_logged_in() ) {
				$current_user_id                      = get_current_user_id();
				$sa_saved_formatted_billing_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', true );
				$sa_saved_billing_addresses           = get_user_meta( $current_user_id, 'sa_saved_billing_addresses', true );

				if ( ! empty( $sa_saved_formatted_billing_addresses ) ) {
					$this->saw_include_template( $sa_saved_formatted_billing_addresses, $sa_saved_billing_addresses, 'billing' );
				}
			}
		}

		/**
		 * Function to enclose the shipping address form in order to hide and show the form on click of 'Ship to a new address' button
		 */
		public function enclose_the_billing_form() {
			if ( is_checkout() && is_user_logged_in() ) {
				$current_user_id                      = get_current_user_id();
				$sa_saved_formatted_billing_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', true );
				if ( ! empty( $sa_saved_formatted_billing_addresses ) ) {
					echo '</div>';
				}
			}
		}

		/**
		 * Function to get shipping addresses for a logged in user who has previously placed order
		 */
		public function get_shipping_addresses() {
			if ( is_checkout() && is_user_logged_in() ) {
				$current_user_id                       = get_current_user_id();
				$sa_saved_formatted_shipping_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_addresses', true );
				$sa_saved_shipping_addresses           = get_user_meta( $current_user_id, 'sa_saved_shipping_addresses', true );

				if ( ! empty( $sa_saved_formatted_shipping_addresses ) ) {
					$this->saw_include_template( $sa_saved_formatted_shipping_addresses, $sa_saved_shipping_addresses, 'shipping' );
				}
			}
		}

		/**
		 * Function to enclose the shipping address form in order to hide and show the form on click of 'Ship to a new address' button
		 */
		public function enclose_the_shipping_form() {
			if ( is_checkout() && is_user_logged_in() ) {
				$current_user_id              = get_current_user_id();
				$sa_saved_formatted_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_addresses', true );
				if ( ! empty( $sa_saved_formatted_addresses ) ) {
					echo '</div>';
				}
			}
		}

		/**
		 * Function to fill billing and/or shipping details through ajax
		 */
		public function select_address() {
			check_ajax_referer( 'saw-select-address-nonce', 'security' );

			$selected_address = array();

			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$address_id      = ( isset( $_POST['address_id'] ) ) ? wc_clean( wp_unslash( $_POST['address_id'] ) ) : ''; // phpcs:ignore

				if ( '' !== $address_id ) {
					$current_action   = current_action();
					$address          = ( 'wp_ajax_select_shipping_address' === $current_action ) ? get_user_meta( $current_user_id, 'sa_saved_shipping_addresses', true ) : get_user_meta( $current_user_id, 'sa_saved_billing_addresses', true );
					$selected_address = ( ! empty( $address[ $address_id ] ) ) ? $address[ $address_id ] : array();
				}
			}

			echo wp_json_encode( $selected_address );
			die();
		}

		/**
		 * Function to delete billing and/or shipping details through ajax
		 */
		public function delete_address() {
			check_ajax_referer( 'saw-delete-address-nonce', 'security' );

			$deleted = false;

			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$delete_id       = ( isset( $_POST['delete_id'] ) ) ? wc_clean( wp_unslash( $_POST['delete_id'] ) ) : ''; // phpcs:ignore

				if ( '' !== $delete_id ) {
					$current_action = current_action();
					if ( 'wp_ajax_delete_billing_address' === $current_action ) {
						$user_address_type               = 'billing';
						$user_address_meta_key           = 'sa_saved_billing_addresses';
						$user_formatted_address_meta_key = 'sa_saved_formatted_billing_addresses';
					} elseif ( 'wp_ajax_delete_shipping_address' === $current_action ) {
						$user_address_type               = 'shipping';
						$user_address_meta_key           = 'sa_saved_shipping_addresses';
						$user_formatted_address_meta_key = 'sa_saved_formatted_addresses';
					}

					$user_address           = get_user_meta( $current_user_id, $user_address_meta_key, true );
					$formatted_user_address = get_user_meta( $current_user_id, $user_formatted_address_meta_key, true );

					unset( $user_address[ $delete_id ] );
					unset( $formatted_user_address[ $delete_id ] );

					update_user_meta( $current_user_id, $user_address_meta_key, $user_address );
					update_user_meta( $current_user_id, $user_formatted_address_meta_key, $formatted_user_address );

					$default_address_keys = get_user_meta( $current_user_id, 'sa_saved_default_address_keys', true );
					if ( $delete_id === $default_address_keys[ $user_address_type ] ) {
						$default_address_keys[ $user_address_type ] = str_replace( $delete_id, '', $default_address_keys[ $user_address_type ] );
						update_user_meta( $current_user_id, 'sa_saved_default_address_keys', $default_address_keys );
					}
					// TODO: delete from an order post meta when deleted from here.
					$deleted = true;
				}
			}

			echo wp_json_encode(
				array(
					'deleted'   => $deleted,
					'delete_id' => $delete_id,
				)
			);
			die();
		}

		/**
		 * Function to insert custom hidden fields at the checkout for billing and shipping forms
		 *
		 * @param  array $checkout_fields The array of form fields.
		 *
		 * @return array $checkout_fields The updated array of form fields.
		 */
		public function custom_checkout_field_for_addresses( $checkout_fields ) {
			$checkout_fields['billing']['saw_billing_address_id'] = array(
				'type'        => 'hidden',
				'label'       => '',
				'class'       => array( 'form-row-wide saw-billing-address-id' ),
				'label_class' => array( 'hidden' ),
			);

			$checkout_fields['shipping']['saw_shipping_address_id'] = array(
				'type'        => 'hidden',
				'label'       => '',
				'class'       => array( 'form-row-wide saw-shipping-address-id' ),
				'label_class' => array( 'hidden' ),
			);

			return $checkout_fields;
		}

		/**
		 * Function to save and compare billing and shipping address after placing order
		 *
		 * @param int   $order_id Current order it.
		 * @param array $posted_data The posted data from checkout form.
		 */
		public function check_update_address_details( $order_id, $posted_data ) {
			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$order           = new WC_Order( $order_id );

				// Get formatted address from order.
				$order_formatted_billing_address  = $order->get_formatted_billing_address();
				$order_formatted_shipping_address = $order->get_formatted_shipping_address();

				// Get normal address from order.
				$order_billing_details  = $this->get_billing_details( $order );
				$order_shipping_details = $this->get_shipping_details( $order );

				// Billing: Get formatted address from usermeta.
				$user_saved_formatted_billing_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', true );
				$stored_formatted_billing_addresses     = ( empty( $user_saved_formatted_billing_addresses ) ) ? array() : $user_saved_formatted_billing_addresses;

				// Billing: Get normal address from usermeta.
				$user_saved_billing_addresses = get_user_meta( $current_user_id, 'sa_saved_billing_addresses', true );
				$stored_billing_addresses     = ( empty( $user_saved_billing_addresses ) ) ? array() : $user_saved_billing_addresses;

				// Shipping: Get formatted address from usermeta.
				$user_saved_formatted_shipping_addresses = get_user_meta( $current_user_id, 'sa_saved_formatted_addresses', true );
				$stored_formatted_shipping_addresses     = ( empty( $user_saved_formatted_shipping_addresses ) ) ? array() : $user_saved_formatted_shipping_addresses;

				// Shipping: Get normal address from usermeta.
				$user_saved_shipping_addresses = get_user_meta( $current_user_id, 'sa_saved_shipping_addresses', true );
				$stored_shipping_addresses     = ( empty( $user_saved_shipping_addresses ) ) ? array() : $user_saved_shipping_addresses;

				$first = false;
				// Detect if it is a first order for a user.
				if ( empty( $stored_formatted_billing_addresses ) && empty( $stored_billing_addresses ) && empty( $stored_formatted_shipping_addresses ) && empty( $stored_shipping_addresses ) ) {
					$first                = true;
					$default_address_keys = array();
				}

				$billing_address_index  = '';
				$shipping_address_index = '';

				// Billing address.
				// Do not check for !empty as 0 address key will not be considered.
				if ( '' == $posted_data['saw_billing_address_id'] ) {   // phpcs:ignore
					if ( ! empty( $order_formatted_billing_address ) ) {

						// Check if billing address is already present in saved addresses.
						// 1. formatted billing address.
						if ( ! in_array( $order_formatted_billing_address, $stored_formatted_billing_addresses, true ) ) {
							array_push( $stored_formatted_billing_addresses, $order_formatted_billing_address );
							update_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', $stored_formatted_billing_addresses );

							// 2. non-formatted billing address.
							if ( ! in_array( $order_billing_details, $stored_billing_addresses, true ) ) {
								array_push( $stored_billing_addresses, $order_billing_details );
								update_user_meta( $current_user_id, 'sa_saved_billing_addresses', $stored_billing_addresses );
							}
						}
					}

					$billing_address_index = array_search( $order_billing_details, $stored_billing_addresses, true );
				} else {
					$billing_address_index   = $posted_data['saw_billing_address_id'];
					$plucked_billing_address = $stored_billing_addresses[ $billing_address_index ];

					$diff_in_billing_address = array();

					$diff_in_billing_address = array_diff( $order_billing_details, $plucked_billing_address );
					if ( ! empty( $diff_in_billing_address ) ) {
						$stored_formatted_billing_addresses[ $billing_address_index ] = $order_formatted_billing_address;
						update_user_meta( $current_user_id, 'sa_saved_formatted_billing_addresses', $stored_formatted_billing_addresses );

						$stored_billing_addresses[ $billing_address_index ] = $order_billing_details;
						update_user_meta( $current_user_id, 'sa_saved_billing_addresses', $stored_billing_addresses );
					}
				}

				// Shipping address.
				// Do not check for !empty as 0 address key will not be considered.
				if ( isset( $posted_data['saw_shipping_address_id'] ) && '' == $posted_data['saw_shipping_address_id'] ) { // phpcs:ignore
					if ( ! empty( $order_formatted_shipping_address ) ) {

						// Check if shipping address is already present in saved addresses.
						// 1. formatted shipping address.
						if ( ! in_array( $order_formatted_shipping_address, $stored_formatted_shipping_addresses, true ) ) {
							array_push( $stored_formatted_shipping_addresses, $order_formatted_shipping_address );
							update_user_meta( $current_user_id, 'sa_saved_formatted_addresses', $stored_formatted_shipping_addresses );

							// 2. non-formatted shipping address.
							if ( ! in_array( $order_shipping_details, $stored_shipping_addresses, true ) ) {
								array_push( $stored_shipping_addresses, $order_shipping_details );
								update_user_meta( $current_user_id, 'sa_saved_shipping_addresses', $stored_shipping_addresses );
							}
						}
					}

					$shipping_address_index = array_search( $order_shipping_details, $stored_shipping_addresses, true );
				} else {
					if ( isset( $posted_data['saw_shipping_address_id'] ) && '' != $posted_data['saw_shipping_address_id'] ) { // phpcs:ignore
						$shipping_address_index = $posted_data['saw_shipping_address_id'];

						$plucked_shipping_address = $stored_shipping_addresses[ $shipping_address_index ];

						$diff_in_shipping_address = array();

						$diff_in_shipping_address = array_diff( $order_shipping_details, $plucked_shipping_address );
						if ( ! empty( $diff_in_shipping_address ) ) {
							$stored_formatted_shipping_addresses[ $shipping_address_index ] = $order_formatted_shipping_address;
							update_user_meta( $current_user_id, 'sa_saved_formatted_addresses', $stored_formatted_shipping_addresses );

							$stored_shipping_addresses[ $shipping_address_index ] = $order_shipping_details;
							update_user_meta( $current_user_id, 'sa_saved_shipping_addresses', $stored_shipping_addresses );
						}
					}
				}

				// Update address index in order post meta.
				if ( '' !== $billing_address_index ) {
					update_post_meta( $order_id, '_saw_billing_address_id', sanitize_text_field( $billing_address_index ) );
				}
				if ( '' !== $shipping_address_index ) {
					update_post_meta( $order_id, '_saw_shipping_address_id', sanitize_text_field( $shipping_address_index ) );
				}

				// Set default address if first order of a user.
				if ( true === $first ) {
					$default_address_keys['billing']  = $billing_address_index;
					$default_address_keys['shipping'] = $shipping_address_index;
					add_user_meta( $current_user_id, 'sa_saved_default_address_keys', $default_address_keys, true );
				}
			}
		}

		/**
		 * Function to handle addresses to edit & add from My Account > Address.
		 *
		 * @param string $address Address to edit.
		 * @param string $load_address Address type.
		 *
		 * @return modified address
		 */
		public function addresses_to_edit( $address, $load_address ) {
			if ( is_wc_endpoint_url( 'edit-address' ) ) {

				if ( 'saw_billing' === $load_address || 'saw_shipping' === $load_address ) {
					// TODO: Check if woocommerce_default_address_fields filter can be used OR get_address_fields function and following code can be improved.
					// TODO: State refresh is not working correctly for add in some cases.
					$action = ( isset( $_GET['saw_type'] ) ) ? wc_clean( wp_unslash( $_GET['saw_type'] ) ) : ''; // phpcs:ignore

					if ( ! empty( $action ) && 'add' === $action && 'saw_billing' === $load_address ) {
						$modified_address = $address;

						$phone = array();
						$email = array();
						if ( ! array_key_exists( 'saw_billing_phone', $address ) ) {
							$phone = array(
								'saw_billing_phone' => array(
									'label'        => __( 'Phone', 'saved-addresses-for-woocommerce' ),
									'required'     => 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ),
									'type'         => 'tel',
									'class'        => array( 'form-row-wide' ),
									'validate'     => array( 'phone' ),
									'autocomplete' => 'tel',
									'priority'     => '100',
									'value'        => '',
								),
							);
						}
						if ( ! array_key_exists( 'saw_billing_email', $address ) ) {
							$email = array(
								'saw_billing_email' => array(
									'label'        => __( 'Email address', 'woocommerce' ),
									'required'     => true,
									'type'         => 'email',
									'class'        => array( 'form-row-wide' ),
									'validate'     => array( 'email' ),
									'autocomplete' => 'email username',
									'priority'     => '110',
									'value'        => '',
								),
							);
						}
						$updated_address = array_merge( $modified_address, $phone, $email );

						return $updated_address;
					}
				} elseif ( 'billing' === $load_address || 'shipping' === $load_address ) {
					$get_saved_address_key = ( isset( $_GET['saw'] ) ) ? wc_clean( wp_unslash( $_GET['saw'] ) ) : ''; // phpcs:ignore

					// Do not check for !empty as 0 address key will not be considered.
					if ( '' != $get_saved_address_key ) { // phpcs:ignore
						$current_address_index = sanitize_text_field( wp_unslash( $get_saved_address_key ) );
						$user_id               = get_current_user_id();
						$user_meta_key         = ( 'billing' === $load_address ) ? 'sa_saved_billing_addresses' : 'sa_saved_shipping_addresses';
						$saved_addresses       = get_user_meta( $user_id, $user_meta_key, true );
						foreach ( $address as $key => $value ) {
							$new_key                  = str_replace( $load_address . '_', '', $key );
							$address[ $key ]['value'] = $saved_addresses[ $current_address_index ][ $new_key ];
						}
					}
				}
			}

			return $address;
		}

		/**
		 * Function to save edited address from My Account
		 *
		 * @param int    $user_id Current user's id.
		 * @param string $load_address Address type.
		 */
		public function save_address( $user_id, $load_address ) {
			if ( is_wc_endpoint_url( 'edit-address' ) ) {

				$post_edit_address_wc_nonce = ( isset( $_POST['woocommerce-edit-address-nonce'] ) ) ? wc_clean( wp_unslash( $_POST['woocommerce-edit-address-nonce'] ) ) : ''; // phpcs:ignore
				if ( ( empty( $post_edit_address_wc_nonce ) ) && ( ! wp_verify_nonce( $post_edit_address_wc_nonce, 'woocommerce-edit_address' ) ) ) {
					return;
				}

				if ( 'billing' === $load_address || 'shipping' === $load_address ) {
					$get_saved_address_key = ( isset( $_GET['saw'] ) ) ? wc_clean( wp_unslash( $_GET['saw'] ) ) : 0; // phpcs:ignore

					if ( isset( $get_saved_address_key ) ) {
						$user_meta_key           = ( 'billing' === $load_address ) ? 'sa_saved_billing_addresses' : 'sa_saved_shipping_addresses';
						$user_meta_key_formatted = ( 'billing' === $load_address ) ? 'sa_saved_formatted_billing_addresses' : 'sa_saved_formatted_addresses';

						if ( isset( $_POST ) && is_array( $_POST ) && isset( $_POST['action'] ) && ( 'edit_address' === $_POST['action'] ) ) {
							$posted_data     = $_POST;
							$address_to_save = array();
							foreach ( $posted_data as $key => $value ) {
								if ( false !== strpos( $key, $load_address ) ) {
									$new_key                     = str_replace( $load_address . '_', '', $key );
									$address_to_save[ $new_key ] = $value;
								}
							}
							$modified_formatted_adress = WC()->countries->get_formatted_address( $address_to_save );
						}

						// Non-formatted billing & shipping.
						$saved_address = get_user_meta( $user_id, $user_meta_key, true );
						if ( empty( $saved_address ) ) {
							$saved_address = array();
						}
						$saved_address[ $get_saved_address_key ] = $address_to_save;
						update_user_meta( $user_id, $user_meta_key, $saved_address );

						// Formatted billing & shipping.
						$saved_formatted_address = get_user_meta( $user_id, $user_meta_key_formatted, true );
						if ( empty( $saved_formatted_address ) ) {
							$saved_formatted_address = array();
						}
						$saved_formatted_address[ $get_saved_address_key ] = $modified_formatted_adress;
						update_user_meta( $user_id, $user_meta_key_formatted, $saved_formatted_address );
					}
				} elseif ( 'saw_billing' === $load_address || 'saw_shipping' === $load_address ) {
					$get_address_action = ( isset( $_GET['saw_type'] ) ) ? wc_clean( wp_unslash( $_GET['saw_type'] ) ) : ''; // phpcs:ignore

					if ( ! empty( $get_address_action ) && 'add' === $get_address_action ) {
						$user_meta_key           = ( 'saw_billing' === $load_address ) ? 'sa_saved_billing_addresses' : 'sa_saved_shipping_addresses';
						$user_meta_key_formatted = ( 'saw_billing' === $load_address ) ? 'sa_saved_formatted_billing_addresses' : 'sa_saved_formatted_addresses';

						if ( isset( $_POST ) && is_array( $_POST ) && isset( $_POST['action'] ) && ( 'edit_address' === $_POST['action'] ) ) {
							$posted_data     = $_POST;
							$address_to_save = array();
							$meta_to_delete  = array();
							foreach ( $posted_data as $key => $value ) {
								if ( false !== strpos( $key, $load_address ) ) {
									$new_key                     = str_replace( $load_address . '_', '', $key );
									$address_to_save[ $new_key ] = $value;
									// to prevent save of individual key and delete later.
									array_push( $meta_to_delete, $key );
								}
							}

							$modified_formatted_adress = WC()->countries->get_formatted_address( $address_to_save );
						}

						// 1. Formatted billing & shipping.
						$saved_formatted_address = get_user_meta( $user_id, $user_meta_key_formatted, true );
						$saved_formatted_address = ( empty( $saved_formatted_address ) ) ? array() : $saved_formatted_address;

						// 2. Non-formatted billing & shipping.
						$saved_address = get_user_meta( $user_id, $user_meta_key, true );
						$saved_address = ( empty( $saved_address ) ) ? array() : $saved_address;

						if ( ! in_array( $modified_formatted_adress, $saved_formatted_address, true ) ) {
							// 1. Formatted billing & shipping.
							array_push( $saved_formatted_address, $modified_formatted_adress );
							update_user_meta( $user_id, $user_meta_key_formatted, $saved_formatted_address );

							// 2. Non-formatted billing & shipping.
							if ( ! in_array( $address_to_save, $saved_address, true ) ) {
								array_push( $saved_address, $address_to_save );
								update_user_meta( $user_id, $user_meta_key, $saved_address );
							}
						}

						// Delete additional user_meta added for each individual key.
						if ( isset( $meta_to_delete ) ) {
							foreach ( $meta_to_delete as $key => $value ) {
								delete_user_meta( $user_id, $value );
							}
						}
					}
				}
			}
		}

		/**
		 * Function to change add/edit address page title.
		 *
		 * @param string $page_title   Current page title.
		 * @param string $load_address The address type.
		 *
		 * @return string Updated page title.
		 */
		public function change_edit_address_title( $page_title, $load_address ) {
			switch ( $load_address ) {
				case 'saw_billing':
					$page_title = __( 'Add new billing address', 'saved-addresses-for-woocommerce' );
					break;

				case 'saw_shipping':
					$page_title = __( 'Add new shipping address', 'saved-addresses-for-woocommerce' );
					break;

				case 'billing':
					$page_title = __( 'Edit billing address', 'saved-addresses-for-woocommerce' );
					break;

				case 'shipping':
					$page_title = __( 'Edit shipping address', 'saved-addresses-for-woocommerce' );
					break;

				default:
					$page_title = __( 'Address', 'saved-addresses-for-woocommerce' );
					break;
			}

			return $page_title;
		}

		/**
		 * Function to set default billing and/or shipping details through ajax.
		 */
		public function default_address() {
			check_ajax_referer( 'saw-default-address-nonce', 'security' );

			$default = false;

			if ( is_user_logged_in() ) {
				$current_user_id = get_current_user_id();
				$default_id      = ( isset( $_POST['default_id'] ) ) ? wc_clean( wp_unslash( $_POST['default_id'] ) ) : ''; // phpcs:ignore

				if ( '' !== $default_id ) {
					$current_action = current_action();
					if ( 'wp_ajax_default_billing_address' === $current_action ) {
						$address_name = 'billing';
					} elseif ( 'wp_ajax_default_shipping_address' === $current_action ) {
						$address_name = 'shipping';
					}
					$default_address_keys = get_user_meta( $current_user_id, 'sa_saved_default_address_keys', true );

					if ( ! empty( $default_address_keys ) ) {
						unset( $default_address_keys[ $address_name ] );
					} else {
						$default_address_keys             = array();
						$default_address_keys['billing']  = 0;
						$default_address_keys['shipping'] = 0;
					}
					$default_address_keys[ $address_name ] = $default_id;
					update_user_meta( $current_user_id, 'sa_saved_default_address_keys', $default_address_keys );
					$default = true;
				}
			}

			echo wp_json_encode(
				array(
					'default'    => $default,
					'default_id' => $default_id,
				)
			);
			die();
		}

		/**
		 * Function to update last displayed address via change in select2.
		 */
		public function update_last_address() {
			check_ajax_referer( 'saw-search-address-nonce', 'security' );

			if ( is_user_logged_in() ) {
				$current_user_id       = get_current_user_id();
				$address_key_to_add    = ( isset( $_POST['address_key_to_add'] ) ) ? wc_clean( wp_unslash( $_POST['address_key_to_add'] ) ) : ''; // phpcs:ignore
				$address_key_to_remove = ( isset( $_POST['address_key_to_remove'] ) ) ? wc_clean( wp_unslash( $_POST['address_key_to_remove'] ) ) : ''; // phpcs:ignore

				$current_action = current_action();
				if ( 'wp_ajax_update_last_billing_address' === $current_action ) {
					$address_type      = 'billing';
					$formatted_address = 'sa_saved_formatted_billing_addresses';
					$raw_address       = 'sa_saved_billing_addresses';
				} elseif ( 'wp_ajax_update_last_shipping_address' === $current_action ) {
					$address_type      = 'shipping';
					$formatted_address = 'sa_saved_formatted_addresses';
					$raw_address       = 'sa_saved_shipping_addresses';
				}

				$formatted_user_address = get_user_meta( $current_user_id, $formatted_address, true );

				$address_to_add = $formatted_user_address[ $address_key_to_add ];

				// Split it by br.
				$address = explode( '<br/>', $address_to_add );
				// Make first value as bold.
				$address[0]     = '<b>' . $address[0] . '</b>';
				$address_to_add = implode( '<br/>', $address );

				$raw_user_address  = get_user_meta( $current_user_id, $raw_address, true );
				$address_to_remove = $raw_user_address[ $address_key_to_remove ];

				$format_address_to_remove = $this->saw_format_address_for_checkout( $address_to_remove, $address_type );
			}

			echo wp_json_encode(
				array(
					'address_to_add'    => $address_to_add,
					'address_to_remove' => $format_address_to_remove,
				)
			);
			die();

		}

		/**
		 * Function to format a given address for select display.
		 * Format: Full Name - City - Zip - Email - Phone - Full Address - State - Country - Company
		 *
		 * @param array  $user_address A user address.
		 * @param string $address_type Billing or Shipping address.
		 *
		 * @return string Formatted address.
		 */
		public function saw_format_address_for_checkout( $user_address, $address_type ) {
			$default_args = array();

			if ( empty( $user_address ) && ! is_array( $user_address ) ) {
				return $default_args;
			}

			if ( empty( $address_type ) ) {
				$address_type = 'billing';
			}

			// Full Name.
			$default_args ['name'] = ( ! empty( $user_address['first_name'] ) ) ? $user_address['first_name'] : '';
			if ( ! empty( $user_address['last_name'] ) ) {
				$default_args['name'] .= ' ' . $user_address['last_name'];
			}

			$default_args ['city']     = ( ! empty( $user_address['city'] ) ) ? $user_address['city'] : '';
			$default_args ['postcode'] = ( ! empty( $user_address['postcode'] ) ) ? $user_address['postcode'] : '';

			if ( 'billing' === $address_type ) {
				$default_args ['email'] = ( ! empty( $user_address['email'] ) ) ? $user_address['email'] : '';
				$default_args ['phone'] = ( ! empty( $user_address['phone'] ) ) ? $user_address['phone'] : '';
			}

			// Full address.
			$default_args ['full_address'] = ( ! empty( $user_address['address_1'] ) ) ? $user_address['address_1'] : '';
			if ( ! empty( $user_address['address_2'] ) ) {
				$default_args['full_address'] .= ' ' . $user_address['address_2'];
			}

			$state_code   = $user_address['state'];
			$country_code = $user_address['country'];

			// Get state name from state code.
			if ( ! empty( $state_code ) && 'N/A' != $state_code ) { // phpcs:ignore
				$state                  = isset( WC()->countries->states[ $country_code ][ $state_code ] ) ? WC()->countries->states[ $country_code ][ $state_code ] : $state_code;
				$default_args ['state'] = $state;
			}

			// Get country name from country code.
			if ( ! empty( $country_code ) ) {
				$country                  = isset( WC()->countries->countries[ $country_code ] ) ? WC()->countries->countries[ $country_code ] : $country_code;
				$default_args ['country'] = $country;
			}

			$default_args ['company'] = ( ! empty( $user_address['company'] ) ) ? $user_address ['company'] : '';

			// Remove empty entries.
			$default_args = array_filter( $default_args, 'strlen' );

			// Seprate by separator.
			$default_args = implode( ' - ', $default_args );

			return $default_args;
		}

	} //class end
}
