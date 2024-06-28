<?php
/**
 * Shortcode Content
 *
 * @package Buy Again for Woocommerce\Admin\Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Action hook to adjust Before Shortcode Contents.
 *
 * @since 1.0
 */
do_action( 'bya_before_shortcode_contents' );
?>
<table class="form-table bya_shortcodes_info widefat striped">
	<thead>
		<tr>
			<th><?php esc_html_e( 'Shortcode', 'buy-again-for-woocommerce' ); ?></th>
			<th><?php esc_html_e( 'Parameter Support', 'buy-again-for-woocommerce' ); ?></th>
			<th><?php esc_html_e( 'Description', 'buy-again-for-woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ( bya_check_is_array( $shortcodes_info ) ) {
			foreach ( $shortcodes_info as $key => $values ) {
				?>
				<tr>
					<td><?php echo esc_html( $key ); ?></td>
					<td><?php echo esc_html( $values['supported_parameters'] ); ?></td>
					<td><?php echo esc_html( $values['usage'] ); ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
</table>

<?php
/**
 * Action hook to adjust After Shortcode Contents.
 *
 * @since 1.0
 */
do_action( 'bya_after_shortcodes_content' );
