<?php

class THWMSC_Utils_Section { 
	static $DEFAULT_SECTIONS = array('login' => 'Login', 'billing' => 'Billing Details', 'shipping' => 'Shipping Details', 'order_info' => 'Order Info', 'payment_info' => 'Payment Info');
	static $SECTION_PROPS = array(
		'name' 	   => array('name'=>'name', 'value'=>''),		
		'default' 	   => array('name'=>'default', 'value'=>''),		
		'order' => array('name'=>'order', 'value'=>''), 		
		'enable' => array('name'=>'enable', 'value'=>''),	
	);
	
	public static function is_valid_section($section){
		if(isset($section) && $section instanceof THWMSC_Page_Section && !empty($section->name)){
			return true;
		} 
		return false;
	}
	
	public static function is_enabled($section){
		if($section->get_property('enabled')){
			return true;
		}
		return false;
	}
	
	public static function is_custom_section($section){
		//return $section->custom_section;
		return true;
	}
	
	public static function prepare_default_section(){       
		$section = array();
		$order = 0;

		foreach(self::$DEFAULT_SECTIONS as $key => $value){
			$section = new THWMSC_Page_Section();

			$section->set_property('id', $key); 
			$section->set_property('name', $value);
			$section->set_property('default', $value);
			$section->set_property('order', $order);
			$section->set_property('enable', 1);	 	
			$order++; 	 
			$sections[$key] = $section; 
		} 
				
		return $sections;
	}	
	
	public static function prepare_section_from_posted_data($posted, $form = 'new'){
		$name     = isset($posted['i_name']) ? $posted['i_name'] : '';
		$position = isset($posted['i_position']) ? $posted['i_position'] : '';
		$title    = isset($posted['i_title']) ? $posted['i_title'] : '';

		if(!$name || !$title || !$position){
			return;
		}
		
		if($form === 'edit'){
			$section = THWMSC_Admin_Utils::get_section($name);
		}else{
			$name = strtolower($name);
			$name = is_numeric($name) ? "s_".$name : $name;
				
			$section = new WEPO_Product_Page_Section();
			$section->set_property('id', $name);
		}
		
		foreach( self::$SECTION_PROPS as $pname => $property ){
			$iname  = 'i_'.$pname;
			$pvalue = isset($posted[$iname]) ? $posted[$iname] : $property['value'];
			
			if($pname === 'show_title'){  
				$pvalue = !empty($pvalue) && $pvalue === 'yes' ? 1 : 0;
			}   
			
			$section->set_property($pname, $pvalue);
		}
		
		$section->set_property('custom_section', 1);
		
		//WPML Support
		self::add_wpml_support($section);  
		return $section;
	}
	
	public static function get_property_set($section){
		if(self::is_valid_section($section)){
			$props_set = array();
			
			foreach(self::$SECTION_PROPS as $pname => $props){
				$pvalue = $section->get_property($props['name']);
				
				if(isset($props['value_type']) && $props['value_type'] === 'array' && !empty($pvalue)){
					$pvalue = is_array($pvalue) ? $pvalue : explode(',', $pvalue);
				}
				
				if(isset($props['value_type']) && $props['value_type'] != 'boolean'){
					$pvalue = empty($pvalue) ? $props['value'] : $pvalue;
				}
				
				$props_set[$pname] = $pvalue;
			}
			
			$props_set['custom'] = self::is_custom_section($section);
			
			return $props_set;
		}else{
			return false;
		}
	}
	
	public static function get_property_json($section){
		$props_json = '';
		$props_set = self::get_property_set($section);
		
		if($props_set){
			$props_json = json_encode($props_set);
		}
		return $props_json;
	}
	
	public static function sort_fields($section){
		uasort($section->fields, array('self', 'sort_by_order'));
		return $section;
	}
	
	public static function sort_by_order($a, $b){
	    if($a->get_property('order') == $b->get_property('order')){
	        return 0;
	    }
	    return ($a->get_property('order') < $b->get_property('order')) ? -1 : 1;
	}
	
	public static function add_wpml_support($section){
		THWMSC_i18n::wpml_register_string('Section Title - '.$section->name, $section->title );
		THWMSC_i18n::wpml_register_string('Section Subtitle - '.$section->name, $section->subtitle );
	}
}
