<?php
/**
 * This template displays buy again products quantity
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/stock.php
 *
 * To maintain compatibility, buy again will update the template files and you have to copy the updated files to your theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p class="stock <?php echo esc_attr( $class ); ?>"><?php echo wp_kses_post( $availability ); ?></p>
