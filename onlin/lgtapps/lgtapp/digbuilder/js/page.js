(function () {
    jQuery(window).on('elementor:init', function () {

        jQuery(document).on('change', '.elementor-control-type select, .elementor-control-sub_type select', function () {
            var row = jQuery(this).closest('.elementor-repeater-row-controls');
            var value = row.find('.elementor-control-type select').val();
            if (value === 'custom') {
                var sub_type = row.find('.elementor-control-sub_type');
                if (sub_type.length) {
                    var sub_value = sub_type.find('select').val();
                    var random = sub_value + '_' + jQuery.now();
                    update_element(row.find('.elementor-control-meta_key'), random);
                    if (sub_value == 'tac') {
                        update_element(row.find('.elementor-control-label'), digbuilder.terms_label);
                    }else{
                        update_element(row.find('.elementor-control-label'), sub_value);
                    }
                }
            }
        });

        jQuery(document).on('change', '.elementor-control-field_wc_type select,.elementor-control-field_wp_type select', function () {
            var row = jQuery(this).closest('.elementor-repeater-row-controls');
            var value = jQuery(this).find('option:selected').text();

            update_element(row.find('.elementor-control-label'), value);
        });

        jQuery(document).on('change', '.elementor-control-label input,.elementor-control-meta_key input', function () {
            jQuery(this).removeAttr('data-auto');
        });

        function update_element(elem, value) {
            if (elem.length) {
                elem = elem.find('input');
                if (elem.is(":visible")) {
                    //if (elem.val().length == 0 || elem.attr('data-auto') == 1)
                    elem.val(value).attr('data-auto', '1').trigger('input');
                }
            }
        }

        elementor.settings.page.model.on('change', function (e) {

            var page = document.getElementById('elementor-preview-iframe');
            var pageContents = page.contentDocument || page.contentWindow.document;
            var anim_elem_class = 'digits-popup-anim';

            if (e.changed.hasOwnProperty('entrance_animation_type')) {
                var anim_type = e.changed['entrance_animation_type'];
                var anim_elem = jQuery('.' + anim_elem_class, pageContents);
                anim_elem.attr('class', '')
                    .addClass(anim_elem_class + ' animated ' + anim_type + ' ' + anim_elem.data('animation-speed'))
                    .data('animation-type', anim_type);
            }
            if (e.changed.hasOwnProperty('entrance_animation_speed')) {
                var anim_speed = e.changed['entrance_animation_speed'];

                var anim_elem = jQuery('.' + anim_elem_class, pageContents);
                anim_elem.attr('class', '').addClass(anim_elem_class + ' animated ' + anim_elem.data('animation-type') + ' ' + anim_speed)
                    .data('animation-speed', anim_speed);
                reset_anim(anim_elem);
            }

            if (e.changed.hasOwnProperty('close_button_icon')) {
                var close_button = jQuery(".digits-popup-close-button", pageContents);
                var library = e.changed['close_button_icon'].library;
                var new_icon;
                if (library == 'svg') {
                    new_icon = e.changed['close_button_icon'].value.url;
                    new_icon = '<img src="' + new_icon + '">';
                } else {
                    new_icon = e.changed['close_button_icon'].value;
                    new_icon = '<i class="' + new_icon + '"></i>';
                }
                close_button.empty().html(new_icon);


            }

        });

        function reset_anim($elem) {
            $elem.before($elem.clone(true));
            var $newElem = $elem.prev();
            $elem.remove();
            return $newElem;
        }


        elementor.hooks.addAction('panel/open_editor/widget', function (panel, model, view) {
            function update_digits() {
                document.getElementById('elementor-preview-iframe').contentWindow.jQuery('body').trigger('update_digits');
            };

            elementorFrontend.hooks.addAction('frontend/element_ready/login-register.default', function ($scope) {
                update_digits();
            });
            elementorFrontend.hooks.addAction('frontend/element_ready/register-only.default', function ($scope) {
                update_digits();
            });
            elementorFrontend.hooks.addAction('frontend/element_ready/login-only.default', function ($scope) {
                update_digits();
            });
            elementorFrontend.hooks.addAction('frontend/element_ready/forgot-pass.default', function ($scope) {
                update_digits();
            });
        });
    });

}(jQuery));