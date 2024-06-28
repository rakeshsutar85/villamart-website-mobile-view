<?php
/**
 * This template displays quantity , add to cart and buy again fields
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/display-input-fields.php
 *
 * To maintain compatibility, Buy Again will update the template files and you have to copy the updated files to your theme
 *
 * @package Buy Again for Woocommerce/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_obj                    = wc_get_product( $product_id );
$qty_class                      = ( 0 === $variation_id ) ? 'bya_qty_field_' . $variation_id : 'bya_qty_field_' . $product_id;
$stock_status                   = $product_obj->get_stock_status();
$quantity_max                   = $product_obj->backorders_allowed() ? '' : $product_obj->get_stock_quantity();
$show_add_to_cart               = get_option( 'bya_localization_allow_add_to_cart_col', 'yes' );
$buy_again_label                = get_option( 'bya_localization_buy_again_label', esc_html__( 'Buy Now', 'buy-again-for-woocommerce' ) );
$view_order_menu_slug           = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
$buy_again_my_account_menu_slug = get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' );
$add_to_cart_allow_pages        = get_option( 'bya_general_buy_again_add_to_cart_to_show', array( $view_order_menu_slug, $buy_again_my_account_menu_slug ) );
$buy_again_allow_pages          = get_option( 'bya_general_buy_again_buy_now_to_show', array( $view_order_menu_slug, $buy_again_my_account_menu_slug ) );
$condition_to_show_add_to_cart  = false;
$condition_to_show_buy_now      = false;

if ( bya_check_is_array( $add_to_cart_allow_pages ) ) {
	if ( in_array( $page, $add_to_cart_allow_pages, true ) ) {
		$condition_to_show_add_to_cart = true;
	} elseif ( empty( $page ) && ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$condition_to_show_add_to_cart = true;
	}
}

if ( 'variable' === $product_obj->get_type() && ! empty( $variation_id ) ) {
	$variation_obj = wc_get_product( $variation_id );
	$stock_status  = $variation_obj->get_stock_status();
}

if ( bya_check_is_array( $buy_again_allow_pages ) ) {
	if ( in_array( $page, $buy_again_allow_pages, true ) ) {
		$condition_to_show_buy_now = true;
	} elseif ( empty( $page ) && ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		$condition_to_show_buy_now = true;
	}
}

if ( $view_order_menu_slug !== $page && $buy_again_my_account_menu_slug !== $page ) {
	/**
	 * Filter to Buy Again add to cart condition.
	 *
	 * @since 1.0
	 * */
	$condition_to_show_add_to_cart = apply_filters( 'bya_add_to_cart_button_condition_to_show', $condition_to_show_add_to_cart );

	/**
	 * Filter to Buy Again buy now condition.
	 *
	 * @since 1.0
	 * */
	$condition_to_show_buy_now = apply_filters( 'bya_buy_now_button_condition_to_show', $condition_to_show_buy_now );
}

if ( ! is_object( $product_obj ) ||
	! $product_obj->is_visible() ||
	! $product_obj->is_purchasable() ||
	in_array( $product_obj->get_status(), array( 'draft', 'trash', 'pending' ) )
	) {
	?>
	<div class="bya_out_of_stock">
		<?php esc_html_e( 'This product was currently unavailable', 'buy-again-for-woocommerce' ); ?>
	</div>
	<?php
} elseif ( 'variable' === $product_obj->get_type() && empty( $variation_id ) ) {
	?>
	<div class="bya_out_of_stock">
		<?php esc_html_e( 'This variation was currently unavailable', 'buy-again-for-woocommerce' ); ?>
	</div>
	<?php
} elseif ( '0' == $quantity_max || 'outofstock' === $stock_status ) {
	?>
	<div class="bya_out_of_stock">
		<?php esc_html_e( 'Out of Stock', 'buy-again-for-woocommerce' ); ?>
	</div>
	<?php
} else {
	/* Include admin html settings */
	include 'views/html-quantity-field.php';

	$redirect_url = '';

	if ( post_password_required( $product_id ) ) {
		$redirect_url = $product_obj->get_permalink();
	}

	if ( 'yes' == $show_add_to_cart && $condition_to_show_add_to_cart ) {
		$add_to_cart_label = get_option( 'bya_localization_add_to_cart_label', esc_html__( 'Add to cart', 'buy-again-for-woocommerce' ) );
		$key               = empty( $variation_id ) ? $product_id : $variation_id;
		$unique_class      = 'bya_ajax_add_to_cart_' . $key;
		?>
		<div class="bya_add_to_cart_container">
			<button 
				data-product_id="<?php echo esc_attr( $product_id ); ?>"
				data-redirect_url="<?php echo esc_url( $redirect_url ); ?>"
				data-variation_id ="<?php echo esc_attr( $variation_id ); ?>" 
				data-quantity="1"
				data-bya_order_id ="<?php echo esc_attr( $bya_order_id ); ?>"
				class="button alt bya-add-to-cart product_type_simple bya_add_to_cart_btn <?php echo esc_attr( $unique_class ); ?>"> 
					<?php echo esc_html( $add_to_cart_label ); ?> 
			</button>
		</div>  
		<?php
	}

	if ( $condition_to_show_buy_now ) {
		$key          = empty( $variation_id ) ? $product_id : $variation_id;
		$unique_class = 'bya_buy_again_' . $key;
		?>
		<div class="bya_buy_again_container">
			<button name="buy_again" 
					value="<?php echo esc_attr( $product_id ); ?>" 
					data-redirect_url="<?php echo esc_url( $redirect_url ); ?>"
					data-variation_id ="<?php echo esc_attr( $variation_id ); ?>" 
					data-bya_order_id="<?php echo esc_attr( $bya_order_id ); ?>"
					class="buy_again_btn button alt bya-buy-again <?php echo esc_attr( $unique_class ); ?>"> 
				<?php echo esc_html( $buy_again_label ); ?> 
			</button>
		</div>

		<?php
	}
}
