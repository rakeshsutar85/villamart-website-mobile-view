<?php

/*
 * Plugin Name: DIGITS: Old Account Migrator Addon
 * Description: Let your old account users migrate to phone number login with ease.
 * Version: 1.9
 * Plugin URI: https://digits.unitedover.com/addons
 * Author URI: https://www.unitedover.com/
 * Author: UnitedOver
 * Text Domain: digoldaccntmr
 * Requires PHP: 5.5
 * Domain Path: /languages
 */


if (!defined('ABSPATH')) {
    exit;
}

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


function digits_update_digoldaccntmr_settings()
{
    if (isset($_POST['add_phone_old_users'])) {
        $settings = array();

        $settings['add_phone_old_users'] = sanitize_text_field($_POST['add_phone_old_users']);

        $settings['add_phone_force'] = sanitize_text_field($_POST['add_phone_force']);

        $settings['when_to_show'] = sanitize_text_field($_POST['when_to_show']);


        update_option('dig_old_account_migrator', $settings);

    }
}

add_action('digits_save_settings_data', 'digits_update_digoldaccntmr_settings');


function digits_addon_digoldaccntmr()
{
    return 'digoldaccntmr';
}

function digad_show_oldacctmr_values()
{
    $digemailfit_default = array(
        'add_phone_old_users' => 1,
        'add_phone_force' => 1,
        'when_to_show' => 1,
    );

    return get_option('dig_old_account_migrator', $digemailfit_default);

}


function dig_show_digoldaccntmr_tab($active_tab)
{
    ?>
    <div data-tab="digoldaccntmrtab"
         class="dig_admin_in_pt digoldaccntmrtab digtabview <?php echo $active_tab == 'digoldaccntmr' ? 'digcurrentactive' : '" style="display:none;'; ?>">
        <?php digad_show_digoldaccntmr_settings(); ?>
    </div>

    <?php

}

