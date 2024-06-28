<?php

namespace Acowebs\WCPA;

use WP_Post;

class Product
{
    private $data;
    private $formConf;
    private $cart_error;
    private $has_custom_fields;
    private $relations = array();
    private $form;
    private $ml;
    private $options;
    private $price_dependency = array();

    public function __construct()
    {
        $custom_fields = Config::get_config('product_custom_fields', []);
        if (is_array($custom_fields) && ! empty($custom_fields)) {
            $this->has_custom_fields = true;
        }
        $this->ml = new ML();
    }

    public function get_price_dependency()
    {
        return $this->price_dependency;
    }

    public function get_fields($product_id = false)
    {
        if (($this->data !== null && ! empty($this->data))) {
            return ['fields' => $this->data, 'config' => $this->formConf];
        }

        if (false !== ($data = $this->getCache($product_id))) {
            return $data;
        }


        $this->form    = new Form();
        $this->options = new Options();

        $this->data = array();
        //      $this->cart_error = WCPA_Front_End::get_cart_error($product_id); // need to recheck

        $post_ids = $this->get_form_ids($product_id);

        $prod = wc_get_product($product_id);

        $this->formConf = [
            'price_override' => '',

            'enable_recaptcha'      => false,
            'bind_quantity'         => false,
            'quantity_bind_formula' => false,

            'disp_summ_show_option_price'  => false,
            'disp_summ_show_product_price' => false,
            'disp_summ_show_total_price'   => false,
            'disp_summ_show_fee'           => false,
            'disp_summ_show_discount'      => false,

            'summary_title'         => false,
            'options_total_label'   => false,
            'total_label'           => false,
            'options_product_label' => false,
            'fee_label'             => false,
            'discount_label'        => false,
            'has_price'             => false,

        ];

        $scripts = [
            'file'           => false,
            'datepicker'     => false,
            'color'          => false,
            'select'         => false,
            'productGallery' => false,
            'googlemapplace' => false,
            'recaptcha'      => false,
        ];

        $formulas = [];


//		if ( Config::get_config( 'form_loading_order_by_date' ) === true ) {
        if (is_array($post_ids) && count($post_ids)) {
            $post_ids = get_posts(
                array(
                    'include'        => $post_ids,
                    'fields'         => 'ids',
                    'post_type'      => Form::$CPT,
                    'lang'           => '', // deactivates the Polylang filter
                    'posts_per_page' => -1,
                )
            );
        }
//		}

        $post_ids = $this->re_order_forms($post_ids, $product_id);

        foreach ($post_ids as $id) {
            if (get_post_status(
                    $id
                ) == 'publish') {  // need to check if this check needed as post_ids will be published posts only
                $json_encoded = $this->form->get_form_meta_data($id);
                $formulas     = array_merge($formulas, $this->form->get_formulas($id));

                $form_settings = new FormSettings($id);


                foreach ($this->formConf as $key => $v) {
                    if ($key == 'bind_quantity' && ($v === false || $v == '')) {
                        $this->formConf['bind_quantity'] = $form_settings->get('bind_quantity');
                        if ($this->formConf['bind_quantity']) {
                            $this->formConf['quantity_bind_formula'] = $form_settings->get('quantity_bind_formula');
                            if (empty($this->formConf['quantity_bind_formula']) || trim(
                                                                                       $this->formConf['quantity_bind_formula']
                                                                                   ) == '') {
                                $this->formConf['bind_quantity'] = false;
                            }
                        }
                    } elseif ($v === false || $v === '') {
                        // once it is set as true for a for, it must be true even if the product has multiple forms assigned
                        $this->formConf[$key] = $form_settings->get($key);
                    }
                }


                $form_rules = [
                    'exclude_from_discount' => (Config::get_config('remove_discount_from_fields') ? true : $form_settings->get('exclude_from_discount')),

                    'fee_label' => $form_settings->get('fee_label'),

                    'disp_hide_options_price' => $form_settings->get('disp_hide_options_price'),
                    'disp_show_section_price' => $form_settings->get('disp_show_section_price'),
                    'disp_show_field_price'   => $form_settings->get('disp_show_field_price'),

                    'layout_option'   => $form_settings->get('layout_option'),
                    'pric_use_as_fee' => $form_settings->get('pric_use_as_fee'),
                    'process_fee_as'  => $form_settings->get('process_fee_as')
                ];


                /**
                 * @var keep track of connected global forms, remove if already imported to avoide infinite loop
                 */
                $globalForms      = []; //
                $rowsToResetIndex = [];
                if ($json_encoded && is_object($json_encoded)) {
                    $sectionReIterate = true;

                    while ($sectionReIterate) {
                        $sectionReIterate = false;
                        foreach ($json_encoded as $sectionKey => $section) {
                            $reIterate = true;
                            if ( ! isset($rowsToResetIndex[$sectionKey])) {
                                $rowsToResetIndex[$sectionKey] = [];
                            }
                            while ($reIterate) {
                                $reIterate = false;


                                /**
                                 * Form rules&form_id will be taken from the parent form only, will not be considering form rules from other global form fields added in this form,
                                 */
                                $section->extra->form_id    = $id;
                                $section->extra->form_rules = $form_rules;

                                $layOut = isset($section->extra->layout_option) ? $section->extra->layout_option : false;
                                if ($layOut == false || $layOut == null || $layOut == 'default') {
                                    $layOut = $form_rules['layout_option'];
                                }
                                $section->extra->layout_option = $layOut;

                                $this->process_cl($section->extra, $prod);

                                foreach ($section->fields as $rowIndex => $row) {
                                    foreach ($row as $colIndex => $field) {
                                        if (isset($field->active) && $field->active === false) {
                                            //TODO remove empty row, or section
                                            unset($section->fields[$rowIndex][$colIndex]);
                                            $rowsToResetIndex[$sectionKey][] = $rowIndex;
                                            continue;
                                        }


                                        if ($field->type == 'formselector') {
                                            $globalFormFields = $this->getGlobalFormFields($field);
                                            if ($globalFormFields) {
                                                if ( ! in_array($globalFormFields['key'], $globalForms)) {
                                                    $globalForms[] = $globalFormFields['key'];

                                                    if ($globalFormFields['type'] == 'fields') {
                                                        /**
                                                         * If the global form fields are just fields without sections, just append fields
                                                         */
                                                        array_splice(
                                                            $section->fields[$rowIndex],
                                                            $colIndex,
                                                            1,
                                                            $globalFormFields['fields']
                                                        );
                                                        $newArr = fix_cols($section->fields[$rowIndex]);
                                                        array_splice($section->fields, $rowIndex, 1, $newArr);
                                                    } elseif ($globalFormFields['type'] == 'section') {
                                                        /**
                                                         *   if global form has multiple sections,
                                                         * Split the main section here, and insert the sections after this,
                                                         *  parent section will be split as two parts , part 1 will be as above, and part 2 will be appended after the globally added section
                                                         */
                                                        $part1    = array_slice($section->fields, 0, $rowIndex);
                                                        $part1Col = array_slice(
                                                            $section->fields[$rowIndex],
                                                            0,
                                                            $colIndex
                                                        );
                                                        if (count($part1Col) > 0) {
                                                            $part1[] = $part1Col;
                                                        }

                                                        $part1Col2 = array_slice(
                                                            $section->fields[$rowIndex],
                                                            $colIndex + 1,
                                                            null
                                                        );

                                                        // exclude field in between which will be the formselector
                                                        $part2 = array_slice($section->fields, $rowIndex + 1, null);
                                                        if (count($part1Col2) > 0) {
                                                            $part1Col2[0] = $part1Col2;
                                                            $part2        = array_merge($part1Col2, $part2);
                                                        }
                                                        if (count($part1) > 0) {
                                                            $section->fields = $part1;
                                                        }

                                                        if (count($part2) > 0) {
                                                            $_section                    = clone $section;
                                                            $_section->extra             = clone $section->extra;
                                                            $_section->extra->section_id = $_section->extra->section_id.'_part2';
                                                            $_section->fields            = $part2;
                                                        }


                                                        $json_encoded_arr = (array) $json_encoded;
                                                        $sectionsToAppend = (array) $globalFormFields['fields'];
                                                        $split            = array_search(
                                                            $sectionKey,
                                                            array_keys($json_encoded_arr)
                                                        );

                                                        if (count($part1) > 0 && count($part2) > 0) {
                                                            $json_encoded_NewArr = array_slice(
                                                                                       $json_encoded_arr,
                                                                                       0,
                                                                                       $split + 1,
                                                                                       true
                                                                                   ) +
                                                                                   $sectionsToAppend +
                                                                                   [$sectionKey.'_part2' => $_section] +
                                                                                   array_slice(
                                                                                       $json_encoded_arr,
                                                                                       $split + 1,
                                                                                       null,
                                                                                       true
                                                                                   );
                                                        } elseif (count($part1) > 0 && count($part2) == 0) {
                                                            $json_encoded_NewArr = array_slice(
                                                                                       $json_encoded_arr,
                                                                                       0,
                                                                                       $split + 1,
                                                                                       true
                                                                                   ) +
                                                                                   $sectionsToAppend +
                                                                                   array_slice(
                                                                                       $json_encoded_arr,
                                                                                       $split + 1,
                                                                                       null,
                                                                                       true
                                                                                   );
                                                        } elseif (count($part1) == 0 && count($part2) == 0) {
                                                            $json_encoded_NewArr = $sectionsToAppend;
                                                        } elseif (count($part1) == 0 && count($part2) > 0) {
                                                            $json_encoded_NewArr = array_slice(
                                                                                       $json_encoded_arr,
                                                                                       0,
                                                                                       $split,
                                                                                       true
                                                                                   ) +
                                                                                   $sectionsToAppend +
                                                                                   [$sectionKey.'_part2' => $_section] +
                                                                                   array_slice(
                                                                                       $json_encoded_arr,
                                                                                       $split,
                                                                                       null,
                                                                                       true
                                                                                   );
                                                        }

//                                                            $json_encoded_NewArr = array_slice($json_encoded_arr, 0, $split, true) +
//                                                                $sectionsToAppend + array_slice($json_encoded_arr, $split, null, true);
                                                        $json_encoded = (object) $json_encoded_NewArr;

                                                        $sectionReIterate = true;
                                                        break;
                                                    }

                                                    $reIterate = true;
                                                    break;
                                                } else {
                                                    array_splice($section->fields[$rowIndex], $colIndex, 1);
                                                }
                                            } else {
                                                array_splice($section->fields[$rowIndex], $colIndex, 1);
                                            }
                                        }

                                        $this->find_price_dependency($field, $section->extra->form_id);
                                        /** TODO  */
                                        $this->update_global_options($field);
                                        $this->replace_custom_fields($field, $prod);
                                        $this->process_cl($field, $prod);
                                        $this->processFields($field, $section->extra->form_id);

                                        $this->findScriptsRequired($field, $scripts);

                                        if ( ! $this->formConf['has_price']) {
                                            $this->formConf['has_price'] = true;
                                        }
                                    }
                                    if ($reIterate || $sectionReIterate) {
                                        break;
                                    }
                                }
                                if ($sectionReIterate) {
                                    break;
                                }
                            }
                            if ($sectionReIterate) {
                                break;
                            }
                        }
                    }
                    // check for external forms


                    //   $json_encoded = $this->appendGlobalForm($json_encoded);
                    /**
                     * resetting array index when an column removed from row
                     * @var  $rowIndexes
                     */
                    foreach ($rowsToResetIndex as $sec => $rowIndexes) {
                        $resetSecFieldsIndex = false;
                        foreach ($rowIndexes as $rowIndex) {
                            if (isset($json_encoded->{$sec}->fields[$rowIndex])) {
                                $json_encoded->{$sec}->fields[$rowIndex] = array_values(
                                    $json_encoded->{$sec}->fields[$rowIndex]
                                );
                                if (count($json_encoded->{$sec}->fields[$rowIndex]) == 0) {
                                    unset($json_encoded->{$sec}->fields[$rowIndex]);
                                    $resetSecFieldsIndex = true;
                                }
                            }
                        }
                        if ($resetSecFieldsIndex) {
                            $json_encoded->{$sec}->fields = array_values($json_encoded->{$sec}->fields);
                        }
                    }
                    $this->data = array_merge($this->data, (array) $json_encoded);
                }
            }
        }

//            if ($bind_quantity) {
//                if ($matches = $this->check_field_price_dependency($quantity_bind_formula)) {
//                    foreach ($matches as $match) {
//                        if (!isset($this->price_depends[$match])) {
//                            $this->price_depends[$match] = array();
//                        }
//                        if (isset($v->elementId)) {
//                            if (!in_array($v->elementId, $this->price_depends[$match])) {
//                                $this->price_depends[$match][] = $v->elementId;
//                            }
//                        }
//                    }
//                }
//            }

        if ($this->data !== null) {
            $this->data = (object) $this->data;
            $this->map_dependencies();
        }

        if ($this->formConf['enable_recaptcha']) {
            $scripts['recaptcha'] = true;
        }

        $data = [
            'fields'   => $this->data,
            'config'   => $this->formConf,
            'scripts'  => $scripts,
            'formulas' => $formulas
        ];
        $this->setCache($product_id, $data);

        return $data;

//        $this->data = apply_filters('wcpa_product_form_fields', $this->data, $product_id);

//        if (empty($this->settings) && isset($id)) {
//            $dis_global = wcpa_get_post_meta($id, 'disp_use_global', true);
//            $cont_global = wcpa_get_post_meta($id, 'cont_use_global', true);
//            $this->settings = [
//                'disp_use_global' => $dis_global,
//                'disp_show_field_price' => ($dis_global ? wcpa_get_option('disp_show_field_price', true) : wcpa_get_post_meta($id, 'disp_show_field_price', wcpa_get_option('disp_show_field_price', true))),
//                'disp_summ_show_total_price' => ($dis_global ? wcpa_get_option('disp_summ_show_total_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_total_price', wcpa_get_option('disp_summ_show_total_price', true))),
//                'disp_summ_show_product_price' => ($dis_global ? wcpa_get_option('disp_summ_show_product_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_product_price', wcpa_get_option('disp_summ_show_product_price', true))),
//                'disp_summ_show_option_price' => ($dis_global ? wcpa_get_option('disp_summ_show_option_price', true) : wcpa_get_post_meta($id, 'disp_summ_show_option_price', wcpa_get_option('disp_summ_show_option_price', true))),
//                'pric_overide_base_price' => $pric_overide_base_price,
//                'pric_overide_base_price_if_gt_zero' => $pric_overide_base_price_if_gt_zero,
//                'pric_overide_base_price_fully' => $pric_overide_base_price_fully,
//                'pric_cal_option_once' => $pric_cal_option_once,
//                'pric_use_as_fee' => $pric_use_as_fee,
//                'render_after_acb' => $render_after_acb,
//                'enable_recaptcha' => ($enable_recaptcha ? $enable_recaptcha : wcpa_get_option('enable_recaptcha', false)),
//                'bind_quantity' => $bind_quantity,
//                'quantity_bind_formula' => $quantity_bind_formula,
//                'disp_hide_options_price' => wcpa_get_post_meta($id, 'disp_hide_options_price', false),
//                'cont_use_global' => $cont_global,
//                'options_total_label' => ($cont_global ? wcpa_get_option('options_total_label', 'Options Price', true) : wcpa_get_post_meta($id, 'options_total_label', wcpa_get_option('options_total_label', 'Options Price'))),
//                'options_product_label' => ($cont_global ? wcpa_get_option('options_product_label', 'Product Price', true) : wcpa_get_post_meta($id, 'options_product_label', wcpa_get_option('options_product_label', 'Product Price', true))),
//                'total_label' => ($cont_global ? wcpa_get_option('total_label', 'Total', true) : wcpa_get_post_meta($id, 'total_label', wcpa_get_option('total_label', 'Total'))),
//                'fee_label' => ($cont_global ? wcpa_get_option('fee_label', 'Fee', true) : wcpa_get_post_meta($id, 'fee_label', wcpa_get_option('fee_label', 'Fee'))),
//                'thumb_image' => false,
//                'show_validation_error_box' => wcpa_get_option('wcpa_show_val_error_box', false),
//            ];
//        }
    }

