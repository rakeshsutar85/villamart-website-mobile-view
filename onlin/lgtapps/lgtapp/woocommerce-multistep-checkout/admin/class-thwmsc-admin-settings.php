<?php
/**
 * The admin settings page specific functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/admin
 */
if(!defined('WPINC')){ die; }

if(!class_exists('THWMSC_Admin_Settings')):  

abstract class THWMSC_Admin_Settings {
	protected $page_id = '';	
	public static $section_id = '';
	
	protected $tabs = '';
	protected $sections = '';
	
	public function __construct($page, $section = '') { 
		$this->page_id = $page;
		if($section){
			self::$section_id = $section;
		}else{
			self::set_first_section_as_current();
		}
		$this->tabs = array( 'general_settings' => __('Checkout Steps', 'woocommerce-multistep-checkout'), 'display_settings' => __('Display Settings', 'woocommerce-multistep-checkout'), 'advanced_settings' => __('Advanced Settings', 'woocommerce-multistep-checkout'),'button_settings' => __('Button Setttings', 'woocommerce-multistep-checkout'), 'license_settings' => __('Plugin License', 'woocommerce-multistep-checkout'));
	}
	
	public function get_tabs(){
		return $this->tabs;
	}

	public function get_current_tab(){
		return $this->page_id;
	}
	
	public function get_sections(){
		return $this->sections;
	}
	
	public function get_current_section(){
		return isset( $_GET['section'] ) ? esc_attr( $_GET['section'] ) : self::$section_id;
	}
	
	public static function set_current_section($section_id){
		if($section_id){
			self::$section_id = $section_id;
		}
	}
	
	public static function set_first_section_as_current(){
		$sections = THWMSC_Admin_Utils::get_sections();
		if($sections && is_array($sections)){
			$array_keys = array_keys( $sections );
			if($array_keys && is_array($array_keys) && isset($array_keys[0])){
				self::set_current_section($array_keys[0]);
			}
		}
	}

	public function render_tabs(){
		$current_tab = $this->get_current_tab();
		$tabs = $this->get_tabs();

		if(empty($tabs)){
			return;
		}
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper thwmscp-admin-tab-wrapper">';
		foreach( $tabs as $id => $label ){
			$active = ( $current_tab == $id ) ? 'thwmscp-admin-tab-active' : '';
			$label = sprintf(__('%s', 'woocommerce-multistep-checkout'),$label);
			echo '<span class="" ></span> <a class="nav-tab thwmscp-admin-tab thwmscp-admin-font '.$active.'" href="'. $this->get_admin_url($id) .'"><span class="thwmscp-admin-icon thwmscp-icon-'.$id.'"></span><label>'.$label.'</label></a>';
		}
		echo '</h2>';		
	}
	
	public function render_sections() {
		$current_section = $this->get_current_section();
		$sections = $this->get_sections();

		if(empty($sections)){
			return;
		}
		
		$array_keys = array_keys( $sections );
		$section_html = '';
		
		foreach( $sections as $id => $label ){
			// $label = sprintf(__('%s', 'woocommerce-multistep-checkout'),$label);
			$url   = $this->get_admin_url($this->page_id, sanitize_title($id));	
			$section_html .= '<li><a href="'. $url .'" class="'.($current_section == $id ? 'current' : '').'">'.$label.'</a> '.(end($array_keys) == $id ? '' : '|').' </li>';
		}	
		
		if($section_html){
			echo '<ul class="thpladmin-sections">';
			echo $section_html;	
			echo '</ul>';
		}
	} 
	
	public function get_admin_url($tab = false, $section = false){
		$url = 'admin.php?page=th_multi_step_checkout';
		if($tab && !empty($tab)){
			$url .= '&tab='. $tab;
		}
		if($section && !empty($section)){
			$url .= '&section='. $section;
		}
		return admin_url($url);
	}
		
