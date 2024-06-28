jQuery(document).ready(function () {
    'use strict';
    jQuery('.tab:not(.vi-wcuf-tab-rule)').each(function () {
        let tab = jQuery(this);
        tab.find('.vi-ui.checkbox').checkbox();
        tab.find('.vi-ui.dropdown').dropdown();
        tab.find('.vi-ui.accordion').villatheme_accordion('refresh');
        tab.find('input[type="checkbox"]').unbind().on('change', function () {
            if (jQuery(this).prop('checked')) {
                jQuery(this).parent().find('input[type="hidden"]').val('1');
                if (jQuery(this).hasClass('vi-wcuf-us_mobile_enable-checkbox')) {
                    jQuery('.vi-wcuf-us_mobile_enable-enable').removeClass('vi-wcuf-hidden');
                }
            } else {
                jQuery(this).parent().find('input[type="hidden"]').val('');
                if (jQuery(this).hasClass('vi-wcuf-us_mobile_enable-checkbox')) {
                    jQuery('.vi-wcuf-us_mobile_enable-enable').addClass('vi-wcuf-hidden');
                }
            }
        });
        tab.find('.vi-wcuf-us_desktop_style').dropdown({
            onChange: function (val) {
                let mobile_enable = jQuery('.vi-wcuf-us_mobile_enable-checkbox').prop('checked'),
                    mobile_val = jQuery('.vi-wcuf-us_mobile_style').dropdown('get value');
                jQuery('.vi-wcuf-us_redirect_page_endpoint-wrap, .vi-wcuf-us_desktop_position-wrap').addClass('vi-wcuf-hidden');
                if (val === '1') {
                    jQuery('.vi-wcuf-us_desktop_position-wrap').removeClass('vi-wcuf-hidden');
                }
                if (val === '3' || (mobile_enable && mobile_val === '3')) {
                    jQuery('.vi-wcuf-us_redirect_page_endpoint-wrap').removeClass('vi-wcuf-hidden');
                }
            }
        });
        tab.find('.vi-wcuf-us_mobile_style').dropdown({
            onChange: function (val) {
                let desktop_val = jQuery('.vi-wcuf-us_desktop_style').dropdown('get value');
                jQuery('.vi-wcuf-us_redirect_page_endpoint-wrap, .vi-wcuf-us_mobile_position-wrap').addClass('vi-wcuf-hidden');
                if (val === '1') {
                    jQuery('.vi-wcuf-us_mobile_position-wrap').removeClass('vi-wcuf-hidden');
                }
                if (desktop_val === '3' || val === '3') {
                    jQuery('.vi-wcuf-us_redirect_page_endpoint-wrap').removeClass('vi-wcuf-hidden');
                }
            }
        });
        tab.find('.vi-wcuf-us_pd_template').dropdown({
            onChange: function (val) {
                if (val === '2') {
                    jQuery('.vi-wcuf-us_pd_atc').addClass('vi-wcuf-hidden');
                    jQuery('.vi-wcuf-us_pd_atc_checkbox').removeClass('vi-wcuf-hidden');
                } else {
                    jQuery('.vi-wcuf-us_pd_atc').removeClass('vi-wcuf-hidden');
                    jQuery('.vi-wcuf-us_pd_atc_checkbox').addClass('vi-wcuf-hidden');
                }
            }
        });
        tab.find('.vi-wcuf-us_content').unbind().on('keyup', function () {
            let val = jQuery(this).val();
            var temp = val.split('{content}');
            jQuery(this).removeClass('vi-wcuf-warning-wrap');
            if (temp.length < 2) {
                jQuery(this).addClass('vi-wcuf-warning-wrap');
                jQuery(this).parent().find('.vi-wcuf-warning-message').removeClass('vi-wcuf-hidden');
            } else if (!tab.find('.vi-wcuf-us_content.vi-wcuf-warning-wrap').length) {
                jQuery(this).parent().find('.vi-wcuf-warning-message').addClass('vi-wcuf-hidden');
            }
        });
        viwcuf_set_value_number(tab);
        viwcuf_color_picker(tab);
    });
    //before save settings
    jQuery('.vi-wcuf-save:not(.loading)').on('click', function () {
        jQuery('.vi-wcuf-save').addClass('loading');
        if (!jQuery('input[name="us_redirect_page_endpoint"]').val() && !jQuery('.vi-wcuf-redirect_page_endpoint-wrap').hasClass('vi-wcuf-hidden')) {
            alert('Suggest page cannot be empty!');
            jQuery('.vi-wcuf-save').removeClass('loading');
            return false;
        }
        jQuery('.vi-wcuf-accordion-wrap').removeClass('vi-wcuf-accordion-wrap-warning');
        let nameArr = jQuery('input[name="us_names[]"]');
        let z, v;
        for (z = 0; z < nameArr.length; z++) {
            if (!nameArr.eq(z).val()) {
                alert('Name cannot be empty!');
                jQuery('.vi-wcuf-accordion-' + z).addClass('vi-wcuf-accordion-wrap-warning');
                jQuery('.vi-wcuf-save').removeClass('loading');
                return false;
            }
        }

        for (z = 0; z < nameArr.length - 1; z++) {
            for (v = z + 1; v < nameArr.length; v++) {
                if (nameArr.eq(z).val() === nameArr.eq(v).val()) {
                    alert("Names are unique!");
                    jQuery('.vi-wcuf-accordion-' + v).addClass('vi-wcuf-accordion-wrap-warning');
                    jQuery('.vi-wcuf-save').removeClass('loading');
                    return false;
                }
            }
        }
        jQuery(this).attr('type', 'submit');
    });

    //Auto update
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = jQuery.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
});
viwcuf_rule_init.prototype.change_value = function (rule) {
    rule.find('.vi-wcuf-us_names').unbind().on('keyup', function () {
        jQuery(this).closest('.vi-wcuf-accordion-wrap').find('.vi-wcuf-accordion-name').html(jQuery(this).val());
    });
    viwcuf_set_value_number(rule);
};
viwcuf_rule_child_init.prototype.change_value = function (condition) {
    viwcuf_set_value_number(condition);};
