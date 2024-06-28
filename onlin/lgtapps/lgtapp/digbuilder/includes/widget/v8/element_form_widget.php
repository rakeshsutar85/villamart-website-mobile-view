<?php


if (!defined('ABSPATH')) {
    exit;
}


use Elementor\Plugin as Elementor;
use Elementor\Widget_Base as Widget_Base;

class Elementor_Digits_Widget extends Widget_Base
{

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
    }

    /**
     * Get widget name.
     *
     * Retrieve widget name.
     *
     * @return string Widget name.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_name()
    {

        switch ($this->get_widget_type()) {
            case 1:
                return 'login-register';
            case 2:
                return 'register-only';
            case 3:
                return 'forgot-pass';
            case 4:
                return 'login-only';
        }
    }

    public function get_widget_type()
    {

    }

    /**
     * Get widget title.
     *
     * Retrieve widget title.
     *
     * @return string Widget title.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_title()
    {
        switch ($this->get_widget_type()) {
            case 1:
                return 'Login & Register';
            case 2:
                return 'Register';
            case 3:
                return 'Forgot Password';
            case 4:
                return 'Login';
        }
    }

    public function is_only_register()
    {
        $type = $this->get_widget_type();
        if ($type == 2) {
            return true;
        }
    }

    /**
     * Get widget icon.
     *
     * Retrieve  widget icon.
     *
     * @return string Widget icon.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_icon()
    {
        switch ($this->get_widget_type()) {
            case 1:
                return 'icon-digits-login-reg icon-digits-elem-dims';
            case 2:
                return 'icon-digits-reg icon-digits-elem-dims';
            case 3:
                return 'icon-digits-forgotpass icon-digits-elem-dims';
            case 4:
                return 'icon-digits-login icon-digits-elem-dims';
        }
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the widget belongs to.
     *
     * @return array Widget categories.
     * @since 1.0.0
     * @access public
     *
     */
    public function get_categories()
    {
        return ['digits-form'];
    }


    public function get_form_colors()
    {
        $settings = $this->get_active_settings();

        $style_list = ['primary_color',
            'accent_color',
            'title_color',
            'field_bg_color',
            'form_bg_color',
            'button_text_color'];

        $styles = digits_theme_values();
        $theme = [];
        foreach (['dark', 'light'] as $theme_key) {
            $values = $styles['light'];
            foreach ($style_list as $style_key) {
                $style_info = "{$theme_key}_{$style_key}";
                if (!empty($settings[$style_info])) {
                    $values[$style_key] = $settings[$style_info];
                }
            }
            unset($values['logo']);
            $theme[$theme_key] = $values;
        }
        return $theme;
    }

    /**
     * Render widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */

    protected function render()
    {

        $settings = $this->get_active_settings();

        if (is_user_logged_in()) {
            if (!\Elementor\Plugin::$instance->editor->is_edit_mode() && !\Elementor\Plugin::$instance->preview->is_preview_mode()) {
                return;
            }
        }

        $button_texts = digits_form_button();
        foreach ($button_texts as $button_key => $button_text) {
            if (isset($settings[$button_key])) {
                $settings_button_value = $settings[$button_key];
                if (!empty($settings_button_value)) $button_texts[$button_key] = $settings[$button_key];
            }
        }

        if (isset($settings['dig_enable_forgot_password']) && $settings['dig_enable_forgot_password']) {
            $enable_forgot_password = 1;
        } else {
            $enable_forgot_password = 0;
        }

        $widget_type = $this->get_widget_type();

        $details = array();
        $details['users_can_register'] = $this->is_register();

        if ($enable_forgot_password == 0) {
            $details['disable_forgot'] = true;
        }

        if (!$this->is_register()) {
            $details['disable_register'] = true;
        } else {
            $form_fields_raw = '';
            if (!empty($settings['form__fields'])) {
                $form_fields_raw = $settings['form__fields'];
            } else if (!empty($settings['fields'])) {
                $form_fields_raw = $settings['fields'];
            }
            if (!empty($form_fields_raw)) {
                $fields = digbuilder_get_formatted_fields($form_fields_raw, 1);
            }
            $formatted_fields = digbuilder_parse_form_fields($fields);

            $default_reg_fields = digpage_get_registration_fields_data($form_fields_raw, 2);

            $details['reg_fields'] = $default_reg_fields;
            $details['fields_data'] = $formatted_fields;
        }

        if ($this->get_widget_type() == 2) {
            $details['disable_login'] = true;
            $details['page_type'] = 'register';
        }
        if ($this->get_widget_type() == 3) {
            $details['page_type'] = 'forgot-password';
        }

        if ($this->get_widget_type() != 2) {
            $details['login_details'] = [
                'dig_login_email' => $settings['dig_login_email'] ? 1 : 0,
                'dig_login_mobilenumber' => $settings['dig_login_mobilenumber'] ? 1 : 0,
                'dig_login_username' => $settings['dig_login_username'] ? 1 : 0,
                'dig_login_captcha' => $settings['dig_login_captcha'],
            ];
        }

        if (!empty($settings['login_redirect'])) {
            $details['login_redirect'] = $settings['login_redirect'];
        }

        if (!empty($settings['forgot_redirect'])) {
            $details['forgot_redirect'] = $settings['forgot_redirect'];
        }
        if (!empty($settings['register_redirect'])) {
            $details['register_redirect'] = $settings['register_redirect'];
        }

        if ($this->is_login()) {
            $details['login_title'] = $settings['login_form_title'];
        }
        if ($this->is_forgotpass()) {
            $details['forgot_title'] = $settings['forgot_form_title'];
        }
        if ($this->is_register()) {
            $details['register_title'] = $settings['register_form_title'];
        }

        $details['extra_data'] = '<input type="hidden" name="digbuilder_form" value="1" /><input type="hidden" name="post_id" value="' . esc_attr($this->get_current_id()) . '"/><input type="hidden" name="form_id" value="' . esc_attr($this->get_id()) . '"/>';

        $styles = $this->get_form_colors();
        $details['style'] = $styles;

        $field_id = 'digits_elementor_id_' . esc_attr($this->get_id());

        ?>
        <div id="<?php echo $field_id; ?>">
            <?php
            digits_render_new_form($details);
            ?>
        </div>
        <?php

        $light = digits_new_form_get_style($styles['light']);
        $dark = digits_new_form_get_style($styles['dark']);

        $selector = "#{$field_id} .digits-form_container";
        ?>
        <style>
            <?php echo $selector;?>
            {
            <?php echo $light;?>
            }

            @media (prefers-color-scheme: dark) {
            <?php echo $selector;?> {
            <?php echo $dark;?>
            }
            }
        </style>
        <?php

    }

    public static function get_current_id()
    {
        if (isset(Elementor::$instance->documents) && !empty(Elementor::$instance->documents->get_current())) {
            return Elementor::$instance->documents->get_current()->get_main_id();
        }

        return get_the_ID();

    }

    protected function register_controls()
    {


        $is_reg = $this->is_register();

        $user_roles = array();

        if ($is_reg) {
            foreach (wp_roles()->roles as $key => $value):
                $user_roles[$key] = $value['name'];
            endforeach;
        }

        $this->register_section_login();

        if ($is_reg) {
            $this->register_section_form_fields($user_roles);
        }
        $this->register_section_general_settings($user_roles);

        $this->register_section_redirect();
        $this->register_section_titles();

        $this->register_section_theme();

        $this->register_section_general();
        $this->register_section_label();
        $this->register_section_field();

        $this->register_section_dropdown();

        if ($this->get_widget_type() != 3) {
            $this->register_section_checkbox();
            if ($is_reg) {
                $this->register_section_radio();
                $this->register_section_signup_button();
            }
        }


        $this->register_section_button();

        $this->register_section_text();

    }

    public function is_register()
    {
        $type = $this->get_widget_type();
        if ($type == 1 || $type == 2) {
            return true;
        }
    }

    private function register_section_login()
    {
        if (!$this->is_forgotpass() && !$this->is_login()) {
            return;
        }

        if ($this->is_login()) {
            $field_labels = esc_html__('Login Fields', 'digits');
        } else {
            $field_labels = esc_html__('Forgot Fields', 'digits');
        }

        $this->start_controls_section(
            'section_login_fields',
            [
                'label' => esc_html__($field_labels, 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        if (!$this->is_only_forgotpass()) {
            $this->add_control(
                'dig_login_username',
                [
                    'label' => esc_html__('Username', 'digits'),
                    'type' => \Elementor\Controls_Manager::SWITCHER,
                    'label_on' => esc_html__('Yes', 'digits'),
                    'label_off' => esc_html__('No', 'digits'),
                    'return_value' => 'true',
                    'default' => 'true',
                    'render_type' => 'template',
                ]
            );
        }
        $this->add_control(
            'dig_login_email',
            [
                'label' => esc_html__('Email', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'default' => 'true',
                'render_type' => 'template',
            ]
        );
        $this->add_control(
            'dig_login_mobilenumber',
            [
                'label' => esc_html__('Mobile Number', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );
        if ($this->is_login()) {
            $this->add_control(
                'dig_login_captcha',
                [
                    'label' => esc_html__('Captcha', 'digits'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => '0',
                    'render_type' => 'template',
                    'options' => [
                        '0' => esc_html__('Off', 'digits'),
                        '1' => esc_html__('Simple Captcha', 'digits'),
                        '2' => esc_html__('reCAPTCHA', 'digits'),
                    ],
                ]
            );


            $this->add_control(
                'dig_login_rememberme',
                [
                    'label' => esc_html__('Remember Me', 'digits'),
                    'type' => \Elementor\Controls_Manager::SELECT,
                    'default' => '1',
                    'render_type' => 'template',
                    'options' => [
                        '2' => esc_html__('Always', 'digits'),
                        '1' => esc_html__('Yes', 'digits'),
                        '0' => esc_html__('No', 'digits'),
                    ],
                ]
            );

        }
        $this->end_controls_section();
    }

    public function is_forgotpass()
    {
        $type = $this->get_widget_type();
        if ($type == 1 || $type == 3) {
            return true;
        }
    }

    public function is_login()
    {
        $type = $this->get_widget_type();
        if ($type == 1 || $type == 4) {
            return true;
        }
    }

    public function is_only_forgotpass()
    {
        $type = $this->get_widget_type();
        if ($type == 3) {
            return true;
        }
    }

    private function register_section_form_fields($user_roles)
    {
        $field_types = [
            'username' => esc_html__('Username', 'digits'),
            'mobile_number' => esc_html__('Mobile Number', 'digits'),
            'email' => esc_html__('Email', 'digits'),
            'password' => esc_html__('Password', 'digits'),
            'break' => esc_html__('Form Breaker', 'digits'),
            'form_step_title' => esc_html__('Form Step Title', 'digits'),
            'wp_predefined' => esc_html__('WordPress Fields', 'digits'),
            'wc_predefined' => esc_html__('WooCommerce Fields', 'digits'),
            'custom' => esc_html__('Custom', 'digits'),
        ];
        $wp_fields = [
            'first_name' => esc_html__('First Name', 'digits'),
            'last_name' => esc_html__('Last Name', 'digits'),
            'display_name' => esc_html__('Display Name', 'digits'),
            'user_role' => esc_html__('User Role', 'digits'),
        ];
        $wc_fields = [
            'first_name' => esc_html__('First Name', 'digits'),
            'last_name' => esc_html__('Last Name', 'digits'),
            'company' => esc_html__('Company', 'digits'),
            'addr1' => esc_html__('Address Line 1', 'digits'),
            'addr2' => esc_html__('Address line 2', 'digits'),
            'city' => esc_html__('City', 'digits'),
            'state' => esc_html__('State', 'digits'),
            'country' => esc_html__('Country', 'digits'),
            'zip' => esc_html__('Postcode / ZIP', 'digits'),
        ];

        $sub_field_types = [
            'text' => esc_html__('Text', 'digits'),
            'textarea' => esc_html__('Textarea', 'digits'),
            'number' => esc_html__('Number', 'digits'),
            'dropdown' => esc_html__('DropDown', 'digits'),
            'checkbox' => esc_html__('Checkbox', 'digits'),
            'radio' => esc_html__('Radio', 'digits'),
            'tac' => esc_html__('Terms & Conditions', 'digits'),
            'captcha' => esc_html__('Captcha', 'digits'),
            'recaptcha' => esc_html__('reCAPTCHA', 'digits'),
            'date' => esc_html__('Date', 'digits'),
        ];

        $this->start_controls_section(
            'section_form_registration',
            [
                'label' => esc_html__('Registration', 'digits'),
            ]
        );


        $this->add_control(
            'custom_user_role',
            [
                'label' => esc_html__('Custom User Role', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'default' => '',
            ]
        );

        $this->add_control(
            'custom_user_role_value',
            [
                'label' => esc_html__('User Role', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $user_roles,
                'condition' => [
                    'custom_user_role' => 'true',
                ],
            ]
        );

        $this->add_control(
            'registration_fields',
            [
                'label' => esc_html__('Form Fields', 'digits'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'type',
            [
                'label' => esc_html__('Type', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $field_types,
                'default' => 'username'
            ]
        );
        $repeater->add_control(
            'field_wp_type',
            [
                'label' => esc_html__('Field', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $wp_fields,
                'default' => 'first_name',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'wp_predefined',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'field_wc_type',
            [
                'label' => esc_html__('Field', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $wc_fields,
                'default' => 'first_name',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'wc_predefined',
                        ],
                    ],
                ],
            ]
        );


        $repeater->add_control(
            'sub_type',
            [
                'label' => esc_html__('Sub Type', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $sub_field_types,
                'default' => 'text',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'custom',
                        ],
                    ],
                ],
            ]
        );

        $repeater->add_control(
            'meta_key',
            [
                'label' => esc_html__('Meta Key', 'digits'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'custom',
                        ],
                    ],
                ],
            ]
        );

        $repeater->add_control(
            'label',
            [
                'label' => esc_html__('Label', 'digits'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'value' => '{{ sub_type }}',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => '!in',
                            'value' => [
                                'username',
                                'mobile_number',
                                'email',
                                'password'
                            ],
                        ],
                    ],
                ],
            ]
        );

        $tac_desc = '<div class="elementor-control-field-description">';
        $tac_desc .= esc_html__('Enclose the word(s) between [t] and [/t] for terms and condition and [p] and [/t] for privacy policy.', 'digits');
        $tac_desc .= '<br /><br />';
        $tac_desc .= esc_html__('For example "I Agree [t]Terms and Conditions[/t] & [p]Privacy Policy[/t]"', 'digits');
        $tac_desc .= '</div>';

        $repeater->add_control(
            'tac_desc',
            [
                'label' => $tac_desc,
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'sub_type',
                            'operator' => 'equal',
                            'value' => 'tac',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'terms_link',
            [
                'label' => esc_html__(' Terms & Conditions Link', 'digits'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'label_block' => true,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'sub_type',
                            'operator' => 'equal',
                            'value' => 'tac',
                        ],
                    ],
                ],
            ]
        );
        $repeater->add_control(
            'privacy_link',
            [
                'label' => esc_html__('Privacy Link', 'digits'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'label_block' => true,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'sub_type',
                            'operator' => 'equal',
                            'value' => 'tac',
                        ],
                    ],
                ],
            ]
        );


        $repeater->add_control(
            'field_options',
            [
                'name' => 'field_options',
                'label' => esc_html__('Options', 'digits'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => '',
                'description' => esc_html__('Enter each option in a separate line', 'digits'),
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'sub_type',
                            'operator' => 'in',
                            'value' => [
                                'checkbox',
                                'radio',
                                'dropdown',
                            ],
                        ],
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'custom',
                        ]
                    ],
                ],
            ]
        );

        $repeater->add_control(
            'user_roles',
            [
                'label' => esc_html__('User Role', 'digits'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => true,
                'options' => $user_roles,
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => 'equal',
                            'value' => 'wp_predefined',
                        ],
                        [
                            'name' => 'field_wp_type',
                            'operator' => 'equal',
                            'value' => 'user_role',
                        ],
                    ],
                ],
            ]
        );

        $repeater->add_control(
            'required',
            [
                'label' => esc_html__('Required', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'return_value' => 'true',
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'type',
                            'operator' => '!in',
                            'value' => [
                                'checkbox',
                                'break',
                                'form_step_title'
                            ],
                        ],
                    ],
                ],
            ]
        );

        $this->add_control(
            'form__fields',
            [
                'label' => __('Field Mapping', 'digits'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'type' => 'wp_predefined',
                        'field_wp_type' => 'first_name',
                        'label' => esc_attr__('First Name', 'digits'),
                        'required' => 'true',
                    ],
                    [
                        'type' => 'email'
                    ],
                    [
                        'type' => 'password'
                    ]
                ]

            ]
        );

        $this->end_controls_section();
    }

    private function register_section_general_settings($user_roles)
    {
        $this->start_controls_section(
            'section_general_settings',
            [
                'label' => esc_html__('General Settings', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'dig_enable_forgot_password',
            [
                'label' => esc_html__('Forgot Password', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'default' => 'true',
                'render_type' => 'template',
            ]
        );


        $this->end_controls_section();

    }

    private function register_section_redirect()
    {

        $this->start_controls_section(
            'section_redirect',
            [
                'label' => esc_html__('Redirection', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        if ($this->is_login()) {
            $this->add_control(
                'login_redirect',
                [
                    'label' => esc_html__('Login', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'description' => '<span class="digpage_desc">' . __('Leave blank for auto redirect', 'digits') . '</span>',
                    'default' => '',
                ]
            );
        }

        if ($this->is_forgotpass()) {
            $this->add_control(
                'forgot_redirect',
                [
                    'label' => esc_html__('Forgot Password', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'description' => '<span class="digpage_desc">' . __('Leave blank for auto redirect', 'digits') . '</span>',
                    'default' => '',
                ]
            );
        }

        if ($this->is_register()) {
            $this->add_control(
                'register_redirect',
                [
                    'label' => esc_html__('Register', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'description' => '<span class="digpage_desc">' . __('Leave blank for auto redirect', 'digits') . '</span>',
                    'default' => '',
                ]
            );
        }
        $this->end_controls_section();

    }

    private function register_section_titles()
    {
        $this->start_controls_section(
            'section_form_titles',
            [
                'label' => esc_html__('Form Title', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        if ($this->is_login()) {
            $this->add_control(
                'login_form_title',
                [
                    'label' => esc_html__('Login', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Login',
                ]
            );
        }

        if ($this->is_forgotpass()) {
            $this->add_control(
                'forgot_form_title',
                [
                    'label' => esc_html__('Reset Password', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Reset Password',
                ]
            );
        }

        if ($this->is_register()) {
            $this->add_control(
                'register_form_title',
                [
                    'label' => esc_html__('Register', 'digits'),
                    'type' => \Elementor\Controls_Manager::TEXT,
                    'default' => 'Register',
                ]
            );
        }
        $this->end_controls_section();

    }


    private function register_section_theme()
    {
        $this->start_controls_section(
            'section_light_theme',
            [
                'label' => esc_html__('Light Theme', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->register_theme_section('light');
        $this->end_controls_section();

        $this->start_controls_section(
            'section_dark_theme',
            [
                'label' => esc_html__('Dark Theme', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->register_theme_section('dark');
        $this->end_controls_section();
    }

    private function register_theme_section($suffix)
    {
        $selector = '{{WRAPPER}} .digits-form_container';

        $this->add_control(
            "{$suffix}_primary_color",
            [
                'label' => esc_html__('Primary Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--dprimary: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            "{$suffix}_accent_color",
            [
                'label' => esc_html__('Accent Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--daccent: {{VALUE}} !important;',
                ],
            ]
        );
        $this->add_control(
            "{$suffix}_title_color",
            [
                'label' => esc_html__('Title Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--dtitle: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            "{$suffix}_field_bg_color",
            [
                'label' => esc_html__('Field Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--dfield_bg: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            "{$suffix}_form_bg_color",
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--dform_bg: {{VALUE}}; !important',
                ],
            ]
        );

        $this->add_control(
            "{$suffix}_button_text_color",
            [
                'label' => esc_html__('Button Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    $selector => '--dbutton_text: {{VALUE}}; !important',
                ],
            ]
        );
    }

    private function register_section_general()
    {
        $this->start_controls_section(
            'section_style_general',
            [
                'label' => esc_html__('General', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'form_typography',
                'selector' => '{{WRAPPER}},{{WRAPPER}} *',
            ]
        );

        $this->add_responsive_control(
            'general_row_spacing',
            [
                'label' => esc_html__('Row Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .digits_original > div > div, {{WRAPPER}} .digits-form_tab_container > div' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_label()
    {
        return;
        $this->start_controls_section(
            'section_style_label',
            [
                'label' => esc_html__('Label', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'show_label',
            [
                'label' => esc_html__('Show Label', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'default' => 'true',
            ]
        );


        /* $this->add_control(
             'floating_label',
             [
                 'label' => esc_html__('Floating Labels', 'digits'),
                 'type' => \Elementor\Controls_Manager::SWITCHER,
                 'label_on' => esc_html__('Yes', 'digits'),
                 'label_off' => esc_html__('No', 'digits'),
                 'return_value' => 'true',
                 'default' => '',
                 'condition' => [
                     'show_label' => 'true',
                 ],
             ]
         );*/

        $this->add_control(
            'show_placeholder',
            [
                'label' => esc_html__('Placeholder', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
                'condition' => [
                    'floating_label' => '',
                ],
            ]
        );


        $this->add_control(
            'show_asterisk',
            [
                'label' => esc_html__('Show Asterisk', 'digits'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'digits'),
                'label_off' => esc_html__('No', 'digits'),
                'return_value' => 'true',
            ]
        );


        $this->add_control(
            'label_text_position',
            [
                'label' => esc_html__('Position', 'digits'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label_block' => false,
                'default' => 'left',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'digits'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'digits'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'digits'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input label' => 'text-align: {{VALUE}};',
                ],
            ]
        );


        $this->start_controls_tabs('label_state');

        $this->start_controls_tab(
            'label_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );


        $this->add_control(
            'label_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} label' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .dig-label .current' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .digits-form_input label',
                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'label_state_active',
            [
                'label' => esc_html__('Active', 'digits'),
            ]
        );


        $this->add_control(
            'label_active_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-active label' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_active_typography',
                'selector' => '{{WRAPPER}} .digits-active label',

                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_responsive_control(
            'label_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'conditions' => [
                    'terms' => [
                        [
                            'name' => 'show_label',
                            'operator' => 'equal',
                            'value' => 'true',
                        ],
                        [
                            'name' => 'floating_label',
                            'operator' => 'equal',
                            'value' => '',
                        ],
                    ],
                ],

            ]
        );

        $this->end_controls_section();
    }

    private function register_section_field()
    {
        $this->start_controls_section(
            'section_style_field',
            [
                'label' => esc_html__('Field', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('field_tabs');

        $this->start_controls_tab(
            'field_tab_value',
            [
                'label' => esc_html__('Value', 'digits'),
            ]
        );

        $this->add_control(
            'field_tab_color_value',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'field_tab_typography_value',
                'selector' => '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea',

            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'field_tab_placeholder',
            [
                'label' => esc_html__('Placeholder', 'digits'),
            ]
        );

        $this->add_control(
            'field_tab_color_placeholder',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input::placeholder, {{WRAPPER}} .digits-form_input textarea::placeholder' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'field_tab_typography_placeholder',
                'selector' => '{{WRAPPER}} .digits-form_input input::placeholder, {{WRAPPER}} .digits-form_input textarea::placeholder',

            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'field_padding',
            [
                'label' => esc_html__('Padding', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'floating_label' => '',
                ],
            ]
        );

        $this->add_control(
            'fields_tabs_separator',
            [
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs('field_tabs_state');

        $this->start_controls_tab(
            'field_tab_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'field_tab_background_color_normal',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'field_tab_border_normal',
                'selector' => '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'field_tab_border_radius_normal',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'field_tab_box_shadow_normal',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_input input, {{WRAPPER}} .digits-form_input textarea',
            ]
        );

        $this->end_controls_tab();


        $this->start_controls_tab(
            'field_tab_state_focus',
            [
                'label' => esc_html__('Focus', 'digits'),
            ]
        );

        $this->add_control(
            'field_tab_background_color_focus',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input:focus, {{WRAPPER}} .digits-form_input textarea:focus' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'field_tab_border_focus',
                'selector' => '{{WRAPPER}} .digits-form_input input:focus, {{WRAPPER}} .digits-form_input textarea:focus',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'field_tab_border_radius_focus',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input input:focus, {{WRAPPER}} .digits-form_input textarea:focus' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'field_tab_box_shadow_focus',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_input input:focus, {{WRAPPER}} .digits-form_input textarea:focus',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();
    }

    private function register_section_dropdown()
    {
        $this->start_controls_section(
            'section_style_dropdown',
            [
                'label' => esc_html__('DropDown', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->start_controls_tabs('dropdown_tabs_state');

        $this->start_controls_tab(
            'dropdown_tab_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'dropdown_tab_background_color_normal',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .digits-form_input_row .select2-container--default .select2-selection--single' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .digits-form_input_row .select2-container--default .select2-selection--single .select2-selection__rendered' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'dropdown_tab_border_normal',
                'selector' => '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'dropdown_tab_border_radius_normal',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dropdown_tab_box_shadow_normal',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'dropdown_tab_state_focus',
            [
                'label' => esc_html__('Focus', 'digits'),
            ]
        );

        $this->add_control(
            'dropdown_tab_background_color_focus',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select.select2-container--open .select2-selection--single .select2-selection__rendered' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'dropdown_tab_border_focus',
                'selector' => '{{WRAPPER}} .digits-form_input .digits-form-select.select2-container--open .select2-selection--single',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'dropdown_tab_border_radius_focus',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select.select2-container--open .select2-selection--single' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dropdown_tab_box_shadow_focus',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_input .digits-form-select.select2-container--open .select2-selection--single',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_control(
            'dropdown_tab_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single .select2-selection__rendered' => 'color: {{VALUE}};'
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'dropdown_tab_typography',
                'selector' => '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single .select2-selection__rendered',

            ]
        );


        $this->add_responsive_control(
            'dropdown_padding',
            [
                'label' => esc_html__('Padding', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .digits-form_input .digits-form-select .select2-selection--single' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_checkbox()
    {
        $this->start_controls_section(
            'section_style_checkbox',
            [
                'label' => esc_html__('Checkbox', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'checkbox_row_spacing',
            [
                'label' => esc_html__('Row Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .digits-field-type_tac .dig_opt_mult' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'checkbox_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult .dig_input_wrapper div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .digits-field-type_tac .dig_opt_mult .dig_input_wrapper div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',

                ],
                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );

        $this->add_responsive_control(
            'checkbox_size',
            [
                'label' => esc_html__('Size', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper' => 'padding-left: calc({{SIZE}}{{UNIT}} + 8px);line-height: calc({{SIZE}}{{UNIT}} + 2px);',
                    '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper::before, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper::before' => 'top: calc(100% / 2 - {{SIZE}}{{UNIT}}/2);width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper::after, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper::after' => 'top: calc(100% / 2 - {{SIZE}}{{UNIT}}/2);width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .digits-field-type_tac .dig_input_wrapper' => 'padding-left: calc({{SIZE}}{{UNIT}} + 8px);line-height: calc({{SIZE}}{{UNIT}} + 2px);',
                    '{{WRAPPER}} .digits-field-type_tac .dig_input_wrapper::before' => 'top: calc(100% / 2 - {{SIZE}}{{UNIT}}/2);width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',

                ],
            ]
        );

        $this->add_control(
            'checkbox_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .digits-field-type_tac .dig_input_wrapper' => 'color: {{VALUE}};',

                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'checkbox_typography',
                'selector' => '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper, {{WRAPPER}} .digits-field-type_tac .dig_input_wrapper',

            ]
        );
        $this->start_controls_tabs('checkbox_tabs_state');

        $this->start_controls_tab(
            'checkbox_tab_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'checkbox_tab_background_color_normal',
            [
                'label' => esc_html__('Checkbox Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_rememberme .dig_input_wrapper::before, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .dig_input_wrapper::before' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .digits-field-type_tac .dig_input_wrapper::before' => 'background-color: {{VALUE}};',

                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'checkbox_tab_state_checked',
            [
                'label' => esc_html__('Checked', 'digits'),
            ]
        );

        $this->add_control(
            'checkbox_tab_background_color_checked',
            [
                'label' => esc_html__('Checkbox Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_rememberme .selected .dig_input_wrapper::after, {{WRAPPER}} .digits-field-type_checkbox .dig_opt_mult_con .selected .dig_input_wrapper::after' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} .digits-field-type_tac .selected .dig_input_wrapper::before' => 'background-color: {{VALUE}};',

                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_section_radio()
    {
        $this->start_controls_section(
            'section_style_radio',
            [
                'label' => esc_html__('Radio', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'radio_row_spacing',
            [
                'label' => esc_html__('Row Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'radio_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult .dig_input_wrapper div' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_label' => 'true',
                ],
            ]
        );

        $this->add_responsive_control(
            'radio_size',
            [
                'label' => esc_html__('Size', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper' => 'padding-left: calc({{SIZE}}{{UNIT}} + 8px);line-height: calc({{SIZE}}{{UNIT}} + 2px);',
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper::before' => 'top: calc(100% / 2 - {{SIZE}}{{UNIT}}/2);width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper::after' => 'top: calc(100% / 2 - {{SIZE}}{{UNIT}}/2);width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'radio_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper div' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'radio_typography',
                'selector' => '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper div',

            ]
        );


        $this->start_controls_tabs('radio_tabs_state');

        $this->start_controls_tab(
            'radio_tab_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'radio_tab_background_color_normal',
            [
                'label' => esc_html__('Radio Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .dig_input_wrapper::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'radio_tab_state_checked',
            [
                'label' => esc_html__('Checked', 'digits'),
            ]
        );

        $this->add_control(
            'radio_tab_background_color_checked',
            [
                'label' => esc_html__('Radio Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-field-type_radio .dig_opt_mult_con .selected .dig_input_wrapper::after' => 'background-color: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    private function register_section_signup_button()
    {
        if (!$this->is_login() || !$this->is_register()) {
            return;
        }

        $this->start_controls_section(
            'section_style_signup_button',
            [
                'label' => esc_html__('Signup', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->add_control(
            'signup_text',
            array(
                'label' => esc_html__('Signup Text', 'digits'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ));
        $this->add_control(
            'signup_text_color',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'signup_text_typography',
                'selector' => '{{WRAPPER}} .digits-form_toggle_login_register',

            ]
        );

        $this->add_responsive_control(
            'signup_text_position',
            [
                'label' => esc_html__('Position', 'digits'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'label_block' => false,
                'default' => 'center',
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'digits'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'digits'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'digits'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .dig_login_signup_bar' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'signup_text_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'signup_button_style',
            array(
                'label' => esc_html__('Signup Button', 'digits'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            )
        );

        $this->add_responsive_control(
            'signup_button_height',
            [
                'label' => esc_html__('Height', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'line-height: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'signup_button_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('signup_button_tabs');

        $this->start_controls_tab(
            'signup_button_tab_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'signup_button_tab_color_normal',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'signup_button_tab_typography_normal',
                'selector' => '{{WRAPPER}} .digits-form_toggle_login_register',

            ]
        );

        $this->add_control(
            'signup_digits_background_normal',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'signup_button_tab_hover',
            [
                'label' => esc_html__('Hover', 'digits'),
            ]
        );

        $this->add_control(
            'signup_button_tab_color_hover',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'signup_button_tab_typography_hover',
                'selector' => '{{WRAPPER}} .digits-form_toggle_login_register:hover',

            ]
        );

        $this->add_control(
            'signup_digits_background_hover',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register:hover' => 'background: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'signup_button_border_heading',
            [
                'label' => esc_html__('Border', 'digits'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'signup_button_border',
                'selector' => '{{WRAPPER}} .digits-form_toggle_login_register',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'signup_button_radius',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_toggle_login_register' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'signup_button_box_shadow',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_toggle_login_register',
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_button()
    {
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => esc_html__('Continue Button', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'button_height',
            [
                'label' => esc_html__('Height', 'digits'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 1000,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]' => 'line-height: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_spacing',
            [
                'label' => esc_html__('Spacing', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab(
            'button_tab_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'button_tab_color_normal',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_tab_typography_normal',
                'selector' => '{{WRAPPER}} .digits-form_button[type="submit"]',

            ]
        );

        $this->add_control(
            'digits_background_normal',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]' => 'background: {{VALUE}};',
                ],

            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'button_tab_hover',
            [
                'label' => esc_html__('Hover', 'digits'),
            ]
        );

        $this->add_control(
            'button_tab_color_hover',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_tab_typography_hover',
                'selector' => '{{WRAPPER}} .digits-form_button[type="submit"]:hover',

            ]
        );

        $this->add_control(
            'digits_background_hover',
            [
                'label' => esc_html__('Background Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]:hover' => 'background: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_heading',
            [
                'label' => esc_html__('Border', 'digits'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .digits-form_button[type="submit"]',
                'fields_options' => [
                    'width' => [
                        'label' => esc_html__('Border Width', 'digits'),
                    ],
                    'color' => [
                        'label' => esc_html__('Border Color', 'digits'),
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'button_radius',
            [
                'label' => esc_html__('Border Radius', 'digits'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digits-form_button[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'exclude' => [
                    'box_shadow_position',
                ],
                'separator' => 'before',
                'selector' => '{{WRAPPER}} .digits-form_button[type="submit"]',
            ]
        );

        $this->end_controls_section();
    }

    private function register_section_text()
    {
        $this->start_controls_section(
            'section_style_text',
            [
                'label' => esc_html__('Form Title', 'digits'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        $this->start_controls_tabs('text_tabs_state');

        $this->start_controls_tab(
            'text_tabs_state_normal',
            [
                'label' => esc_html__('Normal', 'digits'),
            ]
        );

        $this->add_control(
            'text_tabs_state_color_normal',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_tabs_sate_typography_normal',
                'selector' => '{{WRAPPER}} .digits-form_heading',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'text_tabs_state_state_focus',
            [
                'label' => esc_html__('Hover', 'digits'),
            ]
        );

        $this->add_control(
            'text_tabs_state_color_hover',
            [
                'label' => esc_html__('Text Color', 'digits'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digits-form_heading:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_tabs_sate_typography_hover',
                'selector' => '{{WRAPPER}} .digits-form_heading:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

}
