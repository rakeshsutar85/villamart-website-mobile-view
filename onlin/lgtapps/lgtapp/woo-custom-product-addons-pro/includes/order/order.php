<?php


namespace Acowebs\WCPA;

class Order
{
    /**
     * @var false|mixed|string|void
     */
    private $show_price;

    public function __construct()
    {
        add_action(
            'woocommerce_checkout_create_order_line_item',
            array($this, 'checkout_create_order_line_item'),
            10,
            4
        );
        /** support for RFQ request quote plugin  */
        add_action(
            'rfqtk_woocommerce_checkout_create_order_line_item',
            array($this, 'checkout_create_order_line_item'),
            10,
            4
        );



        add_action('woocommerce_checkout_update_order_meta', array($this, 'checkout_order_processed'), 1, 1);
        /** support for block checkout */
        add_action('__experimental_woocommerce_blocks_checkout_update_order_meta',
            array($this, 'checkout_order_processed'), 1, 1);



        add_action('woocommerce_checkout_subscription_created', array($this, 'checkout_subscription_created'), 10,
            1); //compatibility with subscription plugin


        add_filter('woocommerce_order_item_display_meta_value', array($this, 'display_meta_value'), 10, 3);

        add_action('woocommerce_before_order_itemmeta', array($this, 'order_item_line_item_html'), 10, 3);


        add_action('woocommerce_order_item_get_formatted_meta_data', array(
            $this,
            'order_item_get_formatted_meta_data',
        ), 10, 2);

        add_filter('woocommerce_display_item_meta', array($this, 'display_item_meta'), 10, 3);
    }

    //TODO to verify
    public function display_item_meta($html, $item, $args)
    {
        $html = str_replace('<strong class="wc-item-meta-label">'.WCPA_EMPTY_LABEL.':</strong>', '', $html);

        return str_replace(WCPA_EMPTY_LABEL.':', '', $html);
    }

    public function order_item_line_item_html($item_id, $item, $product)
    {
        $meta_data = $item->get_meta(WCPA_ORDER_META_KEY);
        $order     = $item->get_order();
        if (is_array($meta_data) && count($meta_data)) {
            $firstKey = \array_key_first($meta_data);
            if (is_string($firstKey)) {
//                include(plugin_dir_path(__FILE__).'meta-line-item.php');
                $meta = new OrderMetaLineItem($item, $product);
                $meta->render();
            } else {
                include(plugin_dir_path(__FILE__).'meta-line-item_v1.php');
            }
        }
    }

    /**
     * To hide showing wcpa meta as default order meta in admin end order details. As we are already showing this data in formatted mode
     */
    public function order_item_get_formatted_meta_data($formatted_meta, $item)
    {
        if (Config::get_config('show_meta_in_order') && did_action('woocommerce_before_order_itemmeta') > 0) {
            foreach ($formatted_meta as $meta_id => $v) {
                if ($this->wcpa_meta_by_meta_id($item, $meta_id)) {
                    unset($formatted_meta[$meta_id]);
                }
            }
        }

        return $formatted_meta;
    }

    private function wcpa_meta_by_meta_id($item, $meta_id)
    {
        $meta_data = $item->get_meta(WCPA_ORDER_META_KEY);


        if (is_array($meta_data) && count($meta_data)) {
            $firstKey = \array_key_first($meta_data);
            if (is_string($firstKey)) {
                /** version 2 Format - including sections  */
                foreach ($meta_data as $sectionKey => $section) {
                    $form_rules = $section['extra']->form_rules;
                    foreach ($section['fields'] as $rowIndex => $row) {
                        foreach ($row as $colIndex => $field) {
                            if (isset($field['meta_id']) && ($meta_id == $field['meta_id'])) {
                                return ['form_rules' => $form_rules, 'field' => $field];
                            }
                        }
                    }
                }
            } else {
                /** version 1 Format */
                foreach ($meta_data as $v) {
                    if (isset($v['meta_id']) && ($meta_id == $v['meta_id'])) {
                        return $v;
                    }
                }
            }
        } else {
            return false;
        }

        return false;
    }

    public function checkout_order_processed($order_id)
    {
        $order = wc_get_order($order_id);
        $items = $order->get_items();
        if (is_array($items)) {
            foreach ($items as $item_id => $item) {
                $this->update_order_item($item, $order_id);
            }
        }
    }

