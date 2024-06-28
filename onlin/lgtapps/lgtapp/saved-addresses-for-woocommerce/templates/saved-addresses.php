<?php
/**
 * Template for saved addresses on the checkout
 *
 * @package saved-addresses-for-woocommerce/templates/
 * @version 1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user_id = get_current_user_id();

if ( 'billing' === $address_type ) {

	// Format address for select2.
	$formatted_billing_address = array();
	foreach ( $sa_saved_addresses as $key => $address ) {
		$formatted_billing_address [ $key ] = SA_Saved_Addresses_For_WooCommerce::get_instance()->saw_format_address_for_checkout( $address, 'billing' );
	}

	$two_billing_addresses = array();
	// Extract 2 addresses - 1 default & 1 last entry from above if 2 or more billing addresses.
	if ( count( $formatted_billing_address ) >= 2 ) {
		// Default billing address.
		$default_billing_address_key = get_default_address_key( $current_user_id, 'billing' );
		if ( array_key_exists( $default_billing_address_key, $formatted_billing_address ) ) {
			$two_billing_addresses[ $default_billing_address_key ] = $formatted_billing_address[ $default_billing_address_key ];
			unset( $formatted_billing_address[ $default_billing_address_key ] );
		}

		// Pull last billing address.

		// Can improve following line with array_key_last() when PHP 7.2+.
		$last_billing_key                           = array_keys( $formatted_billing_address )[ count( $formatted_billing_address ) - 1 ];
		$two_billing_addresses[ $last_billing_key ] = $formatted_billing_address [ $last_billing_key ];
		unset( $formatted_billing_address[ $last_billing_key ] );
	} else {
		$two_billing_addresses     = $formatted_billing_address;
		$formatted_billing_address = array();
	}
	?>
	<div class='sa_billing_addresses_container'>
		<div class='saved_address_options'>
			<div class='options_container'>
				<div class='address_selected'></div>
				<?php
				if ( count( $formatted_billing_address ) > 0 ) {
					?>
					<select id="wc_saved_billing_addresses" name="wc_saved_billing_addresses" class="wc-saw-search" data-placeholder="<?php esc_attr_e( 'Select an address...', 'saved-addresses-for-woocommerce' ); ?>" data-width="500px">
						<option></option>
						<?php
						if ( is_array( $formatted_billing_address ) && ! empty( $formatted_billing_address ) ) {
							foreach ( $formatted_billing_address as $key => $saved_billing_address ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $saved_billing_address ) . '</option>';
							}
						}
						?>
					</select><br><br>
					<?php
				}
				?>
				<a class='sa_saved_billing_addresses_button' id='bill_to_new_address_button'><?php echo esc_attr_e( 'Bill to a new address', 'saved-addresses-for-woocommerce' ); ?></a>
			</div>
		</div>
		<?php
		if ( ! empty( $two_billing_addresses ) ) {
			?>
			<div class='billing_addresses_container'>
			<?php
			if ( count( $two_billing_addresses ) > 0 ) {
				foreach ( $two_billing_addresses as $key => $value ) {
					// Pull address from formatted data.
					$billing_address = $sa_saved_formatted_addresses [ $key ];
					// Split it by br.
					$address = explode( '<br/>', $billing_address );
					// Make first value as bold.
					$address[0]      = '<b>' . $address[0] . '</b>';
					$billing_address = implode( '<br/>', $address );
					?>
					<div class='address_container_billing' id='billing_address_container_<?php echo esc_html( $key ); ?>'>
						<p class='single_address' value='<?php echo wp_kses_post( $billing_address ); ?>'><?php echo wp_kses_post( $billing_address ); ?></p>
						<div class='bill_to_this_address_button'>
							<input type='button' class="button" id='bill_here_button' data-address-id='<?php echo esc_html( $key ); ?>' value='<?php esc_attr_e( 'Bill to this address', 'saved-addresses-for-woocommerce' ); ?>'>
						</div>
						<div class="billing_to_this_address" style="display: none;">
							<div class="billing_address_selected"><span><?php echo esc_html__( 'selected', 'saved-addresses-for-woocommerce' ); ?></span></div>
						</div>
						<div class="billing_address_edit_delete">
							<a class="saw-edit" id="edit_address_<?php echo esc_html( $key ); ?>" data-edit-id='<?php echo esc_html( $key ); ?>'><?php echo esc_attr_e( 'Edit', 'saved-addresses-for-woocommerce' ); ?></a>
							<a class="saw-delete" id="delete_address_<?php echo esc_html( $key ); ?>" data-delete-id='<?php echo esc_html( $key ); ?>'><?php echo esc_attr_e( 'Delete', 'saved-addresses-for-woocommerce' ); ?></a>
						</div>
					</div>
					<?php
				}
			}
			?>
			</div>
			<?php
		}
		?>
	</div>

	<div class='billing_address_form'>
	<?php
} elseif ( 'shipping' === $address_type ) {

	// Format address for select2.
	$formatted_shipping_address = array();
	foreach ( $sa_saved_addresses as $key => $address ) {
		$formatted_shipping_address [ $key ] = SA_Saved_Addresses_For_WooCommerce::get_instance()->saw_format_address_for_checkout( $address, 'shipping' );
	}

	$two_shipping_addresses = array();
	// Extract 2 addresses - 1 default & 1 last entry from above if 2 or more shipping addresses.
	if ( count( $formatted_shipping_address ) >= 2 ) {
		// Default shipping address.
		$default_shipping_address_key = get_default_address_key( $current_user_id, 'shipping' );
		if ( array_key_exists( $default_shipping_address_key, $formatted_shipping_address ) ) {
			$two_shipping_addresses[ $default_shipping_address_key ] = $formatted_shipping_address[ $default_shipping_address_key ];
			unset( $formatted_shipping_address[ $default_shipping_address_key ] );
		}

		// Pull last shipping address.

		// Can improve following line with array_key_last() when PHP 7.2+.
		$last_shipping_key                            = array_keys( $formatted_shipping_address )[ count( $formatted_shipping_address ) - 1 ];
		$two_shipping_addresses[ $last_shipping_key ] = $formatted_shipping_address [ $last_shipping_key ];
		unset( $formatted_shipping_address[ $last_shipping_key ] );
	} else {
		$two_shipping_addresses     = $formatted_shipping_address;
		$formatted_shipping_address = array();
	}

	?>
	<div class='sa_shipping_addresses_container' >
		<div class='saved_address_options'>
			<div class='options_container'>
				<div class='address_selected'></div>
				<?php
				if ( count( $formatted_shipping_address ) > 0 ) {
					?>
					<select id="wc_saved_shipping_addresses" name="wc_saved_shipping_addresses" class="wc-saw-search" data-placeholder="<?php esc_attr_e( 'Select an address...', 'saved-addresses-for-woocommerce' ); ?>" data-width="500px">
						<option></option>
						<?php
						if ( is_array( $formatted_shipping_address ) && ! empty( $formatted_shipping_address ) ) {
							foreach ( $formatted_shipping_address as $key => $saved_shipping_address ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $saved_shipping_address ) . '</option>';
							}
						}
						?>
					</select><br><br>
					<?php
				}
				?>
				<a class='sa_saved_addresses_button' id='ship_to_new_address_button'><?php echo esc_attr_e( 'Ship to a new address', 'saved-addresses-for-woocommerce' ); ?></a>
			</div>
		</div>
		<?php
		if ( ! empty( $two_shipping_addresses ) ) {
			?>
			<div class='shipping_addresses_container'>
			<?php
			if ( count( $two_shipping_addresses ) > 0 ) {
				foreach ( $two_shipping_addresses as $key => $value ) {
					// Pull address from formatted data.
					$shipping_address = $sa_saved_formatted_addresses [ $key ];
					// Split it by br.
					$address = explode( '<br/>', $shipping_address );
					// Make first value as bold.
					$address[0]       = '<b>' . $address[0] . '</b>';
					$shipping_address = implode( '<br/>', $address );
					?>
					<div class='address_container_shipping' id='shipping_address_container_<?php echo esc_html( $key ); ?>'>
						<p class='single_address' value='<?php echo wp_kses_post( $shipping_address ); ?>'><?php echo wp_kses_post( $shipping_address ); ?></p>
						<div class='ship_to_this_address_button'>
							<input type='button' class="button" id='ship_here_button' data-address-id='<?php echo esc_html( $key ); ?>' value='<?php esc_attr_e( 'Ship to this address', 'saved-addresses-for-woocommerce' ); ?>'>
						</div>
						<div class="shipping_to_this_address" style="display: none;">
							<div class="shipping_address_selected"><span><?php echo esc_html__( 'selected', 'saved-addresses-for-woocommerce' ); ?></span></div>
						</div>
						<div class="shipping_address_edit_delete">
							<a class="saw-edit" id="edit_address_<?php echo esc_html( $key ); ?>" data-edit-id='<?php echo esc_html( $key ); ?>'><?php echo esc_attr_e( 'Edit', 'saved-addresses-for-woocommerce' ); ?></a>
							<a class="saw-delete" id="delete_address_<?php echo esc_html( $key ); ?>" data-delete-id='<?php echo esc_html( $key ); ?>'><?php echo esc_attr_e( 'Delete', 'saved-addresses-for-woocommerce' ); ?></a>
						</div>
					</div>
					<?php
				}
			}
			?>
			</div>
			<?php
		}
		?>
	</div>

	<div class='shipping_address_form'>
	<?php
}
