<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woocommerce-checkout-upsell-funnel" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES', VIWCUF_CHECKOUT_UPSELL_FUNNEL_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_ADMIN', VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "admin" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_FRONTEND', VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "frontend" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_LANGUAGES', VIWCUF_CHECKOUT_UPSELL_FUNNEL_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_TEMPLATES', VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "templates" . DIRECTORY_SEPARATOR );
$plugin_url = plugins_url( 'woocommerce-checkout-upsell-funnel' );
$plugin_url = str_replace( '/includes', '', $plugin_url );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS', $plugin_url . "/assets/css/" );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_CSS_DIR', VIWCUF_CHECKOUT_UPSELL_FUNNEL_DIR . "assets/css" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_JS', $plugin_url . "/assets/js/" );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_JS_DIR', VIWCUF_CHECKOUT_UPSELL_FUNNEL_DIR . "assets/js" . DIRECTORY_SEPARATOR );
define( 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_IMAGES', $plugin_url . "/assets/images/" );

/*Include functions file*/
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "functions.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "functions.php";
}
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "support.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "support.php";
}
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "check_update.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "check_update.php";
}
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "update.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "update.php";
}
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "data.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "data.php";
}
if ( is_file( VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "report-table.php" ) ) {
	require_once VIWCUF_CHECKOUT_UPSELL_FUNNEL_INCLUDES . "report-table.php";
}
villatheme_include_folder( VIWCUF_CHECKOUT_UPSELL_FUNNEL_ADMIN, 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Admin_' );
villatheme_include_folder( VIWCUF_CHECKOUT_UPSELL_FUNNEL_FRONTEND, 'VIWCUF_CHECKOUT_UPSELL_FUNNEL_Frontend_' );
