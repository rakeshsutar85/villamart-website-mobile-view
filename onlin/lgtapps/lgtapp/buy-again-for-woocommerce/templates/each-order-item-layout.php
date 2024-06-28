<?php
/**
 * This template displays contents inside each order items in table
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/each-order-item-layout.php
 *
 * To maintain compatibility, Buy Again will update the template files and you have to copy the updated files to your theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$quantity_max = $product_obj->backorders_allowed() ? '' : $product_obj->get_stock_quantity();
$product_type = $product_obj->get_type();
?>
<div class="bya_order_item_fields">
	<?php
	$quantity_id = ( $variation_id ) ? 'bya_qty_' . $variation_id : 'bya_qty_' . $product_id;
	$args        = array(
		'product_id'              => $product_id,
		'variation_id'            => $variation_id,
		'quantity_id'             => $quantity_id,
		'add_to_cart_class'       => 'bya_add_to_cart_' . $product_id,
		'add_to_cart_ajax_enable' => bya_add_to_cart_ajax_enable(),
		'cartlink'                => '?add-to-cart=' . $product_id,
		'qty_allow'               => ( 'yes' === get_option( 'bya_general_hide_qty_field_in_order_detaile_page', 'no' ) ) ? false : true,
		'bya_order_id'            => $bya_order_id,
		'page'                    => get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' ),
	);

	bya_get_template( 'display-input-fields.php', $args );
	?>
</div>
