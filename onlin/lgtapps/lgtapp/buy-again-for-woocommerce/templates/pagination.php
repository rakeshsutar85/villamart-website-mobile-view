<?php
/**
 * This template displays buy again products table pagination
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/pagination.php
 *
 * To maintain compatibility, buy again will update the template files and you have to copy the updated files to your theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<nav class="pagination pagination-centered woocommerce-pagination">
	<ul class="page-numbers">
		<li><span class="page-numbers bya_pagination bya_first_pagination bya_readonly" data-page="1"><<</span></li>
		<li><span class="page-numbers bya_pagination bya_prev_pagination bya_readonly" data-page="<?php echo esc_attr( $prev_page_count ); ?>"><</span></li>
		<span class="paging-input">
			<label for="bya_current_page_selector" class="screen-reader-text"><?php esc_html_e( 'Current Page', 'buy-again-for-woocommerce' ); ?></label>
			<input class="bya-current-page" id="bya_current_page_selector" type="text" name="paged" value="<?php echo esc_attr( $current_page ); ?>">
			<span class="bya-paging-text" data-total_page="<?php echo esc_attr( $page_count ); ?>"> 
			<?php
				/* translators: %s: Total Page count */
				echo sprintf( esc_html__( 'of %s', 'buy-again-for-woocommerce' ), esc_attr( $page_count ) );
			?>
			</span>
		</span>
		<li><span class="page-numbers bya_pagination bya_next_pagination" data-page="<?php echo esc_attr( $next_page_count ); ?>">></span></li>
		<li><span class="page-numbers bya_pagination bya_last_pagination" data-page="<?php echo esc_attr( $page_count ); ?>">>></span></li>
	</ul>
</nav>
<?php