add_action('digits_settings_page', 'dig_show_digoldaccntmr_tab');
function digad_show_digoldaccntmr_settings()
{

    $digpc = get_site_option('dig_purchasecode');
    if (empty($digpc)) {
        return;
    }

    $digad_show_oldacctmr_values = digad_show_oldacctmr_values();
    $add_phone_old_users = $digad_show_oldacctmr_values['add_phone_old_users'];
    $add_phone_force = $digad_show_oldacctmr_values['add_phone_force'];
    $when_to_show = $digad_show_oldacctmr_values['when_to_show'];
    ?>

    <div class="dig_admin_head"><span><?php _e('Old Account Migrator', 'digoldaccntmr'); ?></span></div>
    <div class="dig_admin_tab_grid">
        <div class="dig_admin_tab_grid_elem">

            <div class="dig_admin_section">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label
                                    for="add_phone_old_users"><?php _e('Show "Add Phone Number" Popup to Old Users', 'digoldaccntmr'); ?>
                            </label></th>
                        <td>
                            <select name="add_phone_old_users" id="add_phone_old_users">
                                <option value="1" <?php if ($add_phone_old_users == 1) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('Yes', 'digoldaccntmr'); ?></option>
                                <option value="0" <?php if ($add_phone_old_users == 0) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('No', 'digoldaccntmr'); ?></option>
                            </select>
                            <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('This feature will show popup / modal to old users asking them to enter their mobile number', 'digoldaccntmr'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label
                                    for="add_phone_force"><?php _e('Make it compulsory', 'digoldaccntmr'); ?>
                            </label></th>
                        <td>
                            <select name="add_phone_force" id="add_phone_force">
                                <option value="1" <?php if ($add_phone_force == 1) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('Yes', 'digoldaccntmr'); ?></option>
                                <option value="0" <?php if ($add_phone_force == 0) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('No', 'digoldaccntmr'); ?></option>
                            </select>
                            <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('If set to yes, users will not be able to close the popup if they don\'t enter the mobile number', 'digoldaccntmr'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label
                                    for="when_to_show"><?php _e('When to show', 'digoldaccntmr'); ?>
                            </label></th>
                        <td>
                            <select name="when_to_show" id="when_to_show">
                                <option value="1" <?php if ($when_to_show == 1) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('Only on first login', 'digoldaccntmr'); ?></option>
                                <option value="0" <?php if ($when_to_show == 0) {
                                    echo 'selected=selected';
                                } ?> ><?php _e('Every login until they enter phone number', 'digoldaccntmr'); ?></option>
                            </select>
                            <p class="dig_ecr_desc dig_sel_erc_desc"><?php _e('If set to yes, users will not be able to close the popup if they don\'t enter the mobile number', 'digoldaccntmr'); ?></p>
                        </td>
                    </tr>


                </table>


                <div class="dig_admin_head"><span><?php _e('Database Migrator', 'digoldaccntmr'); ?></span></div>

                <div class="dig_oldaccount_migr_desc" style="color:#7A889A;font-size: 16px;">
                    When can you use this<br/><br/>
                    - Already have phone number data for users<br/>
                    - Have phone number linked to users with the help of some other plugin or form<br/>
                    - Have users phone number in some other meta field<br/>
                    - Want to copy WooCommerce's billing phone number to account phone number (Digits)<br/><br/>
                </div>
                <br/>
                <div class="form-table">
                    <button type="button"
                            class="button digoldaccnt_open_migrator"><?php _e('OPEN PHONE DATABASE MIGRATOR', 'digoldaccntmr'); ?></button>
                </div>


                <div class="dig_presets_modal dig_overlay_modal_content" id="dig_account_migration_content">
                    <div class="dig-flex_center">
                        <div id="dig_presets_modal_box">
                            <div id="dig_presets_modal_body" class="form-table">
                                <div class="modal_head"><?php _e('OLD ACCOUNT MIGRATOR', 'digoldaccntmr'); ?></div>


                                <div class="dig_migration_details">
                                    <table class="form-table dig_admin_in_pt">

                                        <tr>
                                            <th scope="row"><label><?php _e('Users Demographics', 'digoldaccntmr'); ?>
                                                </label></th>
                                            <td>
                                                <select name="dig_old_accnt_user_demographics"
                                                        class="digoldaccntmr_user_demographics"
                                                        dig-save="0">
                                                    <option data-show="digoldaccntmr_user_default_country"
                                                            value="1"><?php _e('From single country', 'digoldaccntmr'); ?></option>
                                                    <option data-show="digoldaccntmr_user_countrycode_meta_key"
                                                            value="2"><?php _e('From different countries', 'digoldaccntmr'); ?></option>
                                                </select>
                                            </td>
                                        </tr>


                                        <tr class="digoldaccntmr-country-code_fields digoldaccntmr_user_default_country"
                                            style="display: table-row;">
                                            <th scope="row"><label><?php _e('User Country', 'digoldaccntmr'); ?>
                                                </label></th>
                                            <td>
                                                <select name="dig_oldaccntmr_country_code"
                                                        class="dig_oldaccntmr_country_code"
                                                        dig-save="0">
                                                    <?php
                                                    $countries = getCountryList();
                                                    foreach ($countries as $country => $code) {
                                                        echo '<option value="+' . $code . '">' . $country . ' (+' . $code . ')</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>


                                        <tr class="digoldaccntmr-country-code_fields digoldaccntmr_user_countrycode_meta_key">
                                            <th scope="row"><label><?php _e('Country Field', 'digoldaccntmr'); ?>
                                                </label></th>
                                            <td>

                                                <select name="dig_old_accnt_country_code_field"
                                                        class="dig_oldaccntmr_copy_from"
                                                        data-trigger="country_meta_key"
                                                        data-modify="dig_old_accnt_country_code_key"
                                                        dig-save="0">
                                                    <option value="1" data-key="billing_country"
                                                            selected><?php _e('WooCommerce Billing Country', 'digoldaccntmr'); ?></option>
                                                    <option value="0"
                                                            data-key=""><?php _e('Another Field', 'digoldaccntmr'); ?></option>
                                                </select>


                                            </td>
                                        </tr>
                                        <tr class="digoldaccntmr-country-code_fields digoldaccntmr_user_countrycode_meta_key country_meta_key">
                                            <th scope="row"><label
                                                        for="dig_old_accnt_country_code_key"><?php _e('Country Field Meta Key', 'digoldaccntmr'); ?>
                                                </label></th>
                                            <td>
                                                <input type="text" id="dig_old_accnt_country_code_key"
                                                       name="dig_old_accnt_country_code_key"
                                                       class="dig_old_accnt_country_code_key"
                                                       dig-save="0"/>

                                            </td>
                                        </tr>


                                        <tr>
                                            <th scope="row"><label><?php _e('Phone Field', 'digoldaccntmr'); ?></label>
                                            </th>
                                            <td>
                                                <select name="dig_oldaccntmr_copy_from" class="dig_oldaccntmr_copy_from"
                                                        data-modify="dig_old_accnt_mr_meta_key"
                                                        data-trigger="dig_oldacct_another_meta_field" dig-save="0">
                                                    <option value="1" data-key="billing_phone"
                                                            selected><?php _e('WooCommerce Billing Phone', 'digoldaccntmr'); ?></option>
                                                    <option value="0"
                                                            data-key=""><?php _e('Another Field', 'digoldaccntmr'); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr class="dig_oldacct_another_meta_field">
                                            <th scope="row"><label
                                                        for="dig_old_accnt_mr_meta_key"><?php _e('Phone Field Meta Key', 'digoldaccntmr'); ?>
                                                </label></th>
                                            <td>
                                                <input type="text" id="dig_old_accnt_mr_meta_key"
                                                       name="dig_old_accnt_mr_meta_key"
                                                       class="dig_old_accnt_mr_meta_key"
                                                       dig-save="0"/>

                                                <input type="hidden" class="dig_old_accnt_nonce"
                                                       value="<?php echo wp_create_nonce('dig_old_accnt_mr') ?>"/>
                                            </td>
                                        </tr>


                                    </table>
                                </div>
                                <div class="dig_migration_success">
                                    <div>
                                        <div class="dig-migration-succss-icon icon-checked-circle icon-checked-circle-dims"></div><?php _e('MIGRATION SUCCESSFUL', 'digoldaccntmr'); ?>
                                    </div>
                                </div>

                                <div class="digits_scrollbar dig_migration_conflicted" style="display: none;"><br/>
                                    <table class="form-table dig_admin_in_pt dig_old_conflict_accounts">
                                        <tr class="head">
                                            <th></th>
                                            <td scope="row"><?php _e('ACCOUNT DETAILS', 'digoldaccntmr'); ?></td>
                                            <td><?php _e('REASON', 'digoldaccntmr'); ?></td>
                                        </tr>

                                    </table>
                                </div>


                                <div class="dig_migrator-button dig_ex_imp_bottom">
                                    <button class="imp_exp_button button" id="dig_old_accnt_run_migrator"
                                            type="button"><?php _e('RUN MIGRATOR', 'digoldaccntmr'); ?></button>
                                    <div class="imp_exp_button imp_exp_cancel dig_presets_modal_head_close"
                                         id="dig_presets_modal_head_close"><?php _e('Cancel', 'digits'); ?></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php

    wp_register_script('digoldaccntmr', plugins_url('/js/digitsoldaccountmerger.js', __FILE__), array('jquery'));

    $jsData = array(
        'user_logged_in' => is_user_logged_in(),
        'ajax_url' => admin_url('admin-ajax.php'),

    );
    wp_localize_script('digoldaccntmr', 'dig_oamtr ', $jsData);
    wp_enqueue_script('digoldaccntmr');

    wp_enqueue_style('digoldaccntmr-style', plugins_url('/css/digitsoldaccountmerger.css', __FILE__));


}

add_action('wp_ajax_dig_migrate_user_database', 'dig_migrate_user_database');
function dig_migrate_user_database()
{
    if (!current_user_can('manage_options')) {
        die();
    }
    $nounce = $_POST['nounce'];

    if (!wp_verify_nonce($nounce, 'dig_old_accnt_mr')) {
        dig_showResponse(false, __('Error', 'digits'));
        die();
    }

    digits_load_gateways();

    $demographics = $_POST['dig_old_accnt_user_demographics'];


    $users = get_users(array('fields' => array('ID')));


    $countrycode_key = sanitize_text_field($_POST['dig_old_accnt_country_code_key']);


    $ccode = sanitize_text_field($_POST['dig_oldaccntmr_country_code']);

    $data_key = sanitize_text_field($_POST['dig_old_accnt_mr_meta_key']);
    $duplicates = array();

    $countrycode_not_found = array();
    $mobile_not_found = array();
    $invalid_mobile = array();
    $invalid_country = array();

    foreach ($users as $user_id) {
        $user_id = $user_id->ID;
        $check_dig_mobile = get_user_meta($user_id, 'digits_phone', true);
        if ($check_dig_mobile != null) {
            continue;
        }

        $user_mob_o_wc = get_user_meta($user_id, $data_key, true);

        $user_mob_o_wc = sanitize_mobile_field_dig(str_replace(array('-', '(', ')', ' '), "", $user_mob_o_wc));


        if ($user_mob_o_wc == null || !is_numeric($user_mob_o_wc)) {
            $mobile_not_found[] = digoldacc_migration_error($user_id);
            continue;
        }

        if ($demographics == 1) {
            $countrycode = $ccode;
        } else {
            $countrycode = digoldaccntmr_getCountryCode(get_user_meta($user_id, $countrycode_key, true));
        }

        $countrycode = digoldaccntmr_filter_countrycode($countrycode);
        $user_mob_o_wc = digoldaccntmr_filter_mobile($countrycode, $user_mob_o_wc);

        $isValid = false;
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $numberProto = $phoneUtil->parse($countrycode . $user_mob_o_wc);
            $isValid = $phoneUtil->isValidNumber($numberProto);
            if ($isValid) {
                $countrycode = $numberProto->getCountryCode();
                $user_mob_o_wc = $numberProto->getNationalNumber();

            }
        } catch (\libphonenumber\NumberParseException $e) {
            $isValid = false;
        }


        if (empty($countrycode) || $countrycode == '+') {
            $countrycode_not_found[] = digoldacc_migration_error($user_id);
            continue;
        }

        if (!is_numeric($countrycode)) {
            $invalid_country[] = digoldacc_migration_error($user_id);
            continue;
        }

        if (!$isValid) {
            $invalid_mobile[] = digoldacc_migration_error($user_id);
            continue;
        }


        $countrycode = digoldaccntmr_filter_countrycode($countrycode);


        $user_mob = $countrycode . $user_mob_o_wc;

        $check_user = getUserFromPhone($user_mob);

        if ($check_user != null) {

            $duplicate = array();
            if (isset($duplicates[$check_user->ID])) {
                $duplicate = $duplicates[$check_user->ID];
            } else {

                $check_user_mail = $check_user->user_email;

                $duplicate[] = dig_oldacc_user_link($check_user->user_login, $check_user_mail, $check_user->ID);

            }

            $user_o = get_user_by('ID', $user_id);


            $user_o_email = $user_o->user_email;


            $duplicate[] = dig_oldacc_user_link($user_o->user_login, $user_o_email, $user_id);


            $duplicates[$check_user->ID] = $duplicate;
            continue;
        } else {
            update_user_meta($user_id, 'digits_phone', $user_mob);
            update_user_meta($user_id, 'digt_countrycode', $countrycode);
            update_user_meta($user_id, 'digits_phone_no', $user_mob_o_wc);
        }
    }

    $data = array();

    if (!empty($countrycode_not_found)) {
        $data[] = array('type' => __('Country Code not found', 'digoldaccntmr'), 'data' => $countrycode_not_found);
    }

    if (!empty($invalid_country)) {
        $data[] = array('type' => __('Invalid Country Code', 'digoldaccntmr'), 'data' => $invalid_country);
    }
    if (!empty($mobile_not_found)) {
        $data[] = array(
            'type' => __('Mobile Number not found', 'digoldaccntmr'),
            'data' => $mobile_not_found
        );
    }

    if (!empty($invalid_mobile)) {
        $data[] = array('type' => __('Invalid Mobile Number', 'digoldaccntmr'), 'data' => $invalid_mobile);
    }

    if (!empty($duplicates)) {
        $data[] = array('type' => __('Same Mobile Number', 'digoldaccntmr'), 'data' => $duplicates);
    }


    dig_showResponse(true, (!empty($data) ? json_encode($data) : '-1'));
}

function digoldacc_migration_error($user_id)
{
    $user = get_user_by('ID', $user_id);
    $email = $user->user_email;

    return array(dig_oldacc_user_link($user->user_login, $email, $user_id));
}

function dig_oldacc_user_link($username, $email, $user_id)
{
    if (!empty($email)) {
        $email = '<span>(' . $email . ')</span>';
    }

    $edit_link = get_edit_user_link($user_id);

    $user_info = '<a href="' . $edit_link . '" class="dig_conf_prof_det" target="_blank">' . $username . ' ' . $email . '</a><br />';;

    return $user_info;
}


$digoldaccntmrUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://bridge.unitedover.com/updates/changelog/addons.php?addon=digoldaccntmr',
    __FILE__,
    'digoldaccntmr'
);

$digoldaccntmrUpdateChecker->addQueryArgFilter('digoldaccntmr_filter_update_checks');
function digoldaccntmr_filter_update_checks($queryArgs)
{


    $queryArgs['license_key'] = get_site_option('dig_purchasecode');


    $queryArgs['request_site'] = network_home_url();

    $queryArgs['license_type'] = get_site_option('dig_license_type', 1);

    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];

    $queryArgs['version'] = $plugin_version;


    return $queryArgs;
}

