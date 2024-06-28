<?php
/**
 *  Quantity HTML Settings
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

	/**
	 * Filter to Buy Again Quantity Field Classes.
	 *
	 * @since 1.0
	 * */
	$classes = apply_filters( 'woocommerce_quantity_input_classes', array( 'bya_qty_field', 'input-text', 'qty', 'text' ), $product_obj );

	/**
	 * Filter to Buy Again Quantity Field Maximum Value.
	 *
	 * @since 1.0
	 * */
	$max_value = apply_filters( 'woocommerce_quantity_input_max', $quantity_max, $product_obj );

	/**
	 * Filter to Buy Again Quantity Field Minimum Value.
	 *
	 * @since 1.0
	 * */
	$min_value = apply_filters( 'woocommerce_quantity_input_min', 1, $product_obj );

	/**
	 * Filter to Buy Again Quantity Field Step Value.
	 *
	 * @since 1.0
	 * */
	$step_value = apply_filters( 'woocommerce_quantity_input_step', 1, $product_obj );

	/**
	 * Filter to Buy Again Quantity Field Pattern.
	 *
	 * @since 1.0
	 * */
	$pattern = apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' );

	/**
	 * Filter to Buy Again Quantity Field Input Method.
	 *
	 * @since 1.0
	 * */
	$input_mode = apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' );

	/**
	 * Filter to Buy Again Quantity Field Place Holder.
	 *
	 * @since 1.0
	 * */
	$place_holder = apply_filters( 'woocommerce_quantity_input_placeholder', '', $product_obj );
	$args         = array(
		'qty_allow'    => $qty_allow,
		'input_id'     => $quantity_id,
		'input_name'   => 'quantity',
		'input_value'  => '1',
		'classes'      => $classes,
		'max_value'    => $max_value,
		'min_value'    => $min_value,
		'step'         => $step_value,
		'pattern'      => $pattern,
		'inputmode'    => $input_mode,
		'product_name' => $product_obj ? $product_obj->get_title() : '',
		'placeholder'  => $place_holder,
		'product_id'   => $product_id,
		'variation_id' => $variation_id,
		'product_obj'  => $product_obj,
	);

	/**
	 * Filter to Buy Again Quantity Field Arguments.
	 *
	 * @since 1.0
	 * */
	$args = apply_filters( 'woocommerce_quantity_input_args', $args, $product_obj );

	bya_get_template( 'quantity-input.php', $args );

