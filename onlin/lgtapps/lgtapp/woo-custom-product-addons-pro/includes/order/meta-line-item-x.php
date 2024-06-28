<?php
use Acowebs\WCPA;

if (is_array($meta_data) && count($meta_data)) {
    ?>
    <table>
        <tr>
            <th><?php _e('Options', 'woo-custom-product-addons-pro') ?></th>
            <th><?php _e('Value', 'woo-custom-product-addons-pro') ?></th>
            <th><?php _e('Cost', 'woo-custom-product-addons-pro') ?></th>
            <th></th>
        </tr>

        <?php

        foreach ($meta_data as $sectionKey => $section) {
            $form_rules = $section['extra']->form_rules;
            foreach ($section['fields'] as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    if (in_array($field['type'], array('checkbox-group',
                            'select',
                            'radio-group',
                            'image-group',
                            'color-group',
                            'productGroup'
                        )) && is_array($field['value'])) {
                        $label_printed = false;
                        foreach ($field['value'] as $l => $v) {
                            ?>
                            <tr class="item_wcpa">
                                <td class="name">
                                    <?php
                                    echo $label_printed ? '' : $field['label'];
                                    $label_printed = true;
                                    ?>
                                </td>

                                <td class="value">
                                    <div class="view">
                                        <?php
                                        if ($field['type'] == 'image-group') {
                                            echo '<strong>' . __('Label:', 'woo-custom-product-addons-pro') . '</strong> ' . __($v['label'], 'woo-custom-product-addons-pro') . '<br>';
                                            if (isset($v['image']) && $v['image'] !== FALSE) {
                                                $img_size_style = ((isset($field['form_data']->disp_size_img) && $field['form_data']->disp_size_img > 0) ? 'style="width:' . $field['form_data']->disp_size_img . 'px"' : '');

                                                echo ' <img class="wcpa_img" ' . $img_size_style . '  src="' . $v['image'] . '" />';
                                            } else
                                                if (isset($v['value']) && $v['value'] !== FALSE) {
                                                    echo ' ' . $v['value'];
                                                }
                                        } else if ($field['type'] == 'productGroup') {
                                            if ($v) {
                                                $edit_url = admin_url('post.php?post=' . $v->get_id()) . '&action=edit';
                                                $pro_image = '';

                                                if ($v->get_image_id()) {
                                                    $pro_image = wp_get_attachment_url($v->get_image_id());
                                                }

                                                if ($pro_image == '') {
                                                    $pro_image = wc_placeholder_img_src('woocommerce_thumbnail');
                                                }
                                                echo "<div class='wcpa_order_details_meta_line_product'>";
                                                if ($pro_image && isset($field['form_data']->show_image) && $field['form_data']->show_image) {
                                                    $img_size_style = ((isset($field['form_data']->disp_size_img) && $field['form_data']->disp_size_img > 0) ? 'style="width:' . $field['form_data']->disp_size_img . 'px"' : '');

                                                    echo ' <img class="wcpa_img" ' . $img_size_style . '  src="' . $pro_image . '" />';
                                                }
                                                echo '<a href="' . $edit_url . '" target="_blank">' . $v->get_title() . '</a>';
                                                $quantity = (isset($field['quantities'][$l]) && !empty($field['quantities'][$l])) ? $field['quantities'][$l] : 1;
                                                echo '<span class="wcpa_productGroup_order_qty">x ' . $quantity . '</span>';
                                                echo "</div>";
                                            }
                                        } else if ($field['type'] == 'color-group') {
                                            echo '<strong>' . __('Label:', 'woo-custom-product-addons-pro') . '</strong> ' . __($v['label'], 'woo-custom-product-addons-pro') . '<br>';
                                            echo '<strong>' . __('Value:', 'woo-custom-product-addons-pro') . '</strong> ' . '<span style="color:' . $v['color'] . ';font-size: 20px;
                                                padding: 0;
                                        line-height: 0;">&#9632;</span>' . $v['value'];
                                        } else if (isset($v['i'])) {
                                            echo '<strong>' . __('Label:', 'woo-custom-product-addons-pro') . '</strong> ' . __($v['label'], 'woo-custom-product-addons-pro') . '<br>';
                                            echo '<strong>' . __('Value:', 'woo-custom-product-addons-pro') . '</strong> ' . $v['value'];
                                        } else {
                                            echo $v;
                                        }
                                        ?>

                                    </div>
                                    <div class="edit" style="display: none;">
                                        <?php
                                        if ($field['type'] == 'image-group') {
                                            ?>
                                            <?php echo '<strong>' . __('Label:', 'woo-custom-product-addons-pro') . '</strong>'; ?>
                                            <input type="text"
                                                   name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]"
                                                   value="<?php echo $v['label'] ?>"> <br>
                                            <?php
                                            if (isset($v['image']) && $v['image'] !== FALSE) {
                                                echo __('Value:', 'woo-custom-product-addons-pro') . '<input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                        value="' . $v['image'] . '">';
                                            } else
                                                if (isset($v['value']) && $v['value'] !== FALSE) {
                                                    echo __('Value:', 'woo-custom-product-addons-pro') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                        value="' . $v['value'] . '">';
                                                }
                                        } else if ($field['type'] == 'productGroup') {
                                            $qts = array();
                                            if (isset($field['quantities']) && !empty($field['quantities'])) {
                                                $qts = $field['quantities'];
                                            }
                                            $current_qty = isset($qts[$l]) ? $qts[$l] : 1;
                                            echo __('Product ID:', 'woo-custom-product-addons-pro') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][value]" 
                                    value="' . $v->get_id() . '">';
                                            echo __('Quantity:', 'woo-custom-product-addons-pro') . ' <input type="text" name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $l . '][quantity]" 
                                    value="' . $current_qty . '">';

                                        } else if (isset($v['i'])) {
                                            ?>
                                            <?php echo '<strong>' . __('Label:', 'woo-custom-product-addons-pro') . '</strong>'; ?>
                                            <input type="text"
                                                   name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][label]"
                                                   value="<?php echo $v['label'] ?>"> <br>
                                            <?php echo '<strong>' . __('Value:', 'woo-custom-product-addons-pro') . '</strong>'; ?>
                                            <input type="text"
                                                   name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>][value]"
                                                   value="<?php echo $v['value'] ?>">
                                            <?php
                                        } else {
                                            ?>
                                            <input type="text"
                                                   name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]"
                                                   value="<?php echo $v ?>">

                                        <?php }
                                        ?>


                                    </div>
                                </td>
                                <td class="item_cost" width="1%">

                                    <?php
                                    if (isset($field['form_data']->enablePrice) && $field['form_data']->enablePrice &&
                                        (!isset($field['is_fee']) || $field['is_fee'] === false)) {
                                        ?>
                                        <div class="view">
                                            <?php echo isset($field['price'][$l]) ? $field['price'][$l] : '0'; ?>
                                        </div>
                                        <div class="edit" style="display: none;">
                                            <input type="text"
                                                   data-price="<?php echo(isset($field['price'][$l]) ? $field['price'][$l] : '0') ?>"
                                                   class="wcpa_has_price"
                                                   name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>][<?php echo $l; ?>]"
                                                   value="<?php echo(isset($field['price'][$l]) ? $field['price'][$l] : '0'); ?>">
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </td>
                                </td>

                                <td class="wc-order-edit-line-item" width="1%">
                                    <div class="wc-order-edit-line-item-actions edit" style="display: none;">
                                        <a class="wcpa_delete-order-item tips" href="#"
                                           data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else if ($field['type'] == 'file') {
                        ?>
                        <tr class="item_wcpa">

                            <td class="name"><?php echo $field['label']; ?></td>
                            <td class="value">
                                <div class="view">
                                    <?php

                                        if (isset($field['value'])) {
                                            foreach ($field['value'] as $dt) {
                                                if (isset($dt['url'])) {
                                                    $display = '<a href="' . $dt['url'] . '"  target="_blank" download="' . $dt['file_name'] . '">';
                                                    if (in_array($dt['type'], array('image/jpg', 'image/png', 'image/gif', 'image/jpeg'))) {
                                                        $display .= '<img class="wcpa_img" style="max-width:100%;" src="' . $dt['url'] . '" />';
                                                    } else {
                                                        $display .= '<img class="wcpa_icon" src="' . wp_mime_type_icon($dt['type']) . '" />';
                                                    }
                                                    $display .= $dt['file_name'] . '</a>';
                                                    echo $display;
                                                } else {
                                                    echo $dt;
                                                }
                                            }
                                        }

                                    ?>
                                </div>
                                <div class="edit" style="display: none;">
                                    <?php
                                    if (isset($field['multiple']) && ($field['multiple'])) {
                                        if (isset($field['value'])) {
                                            $index = 0;
                                            foreach ($field['value'] as $dt) {
                                                if ($dt) {
                                                    echo '<strong>' . __('File URL:', 'woo-custom-product-addons-pro') . '</strong>';
                                                    if (isset($dt['url'])) {
                                                        echo '<input type="text" 
                                                    name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $index . ']"  value="' . $dt['url'] . '">';
                                                    } else {
                                                        echo '<input type="text" 
                                                    name="wcpa_meta[value][' . $item_id . '][' . $k . '][' . $index . ']"  value="' . $dt . '">';
                                                    }
                                                    $index++;
                                                }
                                            }
                                        }
                                    } else {
                                        if (isset($field['value']['url'])) {
                                            echo '<input type="text" 
                                name="wcpa_meta[value][' . $item_id . '][' . $k . ']"  value="' . $field['value']['url'] . '">';
                                        } else {
                                            echo '<input type="text" 
                                name="wcpa_meta[value][' . $item_id . '][' . $k . ']"  value="' . ($field['value']) . '">';
                                        }
                                    }
                                    ?>

                                </div>
                            </td>
                            <td class="item_cost" width="1%">
                                <?php
                                if (isset($field['form_data']->enablePrice) && $field['form_data']->enablePrice) {
                                    ?>
                                    <div class="view">
                                        <?php echo Acowebs\WCPA\wcpaPrice($field['price'], false, ['currency' => $order->get_currency()]); ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text"
                                               data-price="<?php echo $field['price']; ?>"
                                               class="wcpa_has_price"
                                               name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]"
                                               value="<?php echo $field['price'] ?>">
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>

                            <td class="wc-order-edit-line-item" width="1%">
                                <div class="wc-order-edit-line-item-actions edit" style="display: none;">
                                    <a class="wcpa_delete-order-item tips" href="#"
                                       data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    } else if ($field['type'] === 'placeselector') {
                        ?>
                        <tr class="item_wcpa">
                            <td class="name"><?php echo $field['label']; ?></td>
                            <td class="value">
                                <div class="view">
                                    <?php
                                    if (!empty($field['value']['formated'])) {
                                        $display = $field['value']['formated'] . '<br>';
                                        if (!empty($field['value']['splited']['street_number'])) {
                                            $display .= __('Street address:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['splited']['street_number'] . ' ' . $field['value']['splited']['route'] . ' <br>';
                                        }
                                        if (!empty($field['value']['splited']['locality'])) {
                                            $display .= __('City:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['splited']['locality'] . '<br>';
                                        }
                                        if (!empty($field['value']['splited']['administrative_area_level_1'])) {
                                            $display .= __('State:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['splited']['administrative_area_level_1'] . '<br>';
                                        }
                                        if (!empty($field['value']['splited']['postal_code'])) {
                                            $display .= __('Zip code:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['splited']['postal_code'] . '<br>';
                                        }
                                        if (!empty($field['value']['splited']['country'])) {
                                            $display .= __('Country:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['splited']['country'] . '<br>';
                                        }
                                        if (isset($field['value']['cords']['lat']) && !empty($field['value']['cords']['lat'])) {
                                            $display .= __('Latitude:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['cords']['lat'] . '<br>';
                                            $display .= __('Longitude:', 'woo-custom-product-addons-pro') . ' ' . $field['value']['cords']['lng'] . '<br>';
                                            $display .= '<a href="https://www.google.com/maps/?q=' . $field['value']['cords']['lat'] . ',' . $field['value']['cords']['lng'] . '" target="_blank">' . __('View on map', 'woo-custom-product-addons-pro') . '</a> <br>';
                                        }
                                        echo $display;
                                    }
                                    ?>
                                </div>
                                <div class="edit" style="display: none;">

                                    <input type="text"
                                           name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>][formated]"
                                           value="<?php echo $field['value']['formated'] ?>"> <br><br>
                                    <?php
                                    $name = 'wcpa_meta[value][' . $item_id . '][' . $k . ']';
                                    echo __('Street address:', 'woo-custom-product-addons-pro') . '
              <input type="text" class="street_number" name="' . $name . '[street_number]" value="' . (isset($field['value']['splited']['street_number']) ? $field['value']['splited']['street_number'] : '') . '"  >
             <input type="text" class="route" name="' . $name . '[route]" value="' . (isset($field['value']['splited']['route']) ? $field['value']['splited']['route'] : '') . '" > <br>
            ' . __('City:', 'woo-custom-product-addons-pro') . '<input  type="text" name="' . $name . '[locality]" value="' . (isset($field['value']['splited']['locality']) ? $field['value']['splited']['locality'] : '') . '" ><br>
           ' . __('State:', 'woo-custom-product-addons-pro') . '<input type="text"  name="' . $name . '[administrative_area_level_1]" value="' . (isset($field['value']['splited']['administrative_area_level_1']) ? $field['value']['splited']['administrative_area_level_1'] : '') . '" ><br>
            ' . __('Zip code:', 'woo-custom-product-addons-pro') . '<input type="text"  name="' . $name . '[postal_code]" value="' . (isset($field['value']['splited']['postal_code']) ? $field['value']['splited']['postal_code'] : '') . '"   ><br>
           ' . __('Country:', 'woo-custom-product-addons-pro') . '<input type="text" name="' . $name . '[country]" value="' . (isset($field['value']['splited']['country']) ? $field['value']['splited']['country'] : '') . '" ><br>
           ' . __('Latitude:', 'woo-custom-product-addons-pro') . '<input type="text"  name="' . $name . '[lat]" value="' . (isset($field['value']['cords']['lat']) ? $field['value']['cords']['lat'] : '') . '" ><br>
           ' . __('Longitude:', 'woo-custom-product-addons-pro') . '<input  type="text" name="' . $name . '[lng]" value="' . (isset($field['value']['cords']['lng']) ? $field['value']['cords']['lng'] : '') . '" >';
                                    ?>
                                </div>
                            </td>
                            <td class="item_cost" width="1%">
                                <?php
                                if (isset($field['form_data']->enablePrice) && $field['form_data']->enablePrice) {
                                    ?>
                                    <div class="view">
                                        <?php echo Acowebs\WCPA\wcpaPrice($field['price'][0], false, ['currency' => $order->get_currency()]); ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text"
                                               data-price="<?php echo $field['price'][0]; ?>"
                                               class="wcpa_has_price"
                                               name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]"
                                               value="<?php echo $field['price'][0] ?>">
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>

                            <td class="wc-order-edit-line-item" width="1%">
                                <div class="wc-order-edit-line-item-actions edit" style="display: none;">
                                    <a class="wcpa_delete-order-item tips" href="#"
                                       data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <tr class="item_wcpa">

                            <td class="name">

                                <?php
                                if ($field['type'] == 'hidden' && empty($field['label'])) {
                                    echo $field['label'] . '[hidden]';
                                } else {
                                    echo $field['label'];
                                }
                                ?>
                            </td>
                            <td class="value">
                                <div class="view">

                                    <?php
                                    if ($field['type'] == 'color') {
                                        echo '<span style = "color:' . $field['value'] . ';font-size: 20px;
            padding: 0;
    line-height: 0;">&#9632;</span>' . $field['value'];
                                    } else {
                                        echo nl2br($field['value']);
                                    }
                                    ?>
                                </div>

                                <div class="edit" style="display: none;">
                                    <?php
                                    if ($field['type'] == 'paragraph' || $field['type'] == 'header') {
                                        echo $field['value'];
                                        echo '<input type="hidden" 
                                       name="wcpa_meta[value][' . $item_id . '][' . $k . ']" 
                                       value="1">';
                                    } else if ($field['type'] == 'textarea') {
                                        ?>
                                        <textarea
                                                name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]"><?php echo($field['value']) ?></textarea>
                                        <?php
                                    } else {
                                        ?>
                                        <input type="text"
                                               name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k; ?>]"
                                               value="<?php echo htmlspecialchars($field['value']) ?>">
                                        <?php
                                    }
                                    ?>

                                </div>
                            </td>
                            <td class="item_cost" width="1%">
                                <?php
                                if (isset($field['form_data']->enablePrice) && $field['form_data']->enablePrice) {
                                    ?>
                                    <div class="view">
                                        <?php echo Acowebs\WCPA\wcpaPrice($field['price'], false, ['currency' => $order->get_currency()]); ?>
                                    </div>
                                    <div class="edit" style="display: none;">
                                        <input type="text"
                                               data-price="<?php echo $field['price']; ?>"
                                               class="wcpa_has_price"
                                               name="wcpa_meta[price][<?php echo $item_id; ?>][<?php echo $k; ?>]"
                                               value="<?php echo $field['price'] ?>">
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>

                            <td class="wc-order-edit-line-item" width="1%">
                                <div class="wc-order-edit-line-item-actions edit" style="display: none;">
                                    <a class="wcpa_delete-order-item tips" href="#"
                                       data-tip="<?php esc_attr_e('Delete item', 'woocommerce'); ?>"></a>
                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>


                    <?php
                }
            }
        }
        ?>
        <tr>
            <!--   /* dummy field , it will help to iterate through all data for removing last item*/-->
            <input type="hidden" name="wcpa_meta[value][<?php echo $item_id; ?>][<?php echo $k + 99; ?>]" value="">

        </tr>
    </table>

    <?php
}



