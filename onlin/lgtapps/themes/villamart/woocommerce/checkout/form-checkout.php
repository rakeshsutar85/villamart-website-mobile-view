<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- CUSTOM CODE STARTS -->
<?php
if (  ! is_user_logged_in() ) {
?>
<div class="container">
  <div class="row" style="text-align:center;">
    <div class="col-sm" style="">
        <h6 style="font-size:14px !important;font-weight:600;">EXISTING CUSTOMER</h6>
        <a href="/my-account" title="LOGIN"><button type="button" class="btn btn-primary btn-lg" style="font-size:14px !important;font-weight:600;line-height:24px;vertical-align:middle; ">
            <i class="fa-sharp fa-solid fa-key"></i> &nbsp;&nbsp;LOGIN</button></a>
    </div>
    <div class="col-sm" style="">
        <h6 style="font-size:14px !important;font-weight:600;">NEW CUSTOMER</h6>
        <a href="/my-account" title="REGISTER"><button type="button" class="btn btn-success btn-lg" style="font-size:14px !important;font-weight:600;line-height:24px;vertical-align:middle; ">
        <i class="fa-solid fa-user-plus"></i>&nbsp;&nbsp;REGISTER</button></a>
    </div>
    </div>
    <br />
    <div class="clear;"></div>
</div>

    
<?php }else {}?>


<!-- CUSTOM CODE ENDS -->

<?php
do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', esc_html__( 'You must be logged in to checkout.', 'bacola' ) ) );
	return;
}

?>



<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
	<div class="cart-form-wrapper">
		<div class="row content-wrapper sidebar-right">
			<div class="col-12 col-md-12 col-lg-12 content-primary">




				<div class="cart-wrapper">

					<?php if ( $checkout->get_checkout_fields() ) : ?>

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<div class="col2-set" id="customer_details">
							<div class="col-1">
								<?php do_action( 'woocommerce_checkout_billing' ); ?>
							</div>

							<div class="col-2">
								<?php do_action( 'woocommerce_checkout_shipping' ); ?>
							</div>
						</div>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

					<?php endif; ?>


					<div class="order-review-wrapper">
						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
						
						<h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'bacola' ); ?></h3>
						
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>

						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
					</div>

				</div>
				
			</div>
		</div>
	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
