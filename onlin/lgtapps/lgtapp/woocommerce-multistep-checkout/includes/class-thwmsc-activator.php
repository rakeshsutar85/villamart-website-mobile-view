<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/includes
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWMSC_Activator')):

class THWMSC_Activator {

	/**
	 * Copy older version settings if any.
	 *
	 * Use pro version settings if available, if no pro version settings found 
	 * check for free version settings and use it.
	 *
	 * - Check for premium version settings, if found do nothing. 
	 * - If no premium version settings found, then check for free version settings and copy it.
	 */
	public static function activate() {
		self::check_for_premium_settings();
	}
	
	public static function check_for_premium_settings(){
		/*$premium_settings = get_option(THWMSC_Utils::SETTINGS_KEY);
		
		if($premium_settings && is_array($premium_settings)){			
			return;
		}else{		
			self::may_copy_free_version_settings();
		}*/
	}
	
	public static function may_copy_free_version_settings(){
		
	}
}

endif;