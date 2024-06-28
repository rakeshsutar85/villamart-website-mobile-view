jQuery(function () {

    var loader = jQuery(".dig_load_overlay");

    var migration_box = jQuery('#dig_account_migration_content');

    var dig_migration_details = migration_box.find('.dig_migration_details');
    var dig_migration_success = migration_box.find('.dig_migration_success');

    var dig_migration_conflicted = migration_box.find('.dig_migration_conflicted');


    jQuery(".digoldaccnt_open_migrator").on('click', function () {
        migration_box.show();
    })

    jQuery(".dig_oldaccntmr_copy_from").on('change', function () {
        var key = jQuery('option:selected', this).attr('data-key');


        if (key == undefined) return;

        var modify_key = jQuery(this).attr('data-modify');

        var meta_key_input = jQuery("." + modify_key);

        var trigger = jQuery(this).attr('data-trigger');

        meta_key_input.val(key);

        if (key.length > 0) {
            meta_key_input.attr('readonly', true);
            jQuery("." + trigger).hide();
        } else {
            jQuery("." + trigger).show();
            meta_key_input.removeAttr('readonly');
        }
    }).trigger('change');

    jQuery(".digoldaccntmr_user_demographics").on('change', function () {
        var selected = jQuery(this).find('option:selected').data('show');
        var value = jQuery(this).val();
        jQuery('.digoldaccntmr-country-code_fields').hide();
        jQuery('.' + selected).show().find('select').trigger('change');
    });

    var migrator_btn = jQuery("#dig_old_accnt_run_migrator");

    var mgr_btn_status = 0;
    migrator_btn.on('click', function () {

        if (mgr_btn_status == -1) {
            migration_box.find(".dig_presets_modal_head_close").trigger('click');
            return;
        } else if (mgr_btn_status == 1) {
            migration_box.find("#dig_presets_modal_body").css('min-height', '40vh');
            migration_box.find("#dig_presets_modal_body").css('max-height', '60vh');
            migration_box.find("#dig_presets_modal_box").css('bottom', '18vh');
            migration_box.find(".modal_head").text('CONFLICTED ACCOUNTS');
            migration_box.find(".dig_presets_modal_head_close").hide();
            migration_box.find(".dig_ex_imp_bottom").removeClass('dig_ex_imp_bottom');
            migrator_btn.text('Close');
            dig_migration_success.hide();
            dig_migration_conflicted.show();
            mgr_btn_status = -1;


            return;
        }


        var data = migration_box.find("input, select").serialize();

        loader.show();

        var nonce = migration_box.find(".dig_old_accnt_nonce").val();
        jQuery.ajax({
            type: "POST",
            url: dig_oamtr.ajax_url,
            data: data + '&action=dig_migrate_user_database&nounce=' + nonce,
            success: function (data) {
                loader.hide();
                if (data.success === true) {
                    dig_migration_details.hide();
                    dig_migration_success.css('display', 'flex');
                    if (data.msg == -1) {
                        makeMgrtoClose();
                    } else {
                        mgr_btn_status = 1;
                        migrator_btn.text('View Issues');

                        var dig_old_conflict_accounts = jQuery(".dig_old_conflict_accounts");
                        var i = 1;
                        jQuery.each(JSON.parse(data.msg), function (index, response) {
                            var type = response.type;
                            jQuery.each(response.data, function (index, data) {

                                var details = '';
                                jQuery.each(data, function (index, data) {
                                    details = details + data;
                                });
                                dig_old_conflict_accounts.append(
                                    '<tr>' +
                                    '<td>' + i + '</td>' +
                                    '<td>' + details + '</td>' +
                                    '<td>' + type + '</td>' +
                                    '</tr>'
                                );
                                i++;
                            })


                        })

                    }
                }
            },
            error: function () {
                loader.hide();
            }
        });


    })

    function lockScroll() {
        $html = jQuery('html');
        $body = jQuery('body');
        var initWidth = $body.outerWidth();
        var initHeight = $body.outerHeight();

        var scrollPosition = [
            self.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft,
            self.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop
        ];
        $html.data('scroll-position', scrollPosition);
        $html.data('previous-overflow', $html.css('overflow'));
        $html.css('overflow', 'hidden');
        window.scrollTo(scrollPosition[0], scrollPosition[1]);

        var marginR = $body.outerWidth() - initWidth;
        var marginB = $body.outerHeight() - initHeight;
        $body.css({'margin-right': marginR, 'margin-bottom': marginB});
    }


    function makeMgrtoClose() {
        migration_box.find('.dig_presets_modal_head_close').hide();
        migrator_btn.text('Close');

        mgr_btn_status = -1;
    }

});