    public function update_order_item($item, $order_id)
    {
        $wcpa_meta_data = $item->get_meta(WCPA_ORDER_META_KEY);
        $quantity       = $item->get_quantity();
        $save_price     = Config::get_config('show_price_in_order_meta');
        foreach ($wcpa_meta_data as $sectionKey => $section) {
            $form_rules = $section['extra']->form_rules;
            foreach ($section['fields'] as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    $item->add_meta_data(
                        'WCPA_id_'.$colIndex.'_'.$rowIndex.'_'.$sectionKey,
                        // why sectionKey at end? section can be contain '_', so splitting will result worng
                        $this->order_meta_plain($field, $form_rules, $save_price, $quantity)
                    );
                }
            }
        }

        $item->save_meta_data();
        $meta_data = $item->get_meta_data();
        foreach ($meta_data as $meta) {
            $data = (object) $meta->get_data();
            if (($index = $this->check_wcpa_meta($data)) !== false) {
                $metaDataItem = &$wcpa_meta_data[$index->sectionKey]['fields'][$index->rowIndex][$index->colIndex];
                if (
                    $metaDataItem['type'] == 'hidden' ||
                    ! Config::get_config('show_meta_in_order') ||
                    (isset($metaDataItem['form_data']->hideFieldIn_order) && $metaDataItem['form_data']->hideFieldIn_order) ||
                    ($metaDataItem['type'] == 'productGroup' && isset($metaDataItem['form_data']->independent) && $metaDataItem['form_data']->independent)

                ) {
                    $item->update_meta_data('_'.$metaDataItem['label'], $data->value, $data->id);
                } else {
                    $item->update_meta_data($metaDataItem['label'], $data->value, $data->id);
                }

                if ($metaDataItem['type'] == 'productGroup' && is_array($metaDataItem['value'])) {
                    foreach ($metaDataItem['value'] as $v) {
                        $p_id       = $v['value'];
                        $p_quantity = $v['quantity'];
                        $product    = wc_get_product($p_id);
                        if ($product->get_manage_stock()) {
                            $stock_quantity = $product->get_stock_quantity();
                            if ( ! isset($dField['form_data']->independent) || ! $dField['form_data']->independent) {
                                if ( ! isset($dField['form_data']->independentQuantity) || ! $dField['form_data']->independentQuantity) {
                                    $p_quantity *= $quantity;
                                }
                                $new_quantity = $stock_quantity - $p_quantity;
                                $product->set_stock_quantity($new_quantity);
                                $product->save();
                            }
                        }
                    }
                }
                $metaDataItem['meta_id'] = $data->id;
            }
        }

