<?php


if (!defined('ABSPATH')) {
    exit;
}

add_filter('woocommerce_billing_fields', 'digmergphn_remove_billing_phone_field', 100, 1);
function digmergphn_remove_billing_phone_field($fields)
{
    $dig_sync_acc_bill_fields = get_option('dig_sync_acc_bill_phone', 0);
    if ($dig_sync_acc_bill_fields == 1) {
        if (is_checkout()) {
            if (is_user_logged_in()) {

                $phone = get_user_meta(get_current_user_id(), 'digits_phone_no', true);
                if (!empty($phone)) {
                    $fields['billing_phone']['custom_attributes'] = array('readonly' => 'readonly');
                    $fields['billing_phone']['default'] = $phone;
                }
            }
        } else {
            unset($fields['billing_phone']);
        }
    }
    return $fields;
}

function digmergphn_update_checkout_billing_field($value)
{
    $dig_sync_acc_bill_fields = get_option('dig_sync_acc_bill_phone', 0);
    if ($dig_sync_acc_bill_fields == 1) {
        if (is_checkout()) {
            if (is_user_logged_in()) {
                $phone = get_user_meta(get_current_user_id(), 'digits_phone_no', true);
                return $phone;
            }
        }
    }
    return $value;
}

add_filter('woocommerce_process_checkout_field_billing_phone', 'digmergphn_update_checkout_billing_field');

add_filter('woocommerce_process_myaccount_field_billing_phone', 'digmergphn_return_billing_phone', 100);
function digmergphn_return_billing_phone($value)
{
    $dig_sync_acc_bill_fields = get_option('dig_sync_acc_bill_phone', 0);
    if ($dig_sync_acc_bill_fields == 1) {
        $user_id = get_current_user_id();
        $phone = get_user_meta($user_id, 'digits_phone_no', true);
        if (!empty($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone);
        }
        return $phone;
    }
    return $value;
}

add_action('wc_digits_account_updated', 'digmergphn_account_updated');
function digmergphn_account_updated($user_id)
{
    $dig_sync_acc_bill_fields = get_option('dig_sync_acc_bill_phone', 0);
    if ($dig_sync_acc_bill_fields == 1) {
        $phone = get_user_meta($user_id, 'digits_phone_no', true);
        if (!empty($phone)) {
            update_user_meta($user_id, 'billing_phone', $phone);
        }
    }
}
