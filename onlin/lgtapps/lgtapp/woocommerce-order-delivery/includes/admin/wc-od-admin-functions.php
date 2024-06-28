<?php
/**
 * Admin Functions
 *
 * @package WC_OD/Admin/Functions
 * @version 1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Gets the current screen ID.
 *
 * @since 1.6.0
 *
 * @return string|false The screen ID. False otherwise.
 */
function wc_od_get_current_screen_id() {
	$screen_id = false;

	// It may not be available.
	if ( function_exists( 'get_current_screen' ) ) {
		$screen    = get_current_screen();
		$screen_id = isset( $screen, $screen->id ) ? $screen->id : false;
	}

	// Get the value from the request.
	if ( ! $screen_id && ! empty( $_REQUEST['screen'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$screen_id = wc_clean( wp_unslash( $_REQUEST['screen'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	return $screen_id;
}

/**
 * Gets the callback used to output the admin field by type.
 *
 * @since 1.5.0
 *
 * @param string $type The field type.
 * @return mixed
 */
function wc_od_admin_get_field_callback( $type ) {
	switch ( $type ) {
		case 'text':
		case 'hidden':
		case 'textarea':
			$callback = 'woocommerce_wp_' . $type . '_input';
			break;
		case 'select':
		case 'radio':
		case 'checkbox':
			$callback = 'woocommerce_wp_' . $type;
			break;
		default:
			$callback = 'wc_od_admin_' . $type . '_field';
			break;
	}

	/**
	 * Filters the callback used to output the admin field by type.
	 *
	 * @since 1.5.0
	 *
	 * @param mixed $callback The output callback.
	 */
	return apply_filters( "wc_od_admin_{$type}_field_callback", $callback );
}

/**
 * Outputs the admin field.
 *
 * @since 1.5.0
 * @since 2.3.0 Added parameter `$data`.
 *
 * @param array   $field The field data.
 * @param WC_Data $data  WC_Data object, will be preferred over post object when passed.
 */
function wc_od_admin_field( $field, WC_Data $data = null ) {
	if ( ! isset( $field['type'] ) ) {
		$field['type'] = 'text';
	}

	if ( isset( $field['class'] ) && is_array( $field['class'] ) ) {
		$field['class'] = join( ' ', $field['class'] );
	}

	$callback = wc_od_admin_get_field_callback( $field['type'] );

	if ( is_callable( $callback ) ) {
		call_user_func( $callback, $field, $data );
	}
}

/**
 * Outputs a time frame field.
 *
 * @since 1.5.0
 * @since 2.4.0 Added parameter `$data`.
 *
 * @param array   $field The field data.
 * @param WC_Data $data  WC_Data object, will be preferred over post object when passed.
 */
function wc_od_admin_time_frame_field( $field, WC_Data $data = null ) {
	global $post;

	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['value']         = isset( $field['value'] ) ? $field['value'] : wc_od_get_post_or_object_meta( $post, $data, $field['id'], true );
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	if ( ! is_array( $field['value'] ) ) {
		$field['value'] = array(
			'time_from' => '',
			'time_to'   => '',
		);
	}

	echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">';
	echo '<label>' . wp_kses_post( $field['label'] ) . '</label>';

	echo '<span class="time-range">';
	printf(
		'<input type="text" class="timepicker time-from" name="%1$s" id="%2$s" value="%3$s" placeholder="%4$s" />',
		esc_attr( $field['name'] . '[time_from]' ),
		esc_attr( $field['id'] . '_time_from' ),
		esc_attr( $field['value']['time_from'] ),
		esc_attr( __( 'time from', 'woocommerce-order-delivery' ) )
	);

	printf(
		'<input type="text" class="timepicker time-to" name="%1$s" id="%2$s" value="%3$s" placeholder="%4$s" />',
		esc_attr( $field['name'] . '[time_to]' ),
		esc_attr( $field['id'] . '_time_to' ),
		esc_attr( $field['value']['time_to'] ),
		esc_attr( __( 'time to', 'woocommerce-order-delivery' ) )
	);
	echo '</span>';

	echo '</p>';
}

/**
 * Gets the checkout location choices to use them in a select field.
 *
 * @since 1.9.4
 *
 * @return array
 */
function wc_od_get_checkout_location_choices() {
	/**
	 * Filters the checkout location choices.
	 *
	 * @since 1.9.4
	 *
	 * @param array $choices The checkout location choices.
	 */
	return apply_filters(
		'wc_od_checkout_location_choices',
		array(
			'before_customer_details' => __( 'Before customer details', 'woocommerce-order-delivery' ),
			'before_billing'          => __( 'Before billing details', 'woocommerce-order-delivery' ),
			'after_billing'           => __( 'After billing details', 'woocommerce-order-delivery' ),
			'before_order_notes'      => __( 'Before order notes', 'woocommerce-order-delivery' ),
			'after_order_notes'       => __( 'After order notes', 'woocommerce-order-delivery' ),
			'after_additional_fields' => __( 'After additional fields', 'woocommerce-order-delivery' ),
			'after_order_review'      => __( 'Between order review and payments', 'woocommerce-order-delivery' ),
			'after_customer_details'  => __( 'After customer details', 'woocommerce-order-delivery' ),
		)
	);
}
