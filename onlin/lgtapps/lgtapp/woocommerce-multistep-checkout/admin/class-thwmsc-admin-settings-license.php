<?php
/**
 * The admin license settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/admin 
 */
if(!defined('WPINC')){	die; }

if(!class_exists('THWMSC_Admin_Settings_License')):

class THWMSC_Admin_Settings_License extends THWMSC_Admin_Settings{
	protected static $_instance = null;
	
	public $ame_data_key;
	public $ame_deactivate_checkbox;
	public $ame_activation_tab_key;
	public $ame_deactivation_tab_key;
	
	public function __construct() {
		parent::__construct('license_settings');
		
		$this->data_prefix = str_ireplace( array( ' ', '_', '&', '?' ), '_', strtolower(THWMSC_SOFTWARE_TITLE) );
		$this->data_prefix = str_ireplace( 'woocommerce', 'th', $this->data_prefix );
		$this->ame_data_key             = $this->data_prefix . '_data';
		$this->ame_deactivate_checkbox  = $this->data_prefix . '_deactivate_checkbox';
		$this->ame_activation_tab_key   = $this->data_prefix . '_license_activate';
		$this->ame_deactivation_tab_key = $this->data_prefix . '_license_deactivate';		
	}
	
	public static function instance() {
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	} 
	
	public function render_page(){
		settings_errors();
		$this->render_tabs();
		$this->render_content();
	}

	private function render_content(){
		echo do_shortcode('[th_multi_step_checkout_for_woocommerce_license_form]');
	}
	
	private function render_content_old(){
		?>            
        <div class="thpladmin-license-settings" style="padding-left: 30px;"> 
			<p class="info-text">
				<?php __('You need to have a valid License in order to get upgrades or support for this plugin', 'woocommerce-multistep-checkout'); ?>
			</p>       
			       
		    <form action="options.php" method='post'>
				<div class="form-table thpladmin-form-table thpladmin-license-settings-grid">
					<?php
					settings_fields( $this->ame_data_key );
					do_settings_sections( $this->ame_activation_tab_key );
					submit_button(__('Save Changes', 'woocommerce-multistep-checkout'));
					?>
				</div>
            </form>
			
			<form action="options.php" method='post'>
				<div class="form-table thpladmin-form-table thpladmin-license-settings-grid">
					<?php
					settings_fields( $this->ame_deactivate_checkbox );
					do_settings_sections( $this->ame_deactivation_tab_key );
					submit_button(__('Save Changes', 'woocommerce-multistep-checkout'));
					?>
				</div>
			</form>
    	</div>       
    	<?php
	}
}

endif;