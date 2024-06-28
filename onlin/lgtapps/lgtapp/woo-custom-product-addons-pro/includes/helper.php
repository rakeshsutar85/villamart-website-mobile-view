<?php

namespace Acowebs\WCPA;


function extractFormData($field)
{
    $attrs  = [
        'name',
        'label',
        'value',
        'hideFieldIn_order',
        'hideFieldIn_cart',
        'isClone',
        'enablePrice',
        'price',
        'pricingType',
        'isTemplate',
        'excl_chars_frm_length',
        'excl_chars_frm_length_is_regex',
        'formulaId',
        'use_as_fee',
        'is_show_price',
        'active',
        'required',
        'elementId',
        'fee_label',
        'hideFieldIn',
        'priceOptions',
        'charleft',
        'pattern',
        'subtype',
        'maxlength',
        'allow_multiple',
        'minlength',
        'uploadSize',
        'hideImageIn',
        'min',
        'max',
        'show_in_checkout',
        'multiple',
        'disp_size_img',
        'disp_type',
        'cart_display_type',
        'upload_type',
        'file_types',
        'contentType',
        'repeater',
        'repeater_bind',
        'repeater_bind_field',
        'repeater_max',
        'enable_quantity',
        'independentQuantity',
        'independent',
        'custom_label',
        'show_image',
        'hideImageIn_cart',
        'hideImageIn_order',
        'hideImageIn_email',
        'parentId',
    ];
    $_field = [];
    foreach ($attrs as $att) {
        if (isset($field->{$att})) {
            $_field[$att] = $field->{$att};
        }
    }

    return (object) $_field;
}

function fieldValueFromCartValue($type, $value)
{
    switch ($type) {
        case 'file':
            if ($value && is_array($value)) {
                $_value = [];
                foreach ($value as $file) {
                    $_value[] = [
                        'name' => sanitize_text_field($file['file_name']),
                        'url'  => sanitize_text_field($file['url']),
                        'type' => sanitize_text_field($file['type'])
                    ];
                }

                return $_value;
            }
            break;
        case 'select':
        case   'radio-group':
        case'checkbox-group':
        case    'color-group':
        case    'image-group':
        case    'productGroup':
            $value = array_values(
                array_map(
                    function ($v) {
                        return $v['value'];
                    },
                    $value
                )
            );

            break;
    }

    return $value;
}


function cloneField($field, $newId, $index)
{
    $nField            = clone $field;
    $nField->elementId = $newId;
    $nField->isClone   = true;
    $nField->repeater  = false;
    $nField->parentId  = $field->elementId;
    $name              = $field->name;

    if (is_array($name)) {
        $name[count($name) - 1] = $name[count($name) - 1].'_cl';
        $name[]                 = $index;
    } else {
        $name = [$field->name.'_cl', $index];
    }
    $nField->name = $name; // [$field->name . '_cl', $index];//$field->name . '_cl[' . $index . ']';

    return $nField;
}

function cloneSection($section, $parentKey, $newKey, $name = false)
{
    $newSection                   = clone $section;
    $newSection->extra            = clone $section->extra;
    $newSection->extra->key       = $newKey;//$sectionKey . '_' . $i;
    $newSection->extra->isClone   = true;
    $newSection->extra->repeater  = false;
    $newSection->extra->parentKey = $parentKey;
//    $name                                             = [ $parentKey, $sectionCounter ];

    $newSection->fields = array_map(
        function ($row) use ($name, $newKey) {
            return array_map(
                function ($field) use ($name, $newKey) {
                    if (is_array($field->name)) {
                        $name = array_merge($name, $field->name);
                    } else {
                        $name[] = $field->name;
                    }
                    $_field            = clone $field;
                    $newId             = $newKey.'_s_'.$_field->elementId;
                    $_field->name      = $name;
                    $_field->elementId = $newId;

                    return $_field;
                },
                $row
            );
        },
        $newSection->fields
    );

    return $newSection;
}

function fieldFromName($name, $action = 'value', $prefix = false)
{
    if ($action == 'isset') {
        if (is_array($name)) {
            $val = $_POST;
            if ($prefix !== false) {
                $name[count($name) - 2] = $name[count($name) - 2].$prefix;
            }
            foreach ($name as $v) {
                if ( ! isset($val[$v])) {
                    return false;
                }
                $val = $val[$v];
            }

            return true;
        } else {
            if ($prefix !== false) {
                return isset($_POST[$name.$prefix]);
            }

            return isset($_POST[$name]);
        }
    }

    if ($action == 'value') {
        if (is_array($name)) {
            if ($prefix !== false) {
                $name[count($name) - 2] = $name[count($name) - 2].$prefix;
            }
            $val = $_POST;
            foreach ($name as $v) {
                $val = $val[$v];
            }

            return $val;
        } else {
            if ($prefix !== false) {
                return isset($_POST[$name.$prefix]) ? $_POST[$name.$prefix] : '';
            }

            return $_POST[$name];
        }
    }
}

