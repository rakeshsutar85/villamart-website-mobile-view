<?php
/**
 * This template displays MyAccount Buy Again
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/myaccount-buy-again.php
 *
 * To maintain compatibility, buy again will update the template files and you have to copy the updated files to your theme
 *
 * @package Buy Again for Woocommerce\Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Action hook Adjust Befor Myaccount buy again content.
 *
 * @since 1.0
 */
do_action( 'bya_before_myaccount_buy_again_content' );
$columns             = bya_get_buy_again_product_table_heading();
$display_label_count = $columns['display_label_count'];
unset( $columns['display_label_count'] );
?>
<div class="bya_myaccount_buy_again_wrapper"> 
	<?php
		bya_get_template( 'buy-again-filters.php', array( 'product_count' => $product_count ) );
	?>
	<div class="bya_product_table_container">
		<table class="bya_buy_again_product_table">
			<thead>
				<tr>
					<?php
					foreach ( $columns as $column_name ) :
						if ( true === $column_name['display'] && ! empty( $column_name['value'] ) ) :
							?>
							<th>
								<?php echo wp_kses_post( $column_name['value'] ); ?>
							</th>
							<?php
						endif;
					endforeach;
					?>
				</tr>
			</thead>
			<?php if ( ! bya_check_is_array( $product_ids ) ) { ?>
				<tbody>
					<tr>
						<td colspan=<?php echo esc_attr( $display_label_count ); ?>><?php esc_html_e( 'No Product(s) Found', 'buy-again-for-woocommerce' ); ?></td>
					</tr>
				</tbody>
				<?php
			} else {
				$args = array(
					'user_id'                          => $user_id,
					'product_ids'                      => $product_ids,
				);

				bya_get_template( 'myaccount-buy-again-table.php', $args );
			}
			?>
			<?php if ( $pagination['page_count'] > 1 ) { ?>
				<tfoot>
					<tr>
						<td colspan="<?php echo esc_attr( count( $columns ) ); ?>" class="actions footable-visible">
							<?php bya_get_template( 'pagination.php', $pagination ); ?>
						</td>
					</tr>
				</tfoot>
			<?php } ?>
		</table>
	</div>
	<?php

	/**
	 * Action hook to adjust After Buy Again Content.
	 *
	 * @since 1.0
	 */
	do_action( 'bya_before_myaccount_buy_again_content' );
	?>
</div>
<?php
