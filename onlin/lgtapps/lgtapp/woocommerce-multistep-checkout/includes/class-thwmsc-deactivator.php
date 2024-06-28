<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWMSC_Deactivator')):

class THWMSC_Deactivator {

	public static function deactivate() {

	}
}

endif;