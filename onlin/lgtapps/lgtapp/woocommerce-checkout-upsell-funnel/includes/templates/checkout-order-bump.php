<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! $shortcode_html || ! $rule_id ) {
	return;
}
$div_class = array(
	'viwcuf-checkout-ob-shortcode',
	'viwcuf-checkout-ob-shortcode-' . $rule_id,
);
$div_class = trim( implode( ' ', $div_class ) );
?>
<div class="<?php echo esc_attr( $div_class ) ?>">
    <div class="vi-wcuf-loading-wrap vi-wcuf-disable">
        <div class="vi-wcuf-loading">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
	<?php
	global $wp_version;
	if ( version_compare( $wp_version, '5.5', '>=' ) ) {
		echo wp_kses( $shortcode_html, VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data::extend_post_allowed_html() );
	} else {
		echo $shortcode_html;
	}
	//	if (strpos(wp_kses( $shortcode_html, VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data::extend_post_allowed_html() ),'data-product_id') === false){
	//		echo $shortcode_html;
	//	}else{
	//		echo wp_kses( $shortcode_html, VIWCUF_CHECKOUT_UPSELL_FUNNEL_Data::extend_post_allowed_html() );
	//	}
	?>
</div>