    public function getCache($product_id)
    {
        return  get_transient('wcpa_fields_' . $product_id);
    }

    /**
     * get forms assigned to product by product id
     *
     * @param $product_id
     *
     * @return array|int|int[]|mixed|void|WP_Post[]
     */
    public function get_form_ids($product_id)
    {
        $key_1_value = get_post_meta($product_id, 'wcpa_exclude_global_forms', true);
        $post_ids    = array();

        if (empty($key_1_value)) {
            $post_ids = get_posts(
                array(
                    'tax_query'      => array(
                        array(
                            'taxonomy'         => 'product_cat',
                            'field'            => 'ids',
                            'include_children' => false,
                            'terms'            => wp_get_object_terms(
                                $product_id,
                                'product_cat',
                                array(
                                    'orderby' => 'name',
                                    'order'   => 'ASC',
                                    'fields'  => 'ids',
                                )
                            ),
                        ),
                    ),
                    'fields'         => 'ids',
                    'post_type'      => Form::$CPT,
                    'posts_per_page' => -1,
                )
            );
        }
        $form_ids_set2 = maybe_unserialize(get_post_meta($product_id, WCPA_PRODUCT_META_KEY, true));

        if ($form_ids_set2 && is_array($form_ids_set2)) {
            $post_ids = array_unique(array_merge($post_ids, $form_ids_set2));
        }

        if ($this->ml->is_active()) {
            $post_ids = $this->ml->lang_object_ids($post_ids, 'post');
        }

        return $post_ids;
    }