viwcuf_rule_init.prototype.checkbox = function (rule) {
    rule.find('input[type="checkbox"]').unbind().on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery(this).parent().find('input[type="hidden"]').val('1');
        } else {
            jQuery(this).parent().find('input[type="hidden"]').val('');
        }
    });
};
viwcuf_rule_init.prototype.dropdown = function (rule) {
    rule.find('.vi-wcuf-us_discount_type').dropdown({
        onChange: function (val) {
            val = parseInt(val);
            rule.find('.vi-wcuf-discount-amount-notice').addClass('vi-wcuf-hidden');
            rule.find('.vi-wcuf-discount-amount-notice.vi-wcuf-discount-amount-notice-'+val).removeClass('vi-wcuf-hidden');
            if (val) {
                let max = val===1 || val === 3 ? 100 : '';
                jQuery(this).parent().find('.vi-wcuf-us_discount_amount').removeClass('vi-wcuf-hidden').attr('max',max);
            } else {
                jQuery(this).parent().find('.vi-wcuf-us_discount_amount').addClass('vi-wcuf-hidden');
            }
        }
    });
    rule.find('.vi-wcuf-us_product_type').dropdown({
        onChange: function (val) {
            if (val == 13) {
                let check = false;
                rule.find('.vi-wcuf-pd-condition-product_rule_type').each(function () {
                    if (jQuery(this).dropdown('get value') == 'product_include') {
                        check = true;
                        return false;
                    }
                });
                if (!check) {
                    rule.find('.vi-wcuf-pd_rule-add-condition').trigger('click');
                    rule.find('.vi-wcuf-pd-condition-product_rule_type').last().dropdown('set selected', 'product_include');
                }
            }
        }
    });
};