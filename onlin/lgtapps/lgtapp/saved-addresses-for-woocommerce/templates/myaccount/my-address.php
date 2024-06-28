<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-address.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

$b_oldcol                = 1;
$b_col                   = 1;
$saved_billing_addresses = get_user_meta( $customer_id, 'sa_saved_formatted_billing_addresses', true );
?>

<div class="u-columns woocommerce-Addresses col2-set addresses">
	<?php
	if ( ! empty( $saved_billing_addresses ) ) {
		?>
		<h3 class="saw-billing"><?php echo esc_html__( 'Saved billing addresses', 'saved-addresses-for-woocommerce' ); ?>
			<a href="<?php echo esc_url( get_saw_endpoint_url( 'edit-address', 'saw_billing', 'add' ) ); ?>" class="add"><?php echo esc_html_x( 'Add new', 'billing address', 'saved-addresses-for-woocommerce' ); ?></a>
		</h3>
		<?php
		foreach ( $saved_billing_addresses as $key => $billing_address ) {
			$b_col    = $b_col * -1;
			$b_oldcol = $b_oldcol * -1;
			?>
			<div class="u-column<?php echo $b_col < 0 ? 1 : 2; ?> col-<?php echo $b_oldcol < 0 ? 1 : 2; ?> woocommerce-Address" id="billing_address_<?php echo esc_html( $key ); ?>">
				<header class="woocommerce-Address-title title">
					<h3></h3>
				</header>
				<address>
					<?php
						echo wp_kses_post( $billing_address );
					?>
					<br><br>
					<div class="account-billing-actions">
						<a title="<?php esc_attr_e( 'Edit this address', 'saved-addresses-for-woocommerce' ); ?>" href="<?php echo esc_url( get_saw_endpoint_url( $key, 'billing', 'edit' ) ); ?>" class="edit saw-edit"><?php echo esc_html__( 'Edit', 'saved-addresses-for-woocommerce' ); ?></a> | 
						<a title="<?php esc_attr_e( 'Delete this address', 'saved-addresses-for-woocommerce' ); ?>" data-delete-id='<?php echo esc_html( $key ); ?>' id="delete-billing" class="saw-delete"><?php echo esc_html__( 'Delete', 'saved-addresses-for-woocommerce' ); ?></a> | 
						<?php
							$is_default_address = SA_Saved_Addresses_For_WooCommerce::get_instance()->is_default_address( $key, 'billing' );
							$default_class      = ( true === $is_default_address ) ? 'is-default' : 'not-is-default';
							$default_text       = ( true === $is_default_address ) ? __( 'Default', 'saved-addresses-for-woocommerce' ) : _x( 'Set default', 'set default billing', 'saved-addresses-for-woocommerce' );
							$default_title      = ( true === $is_default_address ) ? __( 'Default address', 'saved-addresses-for-woocommerce' ) : _x( 'Set this as default address', 'billing', 'saved-addresses-for-woocommerce' );
						?>
						<span>
							<a title="<?php echo sprintf( ( '%s' ), esc_html( $default_title ) ); ?>" data-default-id='<?php echo esc_html( $key ); ?>' id="modify-default-billing" class="<?php echo sprintf( ( '%s' ), esc_html( $default_class ) ); ?>"><?php echo sprintf( ( '%s' ), esc_html( $default_text ) ); ?></a>
						</span>
					</div>
				</address>
			</div>
			<?php
		}
	} else {
		?>
		<header class="woocommerce-Address-title title">
			<h3><?php echo esc_html__( 'Billing address', 'saved-addresses-for-woocommerce' ); ?></h3>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'billing' ) ); ?>" class="add" style="margin-left: 0.5em;"><?php echo esc_html_x( 'Add', 'billing address', 'saved-addresses-for-woocommerce' ); ?></a>
		</header>
		<address>
			<?php
				echo esc_html_e( 'You have not set up this type of address yet.', 'saved-addresses-for-woocommerce' );
			?>
		</address>
		<?php
	}
	?>
