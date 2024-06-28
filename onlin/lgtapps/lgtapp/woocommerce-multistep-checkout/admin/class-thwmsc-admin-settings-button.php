<?php
/**
 * The admin display settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/admin
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC_Admin_Settings_Button')):

class THWMSC_Admin_Settings_Button extends THWMSC_Admin_Settings{
    protected static $_instance = null;
    private $settings_fields_advncd_sttng = NULL;
    private $settings_fields_new_sttng  = NULL;
    private $cell_props_L = array();
    private $cell_props_R = array();
    private $cell_props_CB = array();


    public function __construct() {
        parent::__construct('button_settings');
        $this->init_constants();
    }

    public static function instance() {
        if(is_null(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    } 

    public function init_constants(){
        $this->cell_props_L = array( 
            'label_cell_props' => 'class="titledesc" scope="row" style="width: 20%;"', 
            'input_cell_props' => 'class="forminp"', 
            'input_width' => '250px', 
            'label_cell_th' => true 
        );
        $this->cell_props_CB = array( 
            'cell_props' => 'colspan="2"', 
            'render_input_cell' => true,
            'input_cell_props' => 'class="wmsc-switch"',
        );
        $this->cell_props_R = array( 
            'label_cell_width' => '13%', 
            'input_cell_width' => '34%', 
            'input_width' => '250px' 
        );

        $this->settings_fields_advncd_sttng = $this->get_button_settings_fields();
        $this->settings_fields_new_sttng    = $this->get_button_settings_fields_new();
    }


    public function get_button_settings_fields_new() {

        $display_settings = THWMSC_Utils::get_advanced_settings();
        $thwmsc_layout = isset($display_settings['thwmsc_layout']) ? $display_settings['thwmsc_layout'] : '';
        if($thwmsc_layout == 'thwmsc_vertical_box' || $thwmsc_layout == 'thwmsc_vertical_arrow' || $thwmsc_layout == 'thwmsc_vertical_box_border' || $thwmsc_layout == 'thwmsc_vertical_arrow_border' || $thwmsc_layout == 'thwmsc_accordion_tab' || $thwmsc_layout == 'thwmsc_accordion_icon'){
            $button_position = array( 
                'bottom' => 'Bottom',
                'below_tab' => 'Top'
            );
        }else{
            $button_position = array( 
                'bottom' => 'Bottom',
                'below_tab' => 'Below tab',
                'above_tab' => 'Above tab'
            );
        }

        $button_alignment = array(
            'left' => 'Left',
            'right' => 'Right'
        );


        $layout_field = array(
            'hide_first_step_prev' => array(
                'name'=>'hide_first_step_prev', 'label'=>__('Hide the previous button on the first step', 'woocommerce-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
            ),
            'hide_last_step_next' => array(
                'name'=>'hide_last_step_next', 'label'=>__('Hide the next button for the last step', 'woocommerce-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
            ),
            'button_prev_text' => array(
                'name'=>'button_prev_text', 'label'=>__('Previous button text', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'placeholder'=>''
            ),
            'button_next_text' => array(
                'name'=>'button_next_text', 'label'=>__('Next button text', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'placeholder'=>''
            ),
            'button_position' => array( 
                'name'=>'button_position', 'label'=>__('Button position', 'woocommerce-multistep-checkout'), 'type'=>'select', 'value'=>'bottom', 'options'=> $button_position
            ),
            'button_alignment' => array( 
                'name'=>'button_alignment', 'label'=>__('Button alignment', 'woocommerce-multistep-checkout'), 'type'=>'select', 'value'=>'right', 'options'=> $button_alignment            
            ),
            'place_order_text' => array(
                'name'=>'place_order_text', 'label'=>__('Button place order text', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'placeholder'=>''
            ),
            'enable_back_to_cart_button' => array(
                'name'=>'enable_back_to_cart_button', 'label'=>__('Activate back to cart button', 'woocommerce-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscShowBacktocart(this)'
            ),
            'back_to_cart_button_text' => array(
                'name'=>'back_to_cart_button_text', 'label'=>__('Back to cart button text', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'placeholder'=>''
            ),

        );

        return $layout_field;
    }

    public function get_button_settings_fields(){


        $padding_type = array(
            'button_padding_top' => '10px',
            'button_padding_right' => '22px',
            'button_padding_bottom' => '10px',
            'button_padding_left' => '22px'
        );
        $border_style =  array(
            'none'   => 'None',
            'dashed' => 'Dashed',
            'dotted' => 'Dotted',
            'solid'  => 'Solid'
        );


        $layout_field = array(
            'button_new_class' => array(
                'name'=>'button_new_class', 'label'=>__('Button class', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'placeholder'=>'Seperate classes with comma'
            ),
            'button_style_active' => array(
                'name'=>"button_style_active", 'label'=>__('Activate button styling', 'woocommerce-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscButtonStyleListner(this)' 
            ),
            'button_text_font_size' => array(
                'name'=>'button_text_font_size', 'label'=>__('Font size', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'unittype'=>'mixed', 'placeholder'=>'Eg : 16px'
            ),
            'button_text_font_color' => array(
                'name'=>'button_text_font_color', 'label'=>__('Font color', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#333333'
            ),
            'button_text_font_color_hover' => array(
                'name'=>'button_text_font_color_hover', 'label'=>__('Font color - Hover', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#333333'
            ),
            'button_bg_color' => array(
                'name'=>'button_bg_color', 'label'=>__('Background color', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#eeeeee' 
            ),
            'button_bg_color_hover' => array(
                'name'=>'button_bg_color_hover', 'label'=>__('Background color - Hover', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#d5d5d5' 
            ),
            'button_padding' => array(
                'name'=>'button_padding', 'label'=>__('Padding', 'woocommerce-multistep-checkout'), 'type'=>'propertygroup', 'value'=>'', 'unittype'=>'mixed', 'property_items'=> $padding_type 
            ),
            'button_border_width' => array(
                'name'=>'button_border_width', 'label'=>__('Border width', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'unittype'=>'mixed', 'placeholder'=>'Eg : 2px'
            ),
            'button_border_color' => array(
                'name'=>'button_border_color', 'label'=>__('Border color', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#d5d5d5',
            ),
            'button_border_radius' => array(
                'name'=>'button_border_radius', 'label'=>__('Border radius', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'unittype'=>'mixed', 'placeholder'=>'Eg : 5px'
            ),
            'button_border_style' => array(
                'name'=>'button_border_style', 'label'=>__('Border style', 'woocommerce-multistep-checkout'), 'type'=>'select', 'value'=>'none', 'options'=>$border_style
            ),
            'button_style_place_order' => array(
                'name'=>"button_style_place_order", 'label'=>__('Activate place order button styling', 'woocommerce-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscPlaceorderButtonStyleListner(this)' 
            ),
            'order_btn_text_font_size' => array(
                'name'=>'order_btn_text_font_size', 'label'=>__('Font size', 'woocommerce-multistep-checkout'), 'type'=>'text', 'value'=>'', 'unittype'=>'mixed', 'placeholder'=>'Eg : 20px'
            ),
            'order_btn_text_font_color' => array(
                'name'=>'order_btn_text_font_color', 'label'=>__('Font color', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#ffffff'
            ),
            'order_btn_text_font_color_hover' => array(
                'name'=>'order_btn_text_font_color_hover', 'label'=>__('Font color - Hover', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#ffffff' 
            ),
            'order_btn_bg_color' => array(
                'name'=>'order_btn_bg_color', 'label'=>__('Background color', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#333333' 
            ),
            'order_btn_bg_color_hover' => array(
                'name'=>'order_btn_bg_color_hover', 'label'=>__('Background color - Hover', 'woocommerce-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#1a1a1a' 
            ),
        );

        return $layout_field;
    }

    public function render_page(){
        // $this->render_tabs();
        $this->render_content();
    }

    public function save_new_advanced_settings($settings){
        $result = update_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS, $settings);
        return $result;
    }

    public function save_advanced_settings($settings){
        $result = update_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings);
        return $result;
    }


    private function unit_value_separator($mixed){ 
        if($mixed){
            $unit_value = array();                  
            $value = preg_replace('/[^0-9\.]/','',$mixed);
            $unit = str_replace($value,"",$mixed);

            if(is_numeric($value)){     
                $unit_value['value'] = $value;
                $unit_value['unit'] = $unit ? $unit : 'px';             
                return $unit_value;
            }           
        }
    }

    private function unit_value_concatenation($value, $unit){
        return ($value.$unit);
    }

    private function prepare_settings($button_settings,$settings) {
        foreach( $button_settings as $name => $field ) {
            $value = '';
            $mixed = false; 
            if(isset($field['unittype']) && $field['unittype'] === 'mixed'){
                $mixed = true;
            }

            if($field['type'] === 'checkbox'){
                $value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
            }else if($field['type'] === 'text' || $field['type'] === 'textarea'){
                $value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
                $value = !empty($value) ? wc_clean(wp_unslash($value)) : '';
                    
                if($value && $mixed){
                    $unit_value = $this->unit_value_separator($value); 
                    if(is_array($unit_value)){
                        $settings[$name.'_unit'] = $unit_value['unit'];
                        $value = $unit_value['value'];
                    }
                }
            }else if($field['type'] === 'propertygroup'){
                $property_items = ($field['property_items']) && is_array($field['property_items']) ? $field['property_items'] : array();                                    
                if($property_items){  
                    settype($value, 'array');
                    $property_grp = array();
                    foreach ($property_items as $grp_key => $grp_value) {
                        $pvalue = !empty( $_POST['i_'.$grp_key] ) ? $_POST['i_'.$grp_key] : '';
                        $pvalue = !empty($pvalue) ? wc_clean(wp_unslash($pvalue)) : '';

                        if($pvalue && $mixed){
                            $unit_value = $this->unit_value_separator($pvalue); 
                            if(is_array($unit_value)){
                                $settings[$name.'_unit'] = $unit_value['unit'];
                                $pvalue = $unit_value['value'];
                            }
                        }
                        $property_grp[$grp_key] = $pvalue;   
                    } 
                    $value = $property_grp; 
                }                               
            }else{
                $value = !empty( $_POST['i_'.$name] ) ? $_POST['i_'.$name] : '';
                if($value && $mixed){
                    $unit_value = $this->unit_value_separator($value); 
                    if(is_array($unit_value)){
                        $settings[$name.'_unit'] = $unit_value['unit'];
                        $value = $unit_value['value'];
                    }
                }
            }

            $settings[$name] = $value;

        }

        return $settings;
    }

    private function reset_settings($options){

        $settings_with_key_advncd = array();
        $settings_with_key_new    = array();

        $button_settings_advncd = array();
        $button_settings_new    = array();

        $key_advncd_setting = $this->settings_fields_advncd_sttng;
        $key_new_setting    = $this->settings_fields_new_sttng;

        foreach ($options as $key=>$value) {
            if (array_key_exists($value, $key_advncd_setting)) {
                $button_settings_advncd[] = $value;
            }
        }
        foreach ($options as $key=>$value) {
            if (array_key_exists($value, $key_new_setting)) {
                $button_settings_new[] = $value;
            }
        }

        $get_button_sttng_advanced = get_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS);
        $get_button_sttng_new = get_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS);

        if($get_button_sttng_advanced){
            foreach ($get_button_sttng_advanced as $key=>$value) {
                if (!(in_array($key , $button_settings_advncd))) {
                    $settings_with_key_advncd[$key] = $value; 
                }
            }
        }
        if($get_button_sttng_new){
            foreach ($get_button_sttng_new as $key=>$value) {
                if (!(in_array($key , $button_settings_new))) {
                    $settings_with_key_new[$key] = $value; 
                }
            }
        }

        if (!empty($settings_with_key_advncd)) {
            update_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings_with_key_advncd);
        }
        if (!empty($settings_with_key_new)) {
            update_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS, $settings_with_key_new);
        }

        //if($result){
        // delete_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS);
        // delete_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS);
        echo '<div class="updated notice notice-info thwmscp_admin_notice"><p>'. __('Settings successfully reset', 'woocommerce-multistep-checkout') .'</p></div>';
        //}
    }
    private function save_settings() {
        $settings = array();

        $button_settings_advanced = $this->prepare_settings($this->settings_fields_advncd_sttng, $settings);
        $button_settings_new = $this->prepare_settings($this->settings_fields_new_sttng, $settings);

        $advncd_setting = get_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS);
        $new_setting = get_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS);

        if (gettype($advncd_setting) != 'array') {
            $advncd_setting = explode(' ', $advncd_setting);
        }
        if (gettype($new_setting) != 'array') {
            $new_setting = explode(' ', $new_setting);
        }
        $button_settings_advanced = array_merge($advncd_setting, $button_settings_advanced);
        $button_settings_new      = array_merge($new_setting, $button_settings_new);

        $result = $this->save_advanced_settings($button_settings_advanced);
        $result1 = $this->save_new_advanced_settings($button_settings_new);

        
        if ($result == true | $result1 == true) {
            echo '<div class="updated notice notice-info thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were saved.', 'woocommerce-multistep-checkout') .'</p></div>';
        } else {
            echo '<div class="error notice thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multistep-checkout') .'</p></div>';
        }
        

    }

    private function render_content(){


        $button_settings = THWMSC_Utils::get_button_settings_field();
        $tab_name = isset($_POST['hidden_reset']) && $_POST['hidden_reset'] ? $_POST['hidden_reset'] : 'next_prev_btn';
        $sub_tab = $button_settings[$tab_name];

        if(isset($_POST['reset_settings'])) {
            $this->reset_settings($sub_tab);    
        }
            
        if(isset($_POST['save_settings'])){
            $this->save_settings();
        }

        $this->render_tabs();

        $button_fields_advance = $this->settings_fields_advncd_sttng;
        $button_fields_new = $this->settings_fields_new_sttng;

        $fields = array_merge($button_fields_advance, $button_fields_new);

        $display_settings = THWMSC_Utils::get_advanced_settings();
        $advanced_settings = THWMSC_Utils::get_new_advanced_settings();

        $settings = array_merge( $display_settings, $advanced_settings );


        $display_style = isset($settings['button_style_active']) && ($settings['button_style_active'] == 'yes') ? '' : 'wmsc-blur';
        $order_btn_style = isset($settings['button_style_place_order']) && ($settings['button_style_place_order'] == 'yes') ? '' : 'wmsc-blur';
        $back_to_cart_button = isset($settings['enable_back_to_cart_button']) && ($settings['enable_back_to_cart_button'] == 'yes') ? '' : 'wmsc-blur';

        /* Display styles of each subtab after reloading a page*/

        $next_prev_setting_style = (isset($tab_name) && $tab_name === 'next_prev_btn') ? 'display:block;' : 'display:none';
        $place_order_setting_style = (isset($tab_name) && $tab_name === 'place_orde_btn') ? 'display:block;' : 'display:none';
        $back_to_cart_setting_style = (isset($tab_name) && $tab_name === 'back_to_cart_btn') ? 'display:block;' : 'display:none';

        $next_prev_active_class = (isset($tab_name) && $tab_name === 'next_prev_btn') ? 'active' : '';
        $place_order_active_class = (isset($tab_name) && $tab_name === 'place_orde_btn') ? 'active' : '';
        $back_to_cart_active_class = (isset($tab_name) && $tab_name === 'back_to_cart_btn') ? 'active' : '';



        foreach( $fields as $name => &$field ) {
            $mixed = false; 
            if(isset($field['unittype']) && $field['unittype'] === 'mixed'){
                $mixed = true;
            }

            if($field['type'] != 'separator'){
                if(is_array($settings) && isset($settings[$name])){
                    if($field['type'] === 'checkbox'){
                        if(isset($field['value']) && $field['value'] === $settings[$name]){
                            $field['checked'] = 1;
                        }else{
                            $field['checked'] = 0;
                        }
                    }else if($field['type'] === 'propertygroup'){
                        $property_items = ($field['property_items']) && is_array($field['property_items']) ? $field['property_items'] : array();
                        $db_content = array();
                        if($property_items && is_array($property_items)){
                            $db_content = $settings[$name]; 
                            $unit = isset($settings[$name.'_unit']) ? $settings[$name.'_unit'] : 'px';  
                            $populate_db = array();
                            foreach ($property_items as $grp_key => $grp_value) {
                                $value = $db_content[$grp_key]; 
                                if($value && $mixed){                                   
                                    $value = $this->unit_value_concatenation($value, $unit);                            
                                }
                                $populate_db[$grp_key] = $value; 
                            }
                            $field['property_items'] = $populate_db; 
                        }  
                    }else{
                        $value = esc_attr($settings[$name]); 
                        if(is_numeric($value) && $mixed){
                            $unit = isset($settings[$name.'_unit']) ? $settings[$name.'_unit'] : 'px';
                            $value = $this->unit_value_concatenation($value, $unit);                            
                        }
                        $field['value'] = $value;
                    }
                }
            }
        }

        ?>
        <div style="width: 93%; padding: 30px; background: white; margin-top: 27px; float: left;margin-left: 14px;">  
            <form id="thwmsc_advanced_settings_form" method="post" action="" class="thwmscp_sttng_form">
                <div class="thwmscp-sttng-wrapper">
                    <ul class="thwmscp_settings_section">
                        <li style="margin-right: 30%;">
                            <a id="next_prev_btn" class="btn_settng_links thwmscp_section_font <?php echo $next_prev_active_class; ?>" href="javascript:void(0)" onclick="openButtonTabs(event, 'thwmscp_next_previous')"><?php esc_html_e('Next and Previous Button Settings', 'woocommerce-multistep-checkout');?></a>
                        </li>
                        <li style="margin-right: 30%;">
                            <a id="place_orde_btn" class="btn_settng_links thwmscp_section_font <?php echo $place_order_active_class; ?>" href="javascript:void(0)" onclick="openButtonTabs(event, 'thwmscp_place_order_btn')"><?php esc_html_e('Place order Button Settings', 'woocommerce-multistep-checkout');?></a>
                        </li>
                        <li style="margin-right: 30%;">
                            <a id="back_to_cart_btn" class="btn_settng_links thwmscp_section_font <?php echo $back_to_cart_active_class; ?>" href="javascript:void(0)" onclick="openButtonTabs(event,'thwmscp_back_to_cart')"><?php esc_html_e('Back to cart Button Settings', 'woocommerce-multistep-checkout');?></a>
                        </li>
                    </ul>  
                    <div class="thwmscp-settings-wrapper-div">             
                        <div id="thwmscp_next_previous" class="btn_settings" style="<?php echo $next_prev_setting_style; ?>">
                            <table class="form-table thpladmin-form-table thwmscp-form-table">
                                <tbody> 
                                    <tr>
                                        <?php $this->render_form_field_element($fields['button_new_class'], $this->cell_props_L); ?>
                                        <?php $this->render_form_field_element($fields['button_prev_text'], $this->cell_props_L); ?>
                                        <?php $this->render_form_field_element($fields['button_next_text'], $this->cell_props_L); ?>
                                    </tr>
                                    <tr>
                                        <?php $this->render_form_field_element($fields['button_position'], $this->cell_props_L); ?>
                                        <?php $this->render_form_field_element($fields['button_alignment'], $this->cell_props_L); ?>
                                        <?php $this->render_form_field_blank(1); ?>
                                    </tr>
                                    <tr>
                                        <?php
                                        $cell_props_cb = $this->cell_props_CB;
                                        $cell_props_cb['render_label_cell'] = true;
                                        $this->render_form_field_element($fields['hide_first_step_prev'], $cell_props_cb);
                                        $this->render_form_field_blank(1);
                                        ?>  
                                    </tr>
                                    <tr>
                                        <?php
                                        $this->render_form_field_element($fields['hide_last_step_next'], $cell_props_cb);
                                        $this->render_form_field_blank(1);
                                        ?>  
                                    </tr>
                                    <tr>
                                        <?php     
                                        $this->render_form_field_element($fields['button_style_active'], $cell_props_cb);
                                        $this->render_form_field_blank(1);
                                        ?>
                                    </tr>
                                </tbody>
                                <tbody id="thwmsc_button_styles" class="<?php echo $display_style; ?>">                     
                                    <tr>
                                        <?php          
                                        $this->render_form_field_element($fields['button_text_font_size'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['button_text_font_color'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['button_text_font_color_hover'], $this->cell_props_R);
                                        ?>
                                    </tr>
                                    <tr>
                                        <?php          
                                        $this->render_form_field_element($fields['button_bg_color'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['button_bg_color_hover'], $this->cell_props_R);
                                        $this->render_form_field_element($fields['button_border_style'], $this->cell_props_R);
                                        ?>
                                    </tr>
                                    <tr>
                                        <?php          
                                        $this->render_form_field_element($fields['button_border_width'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['button_border_color'], $this->cell_props_R); 
                                        $this->render_form_field_element($fields['button_border_radius'], $this->cell_props_L);
                                        ?>
                                    </tr>
                                    <tr> 
                                        <?php                                   
                                        $this->render_form_field_element($fields['button_padding'], $this->cell_props_L);  
                                        $this->render_form_field_blank(2); 
                                        ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="thwmscp_place_order_btn" class="btn_settings" style="<?php echo $place_order_setting_style; ?>">
                            <table class="form-table thpladmin-form-table thwmscp-form-table">
                                <tbody>
                                    <tr>
                                    <?php     
                                        $cell_props_cb = $this->cell_props_CB;
                                        $cell_props_cb['render_label_cell'] = true;     
                                        $this->render_form_field_element($fields['button_style_place_order'], $cell_props_cb);
                                        $this->render_form_field_blank(1);
                                    ?>
                                    </tr>
                                </tbody>
                                <tbody id="thwmsc_order_btn_styles" class="<?php echo $order_btn_style; ?>">
                                    <tr>
                                        <?php          
                                        $this->render_form_field_element($fields['order_btn_text_font_size'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['order_btn_text_font_color'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['order_btn_text_font_color_hover'], $this->cell_props_R);
                                        ?>
                                    </tr>
                                    <tr>
                                        <?php
                                        $this->render_form_field_element($fields['order_btn_bg_color'], $this->cell_props_L);
                                        $this->render_form_field_element($fields['order_btn_bg_color_hover'], $this->cell_props_R);
                                        $this->render_form_field_blank(1);
                                        ?>
                                    </tr>
                                    <tr>
                                        <?php 
                                        $this->render_form_field_element($fields['place_order_text'], $this->cell_props_L); 
                                        $this->render_form_field_blank(2);
                                        ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="thwmscp_back_to_cart" class="btn_settings" style="<?php echo $back_to_cart_setting_style; ?>">
                            <table class="form-table thpladmin-form-table thwmscp-form-table">
                                <tbody>
                                    <tr>
                                        <?php
                                        $this->render_form_field_element($fields['enable_back_to_cart_button'], $cell_props_cb);
                                        $this->render_form_field_blank(1);
                                        ?>  
                                    </tr>
                                    <tr id="th-show-backtocart" class="<?php echo $back_to_cart_button; ?>">
                                        <?php $this->render_form_field_element($fields['back_to_cart_button_text'], $this->cell_props_L); ?>
                                        <?php $this->render_form_field_blank(2); ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <input id="hidden_reset" type="hidden" name="hidden_reset" value="">
                        </div>
                    </div>
                </div>
                <p class="submit thwmscp_settings_submit">
                    <input type="submit" name="reset_settings" class="thwmscp_admin_button thwmscp_reset_btn" value="<?php _e('Reset to default', 'woocommerce-multistep-checkout');?>" onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
                    <input type="submit" name="save_settings" class="thwmscp_admin_button thwmscp_save_btn" value="<?php _e('Save changes', 'woocommerce-multistep-checkout');?>">
                </p>
            </form>
        </div>
        <?php

    }
    public function render_form_field_blank($colspan = 3){
        ?>
        <td width="<?php echo $colspan * 33.3 ?>%" colspan="<?php echo $colspan; ?>">&nbsp;</td>  
        <?php
    }

}
endif;
