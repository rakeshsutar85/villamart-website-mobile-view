<?php
/**
 * The file that defines the core plugin class.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/includes
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC')):

class THWMSC {
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @access   protected
	 * @var      $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		if ( defined( 'THWMSC_VERSION' ) ) {
			$this->version = THWMSC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woocommerce-multistep-checkout';
		
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		
		$this->loader->add_action( 'init', $this, 'init' );
		$this->loader->add_filter( 'thwcfe_custom_section_positions', 'THWMSC_Utils', 'custom_step_hooks');
	}
	
	public function init(){
		$this->define_constants();
		//$this->init_auto_updater();
	}
	
	private function define_constants(){
		!defined('THWMSC_ASSETS_URL_ADMIN') && define('THWMSC_ASSETS_URL_ADMIN', THWMSC_URL . 'admin/assets/');
		!defined('THWMSC_ASSETS_URL_PUBLIC') && define('THWMSC_ASSETS_URL_PUBLIC', THWMSC_URL . 'public/assets/');
		!defined('THWMSC_WOO_ASSETS_URL') && define('THWMSC_WOO_ASSETS_URL', WC()->plugin_url() . '/assets/');
		!defined('THWMSC_TEMPLATE_PATH') && define('THWMSC_TEMPLATE_URL', THWMSC_PATH . 'public/templates/');
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - THWMSC_Loader. Orchestrates the hooks of the plugin.
	 * - THWMSC_i18n. Defines internationalization functionality.
	 * - THWMSC_Admin. Defines all hooks for the admin area.
	 * - THWMSC_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @access   private
	 */
	private function load_dependencies() {
		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwmsc-autoloader.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwmsc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-thwmsc-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-thwmsc-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-thwmsc-public.php';

		$this->loader = new THWMSC_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the THWMSC_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new THWMSC_i18n($this->get_plugin_name());
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}
	
	private function init_auto_updater(){
		if(!class_exists('THWMSC_Auto_Update_License') ) {
			$api_url = 'https://themehigh.com/';
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'class-thwmsc-auto-update-license.php';
			THWMSC_Auto_Update_License::instance(__FILE__, THWMSC_SOFTWARE_TITLE, THWMSC_VERSION, 'plugin', $api_url, THWMSC_i18n::TEXT_DOMAIN);
		}
	}
	
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new THWMSC_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action('plugins_loaded', $this, 'thwmsc_misc_actions');
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles_and_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'thwmsc_display_settings_actions' );
		$this->loader->add_action('admin_notices',$this, 'thwmsc_new_ui_notice');
		$this->loader->add_filter( 'woocommerce_screen_ids', $plugin_admin, 'add_screen_id' );
		$this->loader->add_filter( 'plugin_action_links_'.THWMSC_BASE_NAME, $plugin_admin, 'plugin_action_links' );
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_row_meta', 10, 2 );
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'thwmsc_display_feature_announcement' );

		// $general_settings = new THWMSC_Admin_Settings_General();
		// $this->loader->add_action( 'after_setup_theme', $general_settings, 'define_general_admin_hooks' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @access   private
	 */
	private function define_public_hooks() {
		if(!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX)){
			$enable = THWMSC_Utils::is_wmsc_enabled();
        	$enable = apply_filters('thwmsc_msc_enable', $enable);  

        	if($enable) {
        		$plugin_public = new THWMSC_Public( $this->get_plugin_name(), $this->get_version() );
				$hook_priority = apply_filters('thwmsc_public_hook_priority', 20 );
				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles_and_scripts' );
				$this->loader->add_filter( 'woocommerce_locate_template', $plugin_public, 'thwmsc_multistep_template', $hook_priority, 3 ); 
				//$this->loader->add_action( 'thwmsc_checkout_payment_method', $plugin_public, 'thwmsc_checkout_payment_method', 10 );
        	}
		}
	}

	/**
	 * Run to update database when version update occurs.
	 */
	public function thwmsc_misc_actions() {
		if(apply_filters('thwmsc_manage_db_update_notice', THWMSC_Utils::is_user_capable())) {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}
	}
	/**
	 *  Notice to display new UI changes
	*/
	public function thwmsc_new_ui_notice() {
		$current_screen = get_current_screen();

		if($current_screen->id !== 'woocommerce_page_th_multi_step_checkout'){
			return;
		}
		?>
		<div class="notice notice-info thwmsc-new-ui-help-notice">
			<style>
				.thwmsc-new-ui-help-notice a{color: #1f9bff}
				.thwmsc-new-ui-help-notice a:hover{color: #2d81c5}
			</style>

			<div style="display: grid;width: 100%;grid-template-columns: 64px 1fr;">
				<div style="padding: 4px 0; position: relative; ">
					<img style="position:absolute; left: 10px; top: calc(50% - 10px);" src="<?php echo THWMSC_URL ?>admin/assets/images/themehigh_logo.svg" />
				</div>
				<div style="width:100%; float:left; padding: 10px 0; position: relative;">
					<div style="width:95%; float:left;">
						<p>You are currently using the latest version of <strong style="font-size:15px;">Multi-step Checkout for WooCommerce</strong> Please note that it has gone through an overall UI change. If you find any issues after updating, refer to the <a style="text-decoration: none;" href="https://www.themehigh.com/docs/having-trouble-with-the-latest-version-2-1-0/" target="_blank">documentation.</a></p>
					</div>
				</div>
			</div>
		</div>
		<?php

	}

	/**
	 * Function to check if db update required and then update.
	 */
	public function add_notices() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$screens = array('woocommerce_page_th_multi_step_checkout');
		if( !in_array( $screen_id,  $screens ) ){
			return;
		}
		$advanced_settings = THWMSC_Utils::get_new_advanced_settings();
		$display_settings = THWMSC_Utils::get_advanced_settings();
		if(isset($advanced_settings['enable_completed_tab_bg']) && $advanced_settings['enable_completed_tab_bg'] == 'yes') {
			$settings = isset($display_settings) ? $display_settings : array();
			$settings['enable_completed_tab_bg'] = $advanced_settings['enable_completed_tab_bg'];
			$settings['completed_tab_bg_color'] = isset($advanced_settings['completed_tab_bg_color']) ? $advanced_settings['completed_tab_bg_color'] : '';
			$settings['completed_tab_text_color'] = isset($advanced_settings['completed_tab_text_color']) ? $advanced_settings['completed_tab_text_color'] : '';
			$this->save_advanced_settings($settings);

			$settings_advanced_new = get_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS);
			unset($settings_advanced_new['enable_completed_tab_bg']);
			unset($settings_advanced_new['completed_tab_bg_color']);
			unset($settings_advanced_new['completed_tab_text_color']);
			$this->save_new_advanced_settings($settings_advanced_new);
		}
	}

	public function save_advanced_settings($settings) {
		$result = update_option(THWMSC_Utils::OPTION_KEY_ADVANCED_SETTINGS, $settings);
		return $result;
	}

	public function save_new_advanced_settings($settings){
		$result = update_option(THWMSC_Utils::OPTION_KEY_NEW_SETTINGS, $settings);
		return $result;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Loader Object    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

endif;