function getDateFormat($field)
{
    if (isset($field->date_pic_conf) && isset($field->date_pic_conf->dateFormat) && $field->date_pic_conf->dateFormat != '') {
        $dateFormat = $field->date_pic_conf->dateFormat;
    }
    if ($field->type == 'time') {
        $dateFormat = __(get_option('time_format'), 'woo-custom-product-addons-pro');
    } elseif ($field->type == 'datetime-local') {
        $dateFormat = __(get_option('date_format'), 'woo-custom-product-addons-pro').' '.__(
                get_option('time_format'),
                'woo-custom-product-addons-pro'
            );
    } else {
        $dateFormat = __(get_option('date_format'), 'woo-custom-product-addons-pro');
    }

    return $dateFormat;
}

function isEmpty($var)
{
    if (is_array($var)) {
        return empty($var);
    } else {
        return ($var === null || $var === false || $var === '');
    }
}

function emptyObj($obj)
{
    foreach ($obj as $prop) {
        return false;
    }

    return true;
}

function priceToFloat($price)
{
    $locale   = localeconv();
    $decimals = array(wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point']);
    $price    = str_replace($decimals, '.', $price);

    return (float) $price;
}

function getValueFromArrayValues($val)
{
    if (is_array($val)) {
        if (isset($val['value'])) {
            /** place selector */
            return $val['value'];
        } elseif (isset($val['start'])) {
            /* date picker range */
            return $val['start'];
        } elseif (count($val)) {
            /**
             * For array of values, sum the values if the values are numeric
             * Otherwise return the first value
             */

            $p_temp = array_values($val)[0];
            if (count($val) == 1 || isset($p_temp['file_name'])) {
                $p_temp = is_array($p_temp) ? (isset($p_temp['file_name']) ? $p_temp['file_name'] : $p_temp['value']) : $p_temp; /* $p_temp['name'] => for files */

                return $p_temp;
            } else {
                $_i       = -1;
                $valueSum = 0.0;
                foreach ($val as $_p) {
                    $_i++;
                    if (is_array($_p)) {
                        if (is_numeric($_p['value'])) {
                            $valueSum += (float) $_p['value'];
                        } elseif ($_i == 0) {
                            $valueSum = $_p['value'];
                            break;
                        }
                    } else {
                        if (is_numeric($_p)) {
                            $valueSum += (float) $_p;
                        } elseif ($_i == 0) {
                            $valueSum = $_p;
                            break;
                        }
                    }
                }

                return $valueSum;
            }
        }

        return false;
    }

    return $val;
}

function getTaxRate($product, $isCart = false)
{
    $consider_tax = Config::get_config('consider_product_tax_conf');
    $rate         = 1;
    if ($consider_tax) {
        if ($isCart) {
            if (WC()->cart->display_prices_including_tax()) {
                $rate = wc_get_price_including_tax(
                    $product,
                    array(
                        'qty'   => 1,
                        'price' => 1,
                    )
                );
            }else{
                $rate = wc_get_price_excluding_tax(
                    $product,
                    array(
                        'qty'   => 1,
                        'price' => 1,
                    )
                );
            }
        } else {
            if ('incl' === get_option('woocommerce_tax_display_shop')) {
                $rate = wc_get_price_including_tax(
                    $product,
                    array(
                        'qty'   => 1,
                        'price' => 1,
                    )
                );
            }else if('excl' === get_option('woocommerce_tax_display_shop')){
                $rate = wc_get_price_excluding_tax(
                    $product,
                    array(
                        'qty'   => 1,
                        'price' => 1,
                    )
                );
            }
        }
        if ($rate !== false) {
            return $rate;
        }
    }

    return 1;
}

//
//function getShopPrice($product, $args = array(), $regularPrice = false)
//{
//    if (is_array($args) && empty($args)) {
//        // request directly to get product price, in that case it need to apply the tax configuration
//        $consider_tax = true;
//    } else {
//        $consider_tax = Config::get_config('consider_product_tax_conf');
//    }
//
//    if ( ! is_array($args) && $args !== false) {
//        $args = array(
//            'qty'   => 1,
//            'price' => $args,
//        );
//    }
//    if ( ! isset($args['qty']) || empty($args['qty'])) {
//        $args['qty'] = 1;
//    }
//    if ( ! isset($args['price'])) {
//        if ($regularPrice) {
//            $args['price'] = $product->get_regular_price();
//        } else {
//            $args['price'] = $product->get_price();
//        }
//    }
//
//
//    // Remove locale from string.
//    if ( ! is_float($args['price'])) {
//        $price = priceToFloat($args['price']);
//    } else {
//        $price = $args['price'];
//    }
//
//
//    $qty = (int) $args['qty'];
//    if ($price < 0) {
//        return $price;
//    }
//    if ($consider_tax) {
//        return 'incl' === get_option('woocommerce_tax_display_shop') ?
//            wc_get_price_including_tax(
//                $product,
//                array(
//                    'qty'   => $qty,
//                    'price' => $price,
//                )
//            ) :
//            wc_get_price_excluding_tax(
//                $product,
//                array(
//                    'qty'   => $qty,
//                    'price' => $price,
//                )
//            );
//    } else {
//        return $price;
//    }
//}

function getUNIDate($dateString, $type = 'date')
{
    /** using date_create_from_format as it will return dates in wrong format or invalid date as false. DateTime() will return value even for incorrect date value. so avoid using DateTime()  */

    if ($type == 'time') {
        return date_create_from_format('H:i', $dateString);
    } elseif ($type == 'datetime-local' || $type == 'datetime') {
        return date_create_from_format('Y-m-d H:i', $dateString);
    } else {
        return date_create_from_format('Y-m-d', $dateString);
    }
}

//function display_hook($arg)
//{
//    $hooks = apply_filters(
//        'wcpa_display_hooks',
//        [
//            "fields"             => ["woocommerce_before_add_to_cart_button", 10],
//            "price_summary"      => ["wcpa_price_summary_box", 10],
//            "validation_summary" => ["wcpa_validation_summary_box", 10],
//        ]
//    );
//
//    return $hooks[$arg];
//}


/**
 * @return string
 */
function colorClass($hex)
{
    $hex   = str_replace('#', '', $hex);
    $c_r   = hexdec(substr($hex, 0, 2));
    $c_g   = hexdec(substr($hex, 2, 2));
    $c_b   = hexdec(substr($hex, 4, 2));
    $color = ((($c_r * 299) + ($c_g * 587) + ($c_b * 114)) / 1000);
    $class = '';
    if ($color > 235) {
        $class .= 'wcpa_clb_border '; // border needed
    }
    if ($color > 210) {
        $class .= 'wcpa_clb_nowhite '; // no white color
    }

    return $class;
}

function confToCss($conf)
{
    $css = '';
    foreach ($conf as $k => $v) {
        if (strpos($v, '#') == 0 && strlen($v) == 9) {
            /** convert hex color with opacity to rgba */
            list($r, $g, $b, $a) = sscanf($v, "#%02x%02x%02x%02x");
            $a = round($a / 255, 2);
            $v = "rgba($r,$g,$b,$a)";
        }
        $css = $css.'  --wcpa'.$k.':'.$v.'; ';
    }

    return ':root{'.$css.'}';
}

function metaToBoolean($v)
{
    if ($v === '' || $v === '0') {
        return false;
    } elseif ($v === '1') {
        return true;
    }

    return $v;
}

/**
 * convert wcpa 1 field structure to wcpa2
 *
 * @param $fields
 *
 * @return array
 */
function toRowCol($fields)
{
    $newArray = array();
    $row      = 0;
    $col      = 0;
    foreach ($fields as $i => $item) {
        $newItem         = $item;
        $newItem->active = true;
        if ( ! isset($newItem->col)) {
            $newItem->col = 6;
        }
        if (($col + $newItem->col) > 6) {
            $row++;
            $col = $newItem->col;
        } else {
            $col += $newItem->col;
        }
        $newArray[$row][] = $newItem;
    }


    return $newArray;
}

function fix_cols($data)
{
    $newArray = array();
//    $row = 0;
//    $col = 0;
//    foreach ($fields as $i => $item) {
//        $newItem = $item;
//        $newItem->active = true;
//
//        if (($col + $newItem->col) > 6) {
//            $row++;
//            $col = $newItem->col;
//        } else {
//            $col += $newItem->col;
//        }
//        $newArray[$row][] = $newItem;
//    }
    $colCount = 0;
    $rowCount = 0;
    $newArray = array();

    foreach ($data as $row) {
        if (is_array($row)) {
            foreach ($row as $field) {
                if (($colCount + $field->col) > 6) {
                    $rowCount++;
                    $colCount = $field->col;
                } else {
                    $colCount += $field->col;
                }
                $newArray[$rowCount][] = $field;
            }
        } else {
            if (($colCount + $row->col) > 6) {
                $rowCount++;
                $colCount = $row->col;
            } else {
                $colCount += $row->col;
            }
            $newArray[$rowCount][] = $row;
        }
    }

    return $newArray;
}

function fileTypesToExtensions($field, $dot = 'add')
{
    $allowedFileTypes = [];
    if (isset($field->exts_supported) && is_array($field->exts_supported) && count($field->exts_supported)) {
        if (is_array($field->exts_supported)) {
            $allowedFileTypes = array_map(
                function ($ext) use ($dot) {
                    if ($dot == 'add') {
                        if (isset($ext[0]) && $ext[0] !== '.') {
                            /** check first character is dot */
                            $ext = '.'.$ext;
                        }
                    } else {
                        if (isset($ext[0]) && $ext[0] == '.') {
                            /** check first character is dot */
                            $ext = substr($ext, 1);
                        }
                    }


                    return $ext;
                },
                $field->exts_supported
            );
        }
    }
    if (isset($field->file_types) && is_array($field->file_types) && count($field->file_types)) {
        if ( ! in_array('any', $field->file_types)) {
            if (in_array('images', $field->file_types)) {
                $allowedFileTypes[] = 'image/*';
            }
            if (in_array('videos', $field->file_types)) {
                $allowedFileTypes[] = 'video/*';
            }
            if (in_array('audio', $field->file_types)) {
                $allowedFileTypes[] = 'audio/*';
            }
            if (in_array('docs', $field->file_types)) {
                $docsExtensions   = [
                    '.doc',
                    '.docx',
                    '.xml',
                    '.dot',
                    '.docx',
                    '.docm',
                    '.dot',
                    '.dotm',
                    '.dotx',
                    '.htm',
                    '.html',
                    '.odt',
                    '.pdf',
                    '.rtf',
                    '.txt',
                    '.wps',
                    '.xps',
                    'application/msword',
                    'application/vnd',
                ];
                $allowedFileTypes = array_merge($allowedFileTypes, $docsExtensions);
            }
        }

        if (in_array('design', $field->file_types)) {
            $designExtensions   = [
                '.psd',
                '.pdf',
                '.eps',
                '.ai',
                '.indd',
                '.raw',
            ];
            $allowedFileTypes[] = 'image/*';
            $allowedFileTypes   = array_merge($allowedFileTypes, $designExtensions);
        }
        if (in_array('archive', $field->file_types)) {
            $archExtensions   = [
                '.zip',
                '.rar',
                '.7zip',
                '.ar',
                '.tar',
                '.gz',
                '.7z',
            ];
            $allowedFileTypes = array_merge($allowedFileTypes, $archExtensions);
        }
    }

    return array_unique($allowedFileTypes);
}


/**
 * Check if the fields are in old wcpa structure or new
 *
 * @param $data
 */
function checkFieldStructure($data)
{
    $value = reset($data); // get first value
    if (isset($value->fields)) {
        return 2;
    } else {
        return 1;
    }
}

function generateSectionFields($fields = [])
{
    $new_arr                = (object) [];
    $sectionKey             = 'sec_'.uniqSectionId();
    $new_arr->{$sectionKey} = (object) array(
        "extra"  => (object) [
            'key'        => $sectionKey,
            'section_id' => $sectionKey,
            'name'       => __('Default', 'woo-custom-product-addons-pro'),
            'status'     => 1,
            "cl_rule"    => "show",
            "enableCl"   => false,
            "relations"  => [],
            "toggle"     => true,
            "title_tag"  => "h3",
            "show_title" => false,
            "showPrice"  => 'default'

        ],
        "fields" => $fields
    );

    return $new_arr;
}

function uniqSectionId()
{
    return uniqid(rand(0, 10), false);
}

function sanitizeFields(&$formBuilderData, $allowed_html)
{
    foreach ($formBuilderData as $sectionKey => $section) {
        foreach ($section['fields'] as $rowIndex => $row) {
            foreach ($row as $colIndex => $field) {
                $_field = &$formBuilderData[$sectionKey]['fields'][$rowIndex][$colIndex];
                if (isset($field['label']) && ($field['type'] == 'content' || $field['type'] == 'header')) {
                    $_field['label'] = html_entity_decode(wp_kses($field['label'], 'post'));
                } elseif (isset($field['label'])) {
                    $_field['label'] = html_entity_decode(wp_kses($field['label'], array()));
                }
                if (isset($field['description'])) {
                    $_field['description'] = html_entity_decode(wp_kses($field['description'], $allowed_html));
                }
                if (isset($col['tooltip'])) {
                    $_field['tooltip'] = html_entity_decode(wp_kses($field['tooltip'], $allowed_html));
                }
                if (isset($field['values'])) {
                    foreach ($field['values'] as $k => $v) {
                        if (isset($v['label'])) {
                            $_field['values'][$k]['label'] = html_entity_decode(
                                wp_kses($field['values'][$k]['label'], array())
                            );
                        }
                        if (isset($v['description'])) {
                            $_field['values'][$k]['description'] = html_entity_decode(
                                wp_kses($field['values'][$k]['description'], $allowed_html)
                            );
                        }
                        if (isset($v['tooltip'])) {
                            $_field['values'][$k]['tooltip'] = html_entity_decode(
                                wp_kses($field['values'][$k]['tooltip'], $allowed_html)
                            );
                        }
                        if (isset($v['value'])) {
                            $_field['values'][$k]['value'] = trim($field['values'][$k]['value']);
                        }
                    }
                }
            }
        }
    }
}

/**
 * @param $price
 * @param $strikePrice
 * @param  int  $no_style
 * @param  array  $args
 * @param  string  $class
 *
 * @return string
 */
function wcpaPrice($price, $strikePrice = 0, $no_style = 0, $args = array(), $class = 'price_value')
{
    extract(
        array(
            'ex_tax_label'       => false,
            'currency'           => isset($args['currency']) ? $args['currency'] : '',
            'decimal_separator'  => wc_get_price_decimal_separator(),
            'thousand_separator' => wc_get_price_thousand_separator(),
            'decimals'           => wc_get_price_decimals(),
            'price_format'       => get_woocommerce_price_format()
        )
    );
    if ($decimal_separator) {
        $decimal_separator = trim($decimal_separator);
        $price             = str_replace($decimal_separator, '.', $price);
        $strikePrice       = str_replace($decimal_separator, '.', $strikePrice);
    }

    //$unformatted_price = $price;
    $negative    = $price < 0;
    $price       = floatval($negative ? $price * -1 : $price);
    $strikePrice = floatval($negative ? $strikePrice * -1 : $strikePrice);

    $price = apply_filters('raw_woocommerce_price', $price);

    $_price = number_format($price, $decimals, $decimal_separator, $thousand_separator);


    $formatted_price = ($negative ? '-' : '').sprintf(
            $price_format,
            '<span class="woocommerce-Price-currencySymbol">'.
            get_woocommerce_currency_symbol($currency).'</span>',
            '<span class="'.$class.'">'.$_price.'</span>'
        );
    $return          = '<span class="wcpa_price">'.$formatted_price.'</span>';
    if ($no_style) {
        $return = html_entity_decode(
            ($negative ? '-' : '').sprintf($price_format, get_woocommerce_currency_symbol($currency), $price),
            ENT_COMPAT,
            'UTF-8'
        );
    } elseif ($strikePrice > $price) {
        $_price          = number_format($strikePrice, $decimals, $decimal_separator, $thousand_separator);
        $formatted_price = ($negative ? '-' : '').sprintf(
                $price_format,
                '<span class="woocommerce-Price-currencySymbol">'.
                get_woocommerce_currency_symbol($currency).'</span>',
                '<span class="'.$class.'">'.$_price.'</span>'
            );
        $return          = '<del class="wcpa_price">'.$formatted_price.'</del>'.$return;
    }

    return $return;
}

//
//function wcpa_get_price_cart($product, $args = array())
//{
//    if (is_array($args) && empty($args)) {
//        // request directly to get product price, in that case it need to apply the tax configuration
//        $consider_tax = true;
//    } else {
//        $consider_tax = Config::get_config('consider_product_tax_conf');
//    }
//
//    if ( ! is_array($args) && $args !== false) {
//        $args = array(
//            'qty'   => 1,
//            'price' => $args,
//        );
//    }
//
//
//    if ( ! isset($args['qty']) || empty($args['qty'])) {
//        $args['qty'] = 1;
//    }
//
//    if ( ! isset($args['price'])) {
//        $args['price'] = $product->get_price();
//    }
////        else {
////            $args['price'] = apply_filters('woocommerce_product_get_price', $args['price'], $product);
////        }
//
//    // Remove locale from string.
//    if ( ! is_float($args['price'])) {
//        $price = priceToFloat($args['price']);
//    } else {
//        $price = $args['price'];
//    }
//
//    $qty = (int) $args['qty'];
//
//
//    if ($price > 0 && $consider_tax) {
//        if (WC()->cart->display_prices_including_tax()) {
//            $product_price = wc_get_price_including_tax(
//                $product,
//                array(
//                    'qty'   => $qty,
//                    'price' => $price,
//                )
//            );
//        } else {
//            $product_price = wc_get_price_excluding_tax(
//                $product,
//                array(
//                    'qty'   => $qty,
//                    'price' => $price
//                )
//            );
//        }
//    } else {
//        $product_price = $price;
//    }
//
//    return $product_price;
//}

/**
 * Using to check if the date contains from to value ( 2011-1-20 to 2022-02-30)
 */
function processDateValueForCl($val)
{
    $res = [];
    if (is_array($val)) {
        foreach ($val as $dt) {
            $sp = preg_split('/\sto\s/', $dt);
            if (count($sp) == 2) {
                $dt['start'] = $sp[0];
                $dt['end']   = $sp[1];
            }
            if (isset($dt['start'])) {
                $d     = getUNIDate($dt['start']);
                $d2    = getUNIDate($dt['end']);
                $range = (object) ['start' => 0, 'end' => 0];
                if ($d) {
                    $range->start = $d->getTimestamp();
                    if ($d2) {
                        $range->end = $d2->getTimestamp();
                    }
                }
                $res[] = $range;
            } else {
                $d = getUNIDate($dt);
                if ($d) {
                    $res[] = $d->getTimestamp();
                } else {
                    $res[] = $dt;
                }
            }
        }
    } else {
        $d = getUNIDate($val);
        if ($d) {
            $res[] = $d->getTimestamp();
        } else {
            $res[] = $val;
        }
    }

    return $res;
}

/**
 * get product attribute list, custom attrs and attributes,
 *
 * @param $product
 *
 * @return array
 */
function get_pro_attr_list($product)
{
    $attributes = $product->get_attributes();

    $product_attributes = [];
    foreach ($attributes as $key=> $attribute) {
        $values = array();
        if ( !is_a( $attribute, 'WC_Product_Attribute' )) {
            continue;
        }
        if ($attribute->is_taxonomy()) {
            /**
             * $pro->is_type('variable') - Added Because error in getting variation for ticket #10355
             */
            if ($attribute->get_variation() && $product->is_type('variable')) {
                continue; // exclude normal variations
            }
            $attribute_values = wc_get_product_terms(
                $product->get_id(),
                $attribute->get_name(),
                array('fields' => 'all')
            );

            foreach ($attribute_values as $attribute_value) {
                $value_slug = esc_html($attribute_value->slug);

                $values[] = $value_slug;
            }
        } else {
            $values = $attribute->get_options();
        }

        $product_attributes[sanitize_title_with_dashes($attribute->get_name())] = array(
            'label' => $attribute->get_name(),
            'value' => $values,
        );
    }

    return $product_attributes;
}

function getFormEditUrl($post_id){
    return  admin_url('admin.php?page=wcpa-admin-ui#/form/'.$post_id);
}

function hasFormula($str)
{
    if (preg_match('/\#\=(.+?)\=\#/', $str) === 1) {
        return true;
    }

    return false;
}


function formattedDate($value, $dateFormat = false)
{
    return ($dateFormat ? (function_exists('wp_date') ? wp_date($dateFormat,
        strtotime($value)) : date($dateFormat, strtotime($value))) : $value);
}


/**
 * find field by elementID,
 *
 * @param $formData
 * @param $element_id
 * @param  false  $returnIndex  whether to return section=>row>col indexes only or return field itself
 *
 * @param  bool  $isObject
 *
 * @return array|false|mixed
 * @since 5.0
 */

function findFieldById($formData, $element_id, $returnIndex = false, $isObject = false)
{
    $resp = false;
    foreach ($formData as $sectionKey => $section) {
        if ($isObject) {
            if ( ! isset($section->fields)) {
                continue;
            }
            foreach ($section->fields as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    if ($field->elementId === $element_id) {
                        $resp = [
                            'sectionKey' => $sectionKey,
                            'rowIndex'   => $rowIndex,
                            'colIndex'   => $colIndex,
                        ];
                        break;
                    }
                }
                if ($resp !== false) {
                    break;
                }
            }
        } else {
            if ( ! isset($section['fields'])) {
                continue;
            }
            foreach ($section['fields'] as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                   if(!isset($field['elementId'])){
                     continue;
                   }
                    if ($field['elementId'] === $element_id) {
                        $resp = [
                            'sectionKey' => $sectionKey,
                            'rowIndex'   => $rowIndex,
                            'colIndex'   => $colIndex,
                        ];
                        break;
                    }
                }
                if ($resp !== false) {
                    break;
                }
            }
        }

        if ($resp !== false) {
            break;
        }
    }
    if ($returnIndex) {
        return $resp;
    }
    if ($resp == false) {
        return $resp;
    }

    if ($isObject) {
        return $formData->{$resp['sectionKey']}->fields[$resp['rowIndex']][$resp['colIndex']];
    }

    return $formData[$resp['sectionKey']]['fields'][$resp['rowIndex']][$resp['colIndex']];
}

