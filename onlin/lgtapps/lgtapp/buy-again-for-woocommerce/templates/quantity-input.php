<?php
/**
 * This template displays buy again products quantity
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/quantity-input.php
 *
 * To maintain compatibility, buy again will update the template files and you have to copy the updated files to your theme
 */

defined( 'ABSPATH' ) || exit;

if ( ( $max_value && $min_value === $max_value ) || ! $qty_allow || $product_obj->is_sold_individually() ) {
	?>
	<?php if ( $qty_allow ) : ?>
		<label><?php echo esc_attr( $min_value ); ?></label>
	<?php endif; ?>
	<div class="quantity bya_quantity hidden">
		<input 
			type="hidden" 
			id="<?php echo esc_attr( $input_id ); ?>" 
			class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>" 
			name="<?php echo esc_attr( $input_name ); ?>" 
			value="<?php echo esc_attr( $min_value ); ?>" 
			data-product_id="<?php echo esc_attr( $product_id ); ?>" 
			data-variation_id="<?php echo esc_attr( $variation_id ); ?>"/>
	</div>
	<?php
} else {
	?>
	<div class="quantity bya_quantity">
	<input 
		type="number"
		id="<?php echo esc_attr( $input_id ); ?>"
		class="<?php echo esc_attr( join( ' ', (array) $classes ) ); ?>"
		step="<?php echo esc_attr( $step ); ?>"
		min="<?php echo esc_attr( $min_value ); ?>"
		max="<?php echo esc_attr( 0 < $max_value ? $max_value : '' ); ?>"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		title="<?php echo esc_html__( 'Qty', 'buy-again-for-woocommerce' ); ?>"
		size="4"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		inputmode="<?php echo esc_attr( $inputmode ); ?>" 
		data-product_id="<?php echo esc_attr( $product_id ); ?>" 
		data-variation_id="<?php echo esc_attr( $variation_id ); ?>"/>

		<div class="bya_dialog_box" style="display:none"></div>
	</div><br/><br/>
	<?php
}
