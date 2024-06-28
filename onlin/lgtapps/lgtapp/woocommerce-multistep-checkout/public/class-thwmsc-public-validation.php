<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/public 
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWMSC_Public_Validation')):
 
class THWMSC_Public_Validation {

	public function __construct() {
		
	}
	
	public function get_posted_data($request){
		$posted_data = $request['posted'];

		if(apply_filters('thwmsc_clean_and_sanitize_step_data', true)){
			$sanitized = array();

			foreach ($posted_data as $key => $field) {
				$value = isset($posted_data[$key]) ? wc_clean(wp_unslash($field)) : '';
				$sanitized[$key] = $value;
			}

			return $sanitized;
		}

		return $posted_data;
		
		/*foreach ( $this->get_checkout_fields() as $fieldset_key => $fieldset ) {
			if ( $this->maybe_skip_fieldset( $fieldset_key, $data ) ) {
				$skipped[] = $fieldset_key;
				continue;
			}
			foreach ( $fieldset as $key => $field ) {
				$type = sanitize_title( isset( $field['type'] ) ? $field['type'] : 'text' );

				switch ( $type ) {
					case 'checkbox' :
						$value = isset( $_POST[ $key ] ) ? 1 : '';
						break;
					case 'multiselect' :
						$value = isset( $_POST[ $key ] ) ? implode( ', ', wc_clean( $_POST[ $key ] ) ) : '';
						break;
					case 'textarea' :
						$value = isset( $_POST[ $key ] ) ? wc_sanitize_textarea( $_POST[ $key ] ) : '';
						break;
					default :
						$value = isset( $_POST[ $key ] ) ? wc_clean( $_POST[ $key ] ) : '';
						break;
				}

				$data[ $key ] = apply_filters( 'woocommerce_process_checkout_' . $type . '_field', apply_filters( 'woocommerce_process_checkout_field_' . $key, $value ) );
			}
		}*/
	}

	public function validate_checkout_step() {
		try {
			$request = $_POST;
			$error_mode = isset($request['error_display_mode']) ? $request['error_display_mode'] : false;
			$posted_data = $this->get_posted_data($request);
			
			$errors = new WP_Error();
			$result = $this->validate_checkout($posted_data, $errors);
			
			$error_msg = $errors->get_error_messages();
			if(empty($error_msg)){
				$this->send_ajax_success_response();
			}else{
				if($error_mode === 'inline'){
					$response = array(
						'result'   => 'failure',
						'messages' => $errors->get_error_messages(),
					);
					wp_send_json($response);
				}else{
					foreach($errors->get_error_messages() as $message){
						wc_add_notice($message, 'error');
					}
					$this->send_ajax_failure_response();
				}
			}
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			$this->send_ajax_failure_response();
		}
	}
	
	/**
	 * Validates that the checkout has enough info to proceed.
	 *
	 * @since  3.0.0
	 * @param  array $data An array of posted data.
	 * @param  WP_Error $errors
	 */
	protected function validate_checkout(&$data, &$errors){
		$this->validate_posted_data($data, $errors);
		if(apply_filters('thwmsc_enable_check_cart_items_on_step_validation', false)){
			WC()->checkout()->check_cart_items();
		}
		
		if(isset($data['terms'])){
			if(empty($data['woocommerce_checkout_update_totals']) && empty($data['terms']) && apply_filters('woocommerce_checkout_show_terms', wc_get_page_id('terms') > 0)){
				$errors->add('terms', __( 'You must accept our Terms &amp; Conditions.', 'woocommerce'));
			}
		}

		do_action('woocommerce_after_checkout_validation', $data, $errors);
	}
	
