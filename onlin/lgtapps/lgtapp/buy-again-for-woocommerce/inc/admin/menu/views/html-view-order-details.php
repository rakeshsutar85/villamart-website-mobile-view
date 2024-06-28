<?php
/**
 * User Details
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class = "wrap <?php echo esc_attr( self::$plugin_slug ); ?>_wrapper_cover woocommerce">
	<form method = "post" id="bya_buy_again_form" enctype = "multipart/form-data">
		<div class="bya_table_wrap">
			<div class="bya-head-button">
				<?php wc_back_link( '', bya_get_product_details_url( $bya_post_id, $current_user_id ) ); ?>
				<h1 class="wp-heading-inline">
					<?php
					/* translators: %s: User name */
					echo wp_kses( sprintf( esc_html__( 'Username : %s ', 'buy-again-for-woocommerce' ), $user_name ), array() );
					/* translators: %s: Product name */
					echo '<br/>' . wp_kses( sprintf( esc_html__( 'Product Name : %s ', 'buy-again-for-woocommerce' ), $product_name ), array() );
					?>
				</h1>
				<hr class="wp-header-end">
			</div>
			<?php $post_table->display(); ?>
		</div>
	</form>
</div>
<?php
