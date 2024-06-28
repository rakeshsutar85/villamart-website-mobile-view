<?php
/**
 * This template displays MyAccount Buy Again Products
 *
 * This template can be overridden by copying it to yourtheme/buy-again-for-woocommerce/myaccount-buy-again-table.php
 *
 * To maintain compatibility, buy again will update the template files and you have to copy the updated files to your theme
 *
 * @package Buy Again for Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$_columns = bya_get_buy_again_product_table_heading();
?>
<div class="bya_myaccount_buy_again_wrapper">
	<tbody>
		<?php
		foreach ( $product_ids as $product_args ) {
			if ( ! bya_check_is_array( $product_args ) ) {
				continue;
			}

			$product_id   = $product_args['product_id'];
			$variation_id = $product_args['variation_id'];
			$product_obj  = wc_get_product( $product_id );

			if ( $variation_id ) {
				$product_obj = wc_get_product( $variation_id );
			}

			if ( ! is_object( $product_obj ) ) {
				continue;
			}

			$product_price = ( 'excl' === get_option( 'woocommerce_tax_display_cart' ) ) ? wc_get_price_excluding_tax( $product_obj ) : wc_get_price_including_tax( $product_obj );
			$quantity_id   = ( $variation_id ) ? 'bya_qty_' . $variation_id : 'bya_qty_' . $product_id;
			$quantity_max  = $product_obj->backorders_allowed() ? '' : $product_obj->get_stock_quantity();
			?>
			<tr>    
				<?php if ( $_columns['image_label']['display'] ) { ?>
					<td data-title="<?php echo esc_attr( $_columns['image_label']['value'] ); ?>">
						<?php
						$feature_image = wp_get_attachment_image_src( get_post_thumbnail_id( esc_attr( $product_id ) ), 'single-post-thumbnail' );
						if ( $product_obj->get_image_id() && bya_check_is_array( $feature_image ) ) {
							?>
							<img class="bya_product_img" src="<?php echo esc_url( $feature_image[0] ); ?>" data-id="<?php echo esc_attr( $product_id ); ?>">
							<?php
						} else {
							echo sprintf( '<img src="%s" alt="%s" class="bya_product_img wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
						}
						?>
					</td>
					<?php
				}
				if ( $_columns['name_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['name_label']['value'] ); ?>">
						<?php
						/**
						 * Filter to Buy Again Product Name.
						 *
						 * @since 1.0
						 * */
						$product_title = apply_filters(
							'bya_table_item_name',
							$product_obj->get_permalink( $product_args['item'] ),
							array(
								'product_id'   => $product_id,
								'variation_id' => $variation_id,
							),
							''
						);
						echo sprintf( '<a class="bya_link bya_product_title" href="%s">%s</a>', esc_url( $product_title ), esc_attr( $product_args['item']->get_name() ) );
						wc_display_item_meta( $product_args['item'] );

						if ( $_columns['product_description']['display'] ) {
							/**
							 * Filter to Buy Again Product Description.
							 *
							 * @since 1.0
							 * */
							$product_descripiton = apply_filters( 'bya_table_item_description', $product_obj->get_description() );
							?>

						<p class="bya_product_description">
							<?php echo wp_kses_post( wc_format_content( $product_descripiton ) ); ?>
						</p>
						<?php } ?>
					</td>
					<?php
				}

				if ( $_columns['order_count_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['order_count_label']['value'] ); ?>">
						<?php
						$shortcode_array = array( '[qty_count]', '[order_count]' );
						$replace_array   = array( $product_args['qty_count'], $product_args['order_count'] );
						$message_label   = get_option( 'bya_localization_order_count_val', '[qty_count] quantity of [order_count] order(s)' );
						$order_count     = str_replace( $shortcode_array, $replace_array, $message_label );
						echo wp_kses_post( $order_count );
						?>
					</td>
					<?php
				}

				if ( $_columns['order_id_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['order_id_label']['value'] ); ?>">
						<?php
							$view_order_menu_slug = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
							$order_url            = wc_get_endpoint_url( $view_order_menu_slug, $product_args['order_id'], get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
							//echo '<pre>'; print_r($product_args); echo '</pre>';
							echo sprintf( '<a class="bya_link bya_order_id" href="%s">#%s</a>', esc_url( $order_url ), esc_attr( $product_args['order_id'] ) );
						?>
					</td>
					<?php
				}

				if ( $_columns['stock_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['stock_label']['value'] ); ?>">
						<?php
						$availability = $product_obj->get_availability();

						if ( bya_check_is_array( $availability ) && ! empty( $availability['availability'] ) ) {
							bya_get_template(
								'stock.php',
								array(
									'product'      => $product_obj,
									'class'        => $availability['class'],
									'availability' => $availability['availability'],
								)
							);
						} else {
							echo '-';
						}
						?>
					</td>
					<?php
				}

				if ( $_columns['price_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['price_label']['value'] ); ?>">
						<?php bya_price( $product_price ); ?>
					</td>
					<?php
				}

				if ( $_columns['quantity_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['quantity_label']['value'] ); ?>">
						<?php
						$qty_allow = true;
						/* Include admin html settings */
						include 'views/html-quantity-field.php';
						?>
					</td>
					<?php
				}

				if ( $_columns['action_label']['display'] ) {
					?>
					<td data-title="<?php echo esc_attr( $_columns['action_label']['value'] ); ?>">
						<?php
						$args = array(
							'product_id'              => $product_id,
							'variation_id'            => $variation_id,
							'price'                   => $product_price,
							'quantity_id'             => $quantity_id,
							'add_to_cart_class'       => 'bya_add_to_cart_' . $product_id,
							'add_to_cart_ajax_enable' => bya_add_to_cart_ajax_enable(),
							'cartlink'                => '?add-to-cart=' . $product_id,
							'qty_allow'               => false,
							'bya_order_id'            => $product_args['order_id'],
							'page'                    => get_option( 'bya_advanced_my_account_menu_slug', 'buy-again' ),
						);

						bya_get_template( 'display-input-fields.php', $args );
						?>
					</td>
				<?php } ?>
			</tr>
		<?php } ?>
	</tbody>
