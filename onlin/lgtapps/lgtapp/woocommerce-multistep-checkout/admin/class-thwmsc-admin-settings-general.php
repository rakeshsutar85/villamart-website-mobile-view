<?php
/**
 * The admin general settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/admin
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC_Admin_Settings_General')): 

class THWMSC_Admin_Settings_General extends THWMSC_Admin_Settings {
	protected static $_instance = null;
		
	private $cell_props = array();
	private $cell_props_CB = array();
	
	private $section_props = array();
	private $field_props = array();
	private $field_props_display = array();
	
	public function __construct() {
		parent::__construct('general_settings', '');
		$this->init_constants();
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function init_constants(){
		$this->cell_props = array( 
			'label_cell_props' => 'width="20%"', 
			'input_width' => '250px',  
		);
				
		$this->cell_props_CB = array(
			'label_props' => 'style="margin-right: 40px;"',
		);
	}
			
	public function render_page(){ 
		// $this->render_tabs();
		$this->render_content(); 
	}

	private function render_actions_row(){
		?>
        <th colspan="7">
            <input type="submit" name="save_settings" class="thwmscp_admin_button thwmscp_save_btn" value="<?php _e('Save changes', 'woocommerce-multistep-checkout'); ?>" style="float:right;" >
            <input type="submit" name="reset_settings" class="thwmscp_admin_button thwmscp_reset_btn" value="<?php _e('Reset Settings', 'woocommerce-multistep-checkout'); ?>" style="float:right; margin-right: 5px;" 
            onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
        </th>  
    	<?php 
	}
	private function render_add_new_step(){
		?>
        <th colspan="7">
            <button type="button" class="thwmscp_add_new_btn thwmscp_addnew_button" onclick="thwmscOpenNewStepForm()" style="float:right;"><?php _e('Add new step', 'woocommerce-multistep-checkout'); ?></button>
        </th>
        <?php
    }
	
	private function render_step_table_heading($step_settings){
		$advanced_settings = THWMSC_Utils::get_new_advanced_settings();
		$login_enabled = isset($advanced_settings['enable_login_step']) && $advanced_settings['enable_login_step'] == 'yes' ? true : false ;
		?>

		<!-- <th class="sort"></th> -->
		<?php /* ?><th class="name" width="13%"><?php _e('Name', 'woocommerce-multistep-checkout'); ?></th> <?php */ ?>
		<!-- <th class="label thwmscp_admin_title" width="25%"><?php // _e('Title', 'woocommerce-multistep-checkout'); ?></th>
        <th class="label align-center thwmscp_admin_title" width="25%" ><?php // _e('Display Index', 'woocommerce-multistep-checkout'); ?></th> -->

        <?php if($login_enabled){ ?>
        	<th ></th>
			<th class="label thwmscp_admin_title" width="25%"><?php _e('Title', 'woocommerce-multistep-checkout'); ?></th>
	        <th class="label align-center thwmscp_admin_title" width="25%" ><?php _e('Display index', 'woocommerce-multistep-checkout'); ?></th>
        	<th class="label align-center thwmscp_admin_title" width="22%" ><?php _e('Display index When Logged-in', 'woocommerce-multistep-checkout'); ?></th>
        	<th colspan="2" class="label thwmscp_admin_title" width="22%">
    			<button type="button" class="thwmscp_add_new_btn thwmscp_addnew_button" onclick="thwmscOpenNewStepForm()"><?php _e('Add new step', 'woocommerce-multistep-checkout'); ?></button>
    		</th>
    	<?php }else{ ?> 
    		<th ></th>
			<th class="label thwmscp_admin_title" width="34%"><?php _e('Title', 'woocommerce-multistep-checkout'); ?></th>
	        <th class="label align-center thwmscp_admin_title" width="34%" ><?php _e('Display index', 'woocommerce-multistep-checkout'); ?></th>
	        <th></th>
	        <th colspan="2" class="label thwmscp_admin_title" width="22%">
    			<button type="button" class="thwmscp_add_new_btn thwmscp_addnew_button" onclick="thwmscOpenNewStepForm()"><?php _e('Add new step', 'woocommerce-multistep-checkout'); ?></button>
    		</th>
    	<?php } ?>
        <!-- <th class="label" width="20%"><?php // _e('Action Hook Name', 'woocommerce-multistep-checkout'); ?></th> -->
        <!-- <th class="status" width="8%"><?php // _e('Enabled', 'woocommerce-multistep-checkout'); ?></th>
        <th class="actions align-center" width="10%"><?php // _e('Actions', 'woocommerce-multistep-checkout'); ?></th>	   -->       
        <?php
	}
	
	private function render_content(){
		$step_settings = THWMSC_Utils::get_step_settings_admin();

		if(isset($_POST['s_action']) && $_POST['s_action'] == 'new')
			echo $this->create_step();

		if(isset($_POST['s_action']) && $_POST['s_action'] == 'edit')
			echo $this->update_step();
			
		if(isset($_POST['step_name']) && !empty($_POST['step_name']))
			echo $this->delete_step();  

		if(isset($_POST['save_settings']))
			echo $this->save_settings();

		if(isset($_POST['reset_settings']))  
			echo $this->reset_to_default();

		$this->render_tabs();
		
		$step_props = THWMSC_Utils::$STEP_PROPS;
		$step_settings = THWMSC_Utils::get_step_settings_admin();

		$advanced_settings = THWMSC_Utils::get_new_advanced_settings();

		$login_enabled = isset($advanced_settings['enable_login_step']) && $advanced_settings['enable_login_step'] == 'yes' ? true : false ;
		$my_account_login = isset($advanced_settings['use_my_account_login']) && $advanced_settings['use_my_account_login'] == 'yes' ? true : false ;
		$login_row = $login_enabled && !$my_account_login ? ' th-login-raw' : '';

		$shipping_row = isset($advanced_settings['make_billing_shipping_together']) && $advanced_settings['make_billing_shipping_together'] == 'yes' ? ' shipping-hide' : '';
		
		$separator = array('title'=>'', 'type'=>'separator', 'colspan'=>'3')
		?>            
        <div style="margin-top: 2px; margin-right: 30px; margin-left:14px;">               
		    <form id="thwmsc_tab_general_settings_form" method="post" action="">
                <table id="thwmsc_checkout_steps" class="wc_gateways widefat thpladmin_steps_table thwmscp-admin-table" cellspacing="0">
                    <!-- <tr><?php //$this->render_add_new_step(); ?></tr> -->
                    <tr><?php $this->render_step_table_heading($step_settings); ?></tr>
                    <tbody class="ui-sortable thwmsc-steps-row">
                    <?php
                    $i = 0;
					foreach($step_settings as $sname => $step_info){	
						$is_enabled = $step_info['enabled'] ? 1 : 0;
						$separator['title'] = $step_info['title'];
						$hidden_fields_html = '';
						$input_fields_html  = '';

						foreach($step_props as $name => $prop){
							$ftype  = isset($prop['type']) ? $prop['type'] : 'text';
							$fname  = 'i_'.$name.'['.$i.']';
							$fvalue = isset($step_info[$name]) ? $step_info[$name] : '';
							
							if($ftype === 'hidden'){
								$hidden_fields_html .= '<input type="hidden" name="'.$fname.'" value="'.$fvalue.'"/>';
							}else{
								$prop['name']  = $name.'['.$i.']';
								$prop['value'] = $fvalue;
								
								if($ftype === 'checkbox'){
									$cell_props = $this->cell_props_CB;
									$checked = $fvalue ? 'checked' : '';
									$input_fields_html .= '<td><input type="checkbox" name="'.$fname.'" value="'.$fvalue.'" '.$checked.'/></td>';
								}else{
									$cell_props = $this->cell_props;
									$input_fields_html .= '<td><input type="text" name="'.$fname.'" value="'.$fvalue.'" style="width: 300px;"/></td>';
								}
							}
						}						
												
						$is_removable = self::is_removable_tab($step_info['name']);
						$enabled_checked = isset($step_info['enabled']) && $step_info['enabled'] ? ' checked' : '';
						$enabled_checked = $is_removable ? $enabled_checked : ' checked';

                        $indextype	  = isset($step_info['indextype']) ? $step_info['indextype'] : 'text_index';
						$index_media  = isset($step_info['index_media']) ? $step_info['index_media'] : '';
						$index  = isset($step_info['index']) ? $step_info['index'] : '';
						$index_logged_in  = isset($step_info['index_logged_in']) ? $step_info['index_logged_in'] : $index;

						$props_json = $this->get_step_data_set_json($sname);

						$row_css  = 'row_'.$i; 
						//$row_css .= $is_enabled === 1 ? '' : ' thpladmin-disabled';
						$row_css .= $sname === 'order_review' ? ' static' : '';
						$row_css .= $sname === 'login' ? $login_row : '';
						$row_css .= $sname === 'shipping' ? $shipping_row : '';
						?>
                        <tr class="<?php echo $row_css; ?>">
                            <td width="1%" class="sort ui-sortable-handle thwmscp_sort" >
                                <input type="hidden" class="s_name" name="i_name[<?php echo $i; ?>]" value="<?php echo $step_info['name']; ?>"/>
                                <input type="hidden" class="myaccount_form" name="i_myaccount[<?php echo $i; ?>]" value="<?php echo $my_account_login; ?>"/>
                                <input type="hidden" name="i_order[<?php echo $i; ?>]" class="f_order" value="<?php echo $step_info['order']; ?>"/>
                                <input type="hidden" name="i_class[<?php echo $i; ?>]" value="<?php echo $step_info['class']; ?>"/>
                                <input type="hidden" name="i_action[<?php echo $i; ?>]" value="<?php echo $step_info['action']; ?>"/>
                                <input type="hidden" name="i_action_before[<?php echo $i; ?>]" value="<?php echo $step_info['action_before']; ?>"/>
                                <input type="hidden" name="i_action_after[<?php echo $i; ?>]" value="<?php echo $step_info['action_after']; ?>"/>
                                <input type="hidden" name="i_action_sections[<?php echo $i; ?>]" value="<?php echo $step_info['sections']; ?>"/>
                                <input type="hidden" name="i_custom[<?php echo $i; ?>]" value="<?php echo $step_info['custom']; ?>"/>
                                <input type="hidden" name="i_indextype[<?php echo $i; ?>]" value="<?php echo $indextype; ?>"/>

                                <input type="hidden" name="i_props[<?php echo $i; ?>]" class="s_props" value='<?php echo $props_json; ?>' />
                            </td>

                            <td><input type="text" name="i_title[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($step_info['title']); ?>" class="thwmscp_checkout_steps"/></td>                            
							<?php if($indextype == 'icon_index'){ ?>
                            	<td align="center">
                            		<input type="hidden" name="i_index_media[<?php echo $i; ?>]" value="<?php echo $index_media; ?>" style="width: 55px;"/>
                            		<div class="thwmsc-icon-index-preview" style="background-image: url(<?php echo wp_get_attachment_url($index_media)?>);"></div>
                            		<!-- <img class="thwmsc_admin_img thwmscp_dsply_img" src="<?php //echo wp_get_attachment_url($index_media)?> "> -->
                            	</td>
                            	<?php if($login_enabled){ ?>
                            		<td align="center">
                            			<div class="thwmsc-icon-index-preview" style="background-image: url(<?php echo wp_get_attachment_url($index_media)?>);"></div>
                            			<!-- <img class="thwmsc_admin_img thwmscp_dsply_img" src="<?php //echo wp_get_attachment_url($index_media)?> "> -->
                            		</td>
                            	<?php }else{ ?> 
                            		<td></td>
                            	<?php } ?>
                            <?php }else{  ?>
                            	<!-- <td align="center"><input type="text" name="i_index[<?php // echo $i; ?>]" value="<?php //echo htmlspecialchars($index) ; ?>" class="thwmscp_display_index" style="width: 55px;text-align: center;"/></td> -->

	                            <?php if($login_enabled){ ?>
	                            	<td align="center"><input type="text" name="i_index[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($index) ; ?>" class="thwmscp_display_index" style="width: 55px;text-align: center;"/></td>
	                        		<td align="center"><input type="text" class="loggedin-index thwmscp_display_index" name="i_index_logged_in[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($index_logged_in); ?>" class="thwmscp_display_index" style="width: 55px;text-align: center;"/></td>
	                        	<?php }else{ ?> 
	                        		<td width="45%" align="center"><input type="text" name="i_index[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($index) ; ?>" class="thwmscp_display_index" style="width: 55px;text-align: center;"/></td>
	                        		<td><input type="hidden" class="loggedin-index thwmscp_display_index" name="i_index_logged_in[<?php echo $i; ?>]" value="<?php echo htmlspecialchars($index_logged_in); ?>" class="thwmscp_display_index" style="width: 55px;text-align: center;"/></td>
	                        	<?php } ?>
                            <?php  } ?>

                            <td align="right" class="wmsc-switch" style="width: 130px;">
                            <?php
								if($is_removable){
									?>
                                    <input id="wmsc-step-<?php echo $step_info['order']; ?>" type="checkbox" name="i_enabled[<?php echo $i; ?>]" value="1" <?php echo $enabled_checked; ?>/>
                                    <label for="wmsc-step-<?php echo $step_info['order']; ?>"></label>
									<?php
								}else{
									?>
                                    <input type="hidden" name="i_enabled[<?php echo $i; ?>]" value="1"/>
                                    <input type="checkbox" name="static_enabled" value="1" disabled checked/>
                                    <label class="wmsc-blur" for="wmsc-step-<?php echo $step_info['order']; ?>"></label>
									<?php
								}
							?>
                            </td>
                            <td class="td_actions" align="left">
                            	<span class="step_edit_form dashicons thwmscp_edit_icon" onclick="thwmscEditStepForm(this,<?php echo $i; ?>)"></span>
                            	<?php 
	                            	$custom = $step_info['custom'] ? $step_info['custom'] : 0 ;
	                            	if(isset($custom) && ($custom == 1)){
	                            		?>
	                                    <span class="step_delete_form dashicons thwmscp-trash-icon" onclick="thwmscRemoveStep(this, '<?php echo $step_info['name']; ?>')"></span>                                   
	                            		<?php 
	                            	}else{
										//echo '&nbsp;';
	                            	}
	                            ?>
                            </td>
                        </tr>
                        <?php
						
						$i++;
					}
                    ?>
                    </tbody>
                    <tr><?php $this->render_actions_row(); ?></tr>
                </table>
                <input type="hidden" name="step_name" value="">
            </form>
    	</div>
    	<?php
		$this->output_add_step_form_pp();
		$this->output_edit_step_form_pp();
    }

    public function get_step_data_set_json($step){
		$props_set = array();

		$step_props = THWMSC_Utils::$STEP_PROPS;		 
		$step_settings = THWMSC_Utils::get_step_settings_admin();	
		$step = $step_settings[$step];

		foreach( $step_props as $pname => $property ){
			$pvalue = isset($step[$pname]) ? $step[$pname] : $property['value'];
			$pvalue = is_array($pvalue) ? implode(',', $pvalue) : $pvalue;
			// $pvalue = esc_attr($pvalue);
			
			if($property['type'] == 'checkbox'){
				$pvalue = $pvalue ? 1 : 0;
			}
			if($pname == 'index'){
				// $pvalue = htmlspecialchars_decode($pvalue);
			}
			if($pname == 'step_content'){
				// $pvalue = htmlspecialchars($pvalue);
			}
			
			$props_set[$pname] = $pvalue;
		}
						
		$props_set['img_url'] = isset($step['index_media']) ? wp_get_attachment_url($step['index_media']): '';
						
		$encoded_prop = json_encode($props_set, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

		// return json_encode($props_set);
		return $encoded_prop;
	}

    public static function is_removable_tab($key){
    	$static_tabs = array('order_review');
    	return in_array($key, $static_tabs) ? false : true;
    }
	
	private function output_add_step_form_pp(){
		?>
        <div id="thwmsc_new_step_form_pp" title="Create New Step" class="thwmsc_popup_wrapper">
          	<form method="post" id="thwmsc_new_step_form" action="">
          		<input type="hidden" name="s_action" value="new" />
                <table class="thwmscp-popup-tbl" width="100%" border="0">
                    <tr>                
                        <td colspan="4" class="err_msgs"></td>
                    </tr>
                    <tr>
                    	<!-- <td>&nbsp;</td> -->
                    	<td><?php _e('Enabled', 'woocommerce-multistep-checkout'); ?></td>
                        <td class="wmsc-switch">
                        	<input type="checkbox" id="a_fenabled" name="i_enabled" value="1" checked="checked"/>
        					<label for="a_fenabled" ><?php _e('Enabled', 'woocommerce-multistep-checkout'); ?></label>
                		</td>
                    </tr>            	
                    <tr>
                        <td width="25%"><?php _e('Name', 'woocommerce-multistep-checkout'); ?><abbr class="required" title="required">*</abbr></td>
                        <td><?php _e('Step title', 'woocommerce-multistep-checkout'); ?></td>

                    </tr>
                    <tr>    
                    	<td width="35%"><input type="text" name="i_name" class="thwmscp_text_input" style="width:250px;"/></td>
                        <input type="hidden" name="i_custom" value="1" class="thwmscp_text_input" />
                        <td><input type="text" name="i_title" class="thwmscp_text_input" style="width:250px;"/></td>
                    </tr>                                       
                    <tr class="thpladmin_checkbox">                
                        <td><?php _e('Index type', 'woocommerce-multistep-checkout'); ?></td>
                    </tr>
                    <tr class="thpladmin_checkbox">
                        <td>
                        	<label><input type="radio" name="i_indextype" value="text_index" checked="checked" style="width: 18px;height: 18px;border: 1px solid #DDDDDD; margin-right: 10px;" /><?php _e('Text index', 'woocommerce-multistep-checkout'); ?></label>
                        	<label><input type="radio" name="i_indextype" value="icon_index" style="width: 18px;height: 18px;border: 1px solid #DDDDDD; margin-right: 10px; margin-left: 4px;"/><?php _e('Icon index', 'woocommerce-multistep-checkout'); ?></label>
                        	
                        </td> 
                    </tr>                    
                    <tr class="s_index text_index">           
                        <td><?php _e('Display index', 'woocommerce-multistep-checkout'); ?></td>
                        <?php 
                    		$advanced_settings = THWMSC_Utils::get_new_advanced_settings();
                    		$login_enabled = isset($advanced_settings['enable_login_step']) && $advanced_settings['enable_login_step'] == 'yes' ? true : false ;
                    		if($login_enabled){
                    	?>
                        		<td><?php _e('Logged in Index', 'woocommerce-multistep-checkout'); ?></td>
                        	<?php } ?>
                    </tr>
                    <tr class="s_index text_index">
                    	<td><input type="text" name="i_index" class="thwmscp_text_input" style="width:250px;"/></td>
                    	<?php
                    		if($login_enabled){
                    	?>
	                    	<td><input type="text" name="i_index_logged_in" class="thwmscp_text_input" style="width:250px;"/></td>
	                    <?php }else{ ?>
	                    	<input type="hidden" name="i_index_logged_in" style="width:250px;"/>
	                   	<?php } ?>
                    </tr>                                 
                    <tr class="s_index icon_index">                
                        <td>
                        	<div class="icon_preview" style="display: none; padding: 6px 0px 6px 6px;margin: 2px 4px 13px 0px;border: 1px solid #DDDDDD;border-radius: 10px;">
                        		<!-- <img class="thwmsc-icon-index-preview" id="i_index_media_img" src="<?php // echo THWMSC_URL.'admin/assets/images/order.png'; ?>" style="max-height: 45px; width: 45px;"> -->
                        		<div class="thwmsc-icon-index-preview" id="i_index_media_img" src="<?php echo THWMSC_URL.'admin/assets/images/order.png'; ?>" style="max-height: 45px; width: 45px;"></div>
                        	</div>                        	
                        	<input type="hidden" id="i_index_media" name="i_index_media" value="">
                        	<input type="hidden" id="i_index_media_url" name="i_media_url" value="">
                        	<span class="thwmsc_remove_uploaded dashicons" onclick="thwmscRemoveImage(this)"></span>
                        	<div class="thwmscp_upld_wrapper">
                        		<div class="thwmscp_img_upld">
                        			<div class="thwmscp_icn_upld">
                        				<input class="index_icon_upload" type="button" name="i_index_icon" value="<?php _e('Upload image', 'woocommerce-multistep-checkout'); ?>"/>
                        			</div>
                        		</div>
                        	</div>
                        </td>                        
                    </tr>
                    <tr>
                    	<td><?php _e( 'Enable content settings', 'woocommerce-multistep-checkout' ); ?></td>
                        <td class="wmsc-padding wmsc-switch">
                        	<input type="checkbox" id="a_enable_step_bg" onclick="thwmscEnableBg(this)" class="enable-bg" name="i_enable_step_bg" value="1"/>
                        	<label for="a_enable_step_bg"><?php _e( 'Enable content settings', 'woocommerce-multistep-checkout' ); ?></label>
                        </td>
                    </tr>
                    <tr class="content-bg wmsc-blur" style="/* opacity: .4; */">
                    	<td><?php _e( 'Content background color', 'woocommerce-multistep-checkout' ); ?></td>
                    	<td><?php _e( 'Content font color', 'woocommerce-multistep-checkout' ); ?></td>
                    </tr>
                    <tr class="content-bg wmsc-blur" style="/*opacity: .4;*/">
                        <td>
                        	<div class="thwmscp_clrpicker_wrapper">
	                        	<span class="thwmscp_clrpicker thpladmin-colorpickpreview step_bg_preview" style=""></span>
	                        	<input type="text" name="i_step_bg" id="step_bg" value="#f2f268" style="width:250px; background: inherit;" class="thpladmin-colorpick thwmscp-colorpicker" autocomplete="off">
	                        </div>
                        </td>
                        <td>
                        	<div class="thwmscp_clrpicker_wrapper">
	                        	<span class="thwmscp_clrpicker thpladmin-colorpickpreview step_font_preview" style=""></span>
	                        	<input type="text" name="i_step_font" id="step_font" value="#000000" style="width:250px; background: inherit;" class="thpladmin-colorpick thwmscp-colorpicker" autocomplete="off">
	                        </div>
                        </td>
                    </tr>
                    <tr>
                    	<td><?php _e( 'Use only custom content for this step', 'woocommerce-multistep-checkout' ); ?></td>
                        <td class="wmsc-padding wmsc-switch">
                        	<input type="checkbox" id="a_independent" name="i_step_independent" onclick="thwmscEnableCustomContent(this)" value="1" class="enable-custom" checked="checked"/>
                        	<label for="a_independent" ><?php _e( 'Use only custom content for this step', 'woocommerce-multistep-checkout' ); ?></label>
                        </td>
                    </tr>
                    <tr class="custom-content">
                    	<td><?php _e( 'Custom content', 'woocommerce-multistep-checkout' ); ?></td>
                    </tr>
                    <tr class="custom-content">
                        <td colspan="2"><textarea class="step-textarea thwmscp_textarea" id="a_step_content" name="i_step_content"></textarea></td>
                    </tr>
                </table>
          	</form>
        </div>
        <?php
	}	
	
	private function output_edit_step_form_pp(){	
		?>
        <div id="thwmsc_edit_step_form_pp" title="Edit Step" class="thwmsc_popup_wrapper">
          	<form method="post" id="thwmsc_step_form_edit" action="">
          		<input type="hidden" name="s_action" value="edit" />
                <table class="thwmscp-popup-tbl" width="100%" border="0">
                    <tr>                
                        <td colspan="4" class="err_msgs"></td>
                    </tr>
                    <tr class="enable-row">
                    	<!-- <td width="15%">&nbsp;</td>  -->
                    	<td><?php _e('Enabled', 'woocommerce-multistep-checkout'); ?></td>
                        <td class="enable_box wmsc-switch">
                        	<input type="checkbox" id="e_fenabled" name="i_enabled" value="1" checked="checked"/>
        					<label for="e_fenabled" ><?php _e('Enabled', 'woocommerce-multistep-checkout'); ?></label>
                		</td>
                    </tr>            	
                    <tr>     
                        <td width="25%"><?php _e('Name', 'woocommerce-multistep-checkout'); ?><abbr class="required" title="required">*</abbr></td>
                        <td width="15%"><?php _e('Step title', 'woocommerce-multistep-checkout'); ?></td>
                        
                    </tr>
                    <tr>
                        <td width="35%">
                        	<input type="text" name="i_name" class="thwmscp_text_input" style="background:inherit; color: #A9A9A9;" />
                        	<input type="hidden" name="i_order" />
                        	<input type="hidden" name="i_custom" />
                        	<input type="hidden" name="i_action" />
                        	<input type="hidden" name="i_action_before" />
                        	<input type="hidden" name="i_action_after" />
                        </td>
                        <td width="35%"><input type="text" name="i_title" class="thwmscp_text_input" style="background: white;"/></td>
                    </tr> 
                    <tr>
                    	<td colspan="2"><?php _e('Action hook name : ', 'woocommerce-multistep-checkout'); ?>
                    		<b>
                    			<input type="text" id="copy_action" name="i_action" style="min-width: 64%;width: fit-content;border: none;background: inherit;box-shadow: none;color: inherit;" readonly>
                    		</b>
                    		<div class="thwmsc-copy-button-wrapper">
                    			<button type="button" class="thwmscp_copy_action thwmsc-copy-icon copied" onclick="copyToClipBoard(event)"></button>
                    		</div>
                    		<span class="thwmsc-copy-tooltip">
                    			<label style="color:#fff; font-size:12px;"><?php _e('Copy Hook Name', 'woocommerce-multistep-checkout'); ?></label>
                    		</span>
                    	</td>
                    </tr>                   
                    <tr class="thpladmin_checkbox">
                        <td width="15%"><?php _e('Index type', 'woocommerce-multistep-checkout'); ?></td>
                    </tr> 
                    <tr class="thpladmin_checkbox">
                    	<td width="35%"><label><input type="radio" name="i_indextype" value="text_index" style="width: 18px;height: 18px;border: 1px solid #DDDDDD; margin-right: 10px;"/><?php _e('Text index', 'woocommerce-multistep-checkout'); ?></label><label><input type="radio" name="i_indextype" value="icon_index" style="width: 18px;height: 18px;border: 1px solid #DDDDDD; margin-right: 10px; margin-left: 4px;"/><?php _e('Icon index', 'woocommerce-multistep-checkout'); ?></label></td> 
                    </tr>                   
                    <tr class="s_index text_index">              
                        <td width="15%"><?php _e('Display index', 'woocommerce-multistep-checkout'); ?></td>
                        <?php 
                    		$advanced_settings = THWMSC_Utils::get_new_advanced_settings();
                    		$login_enabled = isset($advanced_settings['enable_login_step']) && $advanced_settings['enable_login_step'] == 'yes' ? true : false ;

                    		if($login_enabled){ ?>
                    			<td class="logged_index" width="15%"><?php _e('Logged in Index', 'woocommerce-multistep-checkout'); ?></td>
                    		<?php } ?>
                    </tr>
                    <tr class="s_index text_index">
                    	<td width="35%"><input class="thwmscp_text_input" type="text" name="i_index" style="width:250px;"/></td>
                    	<?php 
                    		if($login_enabled){ ?>
                    			<td class="logged_index" width="35%"><input type="text" name="i_index_logged_in" value="" class="thwmscp_text_input" style="width:250px;"/></td>
                    		<?php }else{ ?>
                    			<input type="hidden" name="i_index_logged_in" value="" style="width:250px;"/>
                    		<?php } ?>
                    </tr>
                    <tr class="s_index icon_index"> 
                    	<td width="35%">
                        	<div class="icon_preview" style="display: none; padding: 6px 0px 6px 6px;margin: 4px 13px 13px 0px;border: 1px solid #DDDDDD;border-radius: 10px;">
                        		<div class="thwmsc-icon-index-preview" id="i_index_media_img" src="" style="max-height: 45px; width: 45px;"></div>
                        	</div>                        	
                        	<input type="hidden" id="i_index_media" name="i_index_media" value="">
                        	<input type="hidden" id="i_index_media_url" name="i_media_url" value="">
                        	<span class="thwmsc_remove_uploaded dashicons" onclick="thwmscRemoveImage(this)"></span>
                        	<div class="thwmscp_upld_wrapper">
                        		<div class="thwmscp_img_upld">
                        			<div class="thwmscp_icn_upld">
                        				<input class="index_icon_upload" type="button" name="i_index_icon" value="<?php _e('Upload image', 'woocommerce-multistep-checkout'); ?>"/>
                        			</div>
                        		</div>
                        	</div>
                        </td>                        
                    </tr>
                    <tr>
                    	<td><?php _e( 'Enable content settings', 'woocommerce-multistep-checkout' ); ?></td>
                        <td class="wmsc-padding wmsc-switch">
                        	<input type="checkbox" id="e_enable_step_bg" onclick="thwmscEnableBg(this)" class="enable-bg" name="i_enable_step_bg" value="1"/>
                        	<label for="e_enable_step_bg"><?php _e( 'Enable content settings', 'woocommerce-multistep-checkout' ); ?></label>
                        </td>
                    </tr>
                    <tr class="content-bg wmsc-blur" style="/*opacity: .4;*/">
                    	<td><?php _e( 'Content background color', 'woocommerce-multistep-checkout' ); ?></td>
                    	<td><?php _e( 'Content font color', 'woocommerce-multistep-checkout' ); ?></td>
                    </tr>
                    <tr class="content-bg wmsc-blur" style="/*opacity: .4;*/">
                    	<td>
                    		<div class="thwmscp_clrpicker_wrapper">
	                        	<span class="thwmscp_clrpicker thpladmin-colorpickpreview step_bg_preview" style=""></span>
	                        	<input type="text" name="i_step_bg" id="step_bg" value="#f2f268" style="width:250px; background: inherit;" class="thpladmin-colorpick thwmscp-colorpicker" autocomplete="off">
                        	</div>
                        </td>
                        <td>
                        	<div class="thwmscp_clrpicker_wrapper">
	                        	<span class="thwmscp_clrpicker thpladmin-colorpickpreview step_font_preview" style=""></span>
	                        	<input type="text" name="i_step_font" id="step_font" value="#000000" style="width:250px; background: inherit;" class="thpladmin-colorpick thwmscp-colorpicker" autocomplete="off">
	                        </div>
                        </td>
                    </tr>
                    <tr class="wmsc-independent">
                    	<td><?php _e( 'Use only custom content for this step', 'woocommerce-multistep-checkout' ); ?></td>
                        <td class="wmsc-padding wmsc-switch">
                        	<input type="checkbox" id="e_independent" name="i_step_independent" class="enable-custom" value="1" onclick="thwmscEnableCustomContent(this)" checked="checked"/>
                        	<label for="e_independent"><?php _e( 'Use only custom content for this step', 'woocommerce-multistep-checkout' ); ?></label>
                        </td>
                    </tr>
                    <tr class="custom-content">
                    	<td>
                    		<?php _e( 'Custom content', 'woocommerce-multistep-checkout' ); ?>
                    		<a href="javascript:void(0)" title="The content of this field will be displayed along with the default fields." class="thpladmin_tooltip" style="float: none;margin-left: 10px;">
                    			<img src="<?php echo THWMSC_ASSETS_URL_ADMIN; ?>images/help_tooltip.svg" title="">
                    		</a>

                    	</td>
                    </tr>
                    <tr class="custom-content">
                    	<td colspan="2"><textarea class="step-textarea thwmscp_textarea" id="e_step_content" name="i_step_content"></textarea></td>
                    </tr>
                    <tr class="custom-content custom-cnt-position">
                    	<td><?php _e( 'Display position', 'woocommerce-multistep-checkout' ); ?></td>
                    </tr>
                    <tr class="custom-content custom-cnt-position">
                        <td>
                        	<select class="custom-postion-select thwmscp_select" name="i_custom_position" id="e_custom_position">
                        		<option value="above_fields"><?php _e( 'Above the default fields', 'woocommerce-multistep-checkout' ); ?></option>
                        		<option value="below_fields"><?php _e( 'Below the default fields', 'woocommerce-multistep-checkout' ); ?></option>
                        	</select>
                        </td>
                    </tr>
                </table>
          	</form>
        </div>
        <?php
	}
	
	public function reset_to_default() {
		delete_option(THWMSC_Utils::OPTION_KEY_STEP_SETTINGS);

		$this->save_default_step_settings();

		return '<div class="updated notice notice-info thwmscp_admin_notice"><p>'. __('Step settings successfully reset', 'woocommerce-multistep-checkout') .'</p></div>';
	}

	public function save_default_step_settings(){
		$steps = THWMSC_Utils::get_complete_steps();
		$steps = $this->reorder_steps_for_display($steps);

		THWMSC_Utils::save_step_settings($steps);
	}

	private function reorder_steps_for_display($steps){
		$steps = THWMSC_Utils::reset_step_display_order($steps);
		$steps = THWMSC_Utils::sort_steps($steps);
		$steps = THWMSC_Utils::prepare_step_display_props($steps);

		return $steps;
	}
	
	private function prepare_step_settings($step_settings, $new_step){		
		$last_step = end($step_settings);
		$second_last_step = prev($step_settings);		
		$second_last_order = $second_last_step['order'];
		
		$new_step['order'] = $second_last_order+1;
		$last_step['order'] = $new_step['order']+1;
		
		if($new_step['indextype'] == 'text_index'){ 
			$new_step['index_media'] = $new_step['index_media'] ? $new_step['index_media'] : '';
		}
		
		$new_step_name = $new_step['name'];
		$last_step_name = $last_step['name'];
		
		$step_settings[$new_step_name] = $new_step;
		$step_settings[$last_step_name] = $last_step;

		$step_settings = THWMSC_Utils::sort_steps($step_settings);				
		return $step_settings;
	}
	
	public function prepare_step_from_posted_data($posted){
		$name = isset($_POST['i_name']) ? wc_clean(wp_unslash($_POST['i_name'])) : '';
		$title = isset($_POST['i_title']) ? wc_clean(wp_unslash($_POST['i_title'])) : '';
		$index = isset($_POST['i_index']) ? wc_clean(wp_unslash($_POST['i_index'])) : '';
		$index_type = isset($_POST['i_indextype']) ? wc_clean(wp_unslash($_POST['i_indextype'])) : 'text_index';
		$index_media = isset($_POST['i_index_media']) ? wc_clean(wp_unslash($_POST['i_index_media'])) : '';
		$order = isset($_POST['i_order']) ? wc_clean(wp_unslash($_POST['i_order'])) : '';	
		$enabled = isset($_POST['i_enabled']) ? wc_clean(wp_unslash($_POST['i_enabled'])) : 0;
		$custom = isset($_POST['i_custom']) ? wc_clean(wp_unslash($_POST['i_custom'])) : 0;

		//New step settings
		$step_bg_enable = isset($_POST['i_enable_step_bg']) ? wc_clean(wp_unslash($_POST['i_enable_step_bg'])) : '';
		$step_bg = isset($_POST['i_step_bg']) ? wc_clean(wp_unslash($_POST['i_step_bg'])) : '';
		$step_font = isset($_POST['i_step_font']) ? wc_clean(wp_unslash($_POST['i_step_font'])) : '';
		$step_independent = isset($_POST['i_step_independent']) ? wc_clean(wp_unslash($_POST['i_step_independent'])) : '';

		// $step_content = isset($_POST['i_step_content']) ? wc_sanitize_textarea(wp_unslash($_POST['i_step_content'])) : '';
		$step_content = isset($_POST['i_step_content']) ? wp_unslash($_POST['i_step_content']) : '';

		$index_logged_in = isset($_POST['i_index_logged_in']) ? wc_clean(wp_unslash($_POST['i_index_logged_in'])) : '';

		$custom_position = isset($_POST['i_custom_position']) ? wc_clean(wp_unslash($_POST['i_custom_position'])) : '';

		if($name == 'order_review'){
			$enabled = 1;
		}

		$class = '';
		$sections = '';
		if($custom){
			$action = 'woocommerce_checkout_'.$name;
			$action_before = 'woocommerce_checkout_before_'.$name;
			$action_after = 'woocommerce_checkout_after_'.$name;
		}else{
			$action = isset($_POST['i_action']) ? wc_clean(wp_unslash($_POST['i_action'])) : '';
			$action_before = isset($_POST['i_action_before']) ? wc_clean(wp_unslash($_POST['i_action_before'])) : '';
			$action_after = isset($_POST['i_action_after']) ? wc_clean(wp_unslash($_POST['i_action_after'])) : '';
		}		
		
		$step_props = array();		
		$step_props['name'] = $name;
		$step_props['title'] = $title;
		$step_props['index'] = $index;
		$step_props['indextype'] = $index_type;
		$step_props['index_media'] = $index_media;
		$step_props['order'] = $order;
		$step_props['enabled'] = $enabled;
		$step_props['class'] = $class;
		$step_props['action'] = $action;
		$step_props['action_before'] = $action_before;
		$step_props['action_after'] = $action_after;
		$step_props['sections'] = $sections;
		$step_props['custom'] = $custom;

		$step_props['enable_step_bg'] = $step_bg_enable;
		$step_props['step_bg'] = $step_bg;
		$step_props['step_font'] = $step_font;
		$step_props['step_independent'] = $step_independent;
		$step_props['step_content'] = $step_content;
		$step_props['index_logged_in'] = $index_logged_in;

		$step_props['custom_position'] = $custom_position;

		self::add_wpml_support($step_props);

		return $step_props;
	}

	public static function add_wpml_support($field){
		THWMSC_Utils::wmsc_wpml_register_string('Field Title - '.$field['name'], $field['title']);
	}
	
	public function create_step(){
		$step = $this->prepare_step_from_posted_data($_POST);
		
		$settings = THWMSC_Utils::get_step_settings();
		$settings = $this->prepare_step_settings($settings, $step);
		$result = THWMSC_Utils::save_step_settings($settings);

		if($result == true) {
			echo '<div class="updated notice notice-info thwmscp_admin_notice"><p>'. __('New step added successfully.', 'woocommerce-multistep-checkout') .'</p></div>';
		} else {
			echo '<div class="error notice thwmscp_admin_notice"><p>'. __('New step not added due to an error.', 'woocommerce-multistep-checkout') .'</p></div>';
		}		
	}

	public function update_step(){
		$step = $this->prepare_step_from_posted_data($_POST);
		
		$settings = THWMSC_Utils::get_step_settings();
		$is_exist = THWMSC_Utils::check_step_is_already_exist($settings, $step['name']);

		if($is_exist){
			$settings[$step['name']] = $step;
			$result = THWMSC_Utils::save_step_settings($settings);

			if($result == true) {
				echo '<div class="updated notice notice-info thwmscp_admin_notice"><p>'. __('Step Updated successfully.', 'woocommerce-multistep-checkout') .'</p></div>';
			} else {
				echo '<div class="error notice thwmscp_admin_notice"><p>'. __('Your changes were not saved due to an error (or you made none!)', 'woocommerce-multistep-checkout') .'</p></div>';
			}

		}else{
			echo '<div class="error notice thwmscp_admin_notice"><p>'. __('This step does not exist.', 'woocommerce-multistep-checkout') .'</p></div>';
		}			
	}

	public function delete_step(){	
		$step_name = !empty($_POST['step_name']) ? $_POST['step_name'] :'';

		$settings = THWMSC_Utils::get_step_settings();
		foreach ($settings  as $step_key => $step_data) {
			unset($settings[$step_name]);
		}		

		$settings = THWMSC_Utils::sort_steps($settings);			
		$result = THWMSC_Utils::save_step_settings($settings);
		
		if($result == true) {
			echo '<div class="updated notice notice-info thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were saved.', 'woocommerce-multistep-checkout') .'</p></div>';
		} else {
			echo '<div class="error notice thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multistep-checkout') .'</p></div>';
		}		 
		
	}

	public function save_settings(){
		$step_settings = THWMSC_Utils::get_step_settings_admin();

		$s_names    	= !empty( $_POST['i_'.'name'] ) ? $_POST['i_'.'name'] : array(); 
		$s_titles   	= !empty( $_POST['i_'.'title'] ) ? $_POST['i_'.'title'] : array();
		$s_order    	= !empty( $_POST['i_'.'order'] ) ? $_POST['i_'.'order'] : array();
		$s_index   	 	= !empty( $_POST['i_'.'index'] ) ? $_POST['i_'.'index'] : array();
		$s_enabled  	= !empty( $_POST['i_'.'enabled'] ) ? $_POST['i_'.'enabled'] : array();
		$s_index_logged_in = !empty( $_POST['i_'.'index_logged_in'] ) ? $_POST['i_'.'index_logged_in'] : array();

		$max = max( array_map( 'absint', array_keys( $s_names ) ) );

		for($i = 0; $i <= $max; $i++) {
			$name  	  = isset($s_names[$i]) ? wc_clean(wp_unslash($s_names[$i])) : '';	
			$title 	  = isset($s_titles[$i]) ? wc_clean(wp_unslash($s_titles[$i])) : '';
			$index 	  = isset($s_index[$i]) ? wc_clean(wp_unslash($s_index[$i])) : '';

			$order 	  = isset($s_order[$i]) ? $s_order[$i] : 0;
			$enabled  = isset($s_enabled[$i]) ? wc_clean(wp_unslash($s_enabled[$i])) : 0;
			$index_logged_in = isset($s_index_logged_in[$i]) ? wc_clean(wp_unslash($s_index_logged_in[$i])) : '';

			if(array_key_exists($name, $step_settings)){
				$step_settings[$name]['title'] = $title;
				$step_settings[$name]['order'] = $order;
				$step_settings[$name]['index'] = $index;
				$step_settings[$name]['index_logged_in'] = $index_logged_in;
				$step_settings[$name]['enabled'] = $enabled;
			}			
		}
		
		$step_settings = THWMSC_Utils::sort_steps($step_settings);
		$result = THWMSC_Utils::save_step_settings($step_settings);


		if($result == true) {
			echo '<div class="updated notice notice-info thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were saved.', 'woocommerce-multistep-checkout') .'</p></div>';
		} else {
			echo '<div class="error notice thwmscp_admin_notice" style="background: #FFFFFF;border-radius: 5px;"><p>'. __('Your changes were not saved due to an error (or you made none!).', 'woocommerce-multistep-checkout') .'</p></div>';
		}	
	}
		
}

endif;