	public function render_form_field_element($field, $atts = array(), $render_cell = true, $return = false, $layout_type = '',$copy_button = ''){
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_colspan' => '',
				'input_cell_colspan' => '',
			), $atts );
			
			$ftype     = isset($field['type']) ? $field['type'] : 'text';
			$flabel    = isset($field['label']) && !empty($field['label']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $field['label']) : '';
			$sub_label = isset($field['sub_label']) && !empty($field['sub_label']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $field['sub_label']) : '';
			$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $field['hint_text']) : '';
			
			$field_html = '';
			
			if($ftype == 'text'){
				$field_html = $this->render_form_field_element_inputtext($field, $atts);
				
			}else if($ftype == 'hidden'){
				$render_cell = false;
				$field_html = $this->render_form_field_element_hidden($field, $atts);
				
			}else if($ftype == 'number'){ 
				$field_html = $this->render_form_field_element_inputnumber($field, $atts);
				
			}else if($ftype == 'textarea'){
				$field_html = $this->render_form_field_element_textarea($field, $atts, $copy_button);
				   
			}else if($ftype == 'select'){
				$field_html = $this->render_form_field_element_select($field, $atts);     
				
			}else if($ftype == 'multiselect'){
				$field_html = $this->render_form_field_element_multiselect($field, $atts);     
				
			}else if($ftype == 'multiselect_grouped'){
				$field_html = $this->render_form_field_element_multiselect_grouped($field, $atts);     
			}else if($ftype == 'colorpicker'){
				$field_html = $this->render_form_field_element_colorpicker($field, $atts);              
            
			}else if($ftype == 'checkbox'){
				$field_html = $this->render_form_field_element_checkbox($field, $atts, $render_cell);   
				if(!(isset($atts['render_label_cell']) && $atts['render_label_cell'])){   
					$flabel = '&nbsp;';  
				}
			}else if($ftype == 'propertygroup'){
				$field_html = $this->render_form_field_element_propertygroup($field, $atts);
				
			}else if($ftype == 'radio'){
				$field_html = $this->render_form_field_element_radio($field, $atts, $layout_type);		
			}
			
			if($render_cell){
				$required_html = isset($field['required']) && $field['required'] ? '<abbr class="required" title="required">*</abbr>' : '';

				$label_cell_props = !empty($args['label_cell_props']) ? $args['label_cell_props'] : '';
				$input_cell_props = !empty($args['input_cell_props']) ? $args['input_cell_props'] : '';
				$cell_html = '';

				if($field['name'] != 'thwmsc_layout' && $field['name'] != 'thwmsc_pg_bar_layouts') {	

					$style 		= $field['type'] === 'checkbox' ? 'style="width: 33.3%;"'	: 'style="width: 33.3%;"';	
					$cell_html .= '<td '.$style.' '.$label_cell_props.' >';
					$cell_html .= '<label class="thpladmin-td-label">' . $flabel . '</label>';
					$cell_html .= $required_html;

					if($sub_label){
						$cell_html .= '<br/><span class="thpladmin-subtitle">'.$sub_label.'</span>';
					}

					if ($field['type'] != 'checkbox') {
						$cell_html .= $this->render_tooltip_new_ui($tooltip, true);
						$cell_html .= '<span class="thpladmin-td-input" '.$input_cell_props.'>'.$field_html.'</span>';
					}
					
					$cell_html .= '</td>';

					// $cell_html .= $this->render_form_fragment_tooltip($tooltip, true);
				}

				if ($field['name'] === 'thwmsc_layout' || $field['type'] === 'checkbox' || $field['name'] === 'thwmsc_pg_bar_layouts') {

					$cell_html .= $field['name'] === 'thwmsc_layout' ? '<td '.$input_cell_props.'style="width: 100%;" >' : '<td '.$input_cell_props.'style="width: 33.3%;" >';

					// $cell_html .= '<td '.$input_cell_props.'style="width: 33.3%;" >';

					if($tooltip){
						$cell_html .= $this->render_tooltip_new_ui($tooltip, true);
					}elseif(!$tooltip && !($field['name'] === 'thwmsc_layout')){
						$cell_html .= '<span class="thpladmin_tooltip thwmscp_tooltip" ></span>';
					}
					
					$cell_html .= $field_html;
					$cell_html .= '</td>';
				}

				// $cell_html .= '<td '.$input_cell_props.' >'.$field_html.'</td>';
				$field_html = $cell_html;
				/*
				?>
				<td <?php echo $label_cell_props ?> >
					<?php echo $flabel; echo $required_html; 
					if($sub_label){
						?>
						<br/><span class="thpladmin-subtitle"><?php echo $sub_label; ?></span>
						<?php
					}
					?>
				</td>
				<?php $this->render_form_fragment_tooltip($tooltip); ?>
				<td <?php echo $input_cell_props ?> ><?php echo $field_html; ?></td>
				<?php*/
			}
			
			if($return){
				return $field_html;
			}else{
				echo $field_html;
			}
		}
	}
		
	private function prepare_form_field_props($field, $atts = array()){
		$field_props = '';
		$args = shortcode_atts( array(
			'input_width' => '',
			'input_name_prefix' => 'i_',
			'input_name_suffix' => '',
		), $atts );

		$ftype = isset($field['type']) ? $field['type'] : 'text';
		$fvalue = isset($field['value']) ? $field['value'] : '';

		if($ftype == 'multiselect' || $ftype == 'multiselect_grouped'){
			if(is_array($fvalue)){
				$fvalue = !empty($fvalue) ? implode(',', $fvalue) : $fvalue;
			}
			$field_props  .= 'data-value="'. $fvalue . '" ';
			$args['input_name_suffix'] = $args['input_name_suffix'].'[]';
		}
		
		$fname  = $args['input_name_prefix'].$field['name'].$args['input_name_suffix'];		
		$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
		$field_props  .= 'name="'. $fname .'" value="'. $fvalue .'" style="'. $input_width .'"';
		$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
		$field_props .= ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.$field['onchange'].'"' : '';

		return $field_props;
	}
	
	private function render_form_field_element_inputtext($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<input class="thwmscp_text_input" type="text" '. $field_props .' />';
		}
		return $field_html;
	}

	private function render_form_field_element_propertygroup($field, $atts = array()){ 
		$field_html = '';
		if($field && is_array($field)){
			$property_items = ($field['property_items']) && is_array($field['property_items']) ? $field['property_items'] : array();
			$atts['input_width'] = '57px';

			if($property_items && is_array($property_items)){
				$group_render = array();
				foreach ($property_items as $grp_key => $grp_value) {  
					$group_render['name'] = $grp_key;
					$group_render['value'] = $grp_value;		
					$field_props = $this->prepare_form_field_props($group_render, $atts); 	 				
					$field_html .= '<input class="thwmscp_small_textbox" type="text" '. $field_props .' />';  
				}
			}
		}
		return $field_html;
	}
	
	private function render_form_field_element_hidden($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="hidden" '. $field_props .' />';
		}
		return $field_html;
	}

	private function render_form_field_element_inputnumber($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			$field_html = '<input type="number" '. $field_props .' />';
		}
		return $field_html;
	}
	
	private function render_form_field_element_textarea($field, $atts = array(),$copy_button){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'rows' => '5',
				'cols' => '100',
			), $atts );
		
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);

			/* If condition works only if Advanced settings backup and import settings textarea render */

			if ($copy_button === 'copy_to_clipboard_backup') {
				$field_html = '<div style="position:relative;">';
				$field_html.= '<textarea id="thwmsc_backup_settings" style="padding-bottom: 25px;" class="thwmscp_textarea" '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'.$fvalue.'</textarea>';
				$field_html.= '<div class="thwmsc-backup-copy-button-wrapper"><button type="button" class="thwmsc-backup-copy thwmsc-copy-icon copied" onclick="copy_backup_settings(event)"></button></div><span class="thwmsc-backup-copy-tooltip"><label style="color:#fff; font-size:12px;">Click to copy Settings</label></span>';
				$field_html.='</div>';
			}else {
				$field_html = '<textarea class="thwmscp_textarea" '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'.$fvalue.'</textarea>';
			}
			
		}
		return $field_html;
	}
	
	private function render_form_field_element_select($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html = '<select '. $field_props .' class="thwmscp_select" >';
			foreach($field['options'] as $value => $label){
				$selected = $value === $fvalue ? 'selected' : '';
				$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. sprintf(__('%s', 'woocommerce-multistep-checkout'), $label) .'</option>';
			}
			$field_html .= '</select>';
		}
		return $field_html;
	}
	
	private function render_form_field_element_multiselect($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html = '<select multiple="multiple" '. $field_props .' class="thpladmin-enhanced-multi-select" >';
			foreach($field['options'] as $value => $label){
				//$selected = $value === $fvalue ? 'selected' : ''; 
				$field_html .= '<option value="'. trim($value) .'" >'. sprintf(__('%s', 'woocommerce-multistep-checkout'), $label) .'</option>';
			}
			$field_html .= '</select>';
		}
		return $field_html;
	}

	private function render_form_field_element_multiselect_grouped($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html = '<select multiple="multiple" '. $field_props .' class="thpladmin-enhanced-multi-select" >';

			foreach($field['options'] as $group_label => $fields){
				$field_html .= '<optgroup label="'. $group_label .'">';
				
				foreach($fields as $value => $label){
					$value = trim($value);
					if(isset($field['glue']) && !empty($field['glue'])){
						$value = $value.$field['glue'].trim($label);
					}
					
					$field_html .= '<option value="'. $value .'">'. sprintf(__('%s', 'woocommerce-multistep-checkout'), $label) .'</option>';
				}
				
				$field_html .= '</optgroup>';
			}

			$field_html .= '</select>';
		}
		return $field_html;
	}
	
	private function render_form_field_element_radio($field, $atts = array(), $layout_type){
		$field_html = '';
		$args = shortcode_atts( array(
			'label_props' => '',
			'cell_props'  => 3,
			'render_input_cell' => false,
			'render_label_cell' => false,
		), $atts );

		$atts = array(
			'input_width' => 'auto',
		);

		if($field && is_array($field)){
			
			$fvalue = isset($field['value']) ? $field['value'] : '';
			$field_props = $this->prepare_form_field_props($field, $atts);			

			foreach($field['options'] as $value => $label){
				if ($layout_type == 'horizontal' && $value == 'thwmsc_horizontal_box' | $value == 'thwmsc_horizontal_arrow' | $value == 'thwmsc_closed_arrow_layout' | $value == 'thwmsc_time_line_step' | $value == 'thwmsc_looped_box_layout' | $value == 'thwmsc_tab_format' | $value == 'thwmsc_simple_dot_format' | $value == 'thwmsc_custom_separator') {
					// $checked ='';
					// $img_layout = '';

					// //$flabel = isset($label) && !empty($label) ? THWMSC_i18n::t($label) : '';
					// $flabel = isset($label['name']) && !empty($label['name']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $label['name']) : '';
					// $onchange = ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.$field['onchange'].'"' : '';
					// $img_layout = isset($label['layout_image']) && !empty($label['layout_image']) ? $label['layout_image'] : '';

					// $checked = $value === $fvalue ? 'checked' : '';				
					// $field_html .='<label for="'. $value .'" '. $args['label_props'] .' > ';				

					// $field_html .= '<input type="radio" name="i_' . $field['name'] . '" id="'. $value . '" value="'. trim($value) .'" ' . $checked . $onchange . '>';
					// //$field_html .= '<span class ="layout-icon ' . $value . '"></span>';
					// $field_html .= '<img src= "'. THWMSC_URL . 'admin/assets/images/' . $img_layout.'">';
					// $field_html .= $flabel.'</label>';

					$field_html =$this->render_wmsc_layouts($label,$field,$value,$fvalue,$field_html,$args);
					
				}
				elseif ($layout_type == 'vertical' && $value == 'thwmsc_vertical_box' | $value == 'thwmsc_vertical_arrow' | $value == 'thwmsc_vertical_box_border' | $value == 'thwmsc_vertical_arrow_border') {
					$field_html =$this->render_wmsc_layouts($label,$field,$value,$fvalue,$field_html,$args);
				}elseif ($layout_type == 'accordion' && $value == 'thwmsc_accordion_tab' | $value == 'thwmsc_accordion_icon') {
					$field_html =$this->render_wmsc_layouts($label,$field,$value,$fvalue,$field_html,$args);
				}
				elseif ($layout_type == '') {
					$field_html =$this->render_wmsc_layouts($label,$field,$value,$fvalue,$field_html,$args);
				}
			}			
		}		
		return $field_html;
	}

	private function render_wmsc_layouts($label,$field,$value,$fvalue,$field_html,$args){
		$checked ='';
		$img_layout = '';

		//$flabel = isset($label) && !empty($label) ? THWMSC_i18n::t($label) : '';
		$flabel = isset($label['name']) && !empty($label['name']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $label['name']) : '';
		$onchange = ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.$field['onchange'].'"' : '';
		$img_layout = isset($label['layout_image']) && !empty($label['layout_image']) ? $label['layout_image'] : '';

		$checked = $value === $fvalue ? 'checked' : '';				
		$field_html .='<label for="'. $value .'" '. $args['label_props'] .' style="position:relative;" > ';				

		$field_html .= '<input type="radio" name="i_' . $field['name'] . '" id="'. $value . '" value="'. trim($value) .'" ' . $checked . $onchange . '>';
		//$field_html .= '<span class ="layout-icon ' . $value . '"></span>';

		// Notify customers about modification in simple dot layout in upcoming version
		if ($value === 'thwmsc_simple_dot_format') {
			$field_html .= '<img class="thwmscp_layouts" src= "'. THWMSC_URL . 'admin/assets/images/' . $img_layout.'">';
			$field_html .= '<span class="thwmsc-simple-dot-warning-tooltip">From the next version onwards, the "Simple Dot Format" will have modifications in both the layout and settings.</span>';
		}else {
			$field_html .= '<img class="thwmscp_layouts" src= "'. THWMSC_URL . 'admin/assets/images/' . $img_layout.'">';
		}
		$field_html .= $flabel.'</label>';

		return $field_html;
	}
	
	private function render_form_field_element_checkbox($field, $atts = array(), $render_cell = true){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'label_props' => '',
				'cell_props'  => 3,
				'render_input_cell' => false,
				'render_label_cell' => false,
				'render_switch_style' => true,
			), $atts );
		
			$fid 	= 'a_f'. $field['name'];
			$flabel = isset($field['label']) && !empty($field['label']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $field['label']) : '';
			
			$field_props  = $this->prepare_form_field_props($field, $atts);
			$field_props .= (isset($field['checked']) && $field['checked']) ? ' checked' : '';
			
			if($args['render_switch_style']){
				$field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
				$field_html .= '<label for="'. $fid .'"> '. $flabel .'</label>';
			}else if($args['render_label_cell']){
				$field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
			}else{
				$field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
				$field_html .= '<label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label>';
			}
		}
		if(!$render_cell && $args['render_input_cell']){
			return '<td '. $args['cell_props'] .' >'. $field_html .'</td>';
		}else{
			return $field_html;
		}
	}
	
	private function render_form_field_element_colorpicker($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$field_props = $this->prepare_form_field_props($field, $atts);
			
			$field_html  = '<div class="thwmscp_clrpicker_wrapper">';
			$field_html .= '<span class="thwmscp_clrpicker thpladmin-colorpickpreview '.$field['name'].'_preview" style=""></span>';
            $field_html .= '<input type="text" '. $field_props .' class="thpladmin-colorpick thwmscp-colorpicker"/> </div>';
		}
		return $field_html;
	}


	public function render_tooltip_new_ui($tooltip = false, $return = false){
		$tooltip_html = '';
		
		if($tooltip){
			$tooltip_html .= '<a href="javascript:void(0)" title="'.$tooltip.'" class="thpladmin_tooltip thwmscp_tooltip"><img src="'.THWMSC_ASSETS_URL_ADMIN.'/images/help_tooltip.svg" title=""/></a>';
		}
		
		if($return){
			return $tooltip_html;
		}else{
			echo $tooltip_html;
		}
	}

	
	public function render_form_fragment_tooltip($tooltip = false, $return = false){
		$tooltip_html = '';
		
		if($tooltip){
			$tooltip_html .= '<td style="width: 26px; padding:0px;">';
			$tooltip_html .= '<a href="javascript:void(0)" title="'.$tooltip.'" class="thpladmin_tooltip thwmscp_tooltip"><img src="'.THWMSC_ASSETS_URL_ADMIN.'/images/help_tooltip.svg" title=""/></a>';
			$tooltip_html .= '</td>';
			/*?>
			<td style="width: 26px; padding:0px;">
				<a href="javascript:void(0)" title="<?php echo $tooltip; ?>" class="thpladmin_tooltip"><img src="<?php echo THWMSC_ASSETS_URL_ADMIN; ?>/css/help.png" title=""/></a>
			</td>
			<?php*/
		}else{
			$tooltip_html .= '<td style="width: 26px; padding:0px;"></td>';
		}
		
		if($return){
			return $tooltip_html;
		}else{
			echo $tooltip_html;
		}
	}
	
	public function render_form_fragment_h_separator($atts = array()){
		$args = shortcode_atts( array(
			'colspan' 	   => 6,
			'padding-top'  => '5px',
			'border-style' => 'dashed',
    		'border-width' => '1px',
			'border-color' => '#cecece',
			'content'	   => '',
		), $atts );
		
		$style  = $args['padding-top'] ? 'padding-top:'.$args['padding-top'].';' : '';
		$style .= $args['border-style'] ? ' border-bottom:'.$args['border-width'].' '.$args['border-style'].' '.$args['border-color'].';' : '';
		
		?>
        <tr><td colspan="<?php echo $args['colspan']; ?>" style="<?php echo $style; ?>"><?php echo $args['content']; ?></td></tr>
        <?php
	}
	
	/*private function output_h_separator($show_line = true){
		$style = $show_line ? 'margin: 5px 0; border-bottom: 1px dashed #ccc' : '';
		echo '<tr><td colspan="6" style="'.$style.'">&nbsp;</td></tr>';
	}*/
	
	public function render_field_form_fragment_h_spacing($padding = 5){
		$style = $padding ? 'padding-top:'.$padding.'px;' : '';
		?>
        <tr><td colspan="6" style="<?php echo $style ?>"></td></tr>
        <?php
	}
	
	public function render_form_field_blank($colspan = 3){
		?>
        <td colspan="<?php echo $colspan; ?>">&nbsp;</td>  
        <?php
	}
	
	public function render_form_section_separator($props, $atts=array()){
		?>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:10px;"></td></tr>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" class="thpladmin-form-section-title" ><?php echo $props['title']; ?></td></tr>
		<tr valign="top"><td colspan="<?php echo $props['colspan']; ?>" style="height:0px;"></td></tr>
		<?php
	}

	public function render_form_section_description($props, $atts=array()){
		?>
		<tr valign="top">
			<td colspan="1"></td>
			<td colspan="<?php echo $props['colspan']; ?>" class="thpladmin-form-section-description thwmscp-form-description" ><?php echo $props['description']; ?></td>
		</tr>
		<?php
	}
}

endif;