function digoldaccntmrls_addon($list)
{
    $list[] = 'digoldaccntmrls';

    return $list;
}

add_filter('digits_addon', 'digoldaccntmrls_addon');


function digoldaccntmrls_addon_tab($tabs)
{
    $tabs['digoldaccntmr'] = array('label' => esc_attr__('Old Account Migrator', 'digits'));
    return $tabs;
}

add_filter('digits_admin_addon_tab', 'digoldaccntmrls_addon_tab');


add_action('wp_footer', function () {

    $digpc = get_site_option('dig_purchasecode');
    if (empty($digpc)) {
        return;
    }


    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();


    if (isset($_POST['mobmail']) && isset($_POST['dig_old_mcr']) && isset($_POST['countrycode'])) {
        $phone = sanitize_mobile_field_dig($_POST['mobmail']);


        $otp = sanitize_text_field($_POST['dig_otp']);
        $code = sanitize_text_field($_POST['code']);
        $csrf = sanitize_text_field($_POST['csrf']);
        $countrycode = sanitize_text_field($_POST['countrycode']);
        if (!empty($code)) {
            if (!wp_verify_nonce($csrf, 'crsf-otp')) {
                return;
            }
        }

        $digit_tapp = get_option('digit_tapp', 1);
        if ($digit_tapp == 1) {

            $json = getUserPhoneFromAccountkit($code);
            $phoneJson = json_decode($json, true);

            $countrycode = $phoneJson['countrycode'];
            $mob = $phoneJson['phone'];
            $phone = $phoneJson['nationalNumber'];
        } else {

            if (verifyOTP($countrycode, $phone, $otp, true)) {
                $mob = $countrycode . $phone;
            } else {
                $mob = null;
            }
        }


        if (!empty($mob)) {
            $tempUser = getUserFromPhone($mob);
            if ($tempUser == null) {

                update_user_meta($user_id, 'digits_phone', $mob);
                update_user_meta($user_id, 'digt_countrycode', $countrycode);
                update_user_meta($user_id, 'digits_phone_no', $phone);
            }
        }


    }


    $check_dig_mobile = get_user_meta($user_id, 'digits_phone', true);
    if ($check_dig_mobile != null) {
        return;
    }


    $digad_show_oldacctmr_values = digad_show_oldacctmr_values();
    $add_phone_old_users = $digad_show_oldacctmr_values['add_phone_old_users'];
    $add_phone_force = $digad_show_oldacctmr_values['add_phone_force'];
    $when_to_show = $digad_show_oldacctmr_values['when_to_show'];


    $dig_phn_migration = 0;
    if (isset($_SESSION['dig_phn_migration'])) {
        $dig_phn_migration = 1;
    }

    if ($add_phone_force == 0 && $dig_phn_migration == 1) {
        return;
    }


    if ($when_to_show == 1 && $add_phone_force) {
        $when_to_show = 0;
    }

    $shownToUser = get_user_meta($user_id, 'digits_migrate_shown', true);

    if ($when_to_show == 1 && $shownToUser == 1) {
        return;
    }


    dig_old_accnt_mr_popup($add_phone_force);

    $_SESSION['dig_phn_migration'] = 1;
    update_user_meta($user_id, 'digits_migrate_shown', 1);
});

