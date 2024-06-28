<?php
/**
 * Customer Signup coupon email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/crp_email_template.php.
 *
 * @package    Coupon_Referral_Program
 * @subpackage Coupon_Referral_Program/emails/template
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Include the woo header.
 *
 * @hooked WC_Emails::email_header() Output the email header
 * @since 1.6.4
 * @param string $email_heading
 * @param string $email
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p><?php esc_html_e( 'Below is the Referral link. ', 'coupon-referral-program' ); ?></p>
<p><?php echo esc_html__( 'Referred By :- ', 'coupon-referral-program' ) . esc_html( $user_name ); ?></p>
<?php
if ( isset( $additional_content ) && '' !== $additional_content ) {
		echo wp_kses_post( $additional_content );
} else {
	?>
<style>
@media screen and (max-width: 600px) {
	.mwb_wuc_price_code_wrapper {
		width: 100% !important;
		display: block;
		padding: 15px 10px !important;
	}
}
</style>
<table class="mwb_wuc_email_template" style="width: 100%!important; max-width: 600px; text-align: center; font-size: 20px;" role="presentation" border="0" width="600" cellspacing="0" cellpadding="0" align="center">
	<tbody>
		<tr>
			<td style="background: #fff;">
				<table style="border: 2px dashed #b9aca1;" border="0" width="100%" cellspacing="0" cellpadding="0">
					<tbody>
						<tr>
							<td colspan="2">
								<div style="text-align: center;"><span style="display: inline-block;padding: 5px 15px; border: 1px dashed #6d5050; margin-bottom: 10px; background-color: rgba(241, 225, 225, 0.12); font-weight: bold;"><?php echo esc_html( $refferal_link ); ?></span></div>
							</td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
	<?php
}

/**
 * Include the woo footer.
 *
 * @hooked WC_Emails::email_footer() Output the email footer
 * @since 1.6.4
 * @param string $email
 */
do_action( 'woocommerce_email_footer', $email );