    /**
     * @param $ids
     * @param $p_id
     *
     * @return array
     */
    public function re_order_forms($ids, $p_id)
    {
        $form_order = get_post_meta($p_id, 'wcpa_product_meta_order', true);

        if ($form_order && is_array($form_order)) {
            $ids_new        = array();
            $form_order_new = array();
            foreach ($ids as $id) {
                if (isset($form_order[$id])) {
                    $form_order_new[$id] = $form_order[$id];
                }
            }
            arsort($form_order_new);

            foreach ($form_order_new as $form_id => $order) {
                $index = array_search($form_id, $ids);
                if ($index !== false) {
                    unset($ids[$index]); // remove item at index 0
                    $ids    = array_values($ids); // 'reindex' array
                    $length = count($ids);
                    if ($order <= 0) {
                        $pos = 0;
                    } elseif ($order > $length) {
                        $pos = $length;
                    } else {
                        $pos = $order - 1;
                    }

                    array_splice($ids, $pos, 0, $form_id);
                }
            }
        }

        return $ids;
    }

    public function process_cl($v, $prod)
    {
        if (isset($v->enableCl) && $v->enableCl && isset($v->relations) && is_array($v->relations)) {
            foreach ($v->relations as $val) {
                foreach ($val->rules as $k) {
                    if ( ! empty($k->rules->cl_field)) {
                        /** change external field_id  */
                        if (strpos($k->rules->cl_field, 'external|') === 0) {
                            $k->rules->cl_field = str_replace('external|', '', $k->rules->cl_field);
                        }
                        if ( ! isset($this->relations[$k->rules->cl_field])) {
                            $this->relations[$k->rules->cl_field] = array();
                        }
                        if ($this->has_custom_fields && isset($k->rules->cl_val) && ! empty($k->rules->cl_val)) {
                            if (is_string($k->rules->cl_val)) {
                                $k->rules->cl_val = $this->replace_custom_field($k->rules->cl_val, $prod);
                            } else {
                                if (isset($k->rules->cl_val->value) && is_string($k->rules->cl_val->value)) {
                                    $k->rules->cl_val->value = $this->replace_custom_field(
                                        $k->rules->cl_val->value,
                                        $prod
                                    );
                                }
                            }
                        }
//removed this as in new version, it saves the arttribute slug directly than the id
//                        if ($k->rules->cl_field === 'attribute' && $k->rules->cl_field_sub) {
//
//                            $atr = wc_get_attribute($k->rules->cl_field_sub);
//                            if ($atr) {
//                                $term                   = get_term_by('id', $k->rules->cl_val->value, $atr->slug);
//                                $k->rules->cl_val       = isset($term->slug) ? $term->slug : '';
//                                $k->rules->cl_field_sub = sanitize_title($atr->slug);
//                            }
//                        }
                        if ($k->rules->cl_field == 'custom_attribute' && $k->rules->cl_field_sub != '') {
                            $k->rules->cl_field_sub = sanitize_title_with_dashes($k->rules->cl_field_sub);
                        }
                        $this->relations[$k->rules->cl_field][] = (isset($v->elementId) ? $v->elementId : false);
                    }
                }
            }
        }
    }

