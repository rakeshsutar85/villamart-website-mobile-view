<?php
/**
 * Some common functions for Saved Addresses For WooCommerce
 *
 * @package     saved-addresses-for-woocommerce/includes/
 * @since       2.3.0
 * @version     1.1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugins data
 *
 * @return array
 */
function get_saw_plugin_data() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	return get_plugin_data( SAW_PLUGIN_FILE );
}

/**
 * Get the saved addresses edit address endpoint URL based on action.
 *
 * @param string $key Current address key.
 * @param string $address_type Billing or Shipping address.
 * @param string $action Whether to add or edit address.
 *
 * @return string
 */
function get_saw_endpoint_url( $key, $address_type, $action ) {

	if ( 'edit' === $action ) {
		$url = wc_get_endpoint_url( 'edit-address', $address_type, get_permalink() );

		return add_query_arg( 'saw', $key, $url );
	} elseif ( 'add' === $action ) {
		$url = wc_get_endpoint_url( 'edit-address', $address_type, get_permalink() );

		return add_query_arg( 'saw_type', $action, $url );
	}

}

/**
 * Function to get default billing & shipping address key for a logged in user.
 *
 * @param number $user_id      The User ID.
 * @param string $address_type Billing or Shipping address.
 *
 * @return number Default address key based on address type.
 */
function get_default_address_key( $user_id, $address_type ) {

	if ( empty( $user_id ) ) {
		return '';
	}
	if ( empty( $address_type ) ) {
		$address_type = 'billing';
	}
	$default_key = '';
	$user        = get_user_by( 'ID', $user_id );

	if ( $user instanceof WP_User ) {
		$default_address_keys = get_user_meta( $user_id, 'sa_saved_default_address_keys', true );
		if ( ! empty( $default_address_keys ) && isset( $default_address_keys[ $address_type ] ) ) {
			$default_key = $default_address_keys [ $address_type ];
		}
	}

	return $default_key;

}
