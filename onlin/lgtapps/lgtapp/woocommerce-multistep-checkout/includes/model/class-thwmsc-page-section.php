<?php
/**
 * 
 *
 * @author      ThemeHiGH
 * @category    Admin
 */

if(!defined('ABSPATH')){ exit; }

if(!class_exists('THWMSC_Page_Section')):

class THWMSC_Page_Section{
	public $id = '';
	public $default = '';
	public $name = '';
	public $order = '';
	public $disable = '';
	
	public function __construct() {
		
	}
	
	public function set_property($name, $value){
		if(property_exists($this, $name)){
			$this->$name = $value;
		}
	}
	
	public function get_property($name){
		if(property_exists($this, $name)){
			return $this->$name;
		}else{
			return '';
		}
	}
	
	/*public function get_section_properties(){
		return array(
			'name' 	   => array('name'=>'name', 'value'=>''),		
			'position' => array('name'=>'position', 'value'=>''),
			//'order'    => array('name'=>'order', 'value'=>''),
			'cssclass' => array('name'=>'cssclass', 'value'=>array(), 'value_type'=>'array'),
			
			'show_title'     => array('name'=>'show_title', 'value'=>1, 'value_type'=>'boolean'),
			//'custom_section' => array('name'=>'custom_section', 'value'=>1, 'value_type'=>'boolean'),
			
			'title' 	     => array('name'=>'title', 'value'=>''),
			'title_type'     => array('name'=>'title_type', 'value'=>''),
			'title_color'    => array('name'=>'title_color', 'value'=>''),
			'title_position' => array('name'=>'title_position', 'value'=>''),
			'title_class'    => array('name'=>'title_class', 'value'=>array(), 'value_type'=>'array'),
			
			'subtitle' 		    => array('name'=>'subtitle', 'value'=>''),
			'subtitle_type'     => array('name'=>'subtitle_type', 'value'=>''),
			'subtitle_color'    => array('name'=>'subtitle_color', 'value'=>''),
			'subtitle_position' => array('name'=>'subtitle_position', 'value'=>''),
			'subtitle_class'    => array('name'=>'subtitle_class', 'value'=>array(), 'value_type'=>'array'),
		);
	}*/
	