    public function replace_custom_field($string = '', $prod = false)
    {
        $cf_prefix = Config::get_config('wcpa_cf_prefix', 'wcpa_pcf_');

        if (is_string($string) && preg_match_all('/\{(\s)*?wcpa_pcf_([^}]*)}/', $string, $matches)) {
            $pro_id = $prod->get_parent_id();
            if ($pro_id == 0) {
                $pro_id = $prod->get_id();
            }

            foreach ($matches[2] as $k => $match) {
                $cf_value = Config::getWcpaCustomField(trim($match), $pro_id);
//                $cf_value = get_post_meta($pro_id, $cf_prefix . trim($match), true);
//                if ($cf_value == '' || $cf_value == false) {
//                    if (is_array($custom_fields)) {
//                        foreach ($custom_fields as $cf) {
//                            if ($cf['name'] == trim($match)) {
//                                $cf_value = $cf['value'];
//                                break;
//                            }
//                        }
//                    }
//                }
                if ($cf_value !== '' || $cf_value !== false) {
                    $string = str_replace($matches[0][$k], $cf_value, $string);
                }
            }
        }

        return $string;
    }

    public function getGlobalFormFields($field)
    {
        if ($field->type == 'formselector' && isset($field->form_id) && is_numeric($field->form_id)) {
            if ($this->ml->is_active()) {
                $form_id = $this->ml->lang_object_ids($field->form_id, 'post');
            } else {
                $form_id = $field->form_id;
            }
            $json_encoded = $this->form->get_form_meta_data($field->form_id);
            if ( ! $json_encoded || $json_encoded == null) {
                return;
            }
            $section_id = '_first_section';
            if (isset($field->section_id) && ! empty($field->section_id)) {
                $section_id = $field->section_id;
            }


            if ($section_id == '_first_section') {
                $firstSection = reset($json_encoded);
                /** assigning global form relations to sub fields */
                if (isset($field->enableCl) && $field->enableCl && isset($field->relations) && is_array($field->relations)) {
                    foreach ($firstSection->fields as $i => $row) {
                        foreach ($row as $j => $_field) {
                            $_field->cl_rule   = $field->cl_rule;
                            $_field->enableCl  = true;
                            $_field->relations = $field->relations;
                        }
                    }
                }
                foreach ($firstSection->fields as $i => $row) {
                    foreach ($row as $j => $_field) {
                        $_field->_form_id = $field->form_id;
                    }
                }

                return ['fields' => $firstSection->fields, 'key' => $form_id.'-'.$section_id, 'type' => 'fields'];
            } elseif ($section_id == '_all') {
                /** assigning global form relations to sub sections */
                if (isset($field->enableCl) && $field->enableCl && isset($field->relations) && is_array($field->relations)) {
                    foreach ($json_encoded as $key => $section) {
                        $section->extra->cl_rule   = $field->cl_rule;
                        $section->extra->enableCl  = true;
                        $section->extra->relations = $field->relations;
                    }
                }
                foreach ($json_encoded as $key => $section) {
                    foreach ($section->fields as $i => $row) {
                        foreach ($row as $j => $_field) {
                            $_field->_form_id = $field->form_id;
                        }
                    }
                }


                return ['fields' => $json_encoded, 'key' => $form_id.'-'.$section_id, 'type' => 'section'];
            } else {
                if (isset($json_encoded->{$section_id})) {
                    $firstSection = $json_encoded->{$section_id};
                    /** assigning global form relations to sub fields */
                    if (isset($field->enableCl) && $field->enableCl && isset($field->relations) && is_array($field->relations)) {
                        foreach ($firstSection->fields as $i => $row) {
                            foreach ($row as $j => $_field) {
                                $_field->cl_rule   = $field->cl_rule;
                                $_field->enableCl  = true;
                                $_field->relations = $field->relations;
                            }
                        }
                    }
                    foreach ($firstSection->fields as $i => $row) {
                        foreach ($row as $j => $_field) {
                            $_field->_form_id = $field->form_id;
                        }
                    }

                    return ['fields' => $firstSection->fields, 'key' => $form_id.'-'.$section_id, 'type' => 'fields'];
                }
            }
        }

        return false;
    }