        $wcpa_meta_data = apply_filters('wcpa_order_meta_data', $wcpa_meta_data, $item, $order_id);
        $item->update_meta_data(WCPA_ORDER_META_KEY, $wcpa_meta_data);
        $item->save_meta_data();
    }

    public function order_meta_plain($v, $form_rules, $show_price = true, $quantity = 1, $product = false)
    {
        $field_price_multiplier = 1;
        if (Config::get_config('show_field_price_x_quantity', false)) {
            $field_price_multiplier = $quantity;
        }

        if (
            (isset($form_rules['pric_cal_option_once'])
             && $form_rules['pric_cal_option_once'] === true)
            || (isset($form_rules['pric_use_as_fee']) && $form_rules['pric_use_as_fee'] === true) ||
            (isset($v['is_fee']) && $v['is_fee'] === true)
        ) {
            $field_price_multiplier = 1;
        }
        $metaValue = '';
        $value     = $v['value'];
        switch ($v['type']) {
            case 'file':
                if (is_array($value)) {
                    /**
                     * Convert files array as string Joining each file name and its URL with a pipe (|)
                     */
                    $metaValue = implode(
                        "\r\n",
                        array_map(
                            function ($a) {
                                return $a['file_name'].' | '.$a['url'];
                            },
                            $value
                        )
                    );
                    if ($v['price'] && $show_price) {
                        $metaValue = $metaValue.'\r\n('.wcpaPrice($v['price'] * $field_price_multiplier, false, 1).')';
                    }
                }
                break;
            case 'image-group':
            case 'productGroup':
                if ($v['price'] && $show_price) {
                    $metaValue = implode(
                        "\r\n",
                        array_map(
                            function ($val, $price) use ($field_price_multiplier, $product) {
                                if ($val['i'] === 'other') {
                                    $_return = $val['label'].': '.$val['value'];
                                } else {
                                    $_return = $val['label'].' | '.$val['value'].' | '.$val['image'];
                                }

                                if ($price) {
                                    $_return .= ' | ('.wcpaPrice($price * $field_price_multiplier, false, 1).')';
                                }

                                return $_return;
                            },
                            $value,
                            $v['price']
                        )
                    );
                } else {
                    $metaValue = implode(
                        "\r\n",
                        array_map(
                            function ($val) {
                                if ($val['i'] === 'other') {
                                    $_return = $val['label'].': '.$val['value'];
                                } else {
                                    $_return = $val['label'].' | '.$val['value'].' | '.$val['image'];
                                }

                                return $_return;
                            },
                            $value
                        )
                    );
                }

                break;

            case 'color-group':
                if ($v['price'] && $show_price) {
                    $metaValue = implode(
                        "\r\n",
                        array_map(
                            function ($val, $price) use ($field_price_multiplier, $product) {
                                if ($val['i'] === 'other') {
                                    $_return = $val['label'].': '.$val['value'];
                                } else {
                                    $_return = $val['label'].' | '.$val['value'].' | '.$val['color'];
                                }

                                if ($price) {
                                    $_return .= ' | ('.wcpaPrice($price * $field_price_multiplier, false, 1).')';
                                }

                                return $_return;
                            },
                            $value,
                            $v['price']
                        )
                    );
                } else {
                    $metaValue = implode(
                        "\r\n",
                        array_map(
                            function ($val) {
                                if ($val['i'] === 'other') {
                                    $_return = $val['label'].': '.$val['value'];
                                } else {
                                    $_return = $val['label'].' | '.$val['value'].' | '.$val['color'];
                                }

                                return $_return;
                            },
                            $value
                        )
                    );
                }

                break;
            case  'placeselector':
                $strings = [
                    'street'    => Config::get_config('place_selector_street'),
                    'city'      => Config::get_config('place_selector_city'),
                    'state'     => Config::get_config('place_selector_state'),
                    'zip'       => Config::get_config('place_selector_zip'),
                    'country'   => Config::get_config('place_selector_country'),
                    'latitude'  => Config::get_config('place_selector_latitude'),
                    'longitude' => Config::get_config('place_selector_longitude'),
                ];
                if ( ! empty($value['value'])) {
                    $metaValue = $value['value'].'<br>';
                    if ( ! empty($value['split']['street_number'])) {
                        $metaValue .= $strings['street'].' '.$value['split']['street_number'].' '.$value['split']['route']."\r\n";
                    }
                    if ( ! empty($value['split']['locality'])) {
                        $metaValue .= $strings['city'].' '.$value['split']['locality']."\r\n";
                    }
                    if ( ! empty($value['split']['administrative_area_level_1'])) {
                        $metaValue .= $strings['state'].' '.$value['split']['administrative_area_level_1']."\r\n";
                    }
                    if ( ! empty($value['split']['postal_code'])) {
                        $metaValue .= $strings['zip'].' '.$value['split']['postal_code']."\r\n";
                    }
                    if ( ! empty($value['split']['country'])) {
                        $metaValue .= $strings['country'].' '.$value['split']['country']."\r\n";
                    }
                    if (isset($value['cords']['lat']) && ! empty($value['cords']['lat'])) {
                        $metaValue .= $strings['latitude'].' '.$value['cords']['lat']."\r\n";
                        $metaValue .= $strings['longitude'].' '.$value['cords']['lng']."\r\n";
                    }
                    if ($v['price'] && $show_price) {
                        $metaValue = $metaValue.'\r\n('.wcpaPrice($v['price'] * $field_price_multiplier, false, 1).')';
                    }
                }

                break;

            case 'date':
            case 'datetime-local':
                $format = isset($v['dateFormat']) ? $v['dateFormat'] : false;
                if (is_array($value)) {
                    if (isset($value['start'])) {
                        $metaValue = formattedDate($value['start'], $format).
                                     __(' to ', 'woo-custom-product-addons-pro').
                                     formattedDate($value['end'], $format);
                    } else {
                        $metaValue = '';
                        foreach ($value as $dt) {
                            $metaValue .= formattedDate($dt, $format).', ';
                        }
                        $metaValue = trim($metaValue, ',');
                    }
                } else {
                    $metaValue = formattedDate($value, $format);
                }

                if ($v['price'] && $show_price) {
                    $metaValue = $metaValue.' ('.wcpaPrice($v['price'] * $field_price_multiplier, false, 1).')';
                }

                break;
            default:
                if (is_array($value) && in_array($v['type'], ['select', 'radio-group', 'checkbox-group'])) {
                    if ($v['price'] && $show_price) {
                        $metaValue = implode(
                            "\r\n",
                            array_map(
                                function ($val, $price) use ($field_price_multiplier, $product) {
                                    if ($val['i'] === 'other') {
                                        $_return = $val['label'].': '.$val['value'];
                                    } else {
                                        $_return = $val['label'].' | '.$val['value'];
                                    }

                                    if ($price) {
                                        $_return .= ' | ('.wcpaPrice($price * $field_price_multiplier, false, 1).')';
                                    }

                                    return $_return;
                                },
                                $value,
                                $v['price']
                            )
                        );
                    } else {
                        $metaValue = implode(
                            "\r\n",
                            array_map(
                                function ($val) {
                                    if ($val['i'] === 'other') {
                                        $_return = $val['label'].': '.$val['value'];
                                    } else {
                                        $_return = $val['label'].' | '.$val['value'];
                                    }

                                    return $_return;
                                },
                                $value
                            )
                        );
                    }
                } else {
                    if ($v['price'] && $show_price) {
                        $metaValue = $value.' ('.wcpaPrice($v['price'] * $field_price_multiplier, false, 1).')';
                    } else {
                        $metaValue = $value;
                    }
                }


                break;
            //TODO check content field
        }

        return $metaValue;
    }


    private function check_wcpa_meta($meta)
    {
        preg_match("/WCPA_id_(.*)/", $meta->key, $matches);
        if ($matches && count($matches)) {
            $pattern = "/([0-9]+)_([0-9]+)_(.*)/";
            preg_match($pattern, $matches[1], $index);
            if (count($index) == 4) {
                return (object) [
                    'sectionKey' => $index[3],
                    'rowIndex'   => $index[2],
                    'colIndex'   => $index[1]
                ];
            }

            return false;
        } else {
            return false;
        }
    }

    /**
     * Prepare addon values as plain text, it can be stored as order line item meta
     * This data can be utilized even if WCPA plugin is inActive
     * Also 3rd party plugins might be using this data, even it is not compatible with product addon, this raw data will be accessible
     */
    //TODO handle version 1 Data
    public function checkout_subscription_created($subscription)
    {
        $items    = $subscription->get_items();
        $order_id = $subscription->get_id();
        if (is_array($items)) {
            foreach ($items as $item_id => $item) {
                $this->update_order_item($item, $order_id);
            }
        }
    }

    public function checkout_create_order_line_item($item, $cart_item_key, $values, $order)
    {
        if (empty($values[WCPA_CART_ITEM_KEY])) {
            return;
        }


        $item->add_meta_data(WCPA_ORDER_META_KEY, $values[WCPA_CART_ITEM_KEY]);
        $item->save();
    }

    /**
     * Display   formatted meta value
     *
     * @param $display_value
     * @param  null  $meta
     * @param  null  $item
     *
     * @return mixed|void
     */
    public function display_meta_value($display_value, $meta = null, $item = null)
    {
        if ($item != null && $meta !== null) {
            $wcpa_data = $this->wcpa_meta_by_meta_id($item, $meta->id);
        } else {
            $wcpa_data = false;
        }
        $out_display_value = $display_value;
        if ($wcpa_data) {
            if (isset($wcpa_data['form_rules'])) {
                $form_rules = $wcpa_data['form_rules'];
                $field      = $wcpa_data['field'];
            } else {
                $form_rules = isset($wcpa_data['form_data']->form_rules)?$wcpa_data['form_data']->form_rules:[];
                $field      = $wcpa_data;
            }
            $this->show_price = Config::get_config('show_price_in_order');
            $quantity         = $item->get_quantity();

            if ($this->show_price == false) {// dont compare with === , $show_price will be 1 for true and 0 for false
                /** if it need to hide the price in order, generate a plain field without price */
                $meta->value = $display_value = $this->order_meta_plain($field, $form_rules, false, $quantity);
            }


            $quantityMultiplier = 1;
            if (Config::get_config('show_field_price_x_quantity')) {
                $quantityMultiplier = $quantity;
            }

            if ((isset($form_rules['pric_cal_option_once']) &&
                 $form_rules['pric_cal_option_once'] === true) ||
                (isset($form_rules['pric_use_as_fee']) &&
                 $form_rules['pric_use_as_fee'] === true) ||
                (isset($field['is_fee']) && $field['is_fee'] === true)
            ) {
                $quantityMultiplier = 1;
            }


            //TODO check currency and taxrate
            $metaDisplay       = new MetaDisplay(false, $this->show_price, $quantityMultiplier);
            $out_display_value = $metaDisplay->display($field, $form_rules);

            /** removed below code as '$display_value' contains rice value it display price twice */
//            if (in_array(
//                $field['type'],
//                [
//                    'date',
//                    'datetime-local',
//                    'content',
//                    'textarea',
//                    'color',
//                    'file',
//                    'image-group',
//                    'color-group',
//                    'placeselector',
//
//                ]
//            )) {
//                $out_display_value = $metaDisplay->display($field, $form_rules);
//            } else {
//                $out_display_value = $metaDisplay->display($field, $form_rules, $display_value);
//            }
        }

        return $out_display_value;
    }


}