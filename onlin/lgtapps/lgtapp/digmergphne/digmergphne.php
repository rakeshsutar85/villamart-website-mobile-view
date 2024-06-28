<?php

/*
 * Plugin Name: DIGITS: Merge Phone Number Addon
 * Description: Merge your Woocommerce billing phone and account phone from checkout page and every other place.
 * Version: 2.3
 * Plugin URI: https://digits.unitedover.com/addons
 * Author URI: https://www.unitedover.com/
 * Author: UnitedOver
 * Text Domain: digmergphne
 * Requires PHP: 5.5
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}


require dirname(__FILE__) . '/handler.php';
require dirname(__FILE__) . '/Puc/v4p6/Factory.php';
require dirname(__FILE__) . '/Puc/v4/Factory.php';
require dirname(__FILE__) . '/Puc/v4p6/Autoloader.php';
new Puc_v4p6_Autoloader();

foreach (
    array(
        'Plugin_UpdateChecker' => 'Puc_v4p6_Plugin_UpdateChecker',
        'Vcs_PluginUpdateChecker' => 'Puc_v4p6_Vcs_PluginUpdateChecker',
    )
    as $pucGeneralClass => $pucVersionedClass
) {
    Puc_v4_Factory::addVersion($pucGeneralClass, $pucVersionedClass, '4.6');

    Puc_v4p6_Factory::addVersion($pucGeneralClass, $pucVersionedClass, '4.6');
}


function digits_update_billing_merge_settings()
{
    if (isset($_POST['dig_bill_merge_phone'])) {
        $dig_bill_ship_fields = sanitize_text_field($_POST['dig_bill_merge_phone']);
        update_option('dig_bill_merge_phone', $dig_bill_ship_fields);

        $dig_sync_acc_bill_fields = sanitize_text_field($_POST['dig_sync_acc_bill_fields']);
        update_option('dig_sync_acc_bill_phone', $dig_sync_acc_bill_fields);

    }
}

add_action('digits_save_settings_data', 'digits_update_billing_merge_settings');


function digits_addon_digmergphne()
{
    return 'digmergphne';
}


function dig_show_billmerge($active_tab)
{
    ?>
    <div data-tab="digmergphnetab"
         class="dig_admin_in_pt digmergphnetab digtabview <?php echo $active_tab == digits_addon_digmergphne() ? 'digcurrentactive' : '" style="display:none;'; ?>">
        <?php digad_show_merge_settings(); ?>
    </div>

    <?php

}

add_action('digits_settings_page', 'dig_show_billmerge');


function digad_show_merge_settings()
{
    $digpc = get_site_option('dig_purchasecode');
    if (empty($digpc)) return;

    $dig_bill_ship_fields = get_option('dig_bill_merge_phone', 0);

    $dig_sync_acc_bill_fields = get_option('dig_sync_acc_bill_phone', 0);
    ?>

    <div class="dig_admin_head">
        <span><?php _e('Merge Phone Number', 'digits'); ?></span>
    </div>

    <div class="dig_admin_tab_grid">
        <div class="dig_admin_tab_grid_elem">

            <table class="form-table">
                <tr>
                    <th scope="row"><label
                                for="dig_bill_ship_fields"><?php _e('Merge Billing and Account Mobile Fields', 'digmergphne'); ?>
                        </label></th>
                    <td>
                        <select name="dig_bill_merge_phone" id="dig_bill_ship_fields">
                            <option value="1" <?php if ($dig_bill_ship_fields == 1) {
                                echo 'selected=selected';
                            } ?> ><?php _e('Yes', 'digmergphne'); ?></option>
                            <option value="0" <?php if ($dig_bill_ship_fields == 0) {
                                echo 'selected=selected';
                            } ?> ><?php _e('No', 'digmergphne'); ?></option>
                        </select>
                        <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('This will merge WooCommerce\'s Billing and Account Phone Number (Digits), Account phone number field will be removed from checkout and Billing Phone Number will be used as account phone number.', 'digmergphne'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label
                                for="dig_sync_acc_bill_fields"><?php _e('Always Sync Billing Phone with Account Number', 'digmergphne'); ?>
                        </label></th>
                    <td>
                        <select name="dig_sync_acc_bill_fields" id="dig_sync_acc_bill_fields">
                            <option value="1" <?php if ($dig_sync_acc_bill_fields == 1) {
                                echo 'selected=selected';
                            } ?> ><?php _e('Yes', 'digmergphne'); ?></option>
                            <option value="0" <?php if ($dig_sync_acc_bill_fields == 0) {
                                echo 'selected=selected';
                            } ?> ><?php _e('No', 'digmergphne'); ?></option>
                        </select>
                        <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('User will not be able to change billing phone number from checkout once you enable this option.', 'digmergphne'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <?php
}

$digmergphneUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://bridge.unitedover.com/updates/changelog/addons.php?addon=digmergphne',
    __FILE__,
    'digmergphne'
);

$digmergphneUpdateChecker->addQueryArgFilter('digmergphne_filter_update_checks');
function digmergphne_filter_update_checks($queryArgs)
{


    $queryArgs['license_key'] = get_site_option('dig_purchasecode');

    $queryArgs['request_site'] = network_home_url();

    $queryArgs['license_type'] = get_site_option('dig_license_type', 1);

    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];

    $queryArgs['version'] = $plugin_version;


    return $queryArgs;
}


function digmergphne_addon($list)
{
    $list[] = 'digmergphne';
    return $list;
}

add_filter('digits_addon', 'digmergphne_addon');

function digmergphne_addon_tab($tabs)
{
    $tabs['digmergphne'] = array('label' => esc_attr__('Merge Phone Number', 'digits'));
    return $tabs;
}

add_filter('digits_admin_addon_tab', 'digmergphne_addon_tab');


function disable_digmergphone_addon(){
    if(function_exists('digits_version')){
        if(digits_version() >= 8){
            return true;
        };
    }
    return false;
}
function digmergphne_scripts()
{
    if(disable_digmergphone_addon()){
        return;
    }
    $digpc = get_site_option('dig_purchasecode');
    if (empty($digpc)) return;


    if (function_exists('is_checkout')) {
        if (is_checkout()) {
            $dig_bill_ship_fields = get_option('dig_bill_merge_phone', 0);

            if ($dig_bill_ship_fields == 0) return;
            wp_register_script('mergphne', plugins_url('/js/digbillmerge.js', __FILE__), array('jquery'));

            $jsData = array(
                'user_logged_in' => is_user_logged_in(),
                'merge' => $dig_bill_ship_fields,
            );
            wp_localize_script('mergphne', 'dig_billmerge', $jsData);
            wp_enqueue_script('mergphne');
        }
    }
}

add_action('wp_enqueue_scripts', 'digmergphne_scripts', 1);


function dig_mergphn_update_wc_checkout_labels($fields)
{

    if(disable_digmergphone_addon()){
        return $fields;
    }
    $dig_bill_ship_fields = get_option('dig_bill_merge_phone', 0);

    if ($dig_bill_ship_fields == 0) return $fields;
    $fields['billing']['billing_email']['label'] = __("Email", "digits");
    $fields['billing']['billing_phone']['label'] = __("Mobile Number", "digits");

    unset($fields['account']['mobile/email']);
    return $fields;
}

add_filter('woocommerce_checkout_fields', 'dig_mergphn_update_wc_checkout_labels', 10);


add_action('wp_head', 'digmergphn_hide_field');
function digmergphn_hide_field()
{
    if(disable_digmergphone_addon()){
        return;
    }

    if (is_user_logged_in()) return;
    if (function_exists('is_checkout')) {
        if (is_checkout()) {
            $dig_bill_ship_fields = get_option('dig_bill_merge_phone', 0);

            if ($dig_bill_ship_fields == 0) return;
            ?>
            <style>#billing_phone {
                    display: none;
                }</style><?php
        }
    }
}