function refreshCaches($form_id = false, $product_id = false)
{
    delete_transient(WCPA_PRODUCTS_TRANSIENT_KEY);
    if ($product_id) {
        delete_transient('wcpa_fields_'.$product_id);
    } elseif ($form_id) {
        $status = delete_transients_with_prefix('wcpa_fields_');
        if ( ! $status) {
            /** some servers stores transients differently, in  that case this bulk action doesnt work,
             * so refresh cache individually findig all products connected with this form id
             */

            $form = new Form();
            $ids  = $form->products_listing($form_id, true);
            global $wpdb;
            $query = "SELECT
distinct object_id from $wpdb->term_relationships
 where term_taxonomy_id"
                     ." in (select tr.term_taxonomy_id from $wpdb->term_relationships as tr left join $wpdb->term_taxonomy as tt on(tt.term_taxonomy_id=tr.term_taxonomy_id) where tr.object_id in (".implode(',',
                    [$form_id]).")"
                     ."and  tt.taxonomy = 'product_cat')";

            $pro_ids = $wpdb->get_col($query);
            $ids     = array_unique(array_merge($pro_ids, $ids));
            foreach ($ids as $id) {
                delete_transient('wcpa_fields_'.$id);
            }
        }
    } else {
        $status = delete_transients_with_prefix('wcpa_fields_');
        if ( ! $status) {
            /** some servers stores transients differently, in  that case this bulk action doesnt work, so refresh cache individually */
            $form = new Form();
            $ids  = $form->get_wcpaProducts();
            foreach ($ids as $id) {
                delete_transient('wcpa_fields_'.$id);
            }
        }
    }

    delete_transients_with_prefix('wcpa_settings_');
}