</div>

<?php
if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$s_oldcol                 = 1;
	$s_col                    = 1;
	$saved_shipping_addresses = get_user_meta( $customer_id, 'sa_saved_formatted_addresses', true );
	?>
	<div class="u-columns woocommerce-Addresses col2-set addresses">
		<?php
		if ( ! empty( $saved_shipping_addresses ) ) {
			?>
			<br>
			<h3 class="saw-shipping"><?php echo esc_html__( 'Saved shipping addresses', 'saved-addresses-for-woocommerce' ); ?>
				<a href="<?php echo esc_url( get_saw_endpoint_url( 'edit-address', 'saw_shipping', 'add' ) ); ?>" class="add"><?php echo esc_html_x( 'Add new', 'shipping address', 'saved-addresses-for-woocommerce' ); ?></a>
			</h3>
			<?php
			foreach ( $saved_shipping_addresses as $key => $shipping_address ) {
				$s_col    = $s_col * -1;
				$s_oldcol = $s_oldcol * -1;
				?>
				<div class="u-column<?php echo $s_col < 0 ? 1 : 2; ?> col-<?php echo $s_oldcol < 0 ? 1 : 2; ?> woocommerce-Address" id="shipping_address_<?php echo esc_html( $key ); ?>">
					<header class="woocommerce-Address-title title">
						<h3></h3>
					</header>
					<address>
						<?php
							echo wp_kses_post( $shipping_address );
						?>
						<br><br>
						<div class="account-shipping-actions">
							<a title="<?php esc_attr_e( 'Edit this address', 'saved-addresses-for-woocommerce' ); ?>" href="<?php echo esc_url( get_saw_endpoint_url( $key, 'shipping', 'edit' ) ); ?>" class="edit saw-edit"><?php echo esc_html__( 'Edit', 'saved-addresses-for-woocommerce' ); ?></a> | 
							<a title="<?php esc_attr_e( 'Delete this address', 'saved-addresses-for-woocommerce' ); ?>" data-delete-id='<?php echo esc_html( $key ); ?>' id="delete-shipping" class="saw-delete"><?php echo esc_html__( 'Delete', 'saved-addresses-for-woocommerce' ); ?></a> | 
							<?php
								$is_default_address = SA_Saved_Addresses_For_WooCommerce::get_instance()->is_default_address( $key, 'shipping' );
								$default_class      = ( true === $is_default_address ) ? 'is-default' : 'not-is-default';
								$default_text       = ( true === $is_default_address ) ? __( 'Default', 'saved-addresses-for-woocommerce' ) : _x( 'Set default', 'set default shipping', 'saved-addresses-for-woocommerce' );
								$default_title      = ( true === $is_default_address ) ? __( 'Default address', 'saved-addresses-for-woocommerce' ) : _x( 'Set this as default address', 'shipping', 'saved-addresses-for-woocommerce' );
							?>
							<span>
								<a title="<?php echo sprintf( ( '%s' ), esc_html( $default_title ) ); ?>" data-default-id='<?php echo esc_html( $key ); ?>' id="modify-default-shipping" class="<?php echo sprintf( ( '%s' ), esc_html( $default_class ) ); ?>"><?php echo sprintf( ( '%s' ), esc_html( $default_text ) ); ?></a>
							</span>
						</div>
					</address>
				</div>
				<?php
			}
		} else {
			?>
			<header class="woocommerce-Address-title title">
				<h3><?php echo esc_html__( 'Shipping address', 'saved-addresses-for-woocommerce' ); ?></h3>
				<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping' ) ); ?>" class="add" style="margin-left: 0.5em;"><?php echo esc_html_x( 'Add', 'shipping address', 'saved-addresses-for-woocommerce' ); ?></a>
			</header>
			<address>
				<?php
					echo esc_html_e( 'You have not set up this type of address yet.', 'saved-addresses-for-woocommerce' );
				?>
			</address>
			<?php
		}
		?>
	</div>
	<?php
}