    /**
     * @param $v
     */
    public function find_price_dependency($v, $form_id)
    {
        if (isset($v->enablePrice) && $v->enablePrice && isset($v->pricingType) && $v->pricingType === 'custom') {
            if (isset($v->priceOptions) && $v->priceOptions == 'different_for_all') {
                foreach ($v->values as $e) {
                    //TODO price dependency for formula
                    if(!isset($e->price)){
                        continue;
                    }
                    $e->price = $this->form->replace_vars($e->price, $form_id,
                        isset($v->_form_id) ? $v->_form_id : false);
                    if ($matches = $this->check_field_price_dependency($e->price)) {
                        foreach ($matches as $match) {
                            if ( ! isset($this->price_dependency[$match])) {
                                $this->price_dependency[$match] = array();
                            }
                            if (isset($v->elementId)) {
                                if ( ! in_array($v->elementId, $this->price_dependency[$match])) {
                                    $this->price_dependency[$match][] = $v->elementId;
                                }
                            }
                        }
                    }
                }
            } elseif (isset($v->price)) {
                $v->price = $this->form->replace_vars($v->price, $form_id, isset($v->_form_id) ? $v->_form_id : false);
                if ($matches = $this->check_field_price_dependency($v->price)) {
                    foreach ($matches as $match) {
                        if ( ! isset($this->price_dependency[$match])) {
                            $this->price_dependency[$match] = array();
                        }
                        if (isset($v->elementId)) {
                            if ( ! in_array($v->elementId, $this->price_dependency[$match])) {
                                $this->price_dependency[$match][] = $v->elementId;
                            }
                        }
                    }
                }
            }
        }
    }

