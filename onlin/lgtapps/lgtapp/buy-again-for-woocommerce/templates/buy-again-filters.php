<?php
/**
 * This template displays contents of buy again table filters
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/buy-again-filters.php
 *
 * To maintain compatibility, Buy Again will update the template files and you have to copy the updated files to your theme
 *
 * @package Buy Again/Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$order_by = isset( $_REQUEST['orderby'] ) ? wc_clean( wp_unslash( $_REQUEST['orderby'] ) ) : '';
$_order   = isset( $_REQUEST['order'] ) ? wc_clean( wp_unslash( $_REQUEST['order'] ) ) : '';

if ( 'title' === $order_by ) {
	$sort_by = ( 'desc' === $_order ) ? '3' : '2';
} elseif ( 'recent' === $order_by ) {
	$sort_by = '1';
} else {
	$sort_by = get_option( 'bya_advanced_buy_again_table_sort', '1' );
}

$keys                  = array( 'search', 'time_filter', 'start_date', 'end_date' );
$bya_filter_form_class = 'bya_hide';

foreach ( $keys as $key ) {
	if ( isset( $_REQUEST[ $key ] ) ) {
		$bya_filter_form_class = '';
		break;
	}
}

?>
<div class="bya_table_filter_controls_wrap">
	<div class="bya_product_count">
		<p class="bya_product_count"> 
			<?php
			/* translators: %s: Product Count */
			echo sprintf( esc_html__( 'Total Product(s): %s', 'buy-again-for-woocommerce' ), esc_attr( $product_count ) );
			?>
		</p>
	</div>
	<div class="bya_product_sort_container">
		<label for="bya_start_date"><?php esc_html_e( 'Sort by: ', 'buy-again-for-woocommerce' ); ?></label>
		<select name="sort_by" class="bya_sort_action_selector">
			<?php foreach ( bya_get_buy_again_product_sort_options() as $option_id => $option_value ) : ?>
				<option value="<?php echo esc_attr( $option_id ); ?>" <?php echo selected( $sort_by, $option_id ); // WPCS: XSS ok. ?>><?php echo esc_html( $option_value ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php if ( bya_check_filter_btn_display() ) : ?>
		<div class="bya_filter_button_container">
			<button class="bya_filter_button"> <?php echo esc_attr( get_option( 'bya_localization_filter_btn_label', 'Filter' ) ); ?></button>
		</div>
	<?php endif; ?>
</div>


<form class="bya_table_filter_wrap <?php echo esc_attr( $bya_filter_form_class ); ?>" method = "post" id="bya_buy_again_table_form" enctype = "multipart/form-data">
	<?php
	if ( 'yes' === get_option( 'bya_localization_allow_search_box', 'yes' ) ) :
		$search_val = isset( $_REQUEST['search'] ) ? wc_clean( wp_unslash( $_REQUEST['search'] ) ) : '';
		?>
			<div class="bya_product_search_container">
				<input type="search" name="search" class="bya_product_search_inp" value="<?php echo esc_attr( $search_val ); ?>" placeholder="<?php echo esc_attr( get_option( 'bya_localization_search_field_label', 'Search Products' ) ); ?>"/>
			</div>
		<?php
		endif;

	if ( 'yes' === get_option( 'bya_advanced_allow_filter_by', 'yes' ) ) :
		?>
	<div class="bya_product_time_filter_container">
		<div class="bya_time_filter_wrap">
		<?php $time_filter_val = isset( $_REQUEST['time_filter'] ) ? wc_clean( wp_unslash( $_REQUEST['time_filter'] ) ) : ''; ?>
			<label for="bya_time_filter"><?php echo esc_attr( get_option( 'bya_localization_filter_by_label', 'Filter By:' ) ); ?></label>
			<select name="time_filter" id="bya_time_filter" class="bya_time_filter">
				<?php foreach ( bya_get_time_filter_options() as $option_id => $option_value ) : ?>
					<option value="<?php echo esc_attr( $option_id ); ?>" <?php echo selected( $time_filter_val, $option_id ); // WPCS: XSS ok. ?>><?php echo esc_html( $option_value ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="bya_start_date_wrap">
			<label for="bya_start_date"><?php esc_html_e( 'From: ', 'buy-again-for-woocommerce' ); ?></label>
			<?php
			$start_date_val = isset( $_REQUEST['start_date'] ) ? wc_clean( wp_unslash( $_REQUEST['start_date'] ) ) : '';
			bya_get_datepicker_html(
				array(
					'id'          => 'bya_start_date',
					'name'        => 'start_date',
					'class'       => 'bya_start_date bya_time_filter_field',
					'with_time'   => false,
					'wp_zone'     => false,
					'placeholder' => 'YYYY-MM-DD',
					'value'       => $start_date_val,
					'date_format' => 'default',
				)
			);
			?>
		</div>

		<div class="bya_end_date_wrap">
			<label for="bya_end_date"><?php esc_html_e( 'To: ', 'buy-again-for-woocommerce' ); ?></label>
			<?php
			$end_date_val = isset( $_REQUEST['end_date'] ) ? wc_clean( wp_unslash( $_REQUEST['end_date'] ) ) : '';
			bya_get_datepicker_html(
				array(
					'id'          => 'bya_end_date',
					'name'        => 'end_date',
					'class'       => 'bya_end_date bya_time_filter_field',
					'with_time'   => false,
					'wp_zone'     => false,
					'placeholder' => 'YYYY-MM-DD',
					'value'       => $end_date_val,
					'date_format' => 'default',
				)
			);
			?>
		</div>
	</div>
	<?php endif; ?>

	<div class="bya-apply-filter">
		<button name="bya_product_list_filter_form" class="bya_product_search_btn button alt" type="submit"><?php echo esc_attr( get_option( 'bya_localization_search_btn_label', 'Search' ) ); ?></button>
		<?php wp_nonce_field( 'bya_filter_submit', '_bya_nonce', false, true ); ?>
	</div>
</form>