function get_transient_keys_with_prefix($prefix)
{
    global $wpdb;

    $prefix = $wpdb->esc_like('_transient_'.$prefix);
    $sql    = "SELECT `option_name` FROM $wpdb->options WHERE `option_name` LIKE '%s'";
    $keys   = $wpdb->get_results($wpdb->prepare($sql, $prefix.'%'), ARRAY_A);

    if (is_wp_error($keys)) {
        return [];
    }

    return array_map(function ($key) {
        // Remove '_transient_' from the option name.
        return ltrim($key['option_name'], '_transient_');
    }, $keys);
}

function delete_transients_with_prefix($prefix)
{
    $status = false;
    foreach (get_transient_keys_with_prefix($prefix) as $key) {
        $status = true;
        delete_transient($key);
    }

    return $status;
}




function beforeCalculateDiscount($product_price, $cart_item)
{

    $price = apply_filters('wcpa_cart_addon_data', false, $cart_item);
    if ($price == false) {
        return $product_price;
    }

    return $price['totalPrice'] - $price['excludeFromDiscount'];

}

function processProductGroup(&$field)
{
    $ml                = new ML();
    $product_ids       = array_map(function ($v) {
        return intval($v->value);
    }, $field->values);
    $new_product_array = array();
    $values            = [];
    $isCustomLabel     = isset($field->custom_label) ? $field->custom_label : false;
    $showImage         = isset($field->show_as_product_image) && $field->show_as_product_image || (isset($field->show_image) ? $field->show_image : false);
    $customImage       = isset($field->custom_image) ? $field->custom_image : false;
    $isProductPrice    = false;

    $disp_size_img        = isset($field->disp_size_img) ? $field->disp_size_img : (object) [
        'width'  => 75,
        'height' => 75
    ];
    $field->disp_size_img = $disp_size_img;
    if (isset($field->enablePrice) && $field->enablePrice && ($field->priceOptions == '' || $field->priceOptions == 'product_price')) {
        $isProductPrice      = true;
        $field->pricingType  = 'fixed';
        $field->priceOptions = 'different_for_all';
    }

    if ( ! empty($product_ids)) {
        if ($ml->is_active()) {
            $product_ids = $ml->lang_object_ids($product_ids, 'product');
            foreach ($product_ids as $i => $_id) {
                $field->values[$i]->value = $_id;
            }
        }


        $args = array(
            'post_status'    => 'publish',
            'include'        => $product_ids,
            'posts_per_page' => -1,
        );

        $product_array = wc_get_products($args);
        // Reorder $product_array
        foreach ($product_array as $val) {
            if (false !== $key = array_search($val->get_id(), $product_ids)) {
                $new_product_array[$val->get_id()] = $val;
            }
        }
        ksort($new_product_array);
        foreach ($field->values as $v) {
            if (isset($new_product_array[trim($v->value)])) {
                $product = $new_product_array[trim($v->value)];
                if ($product->is_type('variable')) {
                    $variations = $product->get_available_variations('object');
                    $count      = count($variations);
                    foreach ($variations as $var) {
                        $_v = clone $v;
                        if ( ! $isCustomLabel || $v->label == '') {
                            $label = $var->get_name();
                        } else {
                            $label = $v->label.str_replace($var->get_title(), "",
                                    $var->get_name());// to append variation name to the label
                        }
                        $_v->value = ''.$var->get_id();
                        $_v->label = $label;
                        $_v->parentId = $product->get_id();
                        if ($isProductPrice) {
//                            $_v->price = $var->get_price('edit');
                            $_v->price = Discounts::getProductPrice($var);
                        }
                        if ($showImage) {
                            if ($customImage && isset($v->image_id) && $count == 1) { // if has one variation and have uploded custom image , use that custom image
                                $img_obj = wp_get_attachment_image_src($v->image_id, [
                                    $disp_size_img->width, empty($disp_size_img->height) ? 0 : $disp_size_img->height
                                ]);
                                $img_objFull = wp_get_attachment_image_src($v->image_id,[1600,1000]);
                                if ($img_obj) {
                                    $_v->thumb_src = $img_objFull[0];
                                    $_v->image     = $img_obj[0];
                                }
                            } else {
                                $image_id = $var->get_image_id();
                                if ($image_id) {
                                    $img_obj = wp_get_attachment_image_src($image_id, [
                                        $disp_size_img->width,
                                        empty($disp_size_img->height) ? 0 : $disp_size_img->height
                                    ]);
                                    $img_objFull = wp_get_attachment_image_src($image_id);
                                    if ($img_obj) {
                                        $_v->image     = $img_objFull[0];
                                        $_v->thumb_src = $img_obj[0];
                                        $_v->image_id  = $image_id;
                                    }
                                } else {
                                    if ($customImage && isset($v->image_id)) {
                                        $img_obj = wp_get_attachment_image_src($v->image_id, [
                                            $disp_size_img->width,
                                            empty($disp_size_img->height) ? 0 : $disp_size_img->height
                                        ]);
                                        if ($img_obj) {
                                            $_v->thumb_src = $img_obj[0];
                                        }
                                    }
                                }
                            }
                        }
                        $values[] = $_v;
                    }
                } else {
                    $_v = clone $v;
                    if ( ! $isCustomLabel || $v->label == '') {
                        $label = $product->get_title();
                    } else {
                        $label = $v->label;
                    }
                    $_v->value = ''.$product->get_id();
                    $_v->label = $label;
                    if ($isProductPrice) {
//                        $_v->price = $product->get_price('edit');
                        $_v->price = Discounts::getProductPrice($product);
                    }

                    if ($showImage) {
                        if ($customImage && isset($v->image_id)) { // if has one variation and have uploded custom image , use that custom image
                            $img_obj = wp_get_attachment_image_src($v->image_id,
                                [$disp_size_img->width, empty($disp_size_img->height) ? 0 : $disp_size_img->height]);

                            $img_objFull = wp_get_attachment_image_src($v->image_id,[1600,1000]);
                            if ($img_obj) {
                                $_v->image     = $img_objFull[0];
                                $_v->thumb_src = $img_obj[0];
                            }
                        } else {
                            $image_id = $product->get_image_id();
                            if ($image_id) {
                                $img_obj = wp_get_attachment_image_src($image_id, [
                                    $disp_size_img->width, empty($disp_size_img->height) ? 0 : $disp_size_img->height
                                ]);
                                $img_objFull = wp_get_attachment_image_src($image_id,[1600,1000]);
                                if ($img_obj) {
                                    $_v->image     = $img_objFull[0];
                                    $_v->thumb_src = $img_obj[0];
                                    $_v->image_id  = $image_id;
                                }
                            }
                        }
                    }

                    $values[] = $_v;
                }
            }
        }


        if (isset($field->show_as_product_image) && $field->show_as_product_image) {
            if ( ! empty($values)) {
                foreach ($values as $k => $val) {
                    if (isset($val->image_id) && $val->image_id > 0) {
                        $attachProps = wc_get_product_attachment_props($val->image_id);
                        if (isset($attachProps['title'])) {
                            $attachProps['title'] = htmlspecialchars($attachProps['title'], ENT_QUOTES);
                        }
                        $values[$k]->productImage = array_merge($attachProps, ['image_id' => $val->image_id]);
                    }
                }
            }
        }
        /**  give priority for enable_product_image  than show_as_product_image, so called it after show_as_product_image   */
        if (isset($field->enable_product_image) && $field->enable_product_image) {
            if ($values && !empty($values)) {
                foreach ($values as $k => $val) {
                    if (isset($val->pimage_id) && $val->pimage_id > 0) {
                        $attachProps = wc_get_product_attachment_props($val->pimage_id);
                        if (isset($attachProps['title'])) {
                            $attachProps['title'] = htmlspecialchars($attachProps['title'], ENT_QUOTES);
                        }
                        $val->productImage = $attachProps + ['image_id' => $val->pimage_id];
                    } elseif (isset($val->pimage) && $val->pimage) {
                        $props             = [
                            'title'   => htmlspecialchars($val->label, ENT_QUOTES),
                            'caption' => '',
                            'url'     => $val->pimage,
                            'alt'     => $val->label,
                            'src'     => $val->pimage,
                            'srcset'  => false,
                            'sizes'   => false,
                            'src_w'   => '',

                            'full_src'              => $val->pimage,
                            'full_src_w'            => '',
                            'full_src_h'            => '',
                            'gallery_thumbnail_src' => $val->pimage,
                        ];
                        $val->productImage = $props;
                    }
                }
            }
        }
    }

    $field->values = $values;
}

/**
 *  function to check a product has product form assigned
 * It can call Acowebs\WCPA\has_form()
 *
 * @param $product_id
 *
 * @return string
 */
function has_form($product_id)
{
    $form         = new Form();
    $wcpaProducts = $form->get_wcpaProducts();

    return in_array($product_id, $wcpaProducts['full']);
}

if ( ! function_exists('array_key_last')) {
    /**
     * Polyfill for `array_key_last()` function added in PHP 7.3.
     *
     * @param  array  $array  An array.
     *
     * @return string|int|null The last key of array if the array
     *.                        is not empty; `null` otherwise.
     */
    function array_key_last(array $array)
    {
        if (empty($array)) {
            return null;
        }

        end($array);

        return key($array);
    }
}