    public function check_field_price_dependency($price_formula)
    {
        $matches = false;

        if (preg_match_all('/\{(\s)*?field\.([^}]*)}/', $price_formula, $matches)) {
            $ids = array();
            foreach ($matches[2] as $match) {
                $ele = explode('.', $match);
                if (is_array($ele) && count($ele) > 1 && in_array(
                        $ele[1],
                        [
                            'value',
                            'price',
                            'count',
                            'days',
                            'seconds',
                            'timestamp',
                        ]
                    )) {
                    $ids[] = $ele[0];
                }
            }

            return array_unique($ids);
        } else {
            return false;
        }
    }

    public function update_global_options($field)
    {
        if (in_array($field->type, ['select', 'image-group', 'color-group', 'radio-group', 'checkbox-group'])) {
            $options = [];
            foreach ($field->values as $i => $option) {
                if (isset($option->type) && $option->type == 'global' && $option->value !== '') {
                    $gOptions = $this->options->get_options_by_key($option->value);
                    if ($gOptions && count($gOptions) == 1) {
                        /** if the options list has only one group (default one), extract the options list only */
                        $gOptions = $this->filterOptionsBasedOnType($field->type, $gOptions[0]->options);
                    }
                    $type     = $field->type;
                    $gOptions = array_map(
                        function ($opt) use ($option, $type) {
                            if (isset($opt->options) && is_array($opt->options)) {
                                $opt->options = array_map(
                                    function ($_opt) use ($option) {
                                        if ( ! $option->selected) {
                                            $_opt->selected = false;
                                        }

                                        return $_opt;
                                    },
                                    $opt->options
                                );
                                $opt->options = $this->filterOptionsBasedOnType($type, $opt->options);
                            } else {
                                if ( ! $option->selected) {
                                    $opt->selected = false;
                                }
                            }

                            return $opt;
                        },
                        $gOptions
                    );


                    $options = array_merge($options, $gOptions);
//                    $options[] = ['label' => $option->label, 'options' => $gOptions];
                } else {
                    $options[] = $option;
                }
            }

            $field->values = $options;
        }

    }


