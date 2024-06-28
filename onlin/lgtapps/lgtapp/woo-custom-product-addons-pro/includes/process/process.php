<?php


namespace Acowebs\WCPA;

use stdClass;
use WC_AJAX;
use WC_Session_Handler;
use WP_REST_Response;

class Process
{
    public $thumb_image = false;
    public $subProducts = [];
    public $checkoutFields = [];
    private $processed_data = array();
    private $form_data = array();
    private $fields = false;
    private $product = false;
    private $quantity = 1;
    private $token;
    private $orderAgainData = false;
    /**
     * @var mixed
     */
    private $formConf;
    private $formulas;
    /**
     * @var false|\WC_Product|null
     */
    private $parentProduct=false;

    public function __construct()
    {
        $this->token = WCPA_TOKEN;
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 4);
        add_filter('wcpa_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 4);
        add_filter('woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'), 10, 4);
        add_action('rest_api_init', array($this, 'register_routes'));
        add_filter('wp_check_filetype_and_ext', [$this, 'add_multiple_mime_types'], 99, 3);
        add_action('wc_ajax_wcpa_ajax_add_to_cart', array($this, 'ajax_add_to_cart'));

        add_filter('woocommerce_order_again_cart_item_data', array($this, 'order_again_cart_item_data'), 50, 3);
    }


    public function order_again_cart_item_data($cart_item_data, $item, $order)
    {
        $meta_data            = $item->get_meta(WCPA_ORDER_META_KEY);
        $this->orderAgainData = $meta_data;
        $product_id           = (int) $item->get_product_id();
        $variation_id         = (int) $item->get_variation_id();
        $quantity             = $item->get_quantity();

        $passed = $this->add_to_cart_validation(true,
            $product_id, $quantity, $variation_id, true);
        if ( ! $passed) {
// set error
            $product = $item->get_product();
            $name    = '';
            if ($product) {
                $name = $product->get_name();
            }
            wc_add_notice(sprintf(
            /* translators: %s Product Name */
                __('Addon options of product %s has been changed, Cannot proceed with older data. 
            You can go to product page and fill the addon fields again inorder to make new order',
                    'woo-custom-product-addons-pro'),
                $name),
                'error');

            return $cart_item_data;
        }

        $cart_item_data = $this->add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity);

        /** remove validation as already done */
        remove_filter('woocommerce_add_to_cart_validation', array($this, 'add_to_cart_validation'));

        return $cart_item_data;
    }

    /**
     * @param $passed
     * @param $product_id
     * @param  int  $qty
     * @param  false  $variation_id
     * @param  false  $variations  Optional, it will be passed for order again validation action
     * @param  false  $cart_item_data  Optional, it will be passed for order again validation action
     *
     * @return bool
     */
    public function add_to_cart_validation(
        $passed,
        $product_id,
        $qty = 1,
        $variation_id = false,
        $ignoreCaptcha = false
    ) {
        if ((($pid = wp_get_post_parent_id($product_id)) != 0) &&
            ($variation_id == false)
        ) {
            $variation_id = $product_id;
            $product_id   = $pid;
        }


        /**
         * ignore checking if $passed is false, as it can be validation error thrown by other plugins or woocommerce itself
         */
        if ($passed === true) {
            /** must pas $product-id, dont pass $variation id */
            $this->setFields($product_id);
            if ( ! $ignoreCaptcha && $this->formConf['enable_recaptcha']) {
                if ($this->is_recaptcha_valid() !== true) {
                    wc_add_notice(__('Please verify you are not a bot', 'woo-custom-product-addons-pro'), 'error');
                    $passed = false;
                    Main::setCartError($product_id, ! $passed);

                    return $passed;
                }
            }

            $this->set_product($product_id, $variation_id, $qty);

            $status = $this->read_form();
            if ($status !== false) {
                $this->process_cl_logic();
                $passed = $this->validateFormData();
            } else {
                $passed = false;
            }
        }

        Main::setCartError($product_id, ! $passed);

        if ($passed) {
            /** in cart edit case, remove items */
            if (isset($_POST['wcpa_current_cart_key']) && ! empty($_POST['wcpa_current_cart_key'])) {
                $cart_key = sanitize_text_field($_POST['wcpa_current_cart_key']);
//                if ($cart_key == $cart_item_key) {
//                    /** when the use resubmit without any changes in values, the key will be same, and the system will increase the quantity
//                     *Here we need to reset the quantity with new value
//                     */
//                    WC()->cart->set_quantity($cart_item_key, $quantity);
//                } else {
//                    WC()->cart->remove_cart_item($cart_key);
//                }
                WC()->cart->remove_cart_item($cart_key);
                unset($_POST['wcpa_current_cart_key']);
                /** reset this once executed, other wise it can cause issue if add on as product groups */
            }
        }

        return $passed;
    }

    /**
     * Initiate form fields if not initiated already,
     *
     * @param $product_id id must be product parent id, dont pass variation id
     *
     * @since 5.0
     */
    public function setFields($product_id)
    {
        if ($this->fields !== false) {
            return;
        }
        $wcpaProduct = new Product();
        $data        = $wcpaProduct->get_fields($product_id);

        if ( ! $data['fields']) {
            return;
        }
        $this->fields   = $data['fields'];
        $this->formConf = $data['config'];
        $this->formulas = $data['formulas'];
    }

    public function is_recaptcha_valid()
    {
        // Make sure this is an acceptable type of submissions
        if (isset($_POST['g-recaptcha-response']) && ! empty($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
            try {
                $url     = 'https://www.google.com/recaptcha/api/siteverify';
                $data    = [
                    'secret'   => Config::get_config('recaptcha_secret_key', ''),
                    'response' => $captcha,
                ];
                $options = [
                    'http' => [
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method'  => 'POST',
                        'content' => http_build_query($data),
                    ],

                ];
                $context = stream_context_create($options);
                $result  = file_get_contents($url, false, $context);

                return json_decode($result)->success;
            } catch (Exception $e) {
                return null;
            }
        } // Not a POST request, set a 403 (forbidden) response code.
        else {
            return false;
        }
    }

    /** set product object, it can use where product objects need
     *
     * @param $product_id
     * @param  bool  $variation_id
     * @param  int  $quantity
     */
    public function set_product($product_id, $variation_id = false, $quantity = 1)
    {

        if ($variation_id !== false) {
            $this->parentProduct  = wc_get_product($product_id);
            $product_id = $variation_id;
        }

        $this->product  = wc_get_product($product_id);

        $this->quantity = $quantity;
    }

    /** Read user submitted data
     *
     * @param $product_id
     *
     * @since 5.0
     */
    public function read_form()
    {
        if ( ! $this->fields) {
            return;
        }
        $this->form_data = [];

        $fieldTemp = new stdClass();

        foreach ($this->fields as $sectionKey => $section) {
            $fieldTemp->{$sectionKey}                       = clone $section;
            $this->form_data[$section->extra->key]['extra'] = (object) [
                'section_id' => $section->extra->section_id,
                'clStatus'   => 'visible',
                'key'        => $section->extra->key,
                'price'      => 0,
                'form_id'    => $section->extra->form_id,
                'isClone'    => isset($section->extra->isClone) ? $section->extra->isClone : false,
                'parentKey'  => isset($section->extra->parentKey) ? $section->extra->parentKey : false,
                'form_rules' => $section->extra->form_rules
            ];

            $status = $this->_read_form($section, $fieldTemp);
            if ($status === false) {
                /** file field can cause error if no files */
                return false;
            }
//            $sectionCounter = 1;
            if (isset($section->extra->repeater) && $section->extra->repeater) {
                $repeaterIndex = 0;
                if (isset($_POST[$sectionKey]) && is_array($_POST[$sectionKey])) {
                    $repeaterIndex = array_key_last($_POST[$sectionKey]);
                }
                if (isset($_FILES[$sectionKey]) && is_array($_FILES[$sectionKey]['name'])) {
                    $repeaterIndex = max($repeaterIndex, array_key_last($_FILES[$sectionKey]['name']));
                    $newFiles      = [];
                    foreach ($_FILES[$sectionKey] as $fileProperties => $_section) {
                        foreach ($_section as $_sectionCounter => $_field) {
                            if ( ! isset($newFiles[$_sectionCounter])) {
                                $newFiles[$_sectionCounter] = [];
                            }
                            foreach ($_field as $_fieldName => $_nameCounter) {
                                $newFiles[$_sectionCounter][$_fieldName][$fileProperties] = $_nameCounter;
                            }
                        }
                    }
                    $_FILES[$sectionKey] = $newFiles;
                }

                if ($repeaterIndex) {
                    for ($i = 1; $i <= $repeaterIndex; $i++) {
//						$newSection                                       = clone $section;
//						$newSection->extra->key                           = $sectionKey . '_' . $i;
//						$newSection->extra->isClone                       = true;
//						$newSection->extra->repeater                      = false;
//						$newSection->extra->parentKey                      = $sectionKey;
//						$name                                             = [ $sectionKey, $sectionCounter ];
//						$newSection->fields                               = array_map( function ( $row ) use ( $name ) {
//							return array_map( function ( $field ) use ( $name ) {
//								$name[]      = $field->name;
//								$field->name = $name;
//
//								return $field;
//							}, $row );
//						}, $newSection->fields );
//						$this->form_data[ $section->extra->key ]['extra'] = [
//							'section_id' => $section->extra->section_id,
//							'clStatus'   => 'visible',
//							'key'        => $section->extra->key,
//							'form_id'    => $section->extra->form_id,
//							'price'      => 0,
//
//							'form_rules' => $section->extra->form_rules
//						];
//						$this->fields[ $newSection->extra->key ]          = $newSection;
//
                        $newKey                            = $sectionKey.'_cl'.$i;
                        $name                              = [$sectionKey, $i];
                        $oSection                          = $this->fields->{$sectionKey};
                        $newSection                        = cloneSection($oSection, $sectionKey, $newKey, $name);
                        $fieldTemp->{$newKey}              = $newSection;
                        $this->form_data[$newKey]['extra'] = (object) [
                            'section_id' => $newSection->extra->section_id,
                            'clStatus'   => 'visible',
                            'key'        => $newSection->extra->key,
                            'form_id'    => $newSection->extra->form_id,
                            'price'      => 0,
                            'isClone'    => isset($newSection->extra->isClone) ? $newSection->extra->isClone : false,
                            'parentKey'  => isset($newSection->extra->parentKey) ? $newSection->extra->parentKey : false,
                            'form_rules' => $newSection->extra->form_rules
                        ];

                        $status = $this->_read_form($newSection, $fieldTemp);
                        if ($status === false) {
                            return false;
                        }
                    }
                    //
                }
            }
        }
        $this->fields = $fieldTemp;
    }

    public function _read_form($section, &$fieldTemp)
    {
        $readForm = new ReadForm($this);


        $hide_empty    = Config::get_config('hide_empty_data', false);
        $zero_as_empty = false;
        if ($hide_empty) {
            $zero_as_empty = apply_filters('wcpa_zero_as_empty', false);
        }
        foreach ($section->fields as $rowIndex => $row) {
            foreach ($row as $colIndex => $field) {
                $form_data = extractFormData($field);
//                unset($form_data->values); //avoid saving large number of data
//                unset($form_data->className); //avoid saving no use data
//                unset($form_data->relations); //avoid saving no use data

                if (in_array($field->type, array('separator', 'groupValidation'))) {
                    continue;
                }

                if ( isset($field->enablePrice) && $field->enablePrice && isset($field->pricingType)) {
                    /** for array fields, it need to set price value or formula while reading the options,
                     * so it need set templateFormula before it to process before read
                     */
                    if ($field->pricingType == 'custom' && isset($field->isTemplate) && $field->isTemplate) {
                        $field->price = '';
                        if (isset($field->formulaId) && $this->formulas[$field->formulaId]) {
                            $field->price = $this->formulas[$field->formulaId];
                        }
                        if (isset($field->values) && is_array($field->values) && isset($field->priceOptions) && $field->priceOptions === 'different_for_all') {
                            foreach ($field->values as $j => $_v) {
                                if (isset($_v->options) && is_array($_v->options)) {
                                    foreach ($_v->options as $k => $__v) {
                                        if (isset($__v->formulaId) && $this->formulas[$__v->formulaId]) {
                                            $__v->price = $this->formulas[$__v->formulaId];

                                        }
                                    }

                                } else {
                                    if (isset($_v->formulaId) && $this->formulas[$_v->formulaId]) {
                                        $_v->price = $this->formulas[$_v->formulaId];
                                    }
                                }
                            }
                        }
                    }

                }



                if ($this->orderAgainData === false) {
                    $_fieldValue = $readForm->_read_form($field, $hide_empty, $zero_as_empty);
                } else {
                    $_fieldValue = $readForm->read_from_order_data($this->orderAgainData, $field, $hide_empty,
                        $zero_as_empty);
                }


                $quantity = false;
                if (isset($field->enable_quantity) && $field->enable_quantity) {
                    if (is_array($_fieldValue) && array_key_exists('quantity',
                            $_fieldValue)) { // isset($_fieldValue['quantity']) returns false for null value in quantity
                        $quantity   = floatval($_fieldValue['quantity']);
                        $fieldValue = $_fieldValue['value'];
                    } else {
                        if (is_array($_fieldValue)) {
                            /** sum quantity values from array **/
                            $quantity = array_sum(array_column($_fieldValue, 'quantity'));
                        }
                        $fieldValue = $_fieldValue;
                    }
                } else {
                    $fieldValue = $_fieldValue;
                }


                if ($field->type == 'file' && $fieldValue === false) {
                    /** for file field, it can cause error if the file is missing in temp folder, then throw error */
                    wc_add_notice(
                        sprintf(__('File %s could not be uploaded.', 'woo-custom-product-addons-pro'), $field->label),
                        'error'
                    );

                    return false;
                }
                if (isEmpty($fieldValue) && $hide_empty) {
                    continue;
                }
                if ($zero_as_empty && ($fieldValue === 0 || $fieldValue === '0')) {
                    continue;
                }
                $label = (isset($field->label)) ? (($field->label == '') ? WCPA_EMPTY_LABEL : $field->label) : WCPA_EMPTY_LABEL;

                $this->form_data[$section->extra->key]['fields'][$rowIndex][$colIndex] = [
                    'type'            => $field->type,
                    'name'            => isset($field->name) ? $field->name : $field->elementId,
                    'label'           => $label,
                    'elementId'       => $field->elementId,
                    'value'           => $fieldValue,
                    'quantity'        => $quantity,
                    //  value fill be false for if the value not set
                    'clStatus'        => 'visible',
                    'price'           => false,
                    // price cannot be calculated here, as it can have cl logic dependency, it can calculate after cl logic processed
                    // must set price as false, to ensure this field price is not processed yet.
//                    'options'   => isset($field->values) ? array_map(
//                        function ($f) {
//                            return [
//                                'value'    => $f->value,
//                                'selected' => isset($f->selected) ? $f->selected : false,
//                                'price'    => isset($f->price) ? $f->price : false,
//                            ];
//                        },
//                        $field->values
//                    ) : [], // removed as no use found
                    'form_data'       => $form_data,

//				  'cur_swit' => $this->getCurrSwitch(), //TODO need to check this
                    'map_to_checkout' => (isset($field->mapToCheckout) && $field->mapToCheckout
                                          && isset($field->mapToCheckoutField) && ! empty($field->mapToCheckoutField)
                                          && isset($field->mapToCheckoutFieldParent) && ! empty($field->mapToCheckoutFieldParent))
                        ? array(
                            'parent' => isset($field->mapToCheckoutFieldParent) ? $field->mapToCheckoutFieldParent : '',
                            'field'  => isset($field->mapToCheckoutField) ? $field->mapToCheckoutField : '',
                            'value'  => $fieldValue
                        ) : false

                ];
                if (isset($field->independent) && $field->independent) {
                    $this->setSubProduct($this->form_data[$section->extra->key]['fields'][$rowIndex][$colIndex]);
                }

                if ($field->type == 'date' || $field->type == 'datetime-local') {
                    $dateFormat = getDateFormat($field);

                    $this->form_data[$section->extra->key]['fields'][$rowIndex][$colIndex]['dateFormat'] = $dateFormat;
                }

                if (isset($field->repeater) && $field->repeater) {
                    //isset($_POST[$field->name . '_cl'])
                    $repeaterIndex = 0;
                    $name          = $field->name;
                    if (is_array($field->name)) {
                        $name[2] = $name[2].'_cl';
                        if (isset($_POST[$name[0]][$name[1]][$name[2]])) {
                            $repeaterIndex = array_key_last($_POST[$name[0]][$name[1]][$name[2]]);
                        }
                        if (isset($_FILES[$name[0]][$name[1]][$name[2]]['name'])) {
                            $repeaterIndex = max($repeaterIndex,
                                array_key_last($_FILES[$name[0]][$name[1]][$name[2]]['name']));

                            $newFiles = [];
                            foreach ($_FILES[$name[0]][$name[1]][$name[2]]['name'] as $_i => $_v) {
                                $newFiles[$_i] = array(
                                    'tmp_name' => $_FILES[$name[0]][$name[1]][$name[2]]['tmp_name'][$_i],
                                    'name'     => $_FILES[$name[0]][$name[1]][$name[2]]['name'][$_i],
                                    'size'     => $_FILES[$name[0]][$name[1]][$name[2]]['size'][$_i],
                                    'type'     => $_FILES[$name[0]][$name[1]][$name[2]]['type'][$_i],
                                    'error'    => $_FILES[$name[0]][$name[1]][$name[2]]['error'][$_i],
                                );
                            }
                            $_FILES[$name[0]][$name[1]][$name[2]] = $newFiles;
                        }
                    } else {
                        if (isset($_POST[$name.'_cl']) && is_array($_POST[$name.'_cl'])) {
                            $repeaterIndex = array_key_last($_POST[$name.'_cl']);
                        }
                        if (isset($_FILES[$name.'_cl']) && is_array($_FILES[$name.'_cl']['name'])) {
                            $repeaterIndex = max($repeaterIndex, array_key_last($_FILES[$name.'_cl']['name']));
                            $newFiles      = [];
                            foreach ($_FILES[$name.'_cl']['name'] as $_i => $_v) {
                                $newFiles[$_i] = array(
                                    'tmp_name' => $_FILES[$name.'_cl']['tmp_name'][$_i],
                                    'name'     => $_FILES[$name.'_cl']['name'][$_i],
                                    'size'     => $_FILES[$name.'_cl']['size'][$_i],
                                    'type'     => $_FILES[$name.'_cl']['type'][$_i],
                                    'error'    => $_FILES[$name.'_cl']['error'][$_i],
                                );
                            }
                            $_FILES[$name.'_cl'] = $newFiles;
                        }
                    }
                    for ($index = 1; $index <= $repeaterIndex; $index++) {
//                        $nField            = clone $field;
//                        $nField->elementId = "{$field->elementId}_cl_{$index}";
//                        $nField->isClone   = true;
//                        $nField->parentId  = $field->elementId;
//                        $name              = $field->name;
//
//                        if (is_array($name)) {
//                            $name[count($name) - 1] = $name[count($name) - 1] . '_cl';
//                            $name[]                 = $index;
//                        } else {
//                            $name = [$field->name . '_cl', $index];
//                        }
//                        $nField->name = $name;// [$field->name . '_cl', $index];//$field->name . '_cl[' . $index . ']';
//                        $row[]        = $nField;
//                        $form_data    = clone $nField;
//                        /** pushing clone field to original fields */
//                        $this->fields->{$section->extra->key}->fields[$rowIndex][] = $nField;

                        $newId  = "{$field->elementId}_cl_{$index}";
                        $nField = cloneField($field, $newId, $index);
//                        $form_data    = clone $nField;
                        $form_data = extractFormData($nField);
//                        $this->fields->{$section->extra->key}->fields[$rowIndex][] = $nField;
                        $fieldTemp->{$section->extra->key}->fields[$rowIndex][] = $nField;

//                        unset($form_data->values); //avoid saving large number of data
//                        unset($form_data->className); //avoid saving no use data
//                        unset($form_data->relations); //avoid saving no use data

                        $_fieldValue = $readForm->_read_form($nField, $hide_empty, $zero_as_empty);

                        $quantity = false;
                        if (isset($nField->enable_quantity) && $nField->enable_quantity) {
                            if (is_array($_fieldValue) && array_key_exists('quantity',
                                    $_fieldValue)) { // isset($_fieldValue['quantity']) returns false for null value in quantity
                                $quantity   = $_fieldValue['quantity'];
                                $fieldValue = $_fieldValue['value'];
                            } else {
                                if (is_array($_fieldValue)) {
                                    /** sum quantity values from array **/
                                    $quantity = array_sum(array_column($_fieldValue, 'quantity'));
                                }
                                $fieldValue = $_fieldValue;
                            }
                        } else {
                            $fieldValue = $_fieldValue;
                        }


                        if ($nField->type == 'file' && $fieldValue === false) {
                            /** for file field, it can cause error if the file is missing in temp folder, then throw error */
                            wc_add_notice(
                                sprintf(__('File %s could not be uploaded.', 'woo-custom-product-addons-pro'),
                                    $nField->label),
                                'error'
                            );

                            return false;
                        }
                        if (($fieldValue === false || $fieldValue == '') && $hide_empty) {
                            continue;
                        }
                        if ($zero_as_empty && ($fieldValue === 0 || $fieldValue === '0')) {
                            continue;
                        }

                        $label = (isset($nField->label)) ? (($nField->label == '') ? WCPA_EMPTY_LABEL : $nField->label) : WCPA_EMPTY_LABEL;
                        if (isset($field->repeater_field_label)) {
                            $label = str_replace('{field_label}', $label, $field->repeater_field_label);
                            $label = str_replace('{counter}', $index + 1, $label);
                            $label = $label == '' ? WCPA_EMPTY_LABEL : $label;
                        }
                        $this->form_data[$section->extra->key]['fields'][$rowIndex][] = [
                            'type'            => $nField->type,
                            'name'            => isset($nField->name) ? $nField->name : $nField->elementId,
                            'label'           => $label,
                            'elementId'       => $nField->elementId,
                            'value'           => $fieldValue,
                            'quantity'        => $quantity,
                            //  value fill be false for if the value not set
                            'clStatus'        => 'visible',
                            'price'           => false,

                            // price cannot be calculated here, as it can have cl logic dependency, it can calculate after cl logic processed
//                            'options'   => isset($nField->values) ? array_map(
//                                function ($f) {
//                                    return [
//                                        'value'    => $f->value,
//                                        'selected' => $f->selected,
//                                        'price'    => isset($f->price) ? $f->price : false,
//                                    ];
//                                },
//                                $nField->values
//                            ) : [], // removed as couldt find any reason
                            'form_data'       => $form_data,
                            'map_to_checkout' => isset($nField->mapToCheckout) && array(
                                    'parent' => isset($nField->mapToCheckoutFieldParent) ? $nField->mapToCheckoutFieldParent : '',
                                    'field'  => isset($nField->mapToCheckoutField) ? $nField->mapToCheckoutField : '',
                                    'value'  => $fieldValue
                                )
                        ];
                        if (isset($nField->independent) && $nField->independent) {
                            $this->setSubProduct(end($this->form_data[$section->extra->key]['fields'][$rowIndex]));
                        }
                    }
                }
            }
        }
    }

    public function setSubProduct($product)
    {
        $this->subProducts[] = $product;
    }

    /**
     * Process conditional logic with user submited data
     *
     * @param $product_id
     *
     * @since 5.0
     */
    public function process_cl_logic()
    {
        $processed_ids      = array();
        $processed_sections = array();
        $cLogic             = new CLogic($this->form_data, $this->fields, $this->product,$this->parentProduct, $this->quantity);
        if ($this->fields) {
            foreach ($this->fields as $sectionKey => $section) {
                $sectionClStatus = 'visible';

                if (isset($section->extra->enableCl) && $section->extra->enableCl && isset($section->extra->relations) && is_array(
                        $section->extra->relations
                    )) {
                    $processed_sections[] = $sectionKey;
                    $clStatus             = $cLogic->evalConditions(
                        $section->extra->cl_rule,
                        $section->extra->relations
                    ); // returns false if it catch error
                    if ($clStatus !== false) {
                        $this->form_data[$sectionKey]['extra']->clStatus = $sectionClStatus = $clStatus;
                    }
                }

                //TODO need to check how to handle if the section has cl dependency with already exicuted fields

                /**
                 * avoid processing CL for fields if the section status is hidden
                 */
                if ($sectionClStatus !== 'visible') {
                    continue;
                }

                foreach ($section->fields as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if (isset($field->enableCl) && $field->enableCl && isset($field->relations) && is_array(
                                $field->relations
                            )) {
                            $clStatus        = $cLogic->evalConditions(
                                $field->cl_rule,
                                $field->relations
                            ); // returns false if it catch error
                            $processed_ids[] = isset($field->elementId) ? $field->elementId : false;

                            if ($clStatus !== false) {
                                /** we have to keep the cl status even if the field has not set while read_form. It needs to check validation required  */
                                if(!isset($this->form_data[$sectionKey]['fields'][$rowIndex])){
                                    $this->form_data[$sectionKey]['fields'][$rowIndex]=[];
                                }
                                if(!isset($this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex])){
                                    $this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex]=[];
                                }
                                $this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex]['clStatus'] = $clStatus;
                                if ($field->cl_dependency) {
                                    $cLogic->processClDependency($field->cl_dependency, $processed_ids);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function validateFormData()
    {
        $validation = new FormValidation($this->product, $this->quantity);
        if ($this->fields) {
            foreach ($this->fields as $sectionKey => $section) {
                if ($this->form_data[$sectionKey]['extra']->clStatus === 'hidden') {
                    /** in PHP end, disable status also treat as hidden, so no need to compare 'disable' */
                    continue;
                }
                foreach ($section->fields as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if ($field->type == 'groupValidation') {
                            $status = $validation->validateGroup($field, false, $this->form_data);
                            if ($status === false) {
                                return false;
                            }
                            continue;
                        }
                        if ( ! isset($this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex])) {
                            if (isset($field->required) && $field->required) {
                                $validation->validate($field, ['value' => false]); // calling this to set error message

                                return false;
                            }
                            continue;
                        }
                        $dField = $this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex];
                        if ($dField['clStatus'] === 'hidden') {

                            continue;
                        }
                        if ( ! isset($this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex]['type'])) {
                            continue;
                        }

                        if (in_array($field->type, ['content', 'separator', 'header'])) {
                            continue;
                        }
                        $status = $validation->validate($field, $dField);
                        if ($status === false) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id = false, $quantity = 1)
    {
        if (isset($cart_item_data['wcpaIgnore'])) {
            return $cart_item_data;
        }

        /**
         * Run only if fields are not set, setting fields and reading data already will be done at validation stage
         */
        if ($this->fields == false) {
            /** must pass $product-id, dont pass $variation id */
            $this->setFields($product_id);
            $this->set_product($product_id, $variation_id, $quantity);
            $this->read_form();
            $this->process_cl_logic();
        }


        if (isset($cart_item_data[WCPA_CART_ITEM_KEY])) {
            $this->form_data = $cart_item_data[WCPA_CART_ITEM_KEY];
            $this->processPricing();
            $this->processContentFormula();
        } else {
            $this->processPricing();
            $this->processContentFormula();
        }

        /**
         * remove  cl Status hidden fields
         */
        $_form_data = [];

        $checkoutFields = [];
        foreach ($this->form_data as $sectionKey => $section) {
            if ($section['extra']->clStatus !== 'visible') {
                continue;
            }
            $_form_data[$sectionKey]['extra'] = $section['extra'];
            if ( ! isset($section['fields'])) {
                $section['fields']                 = []; // keep empty fields if no fields in this section
                $_form_data[$sectionKey]['fields'] = [];
            }
            foreach ($section['fields'] as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    if ($field['clStatus'] !== 'visible') {
                        continue;
                    }
                    if (!isset($field['type'])) {
                        continue;
                    }
                    $_form_data[$sectionKey]['fields'][$rowIndex][$colIndex] = $field;
                    if (isset($field['map_to_checkout']) &&  is_array($field['map_to_checkout'])) {
                        if (isset($checkoutFields[$field['map_to_checkout']['field']])) {
                            /** if it has already mapped, set again if the value is not empty */
                            if ( ! isEmpty($field['map_to_checkout']['value'])) {
                                $checkoutFields[$field['map_to_checkout']['field']] = $field['map_to_checkout']['value'];
                            }
                        } else {
                            $checkoutFields[$field['map_to_checkout']['field']] = isset($field['map_to_checkout']['value'])?$field['map_to_checkout']['value']:'';
                        }
                    }
                }
            }
            if ( ! isset($_form_data[$sectionKey]['fields'])) {
                /**  if all fields are clStatus hidden, 'field' can be not set*/
                $_form_data[$sectionKey]['fields'] = [];
            }
        }
//identify if weight formula
//calculation
  //set weight, set//
        $cart_item_data[WCPA_CART_ITEM_KEY] = $_form_data;


        $cart_item_data['wcpa_cart_rules']  = [
            'price_override'    => $this->formConf['price_override'],
//                'pric_use_as_fee'   => $this->formConf['pric_use_as_fee'],
//                'process_fee_as'    => $this->formConf['process_fee_as'],
            'bind_quantity'     => $this->formConf['bind_quantity'],
            'thumb_image'       => $this->thumb_image,
            'combined_products' => $this->subProducts,
            'checkout_fields'   => $checkoutFields,
            'currency'          => get_woocommerce_currency(),
            'quantity'          => $quantity,
        ];

        // $cart_item_data['wcpa_combined_products'] = $product_array;
        //  $cart_item_data['wcpa_checkout_fields_data'] = $checkout_field_data;


        return $cart_item_data;
    }

    public function processPricing()
    {
        $dependencyFields = [];

        $price = new Price($this->form_data, $this->fields, $this->product, $this->quantity);
        if ($this->fields) {
            foreach ($this->fields as $sectionKey => $section) {
                foreach ($section->fields as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if ( ! isset($this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex]['type'])) {
                            /** empty fields might be skipped while read_form */
                            continue;
                        }

                        $dField = &$this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex];
                        $field  = $this->fields->{$sectionKey}->fields[$rowIndex][$colIndex];
                        if ( ! isset($field->enablePrice) || ! $field->enablePrice) {
                            continue;
                        }

                           if (in_array($field->type, ['separator', 'header'])) {
                            continue;
                        }

                        $status = $price->setFieldPrice($dField, $field);

//						$dField['price'] = $calcPrice;
//						$this->fields->{$sectionKey}->fields[ $rowIndex ][ $colIndex ]->price = $calcPrice;

                        if ($status === 'dependency') {
                            $dependencyFields[] = [$sectionKey, $rowIndex, $colIndex];
                        }
                    }
                }
            }
        }
        if ( ! empty($dependencyFields)) {
            $price->processPriceDependencies($dependencyFields);
        }
    }

    public function processContentFormula()
    {
        $price = new Price($this->form_data, $this->fields, $this->product, $this->quantity);
        if ($this->fields) {
            foreach ($this->fields as $sectionKey => $section) {
                foreach ($section->fields as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if ( ! isset($this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex]['type'])) {
                            /** empty fields might be skipped while read_form */
                            continue;
                        }
                        if (isset($field->hasFormula) && $field->hasFormula) {
                            $dField = &$this->form_data[$sectionKey]['fields'][$rowIndex][$colIndex];
                            if (isset($field->label)) {
                                $formula = $price->contentFormula($field->label, $dField, $field);
                                if (is_string($formula)) {
                                    $dField['label'] = $formula;
                                } else {
                                    $dField['label']        = $formula['label'];
                                    $dField['labelFormula'] = $formula['formula'];
                                }
                            }
                            if (isset($field->description)) {
                                $formula = $price->contentFormula($field->description, $dField, $field);
                                if (is_string($formula)) {
                                    $dField['description'] = $formula;
                                } else {
                                    $dField['description']        = $formula['label'];
                                    $dField['descriptionFormula'] = $formula['formula'];
                                }
                            }
                            if ($field->type == 'content') {
                                $formula = $price->contentFormula($field->value, $dField, $field);
                                if (is_string($formula)) {
                                    $dField['value'] = $formula;
                                } else {
                                    $dField['value']        = $formula['label'];
                                    $dField['valueFormula'] = $formula['formula'];
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function setThumbImage($image)
    {
        $this->thumb_image = $image;
    }

    public function setCheckoutField($field)
    {
        $this->checkoutFields[] = $field;
    }

    /**
     * Ajax Add to Cart
     * @since 5.0
     */
    public function ajax_add_to_cart()
    {
        if ( ! isset($_POST['add-to-cart'])) {
            return;
        }

        $product_id = intval($_POST['add-to-cart']);
        if (isset($_POST['quantity'])) {
            $quantity = intval($_POST['quantity']);
        } else {
            $quantity = 1;
        }

        if (empty(wc_get_notices('error'))) {
            // trigger action for added to cart in ajax
            do_action('woocommerce_ajax_added_to_cart', $product_id);

            if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
                wc_add_to_cart_message(array($product_id => $quantity), true);
            }

            wc_clear_notices();

            WC_AJAX::get_refreshed_fragments();
        } else {
            // If there was an error adding to the cart, redirect to the product page to show any errors.
            $data = array(
                'error'       => true,
                'product_url' => apply_filters(
                    'woocommerce_cart_redirect_after_error',
                    get_permalink($product_id),
                    $product_id
                ),
            );

            wp_send_json($data);
        }
    }

    public function add_multiple_mime_types($check, $file, $filename)
    {
        $custom_mimes_choose = Config::get_config('wcpa_custom_extensions_choose');
        $custom_mimes        = Config::get_config('wcpa_custom_extensions');
        $mimetypes           = [];
        if ($custom_mimes_choose) {
            foreach ($custom_mimes_choose as $ext) {
                switch ($ext) {
                    case 'svgz':
                    case 'svg':
                        $mimetypes[] = [$ext => 'image/svg+xml'];
                        break;
                    case 'cdr':
                        $mimetypes[] = [$ext => 'application/x-cdr'];
                        $mimetypes[] = [$ext => 'application/vnd.corel-draw'];
                        $mimetypes[] = [$ext => 'application/octet-stream'];
                        $mimetypes[] = [$ext => 'application/zip'];
                        $mimetypes[] = [$ext => 'application/coreldraw'];
                        $mimetypes[] = [$ext => 'application/x-coreldraw'];
                        $mimetypes[] = [$ext => 'application/cdr'];
                        $mimetypes[] = [$ext => 'image/cdr'];
                        $mimetypes[] = [$ext => 'image/x-cdr'];
                        break;

                    case 'psd':
                        $mimetypes[] = [$ext => 'image/x-photoshop'];
                        $mimetypes[] = [$ext => 'image/vnd.adobe.photoshop'];
                        break;
                    case 'eps':
                    case 'ai':
                        $mimetypes[] = [$ext => 'application/postscript'];
                        $mimetypes[] = [$ext => 'image/x-eps'];
                        $mimetypes[] = [$ext => 'application/pdf'];
                        break;

                    case 'zip':
                        $mimetypes[] = [$ext => 'application/zip'];
                        $mimetypes[] = [$ext => 'application/x-rar'];
                        $mimetypes[] = [$ext => 'application/x-rar-compressed'];
                        $mimetypes[] = [$ext => 'application/vnd.rar'];
                        $mimetypes[] = [$ext => 'application/octet-stream'];
                        break;
                }
            }
        }
        if ($custom_mimes) {
            foreach ($custom_mimes as $mime) {
                $mimetypes[] = [$mime['ext'] => $mime['mime']];
            }
        }

//        return [
//            [ 'svg' => 'image/svg' ],
//            [ 'svg' => 'image/svg+xml' ],
//        ];

        if (empty($check['ext']) && empty($check['type'])) {
            foreach ($mimetypes as $mime) {
                remove_filter('wp_check_filetype_and_ext', [$this, 'add_multiple_mime_types'], 99);
                $mime_filter = function ($mimes) use ($mime) {
                    return array_merge($mimes, $mime);
                };

                add_filter('upload_mimes', $mime_filter, 99);

                $check = wp_check_filetype_and_ext($file, $filename, $mime);

                remove_filter('upload_mimes', $mime_filter, 99);
                add_filter('wp_check_filetype_and_ext', [$this, 'add_multiple_mime_types'], 99, 3);
                if ( ! empty($check['ext']) || ! empty($check['type'])) {
                    return $check;
                }
            }
        }

        return $check;
    }

    /**
     * Register API routes
     */

    public function register_routes()
    {
//        $this->add_route('/upload/(?P<id>[0-9]+)', 'ajax_upload', 'POST');
        $this->add_route('/upload/(?P<id>[0-9]+)/(?P<fname>[,a-zA-Z0-9_-]+)', 'ajax_upload', 'POST');

    }

    private function add_route($slug, $callBack, $method = 'GET')
    {
        register_rest_route(
            $this->token.'/front',
            $slug,
            array(
                'methods'             => $method,
                'callback'            => array($this, $callBack),
                'permission_callback' => '__return_true',
            )
        );
    }

    public function isSetRepeaterField($name)
    {
        if (is_array($name)) {
            $val = $_POST;
            /**  sectionKey,Index,Name */
            $name[2] = $name[2].'_cl';
            foreach ($name as $v) {
                if ( ! isset($val[$v])) {
                    return false;
                }
                $val = $val[$v];
            }

            return true;
        } else {
            return isset($_POST[$name.'_cl']);
        }
    }

    public function fieldValFromName($name)
    {
        if (is_array($name)) {
            /**  sectionKey,Index,Name */
            return $_POST[$name[0]][$name[1]][$name[2].'_cl'];
        } else {
            return $_POST[$name.'_cl'];
        }
    }

    public function processRepeater()
    {
        $repeater = false;
        if ($this->fields) {
            foreach ($this->fields as $sectionKey => $section) {
                if ($section->repeater || $section->clStatus == 'hidden' || $section->clStatus == 'disabled') {
                    return;
                }
                if ( ! $section->repeater_bind || $section->repeater_bind == '') {
                    return;
                }
            }
        }
    }


    public function ajax_upload($data)
    {
        /*https://stackoverflow.com/questions/65541974/access-wc-class-of-woocommerce-from-anywhere*/
        WC()->frontend_includes();
        WC()->session = new WC_Session_Handler();
        WC()->session->init();

        $post_data = $data->get_params();


        if ( ! isset($data['id'])) {
            return new WP_REST_Response(false, 400);
        }
        if ( ! isset($data['fname'])) {
            return new WP_REST_Response(false, 400);
        }

        $this->setFields($data['id']);

        $field = $this->findFieldByFieldName($data['fname']);
        if ($field === false || $field->type !== 'file') {
            return new WP_REST_Response(false, 400);
        }

        $validation = new FormValidation();
        $status     = $validation->validateFileUpload($field, $_FILES['wcpa_file'],true);
        if ($status !== true) {
            return new WP_REST_Response($status, 422);
        }

        $file   = new File();
        $status = $file->handle_upload_ajax($field, $_FILES['wcpa_file']);

        return new WP_REST_Response($status, 200);
    }

    /**
     * @param $fieldName
     *
     * @return false
     */
    public function findFieldByFieldName($fieldName)
    {
        $fieldNameArray = explode(',', $fieldName);
        $length         = count($fieldNameArray);
        if ($length == 4) {
            $name = $fieldNameArray[$length - 2];//
        } else {
            $name = $fieldNameArray[$length - 1];
        }


        if ($this->fields == false) {
            return false;
        }
        foreach ($this->fields as $sectionKey => $section) {
            foreach ($section->fields as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    if (isset($field->name) && $field->name === $name) {
                        return $field;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $fieldId
     *
     * @return false
     */
    public function findFieldById($fieldId)
    {
        if ($this->fields == false) {
            return false;
        }
        foreach ($this->fields as $sectionKey => $section) {
            foreach ($section->fields as $rowIndex => $row) {
                foreach ($row as $colIndex => $field) {
                    if ($field->elementId === $fieldId) {
                        return $field;
                    }
                }
            }
        }

        return false;
    }


}