	/**
	 * Validates the posted checkout data based on field properties.
	 *
	 * @since  3.0.0
	 * @param  array $data An array of posted data.
	 * @param  WP_Error $errors
	 */
	protected function validate_posted_data( &$data, &$errors ) {
		$checkout_fields = WC()->checkout()->get_checkout_fields();		
		$checkout_fields = apply_filters('thwcfe_remove_disabled_fields_and_sections', $checkout_fields, $data);		
		if(!$checkout_fields){
			$checkout_fields = WC()->checkout()->get_checkout_fields();
		}
		
		foreach ( $checkout_fields as $fieldset_key => $fieldset ) {
			$validate_fieldset = true;
			if ( $this->maybe_skip_fieldset( $fieldset_key, $data ) ) {
				$validate_fieldset = false;
			}
			
			foreach ( $fieldset as $key => $field ) {
				if ( ! isset( $data[ $key ] ) ) {
					continue;
				}
				$required    = ! empty( $field['required'] );
				$format      = array_filter( isset( $field['validate'] ) ? (array) $field['validate'] : array() );
				$field_label = isset( $field['label'] ) ? $field['label'] : '';

				switch ( $fieldset_key ) {
					case 'shipping':
						/* translators: %s: field name */
						$field_label = sprintf( _x( 'Shipping %s', 'checkout-validation', 'woocommerce' ), $field_label );
						break;
					case 'billing':
						/* translators: %s: field name */
						$field_label = sprintf( _x( 'Billing %s', 'checkout-validation', 'woocommerce' ), $field_label );
						break;
				}

				if ( in_array( 'postcode', $format, true ) ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$data[ $key ] = wc_format_postcode( $data[ $key ], $country );

					if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_postcode( $data[ $key ], $country ) ) {
						switch ( $country ) {
							case 'IE':
								/* translators: %1$s: field name, %2$s finder.eircode.ie URL */
								$postcode_validation_notice = sprintf( __( '%1$s is not valid. You can look up the correct Eircode <a target="_blank" href="%2$s">here</a>.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', 'https://finder.eircode.ie' );
								break;
							default:
								/* translators: %s: field name */
								$postcode_validation_notice = sprintf( __( '%s is not a valid postcode / ZIP.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' );
						}
						$errors->add( $key . '_validation', apply_filters( 'woocommerce_checkout_postcode_validation_notice', $postcode_validation_notice, $country, $data[ $key ] ), array( 'id' => $key ) );
					}
				}

				if ( in_array( 'phone', $format, true ) ) {
					if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_phone( $data[ $key ] ) ) {
						/* translators: %s: phone number */
						$errors->add( $key . '_validation', sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), array( 'id' => $key ) );
					}
				}

				if ( in_array( 'email', $format, true ) && '' !== $data[ $key ] ) {
					$email_is_valid = is_email( $data[ $key ] );
					$data[ $key ]   = sanitize_email( $data[ $key ] );

					if ( $validate_fieldset && ! $email_is_valid ) {
						/* translators: %s: email address */
						$errors->add( $key . '_validation', sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), array( 'id' => $key ) );
						continue;
					}
				}

				if ( '' !== $data[ $key ] && in_array( 'state', $format, true ) ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$valid_states = WC()->countries->get_states( $country );

					if ( ! empty( $valid_states ) && is_array( $valid_states ) && count( $valid_states ) > 0 ) {
						$valid_state_values = array_map( 'wc_strtoupper', array_flip( array_map( 'wc_strtoupper', $valid_states ) ) );
						$data[ $key ]       = wc_strtoupper( $data[ $key ] );

						if ( isset( $valid_state_values[ $data[ $key ] ] ) ) {
							// With this part we consider state value to be valid as well, convert it to the state key for the valid_states check below.
							$data[ $key ] = $valid_state_values[ $data[ $key ] ];
						}

						if ( $validate_fieldset && ! in_array( $data[ $key ], $valid_state_values, true ) ) {
							/* translators: 1: state field 2: valid states */
							$errors->add( $key . '_validation', sprintf( __( '%1$s is not valid. Please enter one of the following: %2$s', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', implode( ', ', $valid_states ) ), array( 'id' => $key ) );
						}
					}
				}

				if ( $validate_fieldset && $required && '' === $data[ $key ] ) {
					/* translators: %s: field name */
					$errors->add( $key . '_required', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label ), array( 'id' => $key ) );
				}
			}
		}
	}
	
	/**
	 * See if a fieldset should be skipped.
	 *
	 * @since 3.0.0
	 *
	 * @param string $fieldset_key
	 * @param array $data
	 *
	 * @return bool
	 */
	protected function maybe_skip_fieldset( $fieldset_key, $data ) {
		if ( 'shipping' === $fieldset_key && ( (isset($data['ship_to_different_address']) && !$data['ship_to_different_address']) || ! WC()->cart->needs_shipping_address() ) ) {
			return true;
		}
		if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! WC()->checkout()->is_registration_required() && empty( $data['createaccount'] ) ) ) ) {
			return true;
		}
		return false;
	}
	
	/**
	 * If checkout failed during an AJAX call, send failure response.
	 */
	protected function send_ajax_failure_response() {
		if ( is_ajax() ) {
			// only print notices if not reloading the checkout, otherwise they're lost in the page reload
			if ( ! isset( WC()->session->reload_checkout ) ) {
				ob_start();
				wc_print_notices();
				$messages = ob_get_clean();
			}

			$response = array(
				'result'   => 'failure',
				'messages' => isset( $messages ) ? $messages : '',
				'refresh'  => isset( WC()->session->refresh_totals ),
				'reload'   => isset( WC()->session->reload_checkout ),
			);

			unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

			wp_send_json( $response );
		}
	}
	
	protected function send_ajax_success_response(){
		if(is_ajax()){
			wp_send_json( array(
				'result' => 'success',
			));
		}
	}
}

endif;