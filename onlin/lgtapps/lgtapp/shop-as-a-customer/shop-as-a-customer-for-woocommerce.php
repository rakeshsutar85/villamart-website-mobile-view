<?php
/**
 * Plugin Name: FME Addons: Shop as a Customer for WooCommerce
 * Author: fmeaddons
 * Version: 1.1.8
 * Developed By: fmeaddons Team
 * Description:Switch to any customer profile from administration panel and use WooCommerce store as customer, Create orders  	and Edit user profile.
 * License:    GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 4.4
 * Plugin URI: 		  https://www.fmeaddons.com/
*  Author URI: 		  https://www.fmeaddons.com/
 * Text Domain: shop-as-a-customer-for-woocommerce
 * Domain Path: /languages
 * Tested up to: 6.1.1
 * WC requires at least: 3.0
 * WC tested up to: 7.4.1
 * Woo: 5467980:a75e8762bdf7a49e92759802ab34efb9
 */

error_reporting(0);
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 * if wooCommerce is not active FME Addons: Shop as a Customer for WooCommerce module will not work.
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function my_admin_notice() {

		// Deactivate the plugin
		deactivate_plugins(__FILE__);
		$error_message = __('This plugin requires <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be installed and active!', 'woocommerce');
		echo esc_attr( $error_message );
		die();
	}
	add_action( 'admin_notices', 'my_admin_notice' );
}


class MainFmeaddonsClass {

	public function __construct() {
		$aa=$this->aaaaaaaaaaaa_fmesac();
		if ('yes' == $aa) {
			include 'payment.php';
		}


		define('INDEXCOUNTER', 1);
		

		add_action('init', array($this, 'fmeaddons_script'));
		add_action('admin_footer', array($this, 'fmeaddons_script'));
		add_action('wp_ajax_ajaxxx', array( $this, 'fmeaddons_switchtocustomer'));
		
		add_action('wp_ajax_asguest', array( $this, 'fmeaddons_switchtoguest'));
		add_action('wp_ajax_nopriv_asguest', array( $this, 'fmeaddons_switchtoguest'));
		
		add_action('wp_head', array($this, 'fmeaddons_switchback_tab'));
		add_action('wp_ajax_ajax', array( $this, 'fmeaddons_switchback_to_admin'));
		add_action('wp_ajax_nopriv_ajax', array( $this, 'fmeaddons_switchback_to_admin'));
		
		add_action('wp_ajax_vieworder', array( $this, 'fmeaddons_view_order'));
		add_action('wp_ajax_editprofile', array( $this, 'fmeaddons_edit_profile'));
		
		add_action( 'woocommerce_thankyou', array($this, 'fmeaddons_custom_content_thankyou'), 10, 1 );
		
		add_action('init', array($this, 'fmeaddons_start_session'));        
		add_action('admin_footer', array($this, 'fmeaddons_modal'));
		add_action('admin_bar_menu', array( $this, 'fmeaddons_custom_toolbar_link'), 999);
		add_action('wp_logout', array( $this, 'fmeaddons_end_session'));
		add_action('woocommerce_settings_tabs_array', array($this, 'fmeaddons_menu_pages'), 50);
		add_action( 'woocommerce_settings_shop_as_a_customer_for_woocommerce', array($this, 'fmeaddons_customerlogs') );
		add_action('wp_ajax_my_action' , array($this,'data_fetch'));
		
		add_action('wp_ajax_nextdatafind' , array($this,'nextdatafind'));
		add_action('wp_ajax_saveallroles' , array($this,'fme_saveallroles'));
		add_action('wp_ajax_getcustomersfordatatables' , array($this,'getcustomersfordatatables'));

		add_action('wp_ajax_getcustomerslogs' , array($this,'getcustomerslogs'));
		
		add_action( 'wp_loaded', array($this,'fme_load_textdomain' ));
		add_filter( 'load_textdomain_mofile', array($this,'my_plugin_load_my_own_textdomain'), 10, 2 );

		add_action( 'requests-curl.before_request', array($this,'fma_curl_before_request'), 9999 );
	}
	public function fma_curl_before_request( $curlhandle) {
		session_write_close();
	}