	/*public function set_default_section(){
		$this->id    = 'default';
		$this->name  = 'default';
		$this->title = 'Default';
		$this->show_title = 0;
		$this->position = 'woo_before_add_to_cart_button';
	}*/
	
	
	
	
	
	
	
	
	/*public function populate_section($options){
		if(isset($options) && is_array($options)){
			$this->id    = isset($options['name']) ? $options['name'] : '';
			$this->name  = isset($options['name']) ? $options['name'] : '';
			$this->title = isset($options['title']) ? $options['title'] : '';		    
		}
	}
	
	public function add_wpml_support(){
		$this->wepo_wpml_register_string('Section Title - '.$this->name, $this->title );
		$this->wepo_wpml_register_string('Section Subtitle - '.$this->name, $this->subtitle );
	}
	
	public function prepare_properties(){
		$this->cssclass_str = str_replace(",", " ", $this->cssclass);
		$this->title_class_str = str_replace(",", " ", $this->title_class);
		$this->subtitle_class_str = str_replace(",", " ", $this->subtitle_class);
	}
	
	public function clear_fields(){
		$this->fields = array();
	}
	
	public function add_field($field){
		if($field){
			$this->fields[$field->get_property('name')] = $field;
		}
	}
	
	public function set_fields($fields){
		$this->fields = $fields;
	}
	
	public function get_fields(){
		return empty($this->fields) ? false : $this->fields;
	}

	public function get_title_html(){
		$title_html = '';
		if($this->title){
			$title_type  = $this->title_type ? $this->title_type : 'label';
			$title_style = $this->title_color ? 'style="color:'.$this->title_color.';"' : '';
			
			$title_html .= '<'.$title_type.' class="'.$this->title_class_str.'" '.$title_style.'>'. $this->esc_html__wepo($this->title) .'</'.$title_type.'>';
		}
		
		$subtitle_html = '';
		if($this->subtitle){
			$subtitle_type  = $this->subtitle_type ? $this->subtitle_type : 'span';
			$subtitle_style = $this->subtitle_color ? 'font-size:80%; style="color:'.$this->subtitle_color.';"' : 'font-size:80%;';
			
			$subtitle_html .= '<'.$subtitle_type.' class="'.$this->subtitle_class_str.'" '.$subtitle_style.'>'. $this->esc_html__wepo($this->subtitle) .'</'.$subtitle_type.'>';
		}
		
		$html = $title_html;
		if(!empty($subtitle_html)){
			$html .= $subtitle_html;
		}
		
		if(!empty($html)){
			$html = '<tr><td colspan="2" class="section-title">'.$html.'</td"></tr>';
		}else{
			$html = '<tr><td colspan="2" class="section-title">&nbsp;</td"></tr>';
		}		
		return $html;
	}
	
	public function get_html(){
		$fields = $this->get_fields();
		$html = '';
		$hidden_fields_html = '';
		if($fields){
			$html .= '<table class="extra-options '. $this->cssclass_str .'" cellspacing="0"><tbody>';
			
			if($this->get_property('show_title')){
				$html .= $this->get_title_html();
			}
			
			foreach($fields as $field){
				if($field->get_property('type') === 'hidden'){
					$hidden_fields_html .= $field->get_html();
				}else{
					$html .= $field->get_html();
				}
			}		
			$html .= '</tbody></table>';
		}
		
		$html .= $hidden_fields_html;
		
		return $html;
	}
	
	public function render_section(){		
		echo $this->get_html();
	}
	
	public function is_valid(){
		if(empty($this->name)){
			return false;
		}
		return true;
	}
	
	public function has_fields(){
		if($this->get_fields()){
			return true;
		}
		return false;
	}
	
	public function add_condition_set($condition_set){
		$condition_sets[] = $condition_set;
	}
	
	public function show_section($product, $categories){
		$show = true;
		if(!empty($condition_sets)){			
			foreach($condition_sets as $condition_set){
				if($condition_set->show_element()){
					$show = false;
				}
			}
		}
		return $show;
	}
	
	public function sort_fields(){
		uasort($this->fields, array($this, 'sort_by_order'));
	}
	
	public function sort_by_order($a, $b){
	    if($a->get_property('order') == $b->get_property('order')){
	        return 0;
	    }
	    return ($a->get_property('order') < $b->get_property('order')) ? -1 : 1;
	}
	
	public function get_property_array(){
		if($this->is_valid()){
			$section = array();
			$section_properties = $this->get_section_properties();
			
			foreach($section_properties as $pname => $props){
				$pvalue = $this->get_property($props['name']);
				
				if(isset($props['value_type']) && $props['value_type'] === 'array' && !empty($pvalue)){
					$pvalue = is_array($pvalue) ? $pvalue : explode(',', $pvalue);
				}
				
				if(isset($props['value_type']) && $props['value_type'] != 'boolean'){
					$pvalue = empty($pvalue) ? $props['value'] : $pvalue;
				}
				
				$section[$pname] = $pvalue;
			}
			
			return $section;
		}else{
			return false;
		}
	}
	
	public function get_property_json(){
		$props_json = '';
		$props_array = $this->get_property_array();
		
		if($props_array){
			$props_json = json_encode($props_array);
		}
		return $props_json;
	}*/
	
   /***********************************
	**** Setters & Getters - START ****
	***********************************/
	/*public function set_property($name, $value){
		if(property_exists($this, $name)){
			$this->$name = $value;
		}
	}
	
	public function get_property($name){
		if(property_exists($this, $name)){
			return $this->$name;
		}else{
			return '';
		}
	}*/
	
	/*public function is_custom_section(){
		return true;
	}*/
   /***********************************
	**** Setters & Getters - END ******
	***********************************/
}

endif;