function digoldacc_del_session()
{
    if (session_id() != '' && isset($_SESSION)) {
        unset($_SESSION['dig_phn_migration']);
    }
}

add_action('wp_logout', 'digoldacc_del_session');


function dig_old_accnt_mr_popup($add_phone_force)
{
    $theme = "dark";
    $themee = "lighte";
    $bgtype = "bgdark";
    $bgtransbordertype = "bgtransborderdark";


    $dig_main_re = "dig-modal-con-reno";

    $userCountryCode = getUserCountryCode();

    $page_type = 1;

    $color = get_option('digit_color_modal');

    if (isset($color['type'])) {
        $page_type = $color['type'];
    }
    ?>
    <style>

        .dig_sml_box_msg {
            line-height: 18px;
        }

        .dig-content {
            min-height: 420px;
            padding: 14px 0;
            height: 0 !important;
        }

        .dig_login_cancel a {
            width: 100%;
            position: relative;
            color: #fff !important;
        }

        .dig_bx_cnt_mdl {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dig_verify_mobile_otp_container {
            height: unset !important;
            min-height: unset !important;
        }

        .dig_verify_mobile_otp_container .dig_verify_code_contents .minput label {
            display: none;
        }

        .dig_verify_mobile_otp_container, .dig_verify_mobile_otp {
            width: 100%;
        }

        .dig_oldaccntmr_box .digloginpage {
            padding: 0 12px;
        }

        <?php
            do_action('digits_custom_css');
        ?>
    </style>
    <div class="digits_login_form">

        <div id="dig-ucr-container" <?php if ($add_phone_force == 1) {
            echo 'force="1"';
        } ?> class="<?php if (is_rtl()) {
            echo 'dig_rtl';
        } ?> digits_modal_box dig_lrf_box dig_ma-box dig-box <?php echo $dig_main_re;
        if ($page_type == 1) echo 'dig_pgmdl_1'; else if ($page_type == 2) {
            echo ' dig_pgmdl_2';
        } ?> dig_oldaccntmr_box" style="display:block;">


            <div class="dig-content dig-modal-con<?php echo ' ' . $theme; ?>">
                <div class="dig_bx_cnt_mdl">

                    <div class="dig-log-par digloginpage">
                        <?php
                        $emailmob = __("Mobile Number", "digits");
                        dig_verify_otp_box();
                        ?>
                        <div
                                class="digloginpage">

                            <div class="dig_sml_box_msg_head"><?php _e('Add Phone Number', 'digits'); ?></div>

                            <div class="dig_sml_box_msg">
                                <?php _e('You will be able to login using this mobile number and OTP passcode from next time.', 'digits'); ?>
                                <br/><br/>
                                <?php _e('No need to remember your password.', 'digits'); ?>
                            </div>


                            <form method="post">
                                <input type="hidden" name="code" class="digits_code"/>
                                <input type="hidden" name="csrf" class="digits_csrf"/>


                                <div class="minput">
                                    <div class="minput_inner">
                                        <div class="digits-input-wrapper">
                                            <input type="text" name="mobmail"
                                                   class="mobile_field mobile_format dig-mobmail"
                                                   value="" required/>
                                        </div>
                                        <div class="countrycodecontainer logincountrycodecontainer">
                                            <input type="text" name="countrycode"
                                                   class="input-text countrycode logincountrycode <?php echo $theme; ?>"
                                                   value="<?php if (isset($countrycode)) {
                                                       echo $countrycode;
                                                   } else {
                                                       echo $userCountryCode;
                                                   } ?>"
                                                   maxlength="6" size="3"
                                                   placeholder="<?php echo $userCountryCode; ?>"/>
                                        </div>

                                        <label><?php echo $emailmob; ?></label>
                                        <span class="<?php echo $bgtype; ?>"></span>
                                    </div>
                                </div>


                                <?php
                                $digit_tapp = get_option("digit_tapp", 1);
                                if ($digit_tapp > 1) {
                                    ?>
                                    <div class="minput dig_login_otp" style="display: none;">
                                        <div class="minput_inner">
                                            <input type="text" name="dig_otp" class="dig-login-otp"
                                                   autocomplete="one-time-code"/>
                                            <label><?php _e('OTP', 'digits'); ?></label>
                                            <span class="<?php echo $bgtype; ?>"></span>
                                        </div>
                                    </div>
                                    <?php
                                }


                                ?>


                                <input type="hidden" name="dig_nounce" class="dig_nounce"
                                       value="<?php echo wp_create_nonce('dig_form') ?>">


                                <input type="hidden" name="dig_old_mcr" value="1">


                                <div
                                        class="dig_verify_mobile_no <?php echo $themee; ?> <?php echo $bgtype; ?> button"><?php _e('ADD', 'digits'); ?></div>
                                <?php if ($digit_tapp > 1) {
                                    echo "<div  class=\"dig_resendotp dig_logof_log_resend dig_lo_resend_otp_btn\" dis='1'> " . __('Resend OTP', 'digits') . "<span>(00:<span>" . dig_getOtpTime() . "</span>)</span></div>";
                                } ?>
                                <?php


                                ?>
                            </form>


                            <?php
                            if ($add_phone_force == 0) {
                                ?>
                                <div class="dig_login_cancel dig_login_cancel_mgr" style="top:12px; position:relative;">
                                    <a href="#" class="dig-cont-close"><span><?php _e("Cancel", "digits"); ?></span></a>
                                </div>
                                <?php
                            }
                            ?>

                        </div>


                    </div>

                </div>


            </div>
        </div>
    </div>
    <?php
}