	public function fme_load_textdomain() {
		load_plugin_textdomain( 'shop-as-a-customer-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	public function my_plugin_load_my_own_textdomain( $mofile, $domain ) {

		if ( 'shop-as-a-customer-for-woocommerce' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
			$locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
			$mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
		}
		return $mofile;
	}

	public function aaaaaaaaaaaa_fmesac() {

		if (!session_id()) {
			session_start();
		}

		if (isset($_SESSION['admin']) && 'adminisloggedin' == $_SESSION['admin'] ) {
			session_write_close();
			return 'yes';
		}
		session_write_close();
		return 'no';
		
	}
	public function fme_saveallroles() {
		check_ajax_referer('ajax_nonce', 'security');
		if (isset($_REQUEST['allroless']) || isset($_REQUEST['defselectedp']) || isset($_REQUEST['fme_tabselect']) ) {
			$defselectedp=sanitize_text_field($_REQUEST['defselectedp']);
			$fme_tabselect = sanitize_text_field($_REQUEST['fme_tabselect']);
		}
		

		if (isset($_REQUEST['']) || isset($_REQUEST['defselectedp']) ) {
			$defselectedp=sanitize_text_field($_REQUEST['defselectedp']);
			$allrolessaved= map_deep( wp_unslash( $_REQUEST['allroless']), 'sanitize_text_field' );
		}

		//////////////////////////////////////////updated/////////////////////////
		
		$allrolessaved= map_deep( wp_unslash( $_REQUEST['allroless']), 'sanitize_text_field' );
		if (empty($allrolessaved)) {
			update_option('fme_allrolessaved', '');
		} else {
			update_option('fme_allrolessaved', $allrolessaved);
		}

		//////////////////////////////////////////updated/////////////////////////

		// update_option('fme_allrolessaved', $allrolessaved);

		update_option('fme_defselectedp', $defselectedp);
		update_option('fme_tabselect', $fme_tabselect);

		wp_die();
	}

	public function getcustomersfordatatables() {

		global $wpdb;
		$search_value='';
		if (isset($_REQUEST['search']['value'])) {
			$search_value=sanitize_text_field($_REQUEST['search']['value']);
		}
		/*      $search_query='';
			  if($search_value != ''){
			$search_query = " and (u.display_name like '%".$search_value."%' or 
				 u.ID like '%".$search_value."%' or 
				 u.user_nicename like'%".$search_value."%' ) ";
		 }
		$search_query='';
		*/
		$currIndStart=0;
		if (isset($_REQUEST['start'])) {
			$currIndStart = sanitize_text_field( $_REQUEST['start'] );
		}
		$return_json = array();
		// $blogusers = $wpdb->get_results($wpdb->prepare('SELECT u.ID, u.user_email, u.display_name FROM  ' . $wpdb->prefix . 'users u, ' . $wpdb->prefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $wpdb->prefix . 'capabilities"'. $search_query . '  AND m.meta_value NOT LIKE %s LIMIT '. $currIndStart . ', 10', '%administrator%'));

		$blogusers = get_users( array( 'fields' => array( 'display_name','user_email','ID' ),'role__not_in' => array( 'administrator' ),'offset' =>$currIndStart,'number' =>10, 'orderby' => 'ID', 'order'=>'asc','search'         => '*' . esc_attr( $search_value ) . '*',
			'search_columns' => array(
		'display_name',
		'user_nicename',
		'user_email',
		'ID'
		)
		 ) );
		 
		foreach ($blogusers as $key => $value) {


			$action = '<center>';
			$action.='<button  class="button frompage switchbtn" style="margin: 1%;" value="' . ( $value->ID ) . '">' . __('Switch', 'shop-as-a-customer-for-woocommerce') . '</button>
					<button  class="button frompage vieworder" value="' . ( $value->ID ) . '" style="margin: 1%;">' . __('View Orders', 'shop-as-a-customer-for-woocommerce') . '</button>
						<button  class="button frompage editprofile" value="' . ( $value->ID ) . '" style="margin: 1%;">' . __('Edit Profile', 'shop-as-a-customer-for-woocommerce') . '</button>
								</center>';
			$row = array(

			'ID' => '<center>' . $value->ID . '</center>',
			'Name' =>'<center>' . $value->display_name . '</center>',
			'Email' =>'<center>' . $value->user_email . '</center>',
			'Action' =>$action

			);
			$return_json[] = $row;


		}

		echo json_encode(array('data' => $return_json));
			wp_die();

	}


	public function getcustomerslogs() {

		$start=0;
		if (isset($_REQUEST['start'])) {
			$start = sanitize_text_field($_REQUEST['start']);
		}
		$currIndStart = $start;
		$search_value='';
		if (isset($_REQUEST['search']['value'])) {
			$search_value=sanitize_text_field($_REQUEST['search']['value']);
		}
		$array_for_log = get_option('all_logs');


		if ( '' == $array_for_log ) {
			
			$counnnt=0;
		} else {
			$counnnt=count($array_for_log);

		}
		$return_json = array();
		$counter=0;
			$counnnt=$counnnt-( $currIndStart );
		for ($i=$counnnt-1; $i >= 0 ; $i--) { 
						$idd = $array_for_log[$i]['id'];
						$time = $array_for_log[$i]['time'];
						$customer = $array_for_log[$i]['customer'];
						$customer = explode(' ', $customer);
						$order_billing_phone = $array_for_log[$i]['phone'];
			if (isset($array_for_log[$i]['products'])) {
				$products=$array_for_log[$i]['products'];
			} else {
				$products='';
			}
						$disabled = '';
			if ( '' == $order_billing_phone || 'N/A' == $order_billing_phone) {
				$disabled = 'disabled';
			}

							$whatsapp='<center>
									<a class="' . ( $disabled ) . '" target="_blank" href="https://web.whatsapp.com/send?phone=' . ( $order_billing_phone ) . '&text=" >
										<img style="width: 18%;" src="' . plugin_dir_url( __FILE__ ) . 'whatsapp.png">
									</a>
								</center>';
								$row=array();
			if ('' == $search_value) {
				$row = array(

				'ID' => '<center>' . $idd . '</center>',
				'Logged_in_At' =>'<center>' . $time . '</center>',
				'Customer' =>'<center>' . $customer[0] . '</center>',
				'Products' =>$products,
				'Message_On_Whatsapp' => $whatsapp

								);
			} else if ($search_value == $idd || ( str_contains($customer[0], $search_value) )) {
				$row = array(

				'ID' => '<center>' . $idd . '</center>',
				'Logged_in_At' =>'<center>' . $time . '</center>',
				'Customer' =>'<center>' . $customer[0] . '</center>',
				'Products' =>$products,
				'Message_On_Whatsapp' => $whatsapp

				);
			}
			if (!empty($row)) {
				$return_json[] = $row;
			}
						$counter++;
			if (10 == $counter) {
				break;
			}
		}


			echo json_encode(array('data' => $return_json));
			wp_die();
	}



	
	public function nextdatafind() {
		check_ajax_referer('ajax_nonce', 'security');
		if (isset($_REQUEST['nextvalue'])) {
			$find=sanitize_text_field($_REQUEST['nextvalue']);

		}
		if (isset($_REQUEST['globnextcount'])) {
			$globnextcount=sanitize_text_field($_REQUEST['globnextcount']);

		}
	
		global $wpdb;
		$results = $wpdb->get_results($wpdb->prepare('SELECT u.ID, u.user_email, u.display_name FROM  ' . $wpdb->prefix . 'users u, ' . $wpdb->prefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $wpdb->prefix . 'capabilities" AND m.meta_value NOT LIKE %s AND u.user_email LIKE %s LIMIT %d, 10', '%administrator%', '%' . $find . '%', $globnextcount));
	
		echo json_encode( $results);
		exit();
	}
	public function data_fetch() {
		check_ajax_referer('ajax_nonce', 'security');
		
		if (isset($_REQUEST['value'])) {
			$find=sanitize_text_field($_REQUEST['value']);

		}
		global $wpdb;


		$results = $wpdb->get_results($wpdb->prepare('SELECT u.ID, u.user_email, u.display_name FROM  ' . $wpdb->prefix . 'users u, ' . $wpdb->prefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $wpdb->prefix . 'capabilities" AND m.meta_value NOT LIKE %s AND u.user_email LIKE %s LIMIT 10', '%administrator%', '%' . $find . '%'));
		echo json_encode( $results);

		exit();
	}


	public function fmeaddons_menu_pages( $tabs ) {
		$tabs['shop_as_a_customer_for_woocommerce'] = __('Shop As a Customer', ' shop-as-a-customer-for-woocommerce');
		return $tabs;
	}
	public function fmeaddons_customerlogs() {

		if (is_admin()  && isset($_GET['tab']) && 'shop_as_a_customer_for_woocommerce' == $_GET['tab']) {
			?>
			<style type="text/css">
				.woocommerce-save-button {
				  display: none !important;
				}
				.subsubsub {
					margin-top: -42px !important;
				}
			</style>
			<?php
		}
		$array_for_log = get_option('all_logs');


		if ( '' == $array_for_log ) {
			
			$counnnt=0;
		
		
		} else {
			$counnnt=count($array_for_log);
		}

		global $wpdb;

		$results = $wpdb->get_results($wpdb->prepare('SELECT u.ID, u.user_email, u.display_name FROM  ' . $wpdb->prefix . 'users u, ' . $wpdb->prefix . 'usermeta m WHERE u.ID = m.user_id AND m.meta_key LIKE "' . $wpdb->prefix . 'capabilities" AND m.meta_value NOT LIKE %s ', '%administrator%'));
		

		?>
	
		<div id="savediv"style="display: none;font-weight:bold; color:green;font-size:15px "><br><?php echo esc_html_e('Your settings has been saved!', 'shop-as-a-customer-for-woocommerce'); ?><br><br></div><br>
		<br>		
		<ul class="subsubsub">

			<li>
				<a href="#" class="fme_tabsss current" id="fme_customerlogsbtn">
					<?php echo esc_html_e('Switch To Customers', 'shop-as-a-customer-for-woocommerce'); ?>
				</a>|
			</li>

			<li>
				<a href="#" class="fme_tabsss" id="fme_switchcustomerbtn">
					<?php echo esc_html_e('Customer Logs', 'shop-as-a-customer-for-woocommerce'); ?>
				</a>|
			</li>

			<li>
				<a href="#" class="fme_tabsss" id="fme_settingsbtn">
					<?php echo esc_html_e('Settings', 'shop-as-a-customer-for-woocommerce'); ?>
				</a>
			</li>
		</ul>
		<br>
		<input type="hidden" id="pageefound" value="found">


		<table class="form-table" id="fme_tabofsettings" style="display:none;">
			<?php
			$fme_tabselect=get_option('fme_tabselect');
			?>
			<tbody>
				<tr>
					<th>
						<label ><?php echo esc_html_e('Select Tab to Switch as Customer/Guest', 'shop-as-a-customer-for-woocommerce'); ?></label>
						<span class="woocommerce-help-tip" data-tip="Select The Tab in Which Admin Will be Switch as a Cutomer or Guest"></span>
					</th>
					<td>
						<select id="fme_tabselect">
							<option value="new" 
							<?php
							if ( 'new' == $fme_tabselect) {
								echo esc_attr('selected');

							}
							?>
							>
								<?php echo esc_attr('New'); ?>
							</option>
							<option value="same"

							<?php
							if ( 'same' == $fme_tabselect) {
								echo esc_attr('selected');
								
							}
							?>
							>
							<?php echo esc_attr('Same'); ?>

							</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<label><?php echo esc_html_e('Select Role(s)', 'shop-as-a-customer-for-woocommerce'); ?></label>
						<span class="woocommerce-help-tip" data-tip="Only Selected Roles will be allowed to switch & View orders. If no role is selected all roles will be considered as selected"></span>	
					</th>
					<td>
						<?php 
						global $wp_roles;
						$all_roles = $wp_roles->get_names();
						$savedroles=get_option('fme_allrolessaved');
						$fme_defselectedp=get_option('fme_defselectedp');
						if ('' == $savedroles) {
							$savedroles=$all_roles;
						}

						if (!empty($all_roles)) {
							?>
							<select  id="select_roles" multiple="multiple" style="width: 25%;">
								<?php
								foreach ($all_roles as $key => $value) {
									if ( 'customer' != filter_var(strtolower($value))) {
										$valueuserrole  = strtolower( str_replace( ' ', '_', $value ) );
										?>
										<option value="<?php echo filter_var( $valueuserrole ); ?>"
											<?php
											if (isset($savedroles) && is_array($savedroles) && in_array($valueuserrole, $savedroles)) {
												echo esc_attr('selected');
											}
											?>
											>
											<?php echo filter_var($value); ?>
										</option>
										<?php
									}
								}
								?>
							</select>
							
							<?php

						}
						?>
					</td>
				</tr>
				<tr>
					<th>
						<label style="font-weight: bold;"><?php echo esc_html_e('By Default Order Status During Offline Payment', 'shop-as-a-customer-for-woocommerce'); ?></label>
					</th>
					<td>
						<?php
			
						$vie12p=wc_get_order_statuses();
						?>
						
						<select id="selectdefpm" value="<?php echo esc_attr($fme_defselectedp); ?>">
							<?php
							foreach ($vie12p as $key => $value) {
								?>
							<option value="<?php echo esc_attr($key); ?>">
								<?php echo esc_attr($value); ?>
							</option>
								<?php
							}
							?>
						<script type="text/javascript">
							jQuery('#selectdefpm').val('<?php echo esc_attr($fme_defselectedp); ?>');
						</script>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<button style="padding: 0px 10px 0px 10px;" class="button-primary saveroles"><?php echo esc_html_e('Save Settings', 'shop-as-a-customer-for-woocommerce'); ?></button>
					</th>
				<td>
				<span id="fme_settings_global_msg" class="fme_alert fme_alert-success" style="display:none;">
					<b><?php esc_html_e('Success!', 'shop-as-a-customer-for-woocommerce'); ?></b> <?php echo esc_html__('Settings saved successfully', 'shop-as-a-customer-for-woocommerce'); ?>
				</span>
			</td>
				</tr>
				


			</tbody>
		</table>		

		<div  id="fme_tableofswitch" style="display:block;">
			<table id="allcustomers" class="display responsive widefat ">
				<thead>
					<th>
						<center>
							<?php echo esc_html_e('ID', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>

					</th>
					<th>
						<center>
							<?php echo esc_html_e('Name', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>

					<th>
						<center>
							<?php echo esc_html_e('Email', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th style="width: 30%;">
						<center>
							<?php echo esc_html_e('Action', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					
				</thead>
				<!--  -->
				<tfoot>
					<th >
						<center>
							<?php echo esc_html_e('ID', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>

					</th>
					<th>
						<center>
							<?php echo esc_html_e('Name', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>

					<th>
						<center>
							<?php echo esc_html_e('Email', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th style="width: 30%;">
						<center>
							<?php echo esc_html_e('Action', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
				</tfoot>

			</table>

			<style type="text/css">
				.frompage{
					padding: 0px 10px !important;
				}
				#allcustomers_info{
					display: none;
				}
				.tooltip {
					position: relative;
					background: rgba(0,0,0,0.3);
					padding: 0px 2px;
					border-radius: 100%;
					
					cursor: help;
				}
				.subsubsub {
					float: unset !important;
				}

				.tooltip .tooltiptext {
					visibility: hidden;
					width: 270px;
					background-color: #2f283b;
					color: #fff;
					text-align: center;
					border-radius: 6px;
					padding: 5px 0;
					margin-left: 15px;
					position: absolute;
					z-index: 1;
				}

				.tooltip:hover .tooltiptext {
					visibility: visible;
				}
			.page-item.active .page-link{
				background-color: #2f283b;
				border-color: #2f283b;
			}
			body{
				background-color: #f1f1f1;
			}
			#allcustomers_paginate{
				margin-right: 0.5%;
			}
			
			.dt-buttons{
				margin-left: 0.5%;
			}
			#allcustomers_filter{
				margin-right: 1.5%;
			}
			#allcustomers_info{
				margin-left: 0.5%;
			}
			#allcustomers {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				width: 98%;
				margin-left: 0.5%;
				border: 1px solid #c3c4c7;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
			}
			
			#allcustomers td, #customers th {
				border: 0px solid #ddd;
				padding: 8px;
				line-height: 24px;	
			}

			#allcustomers th{
				border-bottom: 1px solid #c3c4c7;
				font-weight: 400;
				font-size: 14px;
				color: #2c3338
			}

			#allcustomers tr {
				border: 1px solid #ddd;
			}


			#allcustomers {
				background-color: #fff !important;
			}

		</style>
		</div>

		
		<style type="text/css" media="screen">

			#customers tbody tr td:nth-child(1){
				width: 5%;
			} 	