    public function filterOptionsBasedOnType($type, $options)
    {
        if ($type == 'image-group') {
            return array_map(
                function ($option) {
                    $v       = [

                    ];
                    $_option = clone $option;
                    if (isset($option->image)) {
                        $v['image']    = $option->image->url;
                        $v['image_id'] = $option->image->id;
                        unset($_option->image);
                    }
                    $v = array_merge($v, (array) $_option);

                    return (object) $v;
                },
                $options
            );
        }

        return $options;
    }

    public function replace_custom_fields($v, $prod)
    {
        if ($this->has_custom_fields) {
            if (isset($v->label)) {
                $v->label = $this->replace_custom_field($v->label, $prod);
            }
            if (isset($v->value)) {
                $v->value = $this->replace_custom_field($v->value, $prod);
            }
            if (isset($v->placeholder)) {
                $v->placeholder = $this->replace_custom_field($v->placeholder, $prod);
            }
            if (isset($v->description)) {
                $v->description = $this->replace_custom_field($v->description, $prod);
            }
            if (isset($v->price)) {
                $v->price = $this->replace_custom_field($v->price, $prod);
            }
            if (isset($v->values) && is_array($v->values)) {
                foreach ($v->values as $e) {
                    if (isset($e->label)) {
                        $e->label = $this->replace_custom_field($e->label, $prod);
                    }
                    if (isset($e->value)) {
                        $e->value = $this->replace_custom_field($e->value, $prod);
                    }
                    if (isset($e->price)) {
                        $e->price = $this->replace_custom_field($e->price, $prod);
                    }
                }
            }
        }
    }