function digoldaccntmr_getCountryCode($country)
{
    if (is_numeric($country)) {
        return $country;
    }
    $country_codes = array(
        "AF" => "93",
        "AL" => "355",
        "DZ" => "213",
        "AS" => "1",
        "AD" => "376",
        "AO" => "244",
        "AI" => "1",
        "AQ" => "672",
        "AG" => "1",
        "AR" => "54",
        "AM" => "374",
        "AW" => "297",
        "AU" => "61",
        "AT" => "43",
        "AZ" => "994",
        "BS" => "1",
        "BH" => "973",
        "BD" => "880",
        "BB" => "1",
        "BY" => "375",
        "BE" => "32",
        "BZ" => "501",
        "BJ" => "229",
        "BM" => "1",
        "BT" => "975",
        "BO" => "591",
        "BA" => "387",
        "BW" => "267",
        "BR" => "55",
        "IO" => "246",
        "VG" => "1",
        "BN" => "673",
        "BG" => "359",
        "BF" => "226",
        "BI" => "257",
        "KH" => "855",
        "CM" => "237",
        "CA" => "1",
        "CV" => "238",
        "KY" => "1",
        "CF" => "236",
        "TD" => "235",
        "CL" => "56",
        "CN" => "86",
        "CX" => "61",
        "CC" => "61",
        "CO" => "57",
        "KM" => "269",
        "CK" => "682",
        "CR" => "506",
        "HR" => "385",
        "CU" => "53",
        "CW" => "599",
        "CY" => "357",
        "CZ" => "420",
        "CD" => "243",
        "DK" => "45",
        "DJ" => "253",
        "DM" => "1",
        "DO" => "1",
        "TL" => "670",
        "EC" => "593",
        "EG" => "20",
        "SV" => "503",
        "GQ" => "240",
        "ER" => "291",
        "EE" => "372",
        "ET" => "251",
        "FK" => "500",
        "FO" => "298",
        "FJ" => "679",
        "FI" => "358",
        "FR" => "33",
        "PF" => "689",
        "GA" => "241",
        "GM" => "220",
        "GE" => "995",
        "DE" => "49",
        "GH" => "233",
        "GI" => "350",
        "GR" => "30",
        "GL" => "299",
        "GD" => "1",
        "GU" => "1",
        "GT" => "502",
        "GG" => "44",
        "GN" => "224",
        "GW" => "245",
        "GY" => "592",
        "HT" => "509",
        "HN" => "504",
        "HK" => "852",
        "HU" => "36",
        "IS" => "354",
        "IN" => "91",
        "ID" => "62",
        "IR" => "98",
        "IQ" => "964",
        "IE" => "353",
        "IM" => "44",
        "IL" => "972",
        "IT" => "39",
        "CI" => "225",
        "JM" => "1",
        "JP" => "81",
        "JE" => "44",
        "JO" => "962",
        "KZ" => "7",
        "KE" => "254",
        "KI" => "686",
        "XK" => "383",
        "KW" => "965",
        "KG" => "996",
        "LA" => "856",
        "LV" => "371",
        "LB" => "961",
        "LS" => "266",
        "LR" => "231",
        "LY" => "218",
        "LI" => "423",
        "LT" => "370",
        "LU" => "352",
        "MO" => "853",
        "MK" => "389",
        "MG" => "261",
        "MW" => "265",
        "MY" => "60",
        "MV" => "960",
        "ML" => "223",
        "MT" => "356",
        "MH" => "692",
        "MR" => "222",
        "MU" => "230",
        "YT" => "262",
        "MX" => "52",
        "FM" => "691",
        "MD" => "373",
        "MC" => "377",
        "MN" => "976",
        "ME" => "382",
        "MS" => "1",
        "MA" => "212",
        "MZ" => "258",
        "MM" => "95",
        "NA" => "264",
        "NR" => "674",
        "NP" => "977",
        "NL" => "31",
        "AN" => "599",
        "NC" => "687",
        "NZ" => "64",
        "NI" => "505",
        "NE" => "227",
        "NG" => "234",
        "NU" => "683",
        "KP" => "850",
        "MP" => "1",
        "NO" => "47",
        "OM" => "968",
        "PK" => "92",
        "PW" => "680",
        "PS" => "970",
        "PA" => "507",
        "PG" => "675",
        "PY" => "595",
        "PE" => "51",
        "PH" => "63",
        "PN" => "64",
        "PL" => "48",
        "PT" => "351",
        "PR" => "1",
        "QA" => "974",
        "CG" => "242",
        "RE" => "262",
        "RO" => "40",
        "RU" => "7",
        "RW" => "250",
        "BL" => "590",
        "SH" => "290",
        "KN" => "1",
        "LC" => "1",
        "MF" => "590",
        "PM" => "508",
        "VC" => "1",
        "WS" => "685",
        "SM" => "378",
        "ST" => "239",
        "SA" => "966",
        "SN" => "221",
        "RS" => "381",
        "SC" => "248",
        "SL" => "232",
        "SG" => "65",
        "SX" => "1",
        "SK" => "421",
        "SI" => "386",
        "SB" => "677",
        "SO" => "252",
        "ZA" => "27",
        "KR" => "82",
        "SS" => "211",
        "ES" => "34",
        "LK" => "94",
        "SD" => "249",
        "SR" => "597",
        "SJ" => "47",
        "SZ" => "268",
        "SE" => "46",
        "CH" => "41",
        "SY" => "963",
        "TW" => "886",
        "TJ" => "992",
        "TZ" => "255",
        "TH" => "66",
        "TG" => "228",
        "TK" => "690",
        "TO" => "676",
        "TT" => "1",
        "TN" => "216",
        "TR" => "90",
        "TM" => "993",
        "TC" => "1",
        "TV" => "688",
        "VI" => "1",
        "UG" => "256",
        "UA" => "380",
        "AE" => "971",
        "GB" => "44",
        "US" => "1",
        "UY" => "598",
        "UZ" => "998",
        "VU" => "678",
        "VA" => "379",
        "VE" => "58",
        "VN" => "84",
        "WF" => "681",
        "EH" => "212",
        "YE" => "967",
        "ZM" => "260",
        "ZW" => "263"
    );

    $country_codes = array_merge($country_codes, getCountryList());

    return $country_codes[$country];
}

function digoldaccntmr_filter_mobile($countrycode, $user_mob_o_wc)
{
    if (str_replace('+', '', $countrycode) == '242') {
        if (substr($user_mob_o_wc, 0, 1) == '0') {
            $user_mob_o_wc = '0' . $user_mob_o_wc;
        }
    }

    return $user_mob_o_wc;
}

function digoldaccntmr_filter_countrycode($countrycode)
{
    if (strpos($countrycode, "+") !== 0) {
        $countrycode = '+' . $countrycode;
    }

    return $countrycode;
}