			#customers tbody tr td:nth-child(2){
				width: 23%;
			} 	

			#customers tbody tr td:nth-child(3){
				width: 30%;
			} 	

			#customers tbody tr td:nth-child(3){
				width: 24%;
			} 
			#customers{
				width:90%;
			}	

		</style>
		<div id="fme_taboflogs" style="display: none;">
			<table id="customers" class="display responsive widefat">
				<thead>
					<th style="width: 5%;">
						<center>
							<?php echo esc_html_e('ID', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>

					</th>
					<th style="width: 23%;">
						<center>
							<?php echo esc_html_e('Logged in At', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>

					<th style="width: 30%;">
						<center>
							<?php echo esc_html_e('Customer', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th style="width: 24%;">
						<center>
							<?php echo esc_html_e('Products', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th>
						<center>
							<?php echo esc_html_e('Message On Whatsapp', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
				</thead>
				<!--  -->
				<tfoot>
					<th style="width: 5%;">
						<center>
							<?php echo esc_html_e('ID', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>

					</th>
					<th style="width: 23%;">
						<center>
							<?php echo esc_html_e('Logged in At', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>

					<th style="width: 30%;">
						<center>
							<?php echo esc_html_e('Customer', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th style="width: 24%;">
						<center>
							<?php echo esc_html_e('Products', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
					<th>
						<center>
							<?php echo esc_html_e('Message On Whatsapp', 'shop-as-a-customer-for-woocommerce'); ?>
						</center>
					</th>
				</tfoot>

			</table>
		</div>
		<br>
		<br>

		<style type="text/css">
			#customers_info{
				display: none;
			}
			.page-item.active .page-link{
				background-color: #2f283b;
				border-color: #2f283b;
			}
			
			#customers_paginate{
				margin-right: 0.5%;
			}
			
			.dt-buttons{
				margin-left: 0.5%;
			}
			#customers_filter{
				margin-right: 1.5%;
			}
			#customers_info{
				margin-left: 0.5%;
			}
			#customers {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 98%;
				margin-left: 0.5%;
			}

			#customers {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				width: 98%;
				margin-left: 0.5%;
				border: 1px solid #c3c4c7;
				box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
			}
			
			#customers td, #customers th {
				border: 0px solid #ddd;
				padding: 8px;
				line-height: 24px;	
			}

			#customers th{
				border-bottom: 1px solid #c3c4c7;
				font-weight: 400;
				font-size: 14px;
				color: #2c3338
			}

			#customers tr {
				border: 1px solid #ddd;
			}


			#customers {
				background-color: #fff !important;
			}

		</style>

		<?php
	}
	public function fmeaddons_edit_profile() {
		check_ajax_referer('ajax_nonce', 'security');

		$username = 'Admin';
		$user = get_user_by('login', $username );
		if (isset($_REQUEST['id'])) {
			$id=sanitize_text_field($_REQUEST['id']);

		}
		if ( 'Select Customer' == $id) {
			$url= admin_url('/users.php');
			echo esc_attr( $url );
		} else {
			$url=get_edit_user_link( $id);
			echo esc_attr( $url );

		}
		die();
	}
	public function fmeaddons_view_order() {
		
		if (current_user_can('manage_woocommerce')) {

			session_start();
			check_ajax_referer('ajax_nonce', 'security');
		
			$user_meta=get_userdata(get_current_user_ID());

			$user_roles=$user_meta->roles;
			if (!empty(get_option('fme_allrolessaved')) || '' != get_option('fme_allrolessaved')) {
				$found=false;
				foreach ($user_roles as $key => $value) {
					if (is_array(get_option('fme_allrolessaved')) && in_array($value, get_option('fme_allrolessaved'))) {
						$found=true;
					}
				}
			} else {
				$found=true;
			}
			if ($found) {
				$id='';
				$username = 'Admin';
				$array_for_log_child=array();
				$array_for_log=get_option('all_logs');
				$user = get_user_by('login', $username );
				if (isset($_REQUEST['id'])) {
					$id=sanitize_text_field(intval($_REQUEST['id']));
				}
				if ( 'Select Customer' == $id ) {
					$url= admin_url('/edit.php?post_type=shop_order');
					echo filter_var($url);
				} else if ( !is_wp_error( $user ) ) {
					wp_clear_auth_cookie();
					wp_set_current_user ( $id );
					wp_set_auth_cookie  ( $id );

					if ( 'Select Customer'!=$id ) {
						echo filter_var(wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ));
					}
				}
				$useralll = get_userdata($id);
				$nammee=$useralll->data->display_name;
				$emaill=$useralll->data->user_email;

				$nmbrphone=get_user_meta($id, 'billing_phone', true);

				$array_for_log_child['id']=$id;

				$array_for_log_child['time']=gmdate('M d, Y h:i:s A'); 
				$array_for_log_child['customer']=$nammee . ' < ' . $emaill . ' > ';
				$array_for_log_child['phone']=$nmbrphone;
				$array_for_log_child['products']='';
				$array_for_log[]=$array_for_log_child;
				$_SESSION['admin']='adminisloggedin';
				update_option('all_logs', $array_for_log);
				session_write_close();
				die();
			} else {

				echo esc_attr('usernotmatched');
			}
		} else {
			echo esc_attr('You don not have the access');
		
		}
	

		wp_die();

	}
	public function fmeaddons_end_session() {
		session_start();
		if ( isset($_SESSION['admin']) ) {
			unset($_SESSION['admin']);
			session_destroy ();

		}  

	}
	public function fmeaddons_start_session() {
		if (!session_id()) {
			session_start();
		}

	}


	public function fmeaddons_switchtoguest() {
		session_start();
		check_ajax_referer('ajax_nonce', 'security');
		
		$user_meta=get_userdata(get_current_user_ID());

		$user_roles=$user_meta->roles;
		if (!empty(get_option('fme_allrolessaved')) || '' != get_option('fme_allrolessaved')) {

		
			$found=false;
			foreach ($user_roles as $key => $value) {
				if (is_array(get_option('fme_allrolessaved')) && in_array($value, get_option('fme_allrolessaved'))) {
					$found=true;
				}
			}
		} else {
			$found=true;
		}
		if ($found) {

			global $woocommerce;
			$array_for_log_child=array();
			$array_for_log=get_option('all_logs');
			$current_id=get_current_user_ID();
			if (isset($_REQUEST['id'])) {
				$id=sanitize_text_field($_REQUEST['id']);

			}


			$username = 'Admin';
			$user = get_user_by('login', $username );


			if ( !is_wp_error( $user ) ) {
				wp_clear_auth_cookie();
				

				$array_for_log_child['id']='N/A';

				$array_for_log_child['time']=gmdate('M d, Y h:i:s A'); 
				$array_for_log_child['customer']='Guest';
				$array_for_log_child['phone']='N/A';
				$array_for_log[]=$array_for_log_child;

				update_option('all_logs', $array_for_log);
				$redirect_to = user_admin_url();
				$d=get_permalink( wc_get_page_id( 'myaccount' ) );
				if (!is_admin()) {
					echo esc_url($redirect_to);
				} else {
					echo esc_attr($d);
				}
				$admin=array();
				array_push ($admin, $current_id);
				$_SESSION['admin']='adminisloggedin';
				update_option('admin111', $current_id );
				$items = $woocommerce->cart->get_cart();
				update_option('whole_admin_cart', $items);
				$woocommerce->cart->empty_cart();

			}
		} else {
			echo esc_attr('usernotmatched');
		}
		session_write_close();
		wp_die();


	}

	public function fmeaddons_switchtocustomer() {
		check_ajax_referer('ajax_nonce', 'security');
		session_start();
		
		if (isset($_REQUEST['switchbtn_refferer'])) {
			$_SESSION['switchbtn_refferer_page']=filter_var( $_REQUEST['switchbtn_refferer'] );
		}


		$user_meta=get_userdata(get_current_user_ID());

		$user_roles=$user_meta->roles;

		if (!empty(get_option('fme_allrolessaved')) || '' != get_option('fme_allrolessaved')) {

		
			$found=false;
			foreach ($user_roles as $key => $value) {
				if (is_array(get_option('fme_allrolessaved')) && in_array($value, get_option('fme_allrolessaved'))) {
					$found=true;
				}
			}
		} else {
			$found=true;
		}
		if ($found) {
			global $woocommerce;
			$array_for_log_child=array();
			$array_for_log=get_option('all_logs');
			$current_id=get_current_user_ID();
			if (isset($_REQUEST['id'])) {
				$id=sanitize_text_field($_REQUEST['id']);

			}


			$username = 'Admin';
			$user = get_user_by('login', $username );


			if ( !is_wp_error( $user ) ) {
				wp_clear_auth_cookie();
				wp_set_current_user ( $id );
				wp_set_auth_cookie  ( $id );
				$useralll = get_userdata($id);
				$nammee=$useralll->data->display_name;
				$emaill=$useralll->data->user_email;

				$nmbrphone=get_user_meta($id, 'billing_phone', true);

				$array_for_log_child['id']=$id;

				$array_for_log_child['time']=gmdate('M d, Y h:i:s A'); 
				$array_for_log_child['customer']=$nammee . ' < ' . $emaill . ' > ';
				$array_for_log_child['phone']=$nmbrphone;
				$array_for_log_child['products']='';
				$array_for_log[]=$array_for_log_child;

				update_option('all_logs', $array_for_log);


				$redirect_to = user_admin_url();
				$d=get_permalink( wc_get_page_id( 'myaccount' ) );
				if (!is_admin()) {
					echo esc_url($redirect_to);
				} else {
					echo esc_attr($d);
				}
				$admin=array();
				array_push ($admin, $current_id);
				$_SESSION['admin']='adminisloggedin';
				update_option('admin111', $current_id );
				$items = $woocommerce->cart->get_cart();
				update_option('whole_admin_cart', $items);
				$woocommerce->cart->empty_cart();

			}

		} else {
			echo esc_attr('usernotmatched');
		}
		session_write_close();
		wp_die();
	}




	public function fmeaddons_switchback_tab() {
		session_start();
		$flag=0;
		$theme=wp_get_theme();
		$id= get_current_user_ID();

		$admin_id=get_option( 'admin111' );
		if ( $id != $admin_id) {
			if (isset($_SESSION['admin']) && 'adminisloggedin' == $_SESSION['admin'] ) {
				
				if ( '0' != $id) {
					$user=get_userdata( $id );
					
					$display_name=$user->data->display_name;
				} else {
					$display_name='Guest';
				}

				$admin_id=get_option('admin111');
				if ('Shopkeeper' == $theme) {

					?>
					<div id="divtohide"  style="width: 100%; background-color: #2f283b; text-align:center;  margin-top:7%; ">
						<i id="compresstoright" style="color:#FFF; float: left; margin-top: 1%; margin-left: 1%;" class="fa fa-minus" aria-hidden="true"></i>
						<label id="msgmmmlft" style=" color:#FFF; display: none;float: left;margin-top: 1%;margin-left: 1%;"><?php echo esc_html_e('compress to right', 'shop-as-a-customer-for-woocommerce'); ?></label>
						<i  id="compresstoleft" style="color:#FFF; float: right; margin-top: 1%; margin-right: 1%;" class="fa fa-minus" aria-hidden="true"></i>

						<?php

				} else {
					?>
						<div id="divtohide" style="width: 100%; background-color: #2f283b; text-align:center; z-index: 999999; position: fixed; ">

							<i id="compresstoright" style="color:#FFF; float: left; margin-top: 1%; margin-left: 1%;" class="fa fa-minus" aria-hidden="true"></i>
							<label id="msgmmmlft" style=" color:#FFF; display: none;float: left;margin-top: 1%;margin-left: 1%;"><?php echo esc_html_e('compress to right', 'shop-as-a-customer-for-woocommerce'); ?></label>

							<i  id="compresstoleft" style="color:#FFF; float: right; margin-top: 1%; margin-right: 1%;" class="fa fa-minus" aria-hidden="true"></i>
							<?php
				}
				?>
						<center>
							<label style=" color: white; font-size: 16px;"><?php echo esc_html_e('You are now login as ', 'shop-as-a-customer-for-woocommerce'); ?><?php echo esc_html_e($display_name, 'shop-as-a-customer-for-woocommerce'); ?></label>
							<button type="submit" class="btn1" style="cursor: pointer !important;  background-color: #007cba; 
							border: 1px solid #000;margin :10px;
							color: white;
							border-radius: 4px;
							text-align: center;
							text-decoration: none;
							display: inline-block;
							font-size: 17px;" ><?php echo esc_html_e('Switch Back ', 'shop-as-a-customer-for-woocommerce'); ?>
						</button>
						<label id="msgmmmrit" style=" color:#FFF; display: none;float: right;margin-top: 1%;margin-right: 1%;"><?php echo esc_html_e('compress to left', 'shop-as-a-customer-for-woocommerce'); ?></label>
					</center>

				</div>
				<img src="<?php echo esc_attr(plugin_dir_url( __FILE__ ) . 'hi.png'); ?>" id="righticon" style="display: none; float: right; z-index: 999999; width: 4%; position: fixed;  margin-left: 95%;"> 
				<img src="<?php echo esc_attr(plugin_dir_url( __FILE__ ) . 'hi.png'); ?>" id="lefticon" style="display: none; float: left; z-index: 999999; width: 4%; position: fixed;  margin-right: 95%;"> 
				<center><img  id="loader_fme"  src="<?php echo esc_attr(plugin_dir_url( __FILE__ ) . 'loader2.gif'); ?>"></center>
				<input type="hidden" id="mainUrl" value="<?php echo esc_attr(get_site_url()); ?>">
				<?php
			}
		}
		session_write_close();
	}

	public function fmeaddons_switchback_to_admin() {
		session_start();
		
		check_ajax_referer ('ajax_nonce', 'security');
		
		global $woocommerce;
		$admin_id=get_option('admin111');
		$username = 'Admin';
		$user = get_user_by('login', $username );
		$url_to_be_redirected=admin_url();
		if (isset($_SESSION['switchbtn_refferer_page'])) {
			$url_to_be_redirected = $_SESSION['switchbtn_refferer_page'];
		}

		if ( !is_wp_error( $user ) ) {
			update_option('recent_customer', get_current_user_ID());
			wp_clear_auth_cookie();
			wp_set_current_user ($admin_id);
			wp_set_auth_cookie  ( $admin_id );

			$redirect_to = $url_to_be_redirected;
			//wp_safe_redirect( $redirect_to );

			echo filter_var( $redirect_to );
			session_write_close();
			wp_die();
			
			$woocommerce->cart->empty_cart(); 


			$items=get_option('whole_admin_cart');
			foreach ($items as $item => $values) { 

				$ID = $values['data']->get_id();
				$quantity = $values['quantity'];
				WC()->cart->add_to_cart( $ID, $quantity ); 
			} 

			if ( isset($_SESSION['admin']) ) {
				unset($_SESSION['admin']);
				session_destroy ();

			}  
			echo esc_url($url_to_be_redirected);
			session_write_close();
			die();
		}

		echo esc_url($url_to_be_redirected);
		session_write_close();
		die();
	}

	public function fmeaddons_custom_content_thankyou( $order_id ) {
		session_start();
		$admin_id=get_option( 'admin111' );
		$id= get_current_user_ID();
		if (isset( $_SESSION['admin'] ) && 'adminisloggedin' == $_SESSION['admin'] ) {
			$order = wc_get_order( $order_id );
			$items = $order->get_items();
			$products_array='';
			foreach ( $items as $item ) {
				$products_array = $products_array . $item['name'] . ',';
			}
			$array_for_log=get_option('all_logs');
		
			$array_for_log[count($array_for_log)-1]['products']=$products_array;
			
			update_option('all_logs', $array_for_log);
			
			
			?>
			
			<?php

		}
		session_write_close();
	}


	public function fmeaddons_script() {
		
		if (is_admin() && 'Customerslogs' == isset($_GET['page'])) {
			wp_register_style( 'select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
			wp_register_script( 'select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_style( 'select2css' );
			wp_enqueue_script( 'select2' );

			wp_register_script('fmesac_dataTableButtons', 'https://code.jquery.com/jquery-3.5.1.js', '', '1.0');
			wp_enqueue_script('fmesac_dataTableButtons');	


			wp_register_script('fmesac_jsZipCdn', 'https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js', '', '1.0');
			wp_enqueue_script('fmesac_jsZipCdn');	


			wp_register_script('fmesac_pdfMakeCdn', 'https://cdn.datatables.net/buttons/1.6.2/js/dataTables.buttons.min.js', '', '1.0');
			wp_enqueue_script('fmesac_pdfMakeCdn');


			wp_register_script('fmesac_vfsCdn', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', '', '1.0');
			wp_enqueue_script('fmesac_vfsCdn');


			wp_register_script('fmesac_html5Cdn', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', '', '1.0');
			wp_enqueue_script('fmesac_html5Cdn');

			wp_register_script('fmesac_printCdn', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', '', '1.0');
			wp_enqueue_script('fmesac_printCdn');

			wp_register_style('fmesac_responsiveColumns', 'https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css', '', '1.0');
			wp_enqueue_style('fmesac_responsiveColumns');
			wp_register_style('fmesac_responsiveColumns123', 'https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css', '', '1.0');
			wp_enqueue_style('fmesac_responsiveColumns123');

			wp_enqueue_script('fmesac_responsiveColumnsJs', 'https://cdn.datatables.net/buttons/1.6.2/js/buttons.html5.min.js', '', '1.0');
			wp_enqueue_script('fmesac_responsiveColumnsJs');



			wp_register_style( 'responsive', plugin_dir_url(__FILE__) . 'responsive.css', false, '1.1.3', 'all' );
			wp_enqueue_style( 'responsive' );

		} 
		
		////////////////////////////////////////////////////STARTS FROM HERE////////////////////////////

			////////////////////////////////////////font awesome new version enqueue///////////////////
		
			wp_register_style( 'load-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css', '', '1.0' );
			wp_enqueue_style('load-fa');	

		/////////////////////////////////////////////////////////////////////////////////////

			$theme = wp_get_theme(); // gets the current theme
		
		if ( 'Storefront' != $theme->name && 'Storefront' != $theme->parent_theme && 'Avada' != $theme->name && 'Avada' != $theme->parent_theme) {
			wp_register_style( 'otherthemes', plugin_dir_url(__FILE__) . 'otherthemes.css', false, '1.1.3', 'all' );
			wp_enqueue_style( 'otherthemes' );
		}

		////////////////////////////////////////////////STORE FRONT/////////////////////////////
		if ( 'Avada' != $theme->name && 'Avada' != $theme->parent_theme && 'Flatsome' != $theme->name && 'Flatsome' != $theme->parent_theme && 'Woodmart' != $theme->name && 'Woodmart' != $theme->parent_theme  ) {	
			wp_register_style( 'storefrontheme', plugin_dir_url(__FILE__) . 'storefronttheme.css', false, '1.1.3', 'all' );
			wp_enqueue_style( 'storefrontheme' );
		}

		if ( 'Storefront' != $theme->name && 'Storefront' != $theme->parent_theme && 'Flatsome' != $theme->name && 'Flatsome' != $theme->parent_theme && 'Woodmart' != $theme->name && 'Woodmart' != $theme->parent_theme  ) {	
			 wp_register_style( 'Avada', plugin_dir_url(__FILE__) . 'Avada.css', false, '1.1.3', 'all' );
			 wp_enqueue_style( 'Avada' );
		}
				
		/////////////////////////////////////////////////////////ends here////////////////////////////////////////


		wp_enqueue_style( 'bootstrap-min-css123', plugin_dir_url( __FILE__ ) . 'bootstrap-4.3.1-dist/css/bootstrap-iso.css', false, '1.1.3', 'all' );

		// wp_enqueue_style( 'bootstrap-min-css1234', plugin_dir_url( __FILE__ ) . 'bootstrap-4.3.1-dist/css/bootstrap.min.css', false, '1.0', 'all' );


		wp_enqueue_script('bootstrap-min-js', plugin_dir_url( __FILE__ ) . 'bootstrap-4.3.1-dist/js/bootstrap.js', false, '1.0', 'all' );
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('jquery');    
		wp_enqueue_script('myyy_custom_script', plugin_dir_url( __FILE__ ) . 'ajax.js', false, '1.1.7', 'all' );

		$ewcpm_data = array(
			'admin_url' => admin_url('admin-ajax.php'),
		);
		wp_localize_script('myyy_custom_script', 'ewcpm_php_vars', $ewcpm_data);
		wp_localize_script('myyy_custom_script', 'ajax_url_add_pq', array('ajax_url_add_pq_data' => admin_url('admin-ajax.php')));
		
		
		wp_localize_script('myyy_custom_script', 'ajax_nonce', wp_create_nonce('ajax_nonce'));
		

	}


	public function fmeaddons_custom_toolbar_link( $wp_admin_bar ) {

		$args = array(
			'id' => 'switch-to1',
			'title' => __('Switch To Guest', 'shop-as-a-customer-for-woocommerce'), 
			'href' => '#',
			'type' => 'button',

			'parent' => 'user-actions',
			'meta' => array(
				'class' => 'buttonn1 ib-icon', 
				'title' => __('switch to Guest', 'shop-as-a-customer-for-woocommerce')
			)
		);
		$wp_admin_bar->add_node($args);

		$args = array(
			'id' => 'switch-to',
			'title' => __('Switch To Customer', 'shop-as-a-customer-for-woocommerce'), 
			'href' => '#',
			'type' => 'button',

			'parent' => 'user-actions',
			'meta' => array(
				'class' => 'buttonn ib-icon', 
				'title' => __('switch to customer', 'shop-as-a-customer-for-woocommerce')
			)
		);
		$wp_admin_bar->add_node($args);


	}



	public function fmeaddons_modal() {
		$args = array(

			'orderby' => 'last_name',
			'order'   => 'ASC'
		);
		// $users = get_users( $args ); 
		$recent=get_option('recent_customer');
		$datau=get_userdata($recent);
		?>
		<input type="hidden" name="test" id="icon_image" value="<?php echo esc_attr(plugin_dir_url( __FILE__ ) . 'Assets/icon.png'); ?>">
		<div class="modal fade" id="myModal" role="dialog">
			<div class="modal-dialog">

				<div class="modal-content">
					<div class="modal-header" style="background-color: #120f19;color: #FFF;">
						<button type="button" class="close"style="color: #FFF; opacity: 1;" data-dismiss="modal">&times;</button>
						<h4 class="modal-title" style="margin-top: 4%;"><?php echo esc_html_e('Shop as Customer', 'shop-as-a-customer-for-woocommerce'); ?></h4>
					</div>
					<div class="modal-body">

						<div class="searchable">
							<input type="text" id="cusname" autocomplete="off" placeholder="Search customers by email" >
							<input type="hidden" id="cusname1">
							
						</div>
						<label id="fmse_nrf"style="display: none;"><?php echo esc_html_e('No Records found', 'shop-as-a-customer-for-woocommerce'); ?></label>
						<button id="nextfind" class="button-primary" style="margin-top:1%;display: none;"><?php echo esc_html_e('next', 'shop-as-a-customer-for-woocommerce'); ?></button>
						<style>
							@media screen and (max-width:1024px){

								.modal-backdrop{
									display: none !important;
								}
								#myModal{
									z-index: 99999;
								}

							}
							div.searchable {
								width: 100%;

							}
							.searchable input {
								width: 100%;
								height: 50px;
								font-size: 18px;
								padding: 10px;
								-webkit-box-sizing: border-box;
								-moz-box-sizing: border-box; 
								box-sizing: border-box; 
								display: block;
								font-weight: 400;
								line-height: 1.6;
								color: #495057;
								background-color: #fff;
								background-clip: padding-box;
								border: 1px solid #ced4da;
								border-radius: .25rem;
								transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
								background: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E") no-repeat right .75rem center/8px 10px;
							}

							.searchable ul {
								list-style-type: none;
								background-color: #fff;
								border-radius: 0 0 5px 5px;
								border: 1px solid #add8e6;
								border-top: none;
								max-height: 180px;

								overflow-y: scroll;

							}

							.searchable ul li {

								border-bottom: 1px solid #e1e1e1;
								cursor: pointer;
								color: #6e6e6e;
							}

							.searchable ul li.selected {
								background-color: #e8e8e8;
								color: #333;
							}
						</style>

						<script type="text/javascript">
							function filterFunction(that, event) {
								let container, input, filter, li, input_val;
								container = jQuery(that).closest(".searchable");
								input_val = container.find("input").val().toUpperCase();

								if (["ArrowDown", "ArrowUp", "Enter"].indexOf(event.key) != -1) {
									keyControl(event, container)
								} else {
									li = container.find("ul li");
									li.each(function (i, obj) {
										if (jQuery(this).text().toUpperCase().indexOf(input_val) > -1) {
											jQuery(this).show();
										} else {
											jQuery(this).hide();
										}
									});

									container.find("ul li").removeClass("selected");
									setTimeout(function () {
										container.find("ul li:visible").first().addClass("selected");
									}, 100)
								}
							}

							function keyControl(e, container) {
								if (e.key == "ArrowDown") {

									if (container.find("ul li").hasClass("selected")) {
										if (container.find("ul li:visible").index(container.find("ul li.selected")) + 1 < container.find("ul li:visible").length) {
											container.find("ul li.selected").removeClass("selected").nextAll().not('[style*="display: none"]').first().addClass("selected");
										}

									} else {
										container.find("ul li:first-child").addClass("selected");
									}

								} else if (e.key == "ArrowUp") {

									if (container.find("ul li:visible").index(container.find("ul li.selected")) > 0) {
										container.find("ul li.selected").removeClass("selected").prevAll().not('[style*="display: none"]').first().addClass("selected");
									}
								} else if (e.key == "Enter") {
									container.find("input").val(container.find("ul li.selected").text().trim()).blur();
									onSelect(container.find("ul li.selected")[0]['value'])
								}

								container.find("ul li.selected")[0].scrollIntoView({
									behavior: "smooth",
								});
							}

							function onSelect(val) {

								 jQuery('#nextfind').hide();

								jQuery('#cusname1').val(val);
							}

							jQuery(".searchable input").focus(function () {
								jQuery(this).closest(".searchable").find("ul").show();
								jQuery(this).closest(".searchable").find("ul li").show();
							});
							jQuery(".searchable input").blur(function () {
								let that = this;
								setTimeout(function () {
									jQuery(that).closest(".searchable").find("ul").hide();
								}, 300);
							});

							jQuery(document).on('click', '.searchable ul li', function () {
								// console.log(jQuery(this).innerHTML)
								jQuery(this).closest(".searchable").find("input").val(jQuery(this)[0]['innerHTML'].trim()).blur();
								onSelect(jQuery(this)[0]['value']);
							});

							jQuery(".searchable ul li").hover(function () {
								jQuery(this).closest(".searchable").find("ul li.selected").removeClass("selected");
								jQuery(this).addClass("selected");
							});
						</script>
					</div>
					<div class="modal-footer">
						<center>
							<button type="button" class='switchbtn button-primary' style="cursor: pointer !important;   margin: 1%; padding: 2px 2%; " ><?php echo esc_html_e('Switch', 'shop-as-a-customer-for-woocommerce'); ?>
							</button>

							<button type="button" class="vieworder button-primary" style="cursor: pointer !important;     margin: 1%; padding: 2px 2%;" ><?php echo esc_html_e('View Orders', 'shop-as-a-customer-for-woocommerce'); ?>
							</button>
							<button type="button" class='editprofile button-primary' style="cursor: pointer !important;     margin: 1%;  padding: 2px 2%; " ><?php echo esc_html_e('Edit Profile', 'shop-as-a-customer-for-woocommerce'); ?>
							</button><br>
						</center>

					</div>
					<strong style="padding: 1rem;"><label id="label1"><?php echo esc_html_e('Recent Customer:', 'shop-as-a-customer-for-woocommerce'); ?>
						<?php
						if ( '' != $datau->display_name) {
							echo esc_attr($datau->display_name);
						} else {
							echo esc_attr('Guest');
						}
						?>
						
						</label>
					</strong>
				</div>

			</div>
		</div>

		<?php
		if (!is_admin()) {
			?>
			<style type="text/css">
				.editprofile,.vieworder,.switchbtn{
					border-radius: 4px !important;
					padding:5px 7px 5px 7px !important;
				}
			</style>
			<?php
		}

	}

}
new MainFmeaddonsClass();
