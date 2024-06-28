<?php
/**
 * The template related functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/public 
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC_Template_Functions')):
 
class THWMSC_Template_Functions {
	//private $plugin_name;

	public function __construct() {
		add_action('thwmsc_multi_step_tabs', array($this, 'render_multi_step_tabs'));
		add_action('thwmsc_multi_step_before_tab_panels', array($this, 'render_multi_step_before_tab_panels'));
		add_action('thwmsc_multi_step_after_tab_panels', array($this, 'render_multi_step_after_tab_panels'));
		add_action('thwmsc_multi_step_tab_panels', array($this, 'render_multi_step_tab_panels'));
	}

	public function render_multi_step_tabs($checkout){
		$steps = THWMSC_Utils::populate_step_settings();
		$tab_align = '';
		
		?>
		<div id="thwmsc_wrapper" class="thwmsc-wrapper"> 
			<ul id="thwmsc-tabs" class="thwmsc-tabs <?php echo $tab_align; ?>">
			<?php
			foreach($steps as $step){
				render_tab($step);
			}
			?>
			</ul>
		<?php
	}
	
	public function render_multi_step_before_tab_panels($checkout){
		?>
			<div id="thwmsc-tab-panels" class="thwmsc-tab-panels"> 
		<?php
	}
	
	public function render_multi_step_after_tab_panels($checkout){
		?>
			</div>
			<div class="thwmsc-buttons">
				<input type="button" id="action-prev" class="button-prev" value="<?php _e( 'Previous', 'woocommerce-multistep-checkout' ); ?>">
				<input type="button" id="action-next" class="button-next" value="<?php _e( 'Next', 'woocommerce-multistep-checkout' ); ?>">
			</div>
		</div> 
		<?php
	}
	
	public function render_multi_step_tab_panels($checkout){
		$steps = THWMSC_Utils::populate_step_settings();
		foreach($steps as $step){
			$this->render_tab_content($step);
		}
	}
	
	private function render_tab($step){
		$index = $step['index'];
		$title = $step['title'];
		$class = $step['class'];
		
		?>
		<li class="thwmsc-tab">
			<a href="javascript:void(0)" id="step-<?php echo $index; ?>" data-step="<?php echo $index; ?>" class="<?php echo $class; ?>"><?php echo $title; ?></a>
		</li> 
		<?php	
	}
	
	private function render_tab_content($step){
		$action = $step['action'];
		$index  = $step['index'];
		?>
		<div class="thwmsc-tab-panel" id="thwmsc-tab-panel-<?php echo $index; ?>">
			<?php do_action( $action ); ?>
		</div>
		<?php
	}
	
	/*private function get_steps(){
		array{
			'billing' => {
					'index' => 0,
					'title' => 'Billing',
					'class' => 'first active',
					'action' => 'woocommerce_checkout_billing',	
				},
			'shipping' => {
					'index' => 1,
					'title' => 'Shipping',
					'class' => '',
					'action' => 'woocommerce_checkout_shipping',
				},
			'shipping' => {
					'index' => 2,
					'title' => 'Step 3',
					'class' => '',
					'action' => 'woocommerce_checkout_order_review',
				},
			'shipping' => {
					'index' => 3,
					'title' => 'Step 4',
					'class' => 'last',
					'action' => 'woocommerce_checkout_order_review',
				}
		}
	}*/
	
}

endif;