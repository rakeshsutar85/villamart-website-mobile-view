<?php


namespace Acowebs\WCPA;

use WC_Tax;

class Cart
{

    public $config;
    /**
     * @var bool
     */
    private $show_price;
    private $con_unit = false;
    private $taxRate = false;
    private $discountUnit = false;
    private $fees = [];
    private $pg_productID;
    private $pg_cartKey;
    private $mappedFields;

    public function __construct()
    {
        add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 10, 2);

        add_filter('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals_start'), 10, 2);

        /** this checking was done to provide support to remove addon values from applying discount. Now we are trying to make each
         *discount plugins compatible by specific coding. removing this general method as it is not correctly fit for all plugins
         */
//        add_filter('woocommerce_after_calculate_totals', array($this, 'before_calculate_totals_end'), 99999, 2);

//        add_filter('woocommerce_before_calculate_totals', array($this, 'after_calculate_totals'), 999999, 1);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'cart_item_from_session'), 10, 1);
        add_filter('woocommerce_cart_calculate_fees', array($this, 'cart_calculate_fees'), 10, 2);
        add_filter('woocommerce_get_discounted_price', array($this, 'get_discounted_price'), 10, 2);

        add_filter('woocommerce_cart_item_subtotal', array($this, 'cart_item_subtotal'), 10, 3);
        add_action('woocommerce_after_cart_item_name', array($this, 'after_cart_item_name'), 10, 2);

        add_action('woocommerce_add_to_cart', array($this, 'after_product_added_to_cart'), 10, 5);
        add_filter('woocommerce_cart_get_subtotal', array($this, 'cart_get_subtotal'), 10, 1);

        add_filter('woocommerce_cart_item_class', array($this, 'cart_item_class'), 10, 3);

        add_action('woocommerce_after_cart_item_quantity_update', array($this, 'after_cart_item_quantity_update'), 10,
            4);

        add_action('woocommerce_remove_cart_item', array($this, 'remove_cart_item'), 10, 1);

        add_action('woocommerce_cart_item_restored', array($this, 'cart_item_restored'), 10, 2);

        add_filter('woocommerce_checkout_get_value', array($this, 'map_checkout_field'), 9, 2);

        /** poly lang cart item filter */
        add_filter('pllwc_translate_cart_item', array($this, 'pllwc_translate_cart_item'), 10);


//        remove_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal');
//        add_action( 'woocommerce_widget_shopping_cart_total', '\Acowebs\WCPA\widget_shopping_cart_total',10);

//        add_filter('woocommerce_cart_subtotal', array($this, 'cart_subtotal'), 9, 3);
//        return apply_filters( 'woocommerce_cart_subtotal',  );
    }