    public function processFields($field, $form_id)
    {
        /** Check has formula in Label , and in value for Content field */


        if (isset($field->label) && hasFormula($field->label)) {
            $field->hasFormula = true;
            $field->label      = $this->form->replace_vars($field->label, $form_id,
                isset($field->_form_id) ? $field->_form_id : false);
        }
        if (isset($field->description) && hasFormula($field->description)) {
            $field->hasFormula  = true;
            $field->description = $this->form->replace_vars($field->description, $form_id,
                isset($field->_form_id) ? $field->_form_id : false);
        }
        if (isset($field->description)) {
            $field->description = nl2br(trim($field->description));
        }
        if (isset($field->tooltip)) {
            $field->tooltip = nl2br(trim($field->tooltip));
        }
        if ($field->type == 'content' && isset($field->value) && hasFormula($field->value)) {
            $field->hasFormula = true;
            $field->value      = $this->form->replace_vars($field->value, $form_id,
                isset($field->_form_id) ? $field->_form_id : false);
        }

        if ($field->type == 'image-group') {
            if ($field->values && ! empty($field->values)) {
                foreach ($field->values as $k => $val) {
                    if (isset($field->values[$k]->options) && is_array($field->values[$k]->options)) {
                        foreach ($field->values[$k]->options as $_k => $_val) {
                            $field->values[$k]->options[$_k]->thumb_src = $_val->image;
                            if (isset($_val->image_id) && $_val->image_id > 0 && (isset($field->disp_size_img) && $field->disp_size_img->width > 0)) {
                                $img_obj = wp_get_attachment_image_src($_val->image_id, [
                                    $field->disp_size_img->width,
                                    empty($field->disp_size_img->height) ? 0 : $field->disp_size_img->height
                                ]);
                                if ($img_obj) {
                                    $field->values[$k]->options[$_k]->thumb_src = $img_obj[0];
                                }
                            }
                        }
                    } else {
                        $field->values[$k]->thumb_src = $val->image;
                        if (isset($val->image_id) && $val->image_id > 0 && (isset($field->disp_size_img) && $field->disp_size_img->width > 0)) {
                            $img_obj = wp_get_attachment_image_src($val->image_id, [
                                $field->disp_size_img->width,
                                empty($field->disp_size_img->height) ? 0 : $field->disp_size_img->height
                            ]);
                            if ($img_obj) {
                                $field->values[$k]->thumb_src = $img_obj[0];
                            }
                        }
                    }
                }
            }
        }
        if ($field->type == 'file') {
            $allowedFileTypes = fileTypesToExtensions($field);

            $field->allowedFileTypes = implode(',', $allowedFileTypes);
        }
        if ($field->type == 'image-group' && isset($field->show_as_product_image) && $field->show_as_product_image) {
            if ($field->values && ! empty($field->values)) {
                foreach ($field->values as $k => $val) {
                    if (isset($val->image_id) && $val->image_id > 0) {
                        $attachProps = wc_get_product_attachment_props($val->image_id);
                        if (isset($attachProps['title'])) {
                            $attachProps['title'] = htmlspecialchars($attachProps['title'], ENT_QUOTES);
                        }
                        $val->productImage = $attachProps + ['image_id' => $val->image_id];
                    } else {
                        $props             = [
                            'title'                 => htmlspecialchars($val->label, ENT_QUOTES),
                            'caption'               => '',
                            'url'                   => $val->image,
                            'alt'                   => $val->label,
                            'src'                   => $val->image,
                            'srcset'                => false,
                            'sizes'                 => false,
                            'src_w'                 => '',
                            'full_src'              => $val->image,
                            'full_src_w'            => '',
                            'full_src_h'            => '',
                            'gallery_thumbnail_src' => $val->image,
                        ];
                        $val->productImage = $props;
                    }
                }
            }
        }
        /**  give priority for enable_product_image  than show_as_product_image, so called it after show_as_product_image   */
        if (isset($field->enable_product_image) && $field->enable_product_image) {
            if ($field->values && ! empty($field->values)) {
                foreach ($field->values as $k => $val) {
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

    public function findScriptsRequired($field, &$scripts)
    {
        if ( ! $scripts['file'] && $field->type == 'file' && isset($field->upload_type) && $field->upload_type !== 'basic') {
            $scripts['file'] = true;
        }
        if ( ! $scripts['datepicker']
             && (in_array($field->type, ['datetime-local', 'date', 'time']))
             && isset($field->picker_type) && $field->picker_type !== 'basic') {
            $scripts['datepicker'] = true;
        }
        if ( ! $scripts['color']
             && (in_array($field->type, ['color']))
             && isset($field->color_picker_type) && $field->color_picker_type !== 'basic') {
            $scripts['color'] = true;
        }

        if ( ! $scripts['select']
             && $field->type == 'select') {
            if (isset($field->multiple) && $field->multiple) {
                $scripts['select'] = true;
            } else {
                /** check if is grouped*/
                foreach ($field->values as $v) {
                    if (isset($v->options)) {
                        $scripts['select'] = true;
                        break;
                    }
                }
            }
        }
        if ( ! $scripts['productGallery']
             && ((isset($field->enable_product_image) && $field->enable_product_image)
                 || (isset($field->show_as_product_image) && $field->show_as_product_image))
        ) {
            $scripts['productGallery'] = true;
        }
        if ( ! $scripts['googlemapplace']
             && $field->type == 'placeselector') {
            $scripts['googlemapplace'] = true;
        }
    }

    public function map_dependencies()
    {
        if ($this->data && $this->data !== null) {
            foreach ($this->data as $sectionKey => $section) {
                foreach ($section->fields as $rowIndex => $row) {
                    foreach ($row as $colIndex => $field) {
                        if (isset($this->price_dependency[$field->elementId])) {
                            $field->price_dependency = $this->price_dependency[$field->elementId];
                        } else {
                            $field->price_dependency = false;
                        }

                        if (isset($this->relations[$field->elementId])) {
                            $field->cl_dependency = $this->relations[$field->elementId];
                        } else {
                            $field->cl_dependency = false;
                        }
                    }
                }
            }
        }
    }

    public function setCache($product_id, $data)
    {
        set_transient('wcpa_fields_'.$product_id, $data, 24 * HOUR_IN_SECONDS);
    }
}
