<?php
/**
 * The common utility functionalities for the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/includes/utils
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC_Utils')):

class THWMSC_Utils {
	const OPTION_KEY_STEP_SETTINGS   = 'thwmsc_step_settings';
	const OPTION_KEY_ADVANCED_SETTINGS = 'thwmsc_advanced_settings';
	const OPTION_KEY_NEW_SETTINGS = 'thwmsc_new_settings';
 	
	const OPTION_KEY_CUSTOM_SECTIONS   = 'thwmsc_custom_sections';
	//const OPTION_KEY_ADVANCED_SETTINGS = 'thwmsc_advanced_settings';
	
	public static $DEFAULT_SECTIONS = array('billing' => 'Billing Details', 'shipping' => 'Shipping Details', 'additional' => 'Additional Fields'); 
	
	public static $STEP_PROPS = array(
		'name'    		=> array('name'=>"name", 'label'=>'Name', 'type'=>'hidden', 'value'=>''),
		'enabled' 		=> array('name'=>"enabled", 'label'=>'Enabled', 'type'=>'checkbox', 'value'=>0),
		'title'    		=> array('name'=>"title", 'label'=>'Step Title', 'type'=>'text', 'value'=>''),
		'indextype'    	=> array('name'=>"indextype", 'label'=>'Icon Type', 'type'=>'radio', 'value'=>'text_index'),
		'index'    		=> array('name'=>"index", 'label'=>'Display Index', 'type'=>'text', 'value'=>''),		
		'index_media'   => array('name'=>"index_media", 'label'=>'Step Icon', 'type'=>'hidden', 'value'=>''),		
		'order'    		=> array('name'=>"order", 'label'=>'Display Order', 'type'=>'hidden', 'value'=>''),
		'class'    		=> array('name'=>"class", 'label'=>'CSS Class', 'type'=>'hidden', 'value'=>''),
		'action'   		=> array('name'=>"action", 'label'=>'Action', 'type'=>'hidden', 'value'=>''),
		'action_before' => array('name'=>"action_before", 'label'=>'Action Before', 'type'=>'hidden', 'value'=>''),
		'action_after'  => array('name'=>"action_after", 'label'=>'Action After', 'type'=>'hidden', 'value'=>''),
		'sections' 		=> array('name'=>"sections", 'label'=>'Sections', 'type'=>'hidden', 'value'=>''),
		'custom'   		=> array('name'=>"custom", 'label'=>'Custom Step', 'type'=>'hidden', 'value'=>0),

		'enable_step_bg'   	=> array('name'=>"enable_step_bg", 'label'=>'Enable Step Background', 'type'=>'checkbox', 'value'=>0),
		'step_bg'   		=> array('name'=>"step_bg", 'label'=>'Step Content Background', 'type'=>'text', 'value'=>'#ffffff'),
		'step_font'   		=> array('name'=>"step_font", 'label'=>'Step Content Color', 'type'=>'text', 'value'=>'#000000'),
		'step_independent'  => array('name'=>"step_independent", 'label'=>'Independent of WCFE', 'type'=>'checkbox', 'value'=>''),
		'step_content'  	=> array('name'=>"step_content", 'label'=>'Step Content', 'type'=>'textarea', 'value'=>''),
		'index_logged_in'  	=> array('name'=>"index_logged_in", 'label'=>'Logged in Index', 'type'=>'text', 'value'=>''),

		'custom_position'  	=> array('name'=>"custom_position", 'label'=>'Display Position', 'type'=>'select', 'value'=>'above_fields'),
	);
	
	private static function get_default_steps(){
		$steps = array(
			'billing' => array(
				'name'     => 'billing',
				'title'    => 'Billing Details',
				'indextype'=> 'text_index',
				'index'    => 1,
				'index_logged_in' => 1,
				'order'    => 0,
				'enabled'  => 1,
				'class'    => '',
				'action'   => 'woocommerce_checkout_billing',
				'action_before' => 'woocommerce_checkout_before_customer_details',
				'action_after'  => '',
				'sections' => 'billing',
				'custom'   => 0,
			),
			'shipping' => array(
				'name'     => 'shipping',
				'title'    => 'Shipping Details',
				'indextype'=> 'text_index',
				'index'    => 2,
				'index_logged_in' => 2,
				'order'    => 1,
				'enabled'  => 1,
				'class'    => '',
				'action'   => 'woocommerce_checkout_shipping',
				'action_before' => '',
				'action_after'  => 'woocommerce_checkout_after_customer_details',
				'sections' => 'shipping',
				'custom'   => 0,
			),
			'order_review' => array(
				'name'     => 'order_review',
				//'title'    => 'Order Review',
				'title'    => 'Confirm Order',
				'indextype'=> 'text_index',
				'index'    => 3,
				'index_logged_in' => 3,
				'order'    => 2,
				'enabled'  => 1,
				'class'    => '',
				'action'   => 'woocommerce_checkout_order_review',
				'action_before' => 'woocommerce_checkout_before_order_review',
				'action_after'  => 'woocommerce_checkout_after_order_review',
				'sections' => 'additional',
				'custom'   => 0,
			),
		);
		return $steps;
	}

	// Login step settings
	public static function get_login_step_settings(){
		$login['login'] = array(
	      'name'     => 'login',
	      'title'    => 'Login',
	      'indextype'=> 'text_index',
	      'index'    => 0,
	      'index_logged_in' => 0,
	      'order'    => 0,
	      'enabled'  => 1,
	      'class'    => '',
	      'action'   => 'thwmsc_woocommerce_checkout_login',
	      'action_before' => '',
	      'action_after'  => '',
	      'sections' => 'login',
	      'custom'   => 0,
	    );

	    return $login;
	}

	// Get Coupen step settings
	public static function get_coupon_step_settings(){
		$coupon['coupon'] = array(
	      'name'     => 'coupon',
	      'title'    => 'Coupon',
	      'indextype'=> 'text_index',
	      'index'    => 1,
	      'index_logged_in' => 1,
	      'order'    => 1,
	      'enabled'  => 1,
	      'class'    => '',
	      'action'   => 'thwmsc_woocommerce_checkout_coupon',
	      'action_before' => '',
	      'action_after'  => '',
	      'sections' => 'coupon',
	      'custom'   => 0,
	    );

	    return $coupon;
	}

	// Get the review step settings
	public static function get_review_step_settings(){
		$review['review_order'] = array(
	      'name'     => 'review_order',
	      'title'    => 'Review Order',
	      'indextype'=> 'text_index',
	      'index'    => 3,
	      'index_logged_in' => 3,
	      'order'    => 3,
	      'enabled'  => 1,
	      'class'    => '',
	      'action'   => 'thwmsc_woocommerce_checkout_review_order',
	      'action_before' => 'thwmsc_woocommerce_checkout_before_review_order',
	      'action_after'  => 'thwmsc_woocommerce_checkout_after_review_order',
	      'sections' => 'review_order',
	      'custom'   => 0,
	    );

	    return $review;
	}

	public static function get_cart_step_settings(){
		$cart_step['cart'] = array(
		    'name'     => 'cart',
		    'title'    => 'Cart',
		    'indextype'=> 'text_index',
		    'index'    => 0,
		    'index_logged_in' => 0,
		    'order'    => 0,
		    'enabled'  => 1,
		    'class'    => '',
		    'action'   => 'thwmsc_woocommerce_checkout_cart_step',
		    'action_before' => 'thwmsc_woocommerce_checkout_before_cart_step',
		    'action_after'  => 'thwmsc_woocommerce_checkout_after_cart_step',
		    'sections' => '',
		    'custom'   => 0,
		);

		return $cart_step;
	}

	public static function get_complete_steps(){
		$steps = self::get_default_steps();
		$settings = self::get_new_advanced_settings();

		if(isset($settings['enable_coupen_step']) && $settings['enable_coupen_step'] == 'yes'){
			$is_exist = self::check_step_is_already_exist($steps, 'coupon');
			if(!$is_exist){
				$coupon_step = self::get_coupon_step_settings();
				$steps = $coupon_step + $steps;
			}
		}

		if(isset($settings['enable_login_step']) && $settings['enable_login_step'] == 'yes'){
			$is_exist = self::check_step_is_already_exist($steps, 'login');
			if(!$is_exist){
				$login_step = self::get_login_step_settings();
				$steps = $login_step + $steps;
			}
		}

		if(isset($settings['make_order_review_separate']) && $settings['make_order_review_separate'] == 'yes'){
			$steps = THWMSC_Utils::add_order_review_step($steps, $steps);
		}

		if(isset($settings['enable_cart_step']) && $settings['enable_cart_step'] == 'yes'){
			$is_exist = self::check_step_is_already_exist($steps, 'cart');
			if(!$is_exist){
				$cart_step = self::get_cart_step_settings();
				$steps = $cart_step + $steps;
			}
		}

		return $steps;
	}

	public static function add_login_step($steps, $full_settings){
		//if(!is_user_logged_in()){
		$has_login = array_key_exists('login', $full_settings);
		if(!$has_login){
			$login = THWMSC_Utils::get_login_step_settings();
	    	$steps = $login + $steps;
		}
		//}

		return $steps;
	}

	public static function add_coupon_step($steps, $full_settings){
	    $has_coupon = array_key_exists('coupon', $full_settings);
	    if(!$has_coupon){
	    	$coupon = THWMSC_Utils::get_coupon_step_settings();
	    	$steps = $coupon + $steps;
	    }
		return $steps;
	}

	public static function check_step_is_already_exist($settings, $step_name){
		$is_exist = array_key_exists($step_name, $settings);
		return $is_exist;
	}

	public static function check_extra_step_is_activated($activation_name){
		$advanced = self::get_new_advanced_settings();
		$activated = isset($advanced[$activation_name]) && $advanced[$activation_name] == 'yes' ? true : false;
		return $activated;
	}

	public static function remove_extra_steps_added(){
		$steps = self::get_step_settings();
		$additional_steps = array('cart', 'login', 'coupon', 'review_order');
		foreach ($additional_steps as $step) {
			if(array_key_exists($step, $steps)){
				unset($steps[$step]);
			}
		}

		$result = self::save_step_settings($steps);
		return $result;
	}

	public static function add_advanced_step_settings($settings, $step){
		if($step['name'] == 'review_order'){
			$settings = self::add_step_after_settings($settings, $step);

		}else{
			$new_step[$step['name']] = $step;
			$settings = $new_step + $settings;
		}
		return $settings;
	}

	public static function add_step_after_settings($settings, $step){
		$new_step[$step['name']] = $step;
		$settings = array_slice($settings, 0, count($settings) - 1, true) + $new_step + array_slice( $settings, -1, 1, true );
		return $settings;
	}

	public static function add_order_review_step($steps, $full_settings){
		$has_review = array_key_exists('review', $full_settings);
	    if(!$has_review){
	    	$review = THWMSC_Utils::get_review_step_settings();
	    	$steps = array_slice($steps, 0, count($steps) - 1, true) + $review + array_slice( $steps, -1, 1, true );
	    }
		return $steps;
	}
	
	public static function save_step_settings($settings){
		$result = false;
		if($settings){     			
			$result = update_option(THWMSC_Utils::OPTION_KEY_STEP_SETTINGS, $settings);  
		}
		return $result;
	}
	
	public static function get_step_settings(){
		$settings = get_option(self::OPTION_KEY_STEP_SETTINGS);   
		return empty($settings) ? false : $settings;
	}
	
	public static function get_step_settings_admin(){
		$settings = self::get_step_settings();
		if(!$settings){
			$settings = self::get_default_steps();
			self::save_step_settings($settings);
		} 
		//$settings = apply_filters('thwmsc_steps', $settings);
		$settings = apply_filters('thwmsc_add_new_steps', $settings, $settings);
		$settings = apply_filters('thwmsc_steps_back_end', $settings);
		$settings = apply_filters('thwmsc_steps_remove_before_display', $settings);

		$settings = self::reset_step_display_order($settings);
		$settings = self::sort_steps($settings);
		$settings = self::prepare_step_display_props($settings);
		return $settings;
	}
	
	public static function get_step_settings_public(){
		$settings = self::get_step_settings();
		if(!$settings){
			$settings = self::get_default_steps();
		}

		$full_settings = $settings;

		if(apply_filters('thwmsc_disable_step_filtering', true)){
			$settings = self::filter_disabled_steps($settings);
		}

		//$settings = apply_filters('thwmsc_steps', $settings, $full_settings);
		$settings = apply_filters('thwmsc_add_new_steps', $settings, $full_settings);
		$settings = apply_filters('thwmsc_steps_front_end', $settings);
		$settings = apply_filters('thwmsc_steps_remove_before_display', $settings);

		$settings = self::reset_step_display_order($settings);
		$settings = self::sort_steps($settings);
		$settings = self::prepare_step_display_props($settings);
		return $settings;
	}
	
	private static function filter_disabled_steps($settings){
		if(is_array($settings)){
			foreach($settings as $key => $step){
				if(!self::is_enabled($step) || self::skip_step($step)){
					unset($settings[$key]);
				}
			}
		}
		return $settings;
	}

	public static function is_enabled($step){
		if(is_array($step) && isset($step['enabled']) && $step['enabled']){
			return true;
		}
		return false;
	}

	public static function is_valid_enabled($steps, $step_name){
		if($step_name && is_array($steps) && isset($steps[$step_name])){
			if(self::is_enabled($steps[$step_name])){
				return true;
			}
		}
		return false;
	}
	
	private static function skip_step($step){
		$skip = false;
		$disable_wcfe = isset($step['step_independent']) && $step['step_independent'] == '1'  ? false : true ;

		if(is_array($step) && isset($step['custom']) && $step['custom'] && !THWMSC_Utils::has_hooked_sections($step['action']) && $disable_wcfe){
			$skip = true;
		}
		return $skip;
	}
	
	public static function prepare_step_display_props($steps){
		if(is_array($steps) && !empty($steps)){ 
			$keys = array_keys($steps);
			$first_key = $keys[0];
			$last_key = end($keys);
			
			if(isset($steps[$first_key])){
				$first = $steps[$first_key];
				$first['class'] = isset($first['class']) ? $first['class'].' first active' : 'first active';
				$steps[$first_key] = $first;
			}
			
			if(isset($steps[$last_key])){
				$last = $steps[$last_key];
				$last['class'] = isset($last['class']) ? $last['class'].' last' : 'last';
				$steps[$last_key] = $last;
			}
		}
		return $steps;
	}
	
	public static function reset_step_display_order($steps){
		if(is_array($steps)){
			$i = 0;
			foreach($steps as $key => &$step){
				$step['order'] = $i;
				$step['class'] = '';
				$i++;
			}
		}
		return $steps;
	}
	
	public static function custom_step_hooks($hooks){
		$settings = self::get_step_settings();
		if(is_array($settings)){
			foreach($settings as $key => $step){
				$is_custom = isset($step['custom']) && $step['custom'] ? $step['custom'] : 0;
				$step_independent = isset($step['step_independent']) && $step['step_independent'] ? $step['step_independent'] : '' ;

				if(is_array($step) && isset($step['action']) && isset($step['title']) && $is_custom && $step_independent != 1){
					$hooks[$step['action']] = !empty($step['title']) ? $step['title'].' ('.$key.')' : $key;
				}
			}
		} 
		return $hooks;
	}
	
	public static function has_hooked_sections($hook){
		return apply_filters('thwmsc_has_hooked_sections', true, $hook);
	}

	public static function get_custom_sections(){
		$settings = get_option(self::OPTION_KEY_CUSTOM_SECTIONS);   
		return empty($settings) ? false : $settings;
	}
		
	public static function get_advanced_settings(){
		$advanced_settings_default = array(			
			'thwmsc_layout' => 'thwmsc_horizontal_box',
			'tab_align' => 'left',    
		 	'enable_wmsc' => 'yes',
		 	'disable_validation' => '',
		  	'tab_panel_bg_color' => '#FBFBFB',
		  	'step_text_color' => '#696969',
		  	'step_text_color_active' =>'#FFFFFF',
			'step_text_font_size' => '16' ,
			'step_text_font_weight' => 'normal',
		  	'step_text_transform' => 'initial',
		 	'step_bg_color' => '#FFFFFF' ,
		 	'step_bg_color_active' => '#b3b3df',
		 	'step_icon_radius' => '50%',
		 	'step_icon_border_color' => '#d5d5d5',
		 	'thwmsc_pg_bar_layouts' => 'thwmsc_progress_bar_percentage',
		 	'enable_completed_tab_bg' => 'yes',
		 	'completed_tab_bg_color' => '#41359f',
		 	'completed_tab_text_color' => '#fff',
		 	'dot_format_enable_completed_tab_bg' => 'yes',
		); 

		$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
		if (!empty($settings)) {
		 	foreach ($advanced_settings_default as $key => $value) {

		 		if(!array_key_exists($key,$settings)) {
		 			$settings[$key] = $value;
		 		}
		 	}
		}		 

		// if (!empty($settings)) {
		// 	foreach ($advanced_settings_default as $key => $value) {
		// 		if(!in_array($key,$settings)) {
		// 			$settings[$key] = $value;
		// 		}
		// 	}
		// }
		
		return empty($settings) ? $advanced_settings_default : $settings;
	}

	public static function get_button_settings_field() {
		$button_settings_field = array(
			'next_prev_btn'	 => array('button_new_class','button_style_active','button_text_font_size','button_text_font_color','button_text_font_color_hover', 'button_bg_color','button_bg_color_hover','button_padding','button_border_width','button_border_color','button_border_radius','button_border_style','hide_first_step_prev','hide_last_step_next','button_prev_text','button_next_text','button_position','button_alignment'),

			'place_orde_btn' => array('button_style_place_order','order_btn_text_font_size','order_btn_text_font_color','order_btn_text_font_color_hover','order_btn_bg_color','order_btn_bg_color_hover','place_order_text'),

			'back_to_cart_btn'=> array('enable_back_to_cart_button','back_to_cart_button_text')

		);

		return $button_settings_field;
	}

	public static function get_display_settings_field() {
		$display_settings_field = array(
			'dsply_general_sttng' 	=> array('enable_wmsc','disable_validation','enable_step_forward','mobile_accordion'),

			'dsply_layout_sttng' 	=> array('thwmsc_layout','tab_align','time_line_type','time_line_text','tab_vertical_text_align','tab_width','content_width','custom_separator_with_icon','step_bg_color','step_bg_color_active','step_text_color','step_text_color_active','tab_panel_bg_color','dot_format_bg_color','dot_format_dot_color','dot_format_bg_color_active','dot_format_dot_color_active','step_text_font_size','tab_line_height','step_text_font_weight','tab_padding','step_text_transform','custom_separator_color','custom_separator_color_active','enable_completed_tab_bg','completed_tab_bg_color','completed_tab_text_color','completed_custom_separator_color','border_color_tab_format','dot_width_height','dot_padding','dot_format_enable_completed_tab_bg','dot_format_bg_color_completed','dot_format_dot_color_completed'),

			'dsply_progress_sttng' 	=> array('progress_bar_below','thwmsc_pg_bar_layouts','progress_bar_color','progress_bar_bg_color','progress_bar_height'),

			'dsply_stepindx_sttng' 	=> array('enable_step_tickmark','step_tickmark_background','step_tickmark_border','step_tickmark_color','icon_height_width','step_icon_radius','icon_border_space','step_icon_font_color','step_icon_font_color_active','step_icon_border_color','step_icon_border_color_active','step_icon_background_color','step_icon_background_color_active')   
		);

		return $display_settings_field;
	}


	public static function get_advanced_settings_field() {
		$advanced_settings_field = array(
			'advncd_sttng' 		=> array('enable_cart_step','enable_login_step','use_my_account_login','login_form_on_load','enable_coupen_step','coupon_form_on_load','coupon_form_above_payment','make_order_review_separate','show_order_review_right','make_billing_shipping_together'),

			'review_step_detail'=> array('steps_for_review','placeholder_step','show_billing_address_in_shipping','exclude_checkout_fields'),
		);

		return $advanced_settings_field;
	}



	// public static function get_dsply_general_setting(){
	// 	$advanced_settings_default = array(			
	// 	 	'enable_wmsc' => 'yes',
	// 	); 

	// 	$settings = get_option(self::OPTION_KEY_ADVANCED_SETTINGS);
	// 	$test = array(
	// 		'enable_wmsc' => empty($settings['enable_wmsc']) ? '' : $settings['enable_wmsc'],
	// 		'disable_validation', => empty($settings['disable_validation']) ? '' : $settings['disable_validation']
	// 		'enable_step_forward', => empty($settings['enable_step_forward']) ? '' : $settings['enable_step_forward']
	// 		'mobile_accordion' => empty($settings['mobile_accordion']) ? '' : $settings['mobile_accordion']
	// 	);
	// 	return empty($settings) ? $advanced_settings_default : $test;
	// }


	public static function get_new_advanced_settings(){
		$settings = get_option(self::OPTION_KEY_NEW_SETTINGS);
		return empty($settings) ? array() : $settings;
	}
	
	public static function get_setting_value($settings, $key){
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
	
	public static function get_settings($key){
        // $enable = THWMSC_Utils::is_wmsc_enabled();
		$settings = self::get_advanced_settings();
		if(is_array($settings) && isset($settings[$key])){
			return $settings[$key];
		}
		return '';
	}
		
	public static function sort_steps($steps){
		uasort($steps, array('self', 'sort_by_order'));
		return $steps;
	}
	
	public static function sort_by_order($a, $b){
		if(is_array($a) && is_array($b)){
			$order_a = isset($a['order']) && is_numeric($a['order']) ? $a['order'] : 0;
			$order_b = isset($b['order']) && is_numeric($b['order']) ? $b['order'] : 0;
			
			if($order_a == $order_b){
				return 0;
			}
			return ($order_a < $order_b) ? -1 : 1;
		}else{
			return 0;
		}
	}

	public static function is_wmsc_enabled(){
		$enabled = false;
		$display_settings = self::get_advanced_settings();
		$enable = isset($display_settings['enable_wmsc']) && $display_settings['enable_wmsc'] ? $display_settings['enable_wmsc'] : '';
		if($enable == 'yes'){
			$enabled = true;
		}
		return apply_filters('thwmsc_is_msc_enabled', $enabled);
	}

	public static function is_thwcfe_plugin_active(){
		$active = is_plugin_active('woocommerce-checkout-field-editor-pro/woocommerce-checkout-field-editor-pro.php');
		return apply_filters('thwmsc_checkout_field_editor_plugin_enabled', $active);
	}

	public static function get_locale_code(){
		$locale_code = '';
		$locale = get_locale();
		if(!empty($locale)){
			$locale_arr = explode("_", $locale);
			if(!empty($locale_arr) && is_array($locale_arr)){
				$locale_code = $locale_arr[0];
			}
		}		
		return empty($locale_code) ? 'en' : $locale_code;
	}

	public static function wmsc_wpml_register_string($name, $value ){
		$context = 'woocommerce-multistep-checkout';
		$name = "WMSC - ".$value;
		
		if(function_exists('icl_register_string')){
			icl_register_string($context, $name, $value);
		}

	}

	public static function is_user_capable(){
		$capable = false;
		$user = wp_get_current_user();
		$allowed_roles = apply_filters('thwmsc_override_user_capabilities', array('editor', 'administrator') );
		if( array_intersect($allowed_roles, $user->roles ) ) {
   			$capable = true;
   		}
   		return $capable;
	}
	
}

endif;