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
				<?php wc_back_link( '', bya_get_purchase_history_url() ); ?>
				<h1 class="wp-heading-inline">
					<?php
					/* translators: %s: User name */
					echo wp_kses_post( sprintf( esc_html__( 'Username: %s', 'buy-again-for-woocommerce' ), $user_name ) . '<br/>' . esc_html__( 'Products Details', 'buy-again-for-woocommerce' ) );
					?>
				</h1>
				<hr class="wp-header-end">
			</div>
			<?php $post_table->display(); ?>
		</div>
	</form>
</div>
<?php