//    public function cart_subtotal($cart_subtotal, $compound, $cart_obj)
//    {
//        if ($compound) {
//            return $cart_subtotal;
//        }
//        $fee = 0;
//        if (WC()->cart->display_prices_including_tax()) {
//            $total = $cart_obj->get_subtotal() + $cart_obj->get_subtotal_tax();
//        } else {
//            $total = $cart_obj->get_subtotal();
//        }
//        if (method_exists($cart_obj, 'get_cart')) {
//            $cart_contents = $cart_obj->get_cart();
//        } else {
//            $cart_contents = $cart_obj->cart_contents;
//        }
//        if ($cart_contents) {
//            $lastEle = end($cart_contents);
//            if (isset($lastEle['wcpaFee'])) {
//                $fee = array_sum($lastEle['wcpaFee']);
//            }
//        }
//
//        return wc_price($fee + $total);
//    }

    public function map_checkout_field($default, $field)
    {
        if (!$this->mappedFields && WC()->session && WC()->session->get('cart')) {
            $cart_contents = WC()->session->get('cart');
            $this->mappedFields = [];
            if ($cart_contents) {
                foreach ($cart_contents as $item) {
                    if (isset($item['wcpa_cart_rules']) && isset($item['wcpa_cart_rules']['checkout_fields'])) {
                        if (!isEmpty($item['wcpa_cart_rules']['checkout_fields'])) {
                            $this->mappedFields = array_merge($this->mappedFields,
                                $item['wcpa_cart_rules']['checkout_fields']);
                        }
                    }
                }
            }
        }
        if ($this->mappedFields && !isEmpty($this->mappedFields) && isset($this->mappedFields[$field]) && !isEmpty($this->mappedFields[$field])) {
            return getValueFromArrayValues($this->mappedFields[$field]);
        }

        return $default;
    }

    public function remove_cart_item($cart_item_key)
    {
        if (method_exists(WC()->cart, 'get_cart')) {
            $cart_contents = WC()->cart->get_cart();
        } else {
            $cart_contents = WC()->cart->cart_contents;
        }

        if (!empty($cart_contents)) {
            if (isset($cart_contents[$cart_item_key])) {
                $cart_item_data = $cart_contents[$cart_item_key];
                if (
                    isset($cart_item_data['wcpa_cart_rules']) &&
                    !empty($cart_item_data['wcpa_cart_rules']['combined_products'])) {
//                    $current_product_id = (isset($cart_item_data['variation_id']) && $cart_item_data['variation_id'] != 0)
//                        ? $cart_item_data['variation_id']
//                        : $cart_item_data['product_id'];

                    $products = $cart_item_data['wcpa_cart_rules']['combined_products'];
                    if (isset($products) && !empty($products)) {
                        foreach ($products as $i => $dField) {
                            foreach ($dField['value'] as $k => $v) {
                                if (isset($v['cartKey'])) {
                                    WC()->cart->remove_cart_item($v['cartKey']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function cart_item_class($class, $cart_item)
    {
        if (isset($cart_item['wcpa_cart_rules']['bind_quantity']) && $cart_item['wcpa_cart_rules']['bind_quantity']) {
            $class .= ' wcpa_bind_quantity';
        }
        if (isset($cart_item[WCPA_CART_ITEM_KEY]) && count($cart_item[WCPA_CART_ITEM_KEY])) {
            $class .= ' wcpa_cart_has_fields';
        }
        if (isset($cart_item['wcpaIgnore'])) {
            $class .= ' wcpa_prevent_quantity_change';
        }

        return $class;
    }

    public function cart_get_subtotal($total)
    {
        //TODO test for including tax

//        $feeTotal = 0.0;
////        $currency = new Currency();
//        if (isset($this->fees) && is_array($this->fees)) {
//            foreach ($this->fees as $fee) {
//                if (isset($fee['price']) && isset($fee['type']) && $fee['type'] !== 'woo_fee') {
//                    $feeTotal += is_array($fee['price']) ? array_sum($fee['price']) : $fee['price'];
//                }
//            }
//        }
//
//        return $total + ($feeTotal);
        $cart_contents = WC()->cart->get_cart();
        if ($cart_contents) {
            $lastEle = end($cart_contents);
            if (isset($lastEle['wcpaFee']) && !isEmpty($lastEle['wcpaFee'])) {
                $total = $total + $lastEle['wcpaFee']['custom'];
            }
        }

        return $total;
    }

    public function cart_item_restored($cart_item_key, $cartObject)
    {
        $this->add_addon_product_to_cart($cart_item_key);
    }

    public function add_addon_product_to_cart($cart_item_key)
    {
        $cart_contents = WC()->cart->get_cart_contents();
        $cart_item_data = $cart_contents[$cart_item_key];
        $quantity = $cart_item_data['quantity'];
        $product_id = $cart_item_data['product_id'];
        if (isset($cart_item_data['wcpa_cart_rules']) && isset($cart_item_data['wcpa_cart_rules']['combined_products'])) {
            $products = $cart_item_data['wcpa_cart_rules']['combined_products'];
        }
        $this->pg_cartKey = $cart_item_key;
        if (isset($products) && !empty($products)) {
            $flag = false;
            foreach ($products as $i => $dField) {
                foreach ($dField['value'] as $k => $v) {
                    if (isset($v['cartKey']) && !empty($v['cartKey'])) {
                        continue;
                    }
                    $p_id = $v['value'];
                    $p_quantity = $v['quantity'];
                    if (!isset($dField['form_data']->independentQuantity) || !$dField['form_data']->independentQuantity) {
                        $p_quantity *= $quantity;
                    }
                    if ($p_id == $product_id) {
                        continue;
                    } else {
                        add_filter('woocommerce_cart_id', array($this, 'update_cart_item_key'), 10, 1);
                        $_cart_item_key = WC()->cart->add_to_cart($p_id, $p_quantity, $variation_id = 0,
                            $variation = array(),
                            ['wcpaIgnore' => true]);
                        if ($_cart_item_key) {
                            $flag = true;
                            $cart_item_data['wcpa_cart_rules']['combined_products'][$i]['value'][$k]['cartKey'] = $_cart_item_key;
                        }
                    }
                    remove_filter('woocommerce_cart_id', array($this, 'update_cart_item_key'), 10);
                }
            }
            if ($flag) {
                $cart_contents = WC()->cart->get_cart_contents();
                $cart_contents[$cart_item_key] = $cart_item_data;
                WC()->cart->set_cart_contents($cart_contents);
            }
        }
        $this->pg_cartKey = '';
    }

    /**
     * Filter the cart item key
     *
     * @param $cart_key
     *
     * @return string
     * @since 4.1.0
     */
    public
    function update_cart_item_key(
        $cart_key
    )
    {
        return $cart_key . '_' . substr($this->pg_cartKey, 10);
    }

    public function after_product_added_to_cart(
        $cart_item_key,
        $product_id,
        $quantity,
        $variation_id,
        $variation
    )
    {
//        if (isset($_POST['wcpa_current_cart_key']) && ! empty($_POST['wcpa_current_cart_key'])) {
//            $cart_key = sanitize_text_field($_POST['wcpa_current_cart_key']);
//            if ($cart_key == $cart_item_key) {
//                /** when the use resubmit without any changes in values, the key will be same, and the system will increase the quantity
//                 *Here we need to reset the quantity with new value
//                 */
//                WC()->cart->set_quantity($cart_item_key, $quantity);
//            } else {
//                WC()->cart->remove_cart_item($cart_key);
//            }
//            unset($_POST['wcpa_current_cart_key']);
//            /** reset this once executed, other wise it can cause issue if add on as product groups */
//        }
        $this->add_addon_product_to_cart($cart_item_key);
    }

    /**
     * Appending codes after product name in cart
     *
     * @param $cart_item
     * @param $cart_item_key
     *
     * @since 4.1.8
     */
    public
    function after_cart_item_name(
        $cart_item,
        $cart_item_key
    )
    {
        if (Config::get_config('enable_cart_item_edit')) {
            if (
                isset($cart_item[WCPA_CART_ITEM_KEY]) && !empty($cart_item[WCPA_CART_ITEM_KEY]) &&
                isset($cart_item['data']) && !empty($cart_item['data'])
            ) {
                $product = $cart_item['data'];
                $product_link = add_query_arg('cart_key', $cart_item_key, $product->get_permalink($cart_item));
                $edit_text = Config::get_config('cart_edit_text');
                echo apply_filters(
                    'wcpa_cart_product_edit_button',
                    "<a href='$product_link' class='wcpa_edit_product'>$edit_text</a>",
                    $product_link,
                    $cart_item_key,
                    $edit_text,
                    $product
                );
            }
        }
    }

    public
    function after_cart_item_quantity_update(
        $cart_item_key,
        $quantity,
        $old_quantity,
        $cart_obj
    )
    {
        if ($quantity != $old_quantity) {
            if (method_exists($cart_obj, 'get_cart')) {
                $cart_contents = $cart_obj->get_cart();
            } else {
                $cart_contents = $cart_obj->cart_contents;
            }
            $cart_item = $cart_obj->cart_contents[$cart_item_key];

            if (isset($cart_item['wcpa_cart_rules']) && isset($cart_item['wcpa_cart_rules']['combined_products'])) {
                $products = $cart_item['wcpa_cart_rules']['combined_products'];
                if (isset($products) && !empty($products)) {
//                    // Flag to get product ID for editing cart id
//                    $this->pg_productID = $product_id;
//                    $this->pg_cartKey   = $cart_item_key;
                    foreach ($products as $dField) {
                        foreach ($dField['value'] as $k => $v) {
                            $p_id = $v['value'];
                            $p_quantity = $v['quantity'];
                            if (!isset($dField['form_data']->independentQuantity) || !$dField['form_data']->independentQuantity) {
                                $p_quantity *= $quantity;
                                if (isset($cart_obj->cart_contents[$v['cartKey']])) {
                                    WC()->cart->set_quantity($v['cartKey'], $p_quantity, false);
                                    //TODO need handle if the product is out of stock
                                    //  $cart_obj->cart_contents[$v['cartKey']]['quantity'] = $p_quantity;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function cart_item_subtotal(
        $total,
        $cart,
        $cart_item_key
    )
    {
        $_product = $cart['data'];
        $taxRate = getTaxRate($_product, true);// reset for each product, as it can vary for products

        if (isset($cart['wcpaHasFee']) && $cart['wcpaHasFee']) {
            $items = '';

            foreach ($this->fees as $fee) {
                if ($fee['price'] > 0 && in_array($cart_item_key, $fee['key'])) {
                    if (!Config::get_config('show_fee_in_line_subtotal') && $fee['type'] == 'woo_fee') {
                        /* custom_fee has to be show always, even if the free 'show_fee_in_line_subtotal' is disabled  */
                        continue;
                    }
                    $price = 0.0;
                    $_price = 0.0;
                    if (is_array($fee['price'])) {
                        foreach ($fee['key'] as $i => $key) {
                            if ($key == $cart_item_key) {
                                $price += $fee['price'][$i] * $this->con_unit * $this->taxRate;
                                $_price += $fee['_price'][$i] * $this->con_unit * $this->taxRate;
                            }
                        }
                    } else {
                        $price += $fee['price'] * $this->con_unit * $taxRate;
                        $_price += $fee['_price'] * $this->con_unit * $taxRate;
                    }

                    $items .= '<br>' . wcpaPrice(
                            $price, $_price
                        ) . '<small class="woocommerce-Price-taxLabel tax_label">(' . $fee['label'] . ')</small>';
                }
            }

            $total .= $items;
        }

        return $total;
    }

    public
    function get_discounted_price(
        $total,
        $item
    )
    {
        //Using for custom fee calculation

//        $currency = new Currency();
        foreach ($this->fees as $fee) {
            if (isset($fee['price']) && isset($fee['type']) && $fee['type'] !== 'woo_fee') {
                $price = 0.0;
                if (is_array($fee['price'])) {
                    foreach ($fee['key'] as $i => $key) {
                        if ($key == $item['key']) {
                            $price = $fee['price'][$i];
                        }
                    }
                } else {
                    if (in_array($item['key'], $fee['key'])) {
                        $price = $fee['price'];
                    }

                }

                $total += ($price);
            }
        }


        return $total;
    }

    public
    function cart_calculate_fees(
        $cart_obj
    )
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        $cart_contents = $cart_obj->get_cart();
        global $woocommerce;

        $fees = array();

        $discounts = array();
        if (Config::get_config('wcpa_apply_coupon_to_fee')) {
            $totals = $cart_obj->get_totals();
            $sub_total = $totals['subtotal'];
            foreach ($cart_obj->get_coupon_discount_totals() as $coupon => $value) {
                $coupon_obj = new \WC_Coupon($coupon);
                if ($coupon_obj) {
                    if ($coupon_obj->is_type('percent')) {
                        $discounts[] = ['type' => 'percent', 'value' => $coupon_obj->get_amount('edit')];
                    } elseif ($coupon_obj->is_type('fixed_cart')) {
                        $amount = $coupon_obj->get_amount('edit') - $value;
                        $discounts[] = ['type' => 'fixed', 'value' => $amount];
                    }
                }
            }
        }
        // $cart_obj->get_coupon_discount_totals()
        $fee_total = 0;
        // sum fee if labels are same
        $added_fees = array();
        $hide_zero = Config::get_config('cart_hide_price_zero');


        foreach ($this->fees as $fee) {
            if (isset($fee['price']) && isset($fee['type']) && $fee['type'] == 'woo_fee') {

                // $fee_total += $fee['price'];

                $price = is_array($fee['price']) ? array_sum($fee['price']) : $fee['price'];
                if ($hide_zero == true && $price == 0) {
                    continue;
                }
                $fee_id = sanitize_title($fee['label']);
                if (isset($added_fees[$fee_id])) {
                    $fee['label'] = $fee['label'] . '(' . ($added_fees[$fee_id] + 1) . ')';
                    $added_fees[$fee_id] += 1;
                } else {
                    $added_fees[$fee_id] = 1;
                }
                $_product = $cart_contents[$fee['key'][0]]['data'];
                if (is_object($_product)) {
                    $tax_status = $tax_status = $_product->is_taxable();// mow checking the first  cart key only for tax status
                    $tax_class = $cart_contents[$fee['key'][0]]['data']->get_tax_class();
                } else {
                    $tax_status = false;
                    $tax_class = '';
                }


                $consider_tax = Config::get_config('consider_product_tax_conf');
//                $currency     = new Currency();
                if ($consider_tax == false) {
//                    $_price = Currency::mayBeConvert($price, 'add_fee');
                    $woocommerce->cart->add_fee($fee['label'], $price);
                } elseif ($tax_status == true && wc_prices_include_tax()) {
                    $base_tax_rates = WC_Tax::get_base_tax_rates($tax_class);
                    $taxes = WC_Tax::calc_tax($price, $base_tax_rates, true);
                    $_price = ($price - array_sum($taxes));
                    $woocommerce->cart->add_fee($fee['label'], $_price, $tax_status, $tax_class);
                } else {

//                    $_price = Currency::mayBeConvert($price, 'add_fee');
                    $woocommerce->cart->add_fee($fee['label'], $price, $tax_status, $tax_class);
                }

                // apply coupon discount on the fee amount
                if (!empty($discounts)) {
                    $discount = 0;
                    $total_discount = 0;
                    $price = wc_add_number_precision($price);
                    $discounted_price = $price - $discount;
                    foreach ($discounts as $coupon_amount) {
                        $price_to_discount = ('yes' === get_option(
                                'woocommerce_calc_discounts_sequentially',
                                'no'
                            )) ? $discounted_price : $price;
                        if ('percent' == $coupon_amount['type']) {
                            $discount = floor($price_to_discount * ($coupon_amount['value'] / 100));
                        } else {
                            $discount = wc_add_number_precision($coupon_amount['value']);
                        }

                        $discount = min($price_to_discount, $discount);
                        $total_discount += $discount;
                        $discounted_price = $discounted_price - $discount;
                    }
                    $total_discount = wc_remove_number_precision($total_discount);
                    //apply coupon discount on the fee amount
                    $woocommerce->cart->add_fee(
                        $fee['label'] . __(' - Discount', 'woo-custom-product-addons-pro'),
                        -$total_discount,
                        false,
                        ''
                    );
                }
            }
        }
    }

    public
    function before_calculate_totals_start(
        $cart_obj
    )
    {
        $this->before_calculate_totals($cart_obj, 'start');
    }

    public
    function before_calculate_totals(
        $cart_obj,
        $priority = 'start'
    )
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (method_exists($cart_obj, 'get_cart')) {
            $cart_contents = $cart_obj->get_cart();
        } else {
            $cart_contents = $cart_obj->cart_contents;
        }

        // Products Group Product Manage
//        $priceObject    = new Price(false, false, false,false);
        $feeOnceInOrder = Config::get_config('count_fee_once_in_a_order');

        $currency = get_woocommerce_currency();

        foreach ($cart_contents as $key => $cart_item) {
            $price = 0.0;
            $rawPrice = 0.0;
            $excludeDiscountPrice = 0.0;
            $rawExcludeDiscountPrice = 0.0;
            $hasFee = false;
            if (isset($cart_contents[$key]['wcpa_options_price_' . $priority])) {
                continue;
            }
            if (isset($cart_item[WCPA_CART_ITEM_KEY]) && is_array(
                    $cart_item[WCPA_CART_ITEM_KEY]
                ) && !empty($cart_item[WCPA_CART_ITEM_KEY])) {
                $quantity = $cart_item['quantity'];
                if (isset($cart_item['wcpa_cart_rules']) && $cart_item['wcpa_cart_rules']['currency'] !== $currency) {
                    $product_id = $cart_item['data']->get_id();
                    $cart_contents[$key] = $cart_item = apply_filters('wcpa_add_cart_item_data', $cart_item,
                        $product_id, false, $quantity);
                }
                /** if quantity changed, process again  */
                if (isset($cart_item['wcpa_cart_rules']) && $cart_item['wcpa_cart_rules']['quantity'] !== $quantity) {
                    $product_id = $cart_item['data']->get_id();
                    $cart_contents[$key] = $cart_item = apply_filters('wcpa_add_cart_item_data', $cart_item,
                        $product_id, false, $quantity);
                }


                foreach ($cart_item[WCPA_CART_ITEM_KEY] as $sectionKey => $section) {
                    $form_rules = $section['extra']->form_rules;
                    $form_id = $section['extra']->form_id;
                    $_product = $cart_item['data'];
                    $this->discountUnit = 1;
                    if (!isset($form_rules['exclude_from_discount']) || !$form_rules['exclude_from_discount']) {
                        $this->discountUnit = Discounts::getDiscountRule($_product, true);
                    }
                    $this->taxRate = getTaxRate($_product, true);// reset for each product, as it can vary for products

                    foreach ($section['fields'] as $rowIndex => $row) {
                        foreach ($row as $colIndex => $field) {
                            if ($field['type'] == 'productGroup' && isset($field['form_data']->independent) && $field['form_data']->independent) {
                                continue;
                            }

                            $dField = &$cart_contents[$key][WCPA_CART_ITEM_KEY][$sectionKey]['fields'][$rowIndex][$colIndex];

                            /** this checking was done to provide support to remove addon values from applying discount. Now we are trying to make each
                             *discount plugins compatible by specific coding. removing this general method as it is not correctly fit for all plugins
                             */
//                            if (isset($form_rules['exclude_from_discount']) && $form_rules['exclude_from_discount']) {
//                                if ($priority == 'start') {
//                                    continue;
//                                }
//                            } else {
//                                if ($priority == 'end') {
//                                    continue;
//                                }
//                            }


                            /** this codes removing as of now, it was used to re-calcualte formulas when quantity changed
                             *Now this have handled by completely re-processing fields
                             */
//                            foreach (
//                                [
//                                    'label',
//                                    'description',
//                                    'value',
//                                ] as $tag
//                            ) {
//                                $frTag = $tag.'Formula';
//                                if (isset($dField[$frTag]) && ! isEmpty($dField[$frTag])) {
//                                    /* to process the formula with quantity  while changing quantity */
//                                    $formula = $priceObject->contentFormula($dField[$frTag], $dField, false, $quantity);
//                                    if (is_string($formula)) {
//                                        $dField[$tag] = $formula;
//                                    } else {
//                                        $dField[$tag]   = $formula['label'];
//                                        $dField[$frTag] = $formula['formula'];
//                                    }
//                                }
//                            }

//                            if (isset($dField['priceFormula']) && ! isEmpty($dField['priceFormula'])) {
//                                if (is_array($dField['price'])) {
//                                    foreach ($dField['price'] as $k => $_price) {
//                                        if (isset($dField['priceFormula'][$k])) {
//                                            $priceCalculated = $priceObject->process_custom_formula(
//                                                $dField['priceFormula'][$k],
//                                                $dField,
//                                                false,
//                                                false,
//                                                false,
//                                                $quantity
//                                            );
//                                            $optionQuantity  = 1;
//                                            if (isset($dField['value'][$k]) && isset($dField['value'][$k]['quantity'])) {
//                                                $optionQuantity = floatval($dField['value'][$k]['quantity']);
//                                            }
//
//
//                                            if (isset($priceCalculated['hasQuantity'])) {
//                                                $dField['price'][$k]        = $priceCalculated['price'] * $optionQuantity;
//                                                $dField['priceFormula'][$k] = $priceCalculated['hasQuantity'];
//                                            } else {
//                                                $dField['price'][$k] = $priceCalculated * $optionQuantity;
//                                            }
//                                        }
//                                    }
//                                } else {
//                                    $priceCalculated = $priceObject->process_custom_formula(
//                                        $dField['priceFormula'],
//                                        $dField,
//                                        false,
//                                        false,
//                                        false,
//                                        $quantity
//                                    );
//                                    $optionQuantity  = 1;
//                                    if (isset($dField['quantity']) && $dField['quantity']) {
//                                        $optionQuantity = floatval($dField['quantity']);
//                                    }
//
//                                    if (isset($priceCalculated['hasQuantity'])) {
//                                        $dField['price']        = $priceCalculated['price'] * $optionQuantity;
//                                        $dField['priceFormula'] = $priceCalculated['hasQuantity'];
//                                    } else {
//                                        $dField['price'] = $priceCalculated * $optionQuantity;
//                                    }
//                                }
//                            }


                            if (method_exists($cart_obj, 'set_cart_contents')) {
                                $cart_obj->set_cart_contents($cart_contents);
                            } else {
                                $cart_obj->cart_contents = $cart_contents; // for add to quote plugin
                            }


                            $_price = 0.0;
                            $_rawPrice = 0.0;
                            if (isset($field['price']) && is_array($field['price'])) {
                                foreach ($field['price'] as $p) {
                                    $_price += $p;
                                }
                                foreach ($field['rawPrice'] as $p) {
                                    $_rawPrice += $p;
                                }
                            } elseif (isset($field['price']) && $field['price']) {
                                $_price += $field['price'];
                                $_rawPrice += $field['rawPrice'];
                            }
                            //TODO need to test
                            $__price = apply_filters('wcpa_field_price', ['price' => $_price, 'rawPrice' => $_rawPrice], $field, $_product);
                            if (is_array($__price)) {
                                $_price = floatval($__price['price']);
                                $_rawPrice = floatval($__price['rawPrice']);
                            } else {
                                $_price = floatval($__price);
                                $_rawPrice = floatval($__price);
                            }


                            $feeType = $form_rules['process_fee_as'] ? $form_rules['process_fee_as'] : 'woo_fee';
                            if ((isset($form_rules['pric_use_as_fee']) &&
                                $form_rules['pric_use_as_fee'])) {
                                if (!isset($this->fees[$form_id])) {
                                    $this->fees[$form_id] = [
                                        'price' => $feeOnceInOrder ? 0.0 : [], // price applied discount
                                        '_price' => $feeOnceInOrder ? 0.0 : [], // price not applied discount
                                        'rawPrice' => $feeOnceInOrder ? 0.0 : [],
                                        'label' => '',
                                        'key' => [],
                                        'type' => $feeType
                                    ];
                                }

                                if ($feeOnceInOrder) {
                                    $this->fees[$form_id]['price'] = $_price * $this->discountUnit;
                                    $this->fees[$form_id]['_price'] = $_price;
                                    $this->fees[$form_id]['rawPrice'] = $_rawPrice;
                                    $this->fees[$form_id]['key'] = [$key];
                                } else {
                                    $this->fees[$form_id]['price'][] = $_price * $this->discountUnit;
                                    $this->fees[$form_id]['_price'][] = $_price;
                                    $this->fees[$form_id]['rawPrice'][] = $_rawPrice;
                                    $this->fees[$form_id]['key'][] = $key;
                                }
                                $this->fees[$form_id]['label'] = $form_rules['fee_label'];
                                $this->fees[$form_id]['taxRate'] = $this->taxRate;

                                $hasFee = true;
                            } elseif ((isset($field['is_fee']) && $field['is_fee'] === true)) {
                                $hasFee = true;
                                if (is_array($field['price'])) {
                                    foreach ($field['price'] as $i => $p) {
                                        if (isset($field['form_data']->isClone) && $field['form_data']->isClone) {
                                            $elem_id = sanitize_key(
                                                $form_id . '_' . (is_array($field['form_data']->name) ? implode(
                                                    '_',
                                                    $field['form_data']->name
                                                ) : $field['form_data']->name) . '_' . $i
                                            );
                                        } else {
                                            $elem_id = sanitize_key(
                                                $form_id . '_' . $field['form_data']->elementId . '_' . $i
                                            );
                                        }

                                        if (!isset($this->fees[$elem_id])) {
                                            $this->fees[$elem_id] = [
                                                'price' => $feeOnceInOrder ? 0.0 : [],
                                                '_price' => $feeOnceInOrder ? 0.0 : [],
                                                'rawPrice' => $feeOnceInOrder ? 0.0 : [],
                                                'label' => '',
                                                'key' => [],
                                                'type' => $feeType
                                            ];
                                        }

                                        if ($feeOnceInOrder) {
                                            $this->fees[$elem_id]['price'] = $p * $this->discountUnit;
                                            $this->fees[$elem_id]['_price'] = $p;
                                            $this->fees[$elem_id]['rawPrice'] = $field['rawPrice'][$i];
                                            $this->fees[$elem_id]['key'] = [$key];
                                        } else {
                                            $this->fees[$elem_id]['price'][] = $p * $this->discountUnit;
                                            $this->fees[$elem_id]['_price'][] = $p;
                                            $this->fees[$elem_id]['rawPrice'][] = $field['rawPrice'][$i];
                                            $this->fees[$elem_id]['key'][] = $key;
                                        }
                                        $this->fees[$elem_id]['label'] = $this->get_fee_label($field, $i);
                                        $this->fees[$elem_id]['taxRate'] = $this->taxRate;
                                        // $price += $p;
                                    }
                                } elseif ($field['price']) {
                                    if (isset($field['form_data']->isClone) && $field['form_data']->isClone) {
                                        $elem_id = sanitize_key(
                                            $form_id . '_' . (is_array($field['form_data']->name) ? implode(
                                                '_',
                                                $field['form_data']->name
                                            ) : $field['form_data']->name)
                                        );
                                    } else {
                                        $elem_id = sanitize_key($form_id . '_' . $field['form_data']->elementId);
                                    }


                                    if (!isset($this->fees[$elem_id])) {
                                        $this->fees[$elem_id] = [
                                            'price' => $feeOnceInOrder ? 0.0 : [],
                                            '_price' => $feeOnceInOrder ? 0.0 : [],
                                            'rawPrice' => $feeOnceInOrder ? 0.0 : [],
                                            'label' => '',
                                            'key' => [],
                                            'type' => $feeType
                                        ];
                                    }

                                    if ($feeOnceInOrder) {
                                        $this->fees[$elem_id]['price'] = $_price * $this->discountUnit;
                                        $this->fees[$elem_id]['_price'] = $_price;
                                        $this->fees[$elem_id]['rawPrice'] = $_rawPrice;
                                        $this->fees[$elem_id]['key'] = [$key];
                                    } else {
                                        $this->fees[$elem_id]['price'][] = $_price * $this->discountUnit;
                                        $this->fees[$elem_id]['_price'][] = $_price;
                                        $this->fees[$elem_id]['rawPrice'][] = $_rawPrice;
                                        $this->fees[$elem_id]['key'][] = $key;
                                    }
                                    $this->fees[$elem_id]['label'] = $this->get_fee_label($field);
                                    $this->fees[$elem_id]['taxRate'] = $this->taxRate;
                                }
                            } elseif (!isset($field['is_show_price']) || $field['is_show_price'] === false) {
                                $price += $_price;
                                $rawPrice += $_rawPrice;
                                if (isset($form_rules['exclude_from_discount']) && $form_rules['exclude_from_discount']) {
                                    $excludeDiscountPrice += $_price;
                                    $rawExcludeDiscountPrice += $_rawPrice;
                                }
                            }
                        }
                    }
                }

                $cart_contents[$key]['wcpa_options_price_' . $priority] = $price;

                $cart_contents[$key]['wcpaHasFee'] = (!isset($cart_contents[$key]['wcpaHasFee']) || $cart_contents[$key]['wcpaHasFee'] == false) ? $hasFee : $cart_contents[$key]['wcpaHasFee'];

                if (method_exists($cart_obj, 'set_cart_contents')) {
                    $cart_obj->set_cart_contents($cart_contents);
                } else {
                    $cart_obj->cart_contents = $cart_contents; // for add to quote plugin
                }

//                $mcObject = new Currency();
//                $price2 = $price;


//                if ($priority == 'end' && isset($cart_contents[$key]['wcpa_options_price_start'])) {
//                    $price2 = $price + $cart_contents[$key]['wcpa_options_price_start'];
                /* $price2 using in case any of the product price override option has set. in other cases, start price
                will be already have added to the product base price, so adding it here again can cause wrong product price
                 */
//                    $price2 = Currency::mayBeConvert($price2);
//                }
//                $price                = Currency::mayBeConvert($price);
//                $excludeDiscountPrice = Currency::mayBeConvert($excludeDiscountPrice);
//                $productPrice = floatval($cart_item['data']->get_price('edit')); // dont call discounted price here, as we are passing to discount again


                $productPrice = floatval($cart_item['data']->get_price('edit'));

                $productPriceConverted = floatval(Currency::getProductPrice($productPrice, $cart_item['data']));
                $rawProductPrice = $productPrice;
                if ($productPriceConverted > 0 && abs(($productPrice - $productPriceConverted) / $productPriceConverted) < 0.00001) {
                    // checks if not same value
                    $rawProductPrice = $productPrice / Currency::getConUnit();
                }
                //TODO multi currency - some plugins returns converted product price, some not
                if (isset($cart_item['wcpa_cart_rules']['price_override'])) {
                    $priceOverride = $cart_item['wcpa_cart_rules']['price_override'];
                    if ($priceOverride == 'maximum') {
                        if ($price > $productPrice) {
                            $total_price = $price;
                            $total_rawPrice = $rawPrice;
                        } else {
                            $total_price = $productPriceConverted;
                            $total_rawPrice = $rawProductPrice;
                            $excludeDiscountPrice = 0;
                            $rawExcludeDiscountPrice = 0;
                        }
                    } elseif ('if_gt_zero' == $priceOverride && $price > 0) {
                        $total_price = $price;
                        $total_rawPrice = $rawPrice;
                    } elseif ('always' == $priceOverride) {
                        $total_price = $price;
                        $total_rawPrice = $rawPrice;
                    } else {
                        $total_price = $price + $productPriceConverted;
                        $total_rawPrice = $rawPrice + $rawProductPrice;
                    }
                } else {
                    $total_price = $price + $productPriceConverted;
                    $total_rawPrice = $rawPrice + $rawProductPrice;
                }


                if ($total_price < 0) {
                    $total_price = 0; // can't be the price be negative at any case
                }

                //  $value['data']->set_price(round($total_price, wc_get_price_decimals()));
                if (method_exists($cart_obj, 'set_cart_contents')) {
                    $cart_contents[$key]['wcpa_price'] = [
                        'total' => $total_price,
                        'addon' => $price,
                        'product' => $productPrice,
                        'excludeDiscount' => $excludeDiscountPrice
                    ];
                    $cart_obj->set_cart_contents($cart_contents);
                }

                $total_priceUpdated = Discounts::cartPrice($total_price, $excludeDiscountPrice);
                if ($total_priceUpdated < $total_price) {
                    /** if it has reduced excludePrice, reduce oy from rowPrice as well*/
                    $total_rawPrice = $total_rawPrice - $rawExcludeDiscountPrice;
                }
                $total_price = $total_priceUpdated;
//                $cart_item['data']->set_price(Currency::cartSetPrice(Discounts::cartPrice($total_price,
//                    $total_rawPrice,$excludeDiscountPrice)));
//
                //multi currency -  some plugins convert it again, some doesnt
                $cart_item['data']->set_price(Currency::cartSetPrice($total_price, $total_rawPrice));
                // changed as it causing rounding issues for product price set excluding tax #6789


                if (isset($cart_item['wcpa_cart_rules']['thumb_image']) && is_numeric(
                        $cart_item['wcpa_cart_rules']['thumb_image']
                    )) {
                    $cart_item['data']->set_image_id($cart_item['wcpa_cart_rules']['thumb_image']);
                }
            }
        }

        if (isset($key)) {
            /** set fee for last item */
            $fee = $this->fee_sum();
            if ($fee) {
                $cart_contents[$key]['wcpaFee'] = $fee;
                $cart_obj->set_cart_contents($cart_contents);
            } elseif (isset($cart_contents[$key]['wcpaFee'])) {
                $cart_contents[$key]['wcpaFee'] = false;
                $cart_obj->set_cart_contents($cart_contents);
            }
        }


        // remove_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 10);
        //remove_action('woocommerce_cart_loaded_from_session', array($this, 'before_calculate_totals'), 10);
    }

    public function get_fee_label(
        $v,
        $i = false
    )
    {
        if (is_array($v['value']) && $i !== false) {
            if (isset($v['form_data']->fee_label) && !empty($v['form_data']->fee_label)) {
                $fee_label = $v['form_data']->fee_label;
                preg_match("/{(.*)}/", $fee_label, $matches);
                if ($matches && count($matches)) {
                    $fee_label = str_replace(
                        ['{field_label}', '{option_label}', '{option_value}'],
                        [
                            $v['label'],
                            isset($v['value'][$i]['label']) ? $v['value'][$i]['label'] : '',
                            isset($v['value'][$i]['value']) ? $v['value'][$i]['value'] : '',
                        ],
                        $fee_label
                    );

                    return $fee_label;
                } else {
                    return $fee_label;
                }
                // return $v['form_data']->fee_label;
            } else {
                return isset($v['value'][$i]['label']) ? $v['value'][$i]['label'] : (isset($v['value'][$i]['value']) ? $v['value'][$i]['value'] : '');
                //return ($v['label'] == WCPA_EMPTY_LABEL) ? strip_tags($v['value']) : $v['label'];
            }
        } else {
            if (isset($v['form_data']->fee_label) && !empty($v['form_data']->fee_label)) {
                return $v['form_data']->fee_label;
            } else {
                return ($v['label'] == WCPA_EMPTY_LABEL) ? strip_tags(
                    is_array($v['value']) ? '' : $v['value']
                ) : $v['label'];
            }
        }
    }

    public function fee_sum()
    {
        $wooFee = 0;
        $customFee = 0;
        if (isset($this->fees) && is_array($this->fees)) {
            foreach ($this->fees as $fee) {
                if (isset($fee['price']) && isset($fee['type']) && $fee['type'] !== 'woo_fee') {
                    $t = is_array($fee['price']) ? array_sum($fee['price']) : $fee['price'];
                    if ($fee['taxRate']) {
                        $t = $t * $fee['taxRate'];
                    }
                    $customFee += $t;
                } else {
                    $t = is_array($fee['price']) ? array_sum($fee['price']) : $fee['price'];
                    if ($fee['taxRate']) {
                        $t = $t * $fee['taxRate'];
                    }
                    $wooFee += $t;
                }
            }
        }
        if ($wooFee == 0 && $customFee == 0) {
            return false;
        }

        return ['woo_fee' => $wooFee, 'custom' => $customFee];
    }

    public function pllwc_translate_cart_item($item)
    {
        if (isset($item['wcpa_options_price_start'])) {
            unset($item['wcpa_options_price_start']);
        }

        return $item;
    }

    public
    function cart_item_from_session(
        $session_data
    )
    {
        if (isset($session_data['wcpa_options_price'])) {
            unset($session_data['wcpa_options_price']);
        }
        if (isset($session_data['wcpa_options_price_start'])) {
            unset($session_data['wcpa_options_price_start']);
        }
        if (isset($session_data['wcpa_options_price_end'])) {
            unset($session_data['wcpa_options_price_end']);
        }

        return $session_data;
    }

    public
    function before_calculate_totals_end(
        $cart_obj
    )
    {
        $this->before_calculate_totals($cart_obj, 'end');
    }

    public function after_calculate_totals($cart_obj)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        if (method_exists($cart_obj, 'get_cart')) {
            $cart_contents = $cart_obj->get_cart();
        } else {
            $cart_contents = $cart_obj->cart_contents;
        }

        foreach ($cart_contents as $key => $cart_item) {
            if (isset($cart_item['wcpa_price'])) {
                $productPrice = floatval($cart_item['data']->get_price());
                $newPrice = $productPrice + $cart_item['wcpa_price']['excludeDiscount'];
                $cart_item['data']->set_price($newPrice);
            }
        }
    }

    public
    function get_item_data(
        $item_data,
        $cart_item
    )
    {
        if (!is_array($item_data)) {
            $item_data = array();
        }
        $this->config = [
            'show_meta_in_cart' => Config::get_config('show_meta_in_cart'),
            'show_meta_in_checkout' => Config::get_config('show_meta_in_cart'),
            'cart_hide_price_zero' => Config::get_config('cart_hide_price_zero'),
            'show_price_in_cart' => Config::get_config('show_price_in_cart'),
            'show_price_in_checkout' => Config::get_config('show_price_in_checkout'),
            'show_field_price_x_quantity' => Config::get_config('show_field_price_x_quantity'),
        ];


        $_product = $cart_item['data'];
        if ((($this->config['show_meta_in_cart'] && !is_checkout()) ||
                (is_checkout() && $this->config['show_meta_in_checkout'])) &&
            isset($cart_item[WCPA_CART_ITEM_KEY]) &&

            is_array($cart_item[WCPA_CART_ITEM_KEY]) &&
            !empty($cart_item[WCPA_CART_ITEM_KEY])) {
            if ((($this->config['show_price_in_cart']
                    && !is_checkout())
                || (is_checkout() && $this->config['show_price_in_checkout']))) {
                $this->show_price = true;
            } else {
                $this->show_price = false;
            }

            $quantityMultiplier = 1;
            if ($this->config['show_field_price_x_quantity']) {
                $quantityMultiplier = $cart_item['quantity'];
            }


            if ($this->con_unit == false) {
//
//                $this->con_unit = Currency::getConUnit();
                $this->con_unit = 1;
            }
            if ($this->discountUnit == false) {
                $this->discountUnit = Discounts::getDiscountRule($_product, true);
            }
//            if ($this->taxRate == false) {
//                $this->taxRate = getTaxRate($_product, true);
//            }
            $this->taxRate = getTaxRate($_product, true);// reset for each product, as it can vary for products
            $priceMultiplier = $this->con_unit * $this->taxRate;
            $metaDisplay = new MetaDisplay(true, $this->show_price, $priceMultiplier, $quantityMultiplier,
                $this->discountUnit);

            foreach ($cart_item[WCPA_CART_ITEM_KEY] as $sectionKey => $section) {
                $form_rules = $section['extra']->form_rules;
                if (isset($form_rules['exclude_from_discount']) && $form_rules['exclude_from_discount']) {
                    $metaDisplay->setDiscountUnit(1);
                } else {
                    $metaDisplay->setDiscountUnit($this->discountUnit);
                }
                if ((isset($formRules['pric_cal_option_once']) &&
                        $formRules['pric_cal_option_once'] === true) ||
                    (isset($formRules['pric_use_as_fee']) &&
                        $formRules['pric_use_as_fee'] === true)
                ) {
                    $metaDisplay->setQuantityMultiplier(1);
                } else {
                    $metaDisplay->setQuantityMultiplier($quantityMultiplier);
                }


                foreach ($section['fields'] as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if (isset($field['form_data']->hideFieldIn_cart) && $field['form_data']->hideFieldIn_cart) {
                            continue;
                        }
                        if (in_array($field['type'], array('header', 'content', 'hidden',))
                            && (!isset($field['form_data']->show_in_checkout) || $field['form_data']->show_in_checkout == false)) {
                            continue;
                        }

                        if (!in_array($field['type'], array('separator'))) {
                            if ($field['type'] == 'productGroup' && isset($field['form_data']->independent) && $field['form_data']->independent) {
                                continue;
                            }
                            if (isset($field['is_fee']) && $field['is_fee']) {
                                $metaDisplay->setQuantityMultiplier(1);
                            } else {
                                $metaDisplay->setQuantityMultiplier($quantityMultiplier);
                            }
                            $item_data[] = array(
                                'type' => $field['type'],
                                'name' => is_array($field['name']) ? implode(',', $field['name']) : $field['name'],
                                'key' => isset($field['label']['label']) ? $field['label']['label'] : $field['label'],
//                                'value' => $this->cartDisplay($field, $form_rules, $_product, $cart_item['quantity']),
                                'value' => $metaDisplay->display($field, $form_rules),
                            );
                        }
                    }
                }
            }
        }

        return $item_data;
    }

//    public function cartDisplay($v, $form_rules, $product, $quantity = 1)
//    {
//        $display = '';
//
//
//        if ((($this->config['show_price_in_cart']
//              && ! is_checkout())
//             || (is_checkout() && $this->config['show_price_in_checkout']))) {
//            $this->show_price = true;
//        } else {
//            $this->show_price = false;
//        }
//
//        $field_price_multiplier = 1;
//        if ($this->config['show_field_price_x_quantity']) {
//            $field_price_multiplier = $quantity;
//        }
//
//        if ((isset($form_rules['pric_cal_option_once']) &&
//             $form_rules['pric_cal_option_once'] === true) ||
//            (isset($form_rules['pric_use_as_fee']) &&
//             $form_rules['pric_use_as_fee'] === true) ||
//            (isset($v['is_fee']) && $v['is_fee'] === true)
//        ) {
//            $field_price_multiplier = 1;
//        }
//
//        if ($this->con_unit == false) {
//            $currency       = new Currency();
//            $this->con_unit = $currency->getConUnit($product);
//        }
//        if ($this->taxRate == false) {
//            $this->taxRate = getTaxRate($product, true);
//        }
//
//        $field_price_multiplier = $field_price_multiplier * $this->con_unit * $this->taxRate;
//
//        $showPriceHere = true;
//        switch ($v['type']) {
//            case 'text':
//            case 'url':
//            case 'email':
//            case 'number':
//            case 'time':
//            case 'header':
//                $display = $v['value'];
//                break;
//            case 'date':
//            case 'datetime-local':
//                $display = $v['value'];
//                if ($v['value'] !== '' && isset($v['dateFormat'])) {
//                    $display = date($v['dateFormat'], strtotime($v['value']));
//                }
//                break;
//            case 'content':
//                $display = do_shortcode(nl2br($v['value']));
//                break;
//            case 'textarea':
//                $display = nl2br($v['value']);
//                break;
//            case 'color':
//                $display = '<span  style="color:' . $v['value'] . ';font-size: 20px; padding: 0;line-height: 0;">&#9632;</span>' . $v['value'];
//                break;
//            case 'file':
//                $display = $this->cart_display_file($v);
//                break;
//            case 'placeselector':
//                $display = $this->cart_display_place($v);
//                break;
//            case 'select':
//            case 'checkbox-group':
//            case 'radio-group':
//                $showPriceHere = false;
//                $display       = $this->cart_display_array($v, $field_price_multiplier);
//                break;
//            case 'image-group':
//                $display       = $this->cart_display_image($v, $field_price_multiplier);
//                $showPriceHere = false;
//                break;
//            case 'productGroup':
//                $display       = $this->cart_display_productGroup($v, $product, $field_price_multiplier);
//                $showPriceHere = false;
//                break;
//            case 'color-group':
//                $display       = $this->cart_display_colorgroup($v, $field_price_multiplier);
//                $showPriceHere = false;
//                break;
//        }
//
//        if ($showPriceHere) {
//            if ($v['price'] && $this->show_price && ( ! $this->config['cart_hide_price_zero'] || $v['price'] != 0)) {
//                $price   = $field_price_multiplier * $v['price'];
//                $display = $display . ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//            }
//        }
//
//
//        if ($display == '') {
//            $display = '&nbsp;';
//        }
//
//        return apply_filters(
//            'wcpa_display_cart_value',
//            '<div class="wcpa_cart_val wcpa_cart_type_' . $v['type'] . '" >' . $display . '</div>',
//            $display,
//            $v
//        );
//    }

//    public function cart_display_file($v)
//    {
//        $display   = '';
//        $hideImage = false;
//        if (isset($v['form_data']->hideImageIn_cart) && $v['form_data']->hideImageIn_cart) {
//            $hideImage = true;
//        }
//        $value = $v['value'];
//        if (is_array($value)) {
//            foreach ($value as $val) {
//                if (isset($val['url'])) {
//                    $display .= '<a href="' . $val['url'] . '"  target="_blank" download="' . $val['file_name'] . '">';
//                    if ( ! $hideImage && in_array(
//                            $val['type'],
//                            array(
//                                'image/jpg',
//                                'image/png',
//                                'image/gif',
//                                'image/jpeg',
//                            )
//                        )) {
//                        $display .= '<img class="wcpa_img" src="' . $val['url'] . '" />';
//                    } else {
//                        $display .= '<img class="wcpa_icon" src="' . wp_mime_type_icon($val['type']) . '" />';
//                    }
//                    $display .= '<span>' . $val['file_name'] . '</span></a>';
//                }
//            }
//        }
//
//        return $display;
//    }
//
//    public function cart_display_place($v)
//    {
//        $display = '';
//        $strings = [
//            'street'    => Config::get_config('place_selector_street'),
//            'city'      => Config::get_config('place_selector_city'),
//            'state'     => Config::get_config('place_selector_state'),
//            'zip'       => Config::get_config('place_selector_zip'),
//            'country'   => Config::get_config('place_selector_country'),
//            'latitude'  => Config::get_config('place_selector_latitude'),
//            'longitude' => Config::get_config('place_selector_longitude'),
//        ];
//        if ( ! empty($v['value']['value'])) {
//            $display = $v['value']['value'] . '<br>';
//            if ( ! empty($v['value']['split']['street_number'])) {
//                $display .= $strings['street'] . ' ' . $v['value']['split']['street_number'] . ' ' . $v['value']['split']['route'] . ' <br>';
//            }
//            if ( ! empty($v['value']['split']['locality'])) {
//                $display .= $strings['city'] . ' ' . $v['value']['split']['locality'] . '<br>';
//            }
//            if ( ! empty($v['value']['split']['administrative_area_level_1'])) {
//                $display .= $strings['state'] . ' ' . $v['value']['split']['administrative_area_level_1'] . '<br>';
//            }
//            if ( ! empty($v['value']['split']['postal_code'])) {
//                $display .= $strings['zip'] . ' ' . $v['value']['split']['postal_code'] . '<br>';
//            }
//            if ( ! empty($v['value']['split']['country'])) {
//                $display .= $strings['country'] . ' ' . $v['value']['split']['country'] . '<br>';
//            }
//            if (isset($v['value']['cords']['lat']) && ! empty($v['value']['cords']['lat'])) {
//                $display .= $strings['latitude'] . ' ' . $v['value']['cords']['lat'] . '<br>';
//                $display .= $strings['longitude'] . ' ' . $v['value']['cords']['lng'] . '<br>';
//                $display .= '<a href="https://www.google.com/maps/?q=' . $v['value']['cords']['lat'] . ',' . $v['value']['cords']['lng'] . '" target="_blank">' . __(
//                        'View on map',
//                        'woo-custom-product-addons-pro'
//                    ) . '</a> <br>';
//
//        }
//
//        return $display;
//    }
//
//    public function cart_display_array($value, $field_price_multiplier = 1)
//    {
//        $display   = '';
//        $hide_zero = $this->config['cart_hide_price_zero'];
//
//        if (is_array($value['value'])) {
//            foreach ($value['value'] as $k => $v) {
//                if ($k === 'other') {
//                    //Other text has to apply i18n
//                    $display .= '<span>' .$v['label'] . ': ' . $v['value'] . '</span>';
//                } else {
//                    //Label no need to apply i18n.
//                    $display .= '<span>' . $v['label'] . ' </span>';
//                }
//                if ($value['price'] !== false && is_array($value['price']) && $this->show_price) {
//                    if (isset($value['price'][$k]) && $value['price'][$k] !== false && ( ! $hide_zero || $value['price'][$k] != 0)) {
//                        $price   = $value['price'][$k] * $field_price_multiplier;
//                        $display .= '<span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                } else {
//                    if ($value['price'] !== false && $this->show_price && ( ! $hide_zero || $value['price'] != 0)) {
//                        $price   = $value['price'] * $field_price_multiplier;
//                        $display .= ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                }
//                $display .= '<br />';
//            }
//        } else {
//            $display = $value['value'];
//            if ($value['price'] && $this->show_price && ( ! $hide_zero || $value['price'] != 0)) {
//                $price   = $value['price'] * $field_price_multiplier;
//                $display = $display . ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//            }
//        }
//
//        return $display;
//    }
//
//    public function cart_display_image($value, $product, $field_price_multiplier = 1)
//    {
//        $display   = '';
//        $class     = '';
//        $hide_zero = $this->config['cart_hide_price_zero'];
//
//        if (isset($value['form_data']->img_preview) && $value['form_data']->img_preview) {
//            $class = 'class="wcpa_cart_img_preview ';
//            if (isset($value['form_data']->img_preview_disable_mobile) && $value['form_data']->img_preview_disable_mobile) {
//                $class .= 'wcpa_product_img_preview_disable_mobile ';
//            }
//            $class .= '"';
//        }
//        $hideImage = false;
//        if (isset($value['form_data']->hideImageIn_cart) && $value['form_data']->hideImageIn_cart) {
//            $hideImage = true;
//        }
//        if (is_array($value['value'])) {
//            foreach ($value['value'] as $k => $v) {
//                if ($k === 'other') {
//
//                    $display .= '<p>' . $v['label'] . ': ' . $v['value'] . '';
//                } else {
//                    $img_size_style = ((isset($value['form_data']->disp_size_img) && $value['form_data']->disp_size_img > 0) ? 'style="width:' . $value['form_data']->disp_size_img . 'px"' : '');
//
//                    $display .= '<p ' . $class . '>' . (! $hideImage ? '<img ' . $img_size_style . ' data-src="' . $v['image'] . '" src="' . $v['image'] . '" />' : '');
//                    if ( ! empty($v['label'])) {
//                        $display .= ' <span >' . $v['label'] . '</span> ';
//                    }
//                }
//
//                if ($value['price'] && is_array($value['price']) && $this->show_price) {
//                    if (isset($value['price'][$k]) && $value['price'][$k] !== false && ( ! $hide_zero || $value['price'][$k] != 0)) {
//                        $price   = $value['price'][$k] * $field_price_multiplier;
//                        $display .= '<span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                } else {
//                    if ($value['price'] !== false && $this->show_price && ( ! $hide_zero || $value['price'] != 0)) {
//                        $price   = $value['price'] * $field_price_multiplier;
//                        $display .= ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                }
//
//                $display .= '</p>';
//            }
//        } else {
//            $display = $value['value'];
//            if ($value['price'] && $this->show_price && ( ! $hide_zero || $value['price'] != 0)) {
//                $price   = $value['price'] * $field_price_multiplier;
//                $display = $display . ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//            }
//        }
//
//        return $display;
//    }
//
//    public function cart_display_productGroup($value, $product, $field_price_multiplier = 1)
//    {
//        $display   = '';
//        $class     = 'wcpa_cart_productGroup ';
//        $hide_zero = Config::get_config('cart_hide_price_zero', false);
//        if (isset($value['form_data']->img_preview) && $value['form_data']->img_preview) {
//            $class .= ' wcpa_cart_img_preview ';
//            if (isset($value['form_data']->img_preview_disable_mobile) && $value['form_data']->img_preview_disable_mobile) {
//                $class .= 'wcpa_product_img_preview_disable_mobile ';
//            }
//        }
//        if ($class != '') {
//            $class .= ' class="' . $class . '" ';
//        }
//        $hideImage = true;
//        if (isset($value['form_data']->show_image) && $value['form_data']->show_image) {
//            if (isset($value['form_data']->hideImageIn_cart) && $value['form_data']->hideImageIn_cart) {
//                $hideImage = true;
//            } else {
//                $hideImage = false;
//            }
//        }
//
//        if (is_array($value['value'])) {
//            foreach ($value['value'] as $k => $v) {
//                $pro_image = '';
//                if ( ! $hideImage) {
//                    if ($v->get_image_id()) {
//                        $pro_image = wp_get_attachment_url($v->get_image_id());
//                    }
//
//                    if ( ! $pro_image) {
//                        $pro_image = wc_placeholder_img_src('woocommerce_thumbnail');
//                    }
//                }
//                $img_size_style = ((isset($value['form_data']->disp_size_img) && $value['form_data']->disp_size_img > 0) ? 'style="width:' . $value['form_data']->disp_size_img . 'px"' : '');
//
//                $display .= '<p ' . $class . '>' . (! ($hideImage) ? '<img ' . $img_size_style . ' data-src="' . $pro_image . '"  src="' . $pro_image . '" />' : '');
//
//                $label = $v->get_title();
//                if ( ! empty($label)) {
//                    $display .= ' <span >' . $label . '</span> ';
//                }
//
//                if ( ! empty($value['quantities'])) {
//                    $display .= ' <span class="wcpa_productGroup_cart_quantity">x ' . $value['quantities'][$k] . '</span> ';
//                }
//
//                if ($value['price'] && is_array($value['price']) && $this->show_price) {
//                    if (isset($value['price'][$k]) && $value['price'][$k] !== false && ( ! $hide_zero || $value['price'][$k] != 0)) {
//                        $price   = wcpa_get_price_cart($product, $value['price'][$k]);
//                        $display .= '<span class="wcpa_cart_price">(' . wcpa_price(
//                                $price * $field_price_multiplier
//                            ) . ')</span>';
//                    }
//                } else {
//                    if ($value['price'] !== false && $this->show_price && ( ! $hide_zero || $value['price'] != 0)) {
//                        $price   = wcpa_get_price_cart($product, $value['price']);
//                        $display .= ' <span class="wcpa_cart_price">(' . wcpa_price(
//                                $price * $field_price_multiplier
//                            ) . ')</span>';
//                    }
//                }
//
//                $display .= '</p>';
//            }
//        }
//
//        return $display;
//    }
//
//    public function cart_display_colorgroup($value,  $field_price_multiplier = 1)
//    {
//        $display   = '';
//
//
//        if (is_array($value['value'])) {
//            foreach ($value['value'] as $k => $v) {
//                if ($k === 'other') {
//                    $display .= '<p>' .$v['label'] . ': ' . $v['value'] . '';
//                } else {
//                    $display .= '<p>';
//                    $size    = '';
//                    if (isset($value['form_data']->cart_display_type) && $value['form_data']->cart_display_type == 'text') {
//                        $display .= '<span style="color:' . $v['color'] . ';font-size: 20px;padding: 0;line-height: 0;">&#9632;</span>' . (! isEmpty(
//                                $v['label']
//                            ) ? $v['label'] : $v['value']) . '  ';
//                    } else {
//                        if (isset($value['form_data']->disp_size) && $value['form_data']->disp_size > 10) {
//                            $size .= 'height:' . $value['form_data']->disp_size . 'px;';
//                            if (isset($value['form_data']->show_label_inside) && $value['form_data']->show_label_inside) {
//                                $size .= 'min-width:' . $value['form_data']->disp_size . 'px;line-height:' . ($value['form_data']->disp_size - 2) . 'px;';
//                            } else {
//                                $size .= 'width:' . $value['form_data']->disp_size . 'px;';
//                            }
//                        }
//
//                        if (isset($value['form_data']->show_label_inside) && $value['form_data']->show_label_inside) {
//                            $display .= '<span class="wcpa_cart_color label_inside disp_' . $value['form_data']->disp_type . ' ' . colorClass(
//                                    $v['color']
//                                ) . ' ' . ((isset($value['form_data']->adjust_width) && $value['form_data']->adjust_width) ? 'wcpa_adjustwidth' : '') . '"'
//                                        . ' style="background-color:' . $v['color'] . ';' . $size . '" >'
//                                        . '' . $v['label'] . '</span>';
//                        } else {
//                            $display .= '<span class="wcpa_cart_color disp_' . $value['form_data']->disp_type . ' ' . colorClass(
//                                    $v['color']
//                                ) . ' ' . ((isset($value['form_data']->adjust_width) && $value['form_data']->adjust_width) ? 'wcpa_adjustwidth' : '') . '"'
//                                        . ' style="background-color:' . $v['color'] . ';' . $size . '" ></span>';
//                            if ( ! empty($v['label'])) {
//                                $display .= ' <span >' . $v['label'] . '</span> ';
//                            }
//                        }
//                    }
//                }
//
//                if ($value['price'] && is_array(
//                        $value['price']
//                    ) && $this->show_price && ( ! $this->config['cart_hide_price_zero'] || $value['price'] != 0)) {
//                    if (isset($value['price'][$k]) && $value['price'][$k] !== false) {
//                        $price   = $value['price'][$k] * $field_price_multiplier;
//                        $display .= '<span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                } else {
//                    if ($value['price'] !== false && $this->show_price && ( ! $this->config['cart_hide_price_zero'] || $value['price'] != 0)) {
//                        $price   = $value['price'] * $field_price_multiplier;
//                        $display .= ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//                    }
//                }
//
//                $display .= '</p>';
//            }
//        } else {
//            $display = $value['value'];
//            if ($value['price'] && $this->show_price && ( ! $this->config['cart_hide_price_zero'] || $value['price'] != 0)) {
//                $price   = $value['price'] * $field_price_multiplier;
//                $display = $display . ' <span class="wcpa_cart_price">(' . wcpaPrice($price) . ')</span>';
//            }
//        }
//
//        return $display;
//    }
}
