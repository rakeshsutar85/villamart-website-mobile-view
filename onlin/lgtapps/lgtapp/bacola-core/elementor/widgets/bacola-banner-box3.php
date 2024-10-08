<?php

namespace Elementor;

class Bacola_Banner_Box3_Widget extends Widget_Base {

    public function get_name() {
        return 'bacola-banner-box3';
    }
    public function get_title() {
        return 'Banner Box 3 (K)';
    }
    public function get_icon() {
        return 'eicon-slider-push';
    }
    public function get_categories() {
        return [ 'bacola' ];
    }

	protected function register_controls() {

		$this->start_controls_section(
			'content_section',
			[
				'label' => esc_html__( 'Content', 'plugin-name' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control( 'banner_type',
			[
				'label' => esc_html__( 'Banner Type', 'bacola-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'type1',
				'options' => [
					'select-type' => esc_html__( 'Select Type', 'bacola-core' ),
					'type1'	  => esc_html__( 'Type 1', 'bacola-core' ),
					'type2'	  => esc_html__( 'Type 2', 'bacola-core' ),
					'type3'	  => esc_html__( 'Type 3', 'bacola-core' ),
				],
			]
		);

		$defaultimage = plugins_url( 'images/banner-box3.jpg', __DIR__ );
		
        $this->add_control( 'image',
            [
                'label' => esc_html__( 'Image', 'bacola-core' ),
                'type' => Controls_Manager::MEDIA,
                'default' => ['url' => $defaultimage],
            ]
        );
		
        $this->add_control( 'title',
            [
                'label' => esc_html__( 'Title', 'bacola-core' ),
                'type' => Controls_Manager::TEXTAREA,
                'default' => 'Legumes & Cereals',
                'description'=> 'Add a title.',
				'label_block' => true,
            ]
        );
		
        $this->add_control( 'subtitle',
            [
                'label' => esc_html__( 'Subtitle', 'bacola-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => 'Weekend Discount 40%',
                'description'=> 'Add a subtitle.',
				'label_block' => true,
            ]
        );
		
        $this->add_control( 'desc',
            [
                'label' => esc_html__( 'Description', 'bacola-core' ),
                'type' => Controls_Manager::TEXT,
                'default' => 'Bacola Weekend Discount',
                'description'=> 'Add a subtitle.',
				'label_block' => true,
				'condition' => ['banner_type' => ['select-type','type1','type3','type4']]
            ]
        );
		
        $this->add_control( 'btn_title',
            [
                'label' => esc_html__( 'Button Title', 'bacola-core' ),
                'type' => Controls_Manager::TEXT,
                'label_block' => true,
                'default' => 'Shop Now',
                'pleaceholder' => esc_html__( 'Enter button title here', 'bacola-core' ),
            ]
        );
		
        $this->add_control( 'btn_link',
            [
                'label' => esc_html__( 'Button Link', 'bacola-core' ),
                'type' => Controls_Manager::URL,
                'label_block' => true,
                'placeholder' => esc_html__( 'Place URL here', 'bacola-core' )
            ]
        );
		
		$this->add_control( 'button_type',
			[
				'label' => esc_html__( 'Button Type', 'bacola-core' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'button-info-dark',
				'options' => [
					'select-type' => esc_html__( 'Select Type', 'bacola-core' ),
					'button-info-dark'	=> esc_html__( 'Type 1', 'bacola-core' ),
					'button-danger'	  	=> esc_html__( 'Type 2', 'bacola-core' ),
					'button-primary'	=> esc_html__( 'Type 3', 'bacola-core' ),
				],
			]
		);
		
		/*****   END CONTROLS SECTION   ******/
        /*****   START CONTROLS SECTION   ******/
		
		$this->end_controls_section();
		$this->start_controls_section('bacola_styling',
            [
                'label' => esc_html__( 'Style', 'bacola-core' ),
                'tab' => Controls_Manager::TAB_STYLE
            ]
        );
		
		$this->add_control( 'content_heading',
            [
                'label' => esc_html__( 'CONTENT', 'bacola-core' ),
                'type' => Controls_Manager::HEADING,
            ]
        );
		
		$this->add_responsive_control( 'banner_box3_width',
            [
                'label' => esc_html__( 'Width', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .banner-thumbnail img' => 'width: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_responsive_control( 'banner_box3_height',
            [
                'label' => esc_html__( 'Height', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .banner-thumbnail img ' => 'height: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_responsive_control( 'banner_box3_padding',
            [
                'label' => esc_html__( 'Padding', 'bacola-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            ]
        );
		
		$this->add_control( 'title_heading',
            [
                'label' => esc_html__( 'TITLE', 'bacola-core' ),
                'type' => Controls_Manager::HEADING,
				'separator' => 'before'
            ]
        );
		
		$this->add_control( 'title_color',
           [
               'label' => esc_html__( 'Title Color', 'bacola-core' ),
               'type' => Controls_Manager::COLOR,
               'default' => '#3e445a',
               'selectors' => ['{{WRAPPER}} .module-banner .banner-content .entry-title' => 'color: {{VALUE}};']
           ]
        );
		
		$this->add_control( 'title_size',
            [
                'label' => esc_html__( 'Title Size', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => '',
                'selectors' => [ '{{WRAPPER}} .module-banner .banner-content .entry-title' => 'font-size: {{SIZE}}px;' ],
            ]
        );
		
		$this->add_responsive_control( 'title_left',
            [
                'label' => esc_html__( 'Left', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .entry-title' => 'padding-left: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_responsive_control( 'title_top',
            [
                'label' => esc_html__( 'Top', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .entry-title' => 'padding-top: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_control( 'title_opacity_important_style',
            [
                'label' => esc_html__( 'Opacity', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'default' => '',
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .entry-title' => 'opacity: {{VALUE}} ;'],
				
            ]
        );
		
		$this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'title_typo',
                'label' => esc_html__( 'Typography', 'bacola-core' ),
                'scheme' => Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .module-banner .banner-content .entry-title'
            ]
        );
		
		$this->add_control( 'subtitle_heading',
            [
                'label' => esc_html__( 'SUBTITLE', 'bacola-core' ),
                'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
				
            ]
        );
		
		$this->add_control( 'subtitle_color',
           [
               'label' => esc_html__( 'Subtitle Color', 'bacola-core' ),
               'type' => Controls_Manager::COLOR,
               'default' => '#00b853',
               'selectors' => ['{{WRAPPER}} .module-banner .banner-content .discount-text , {{WRAPPER}} .module-banner .banner-content .entry-subtitle.xlight' => 'color: {{VALUE}};'],
			   'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
           ]
        );
		
		$this->add_control( 'subtitle_size',
            [
                'label' => esc_html__( 'Subtitle Size', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => '',
                'selectors' => [ '{{WRAPPER}}  .module-banner .banner-content .discount-text , {{WRAPPER}} .module-banner .banner-content .entry-subtitle.xlight' => 'font-size: {{SIZE}}px;' ],
				'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
			]
        );
		
		$this->add_responsive_control( 'subtitle_left',
            [
                'label' => esc_html__( 'Left', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .discount-text , {{WRAPPER}} .module-banner .banner-content .entry-subtitle.xlight' => 'padding-left: {{SIZE}}{{UNIT}}',
					'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
				]
            ]
        );
		
		$this->add_responsive_control( 'subtitle_top',
            [
                'label' => esc_html__( 'Top', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .discount-text , {{WRAPPER}} .module-banner .banner-content .entry-subtitle.xlight' => 'padding-top: {{SIZE}}{{UNIT}}',
                ],
				'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
            ]
        );
		
		$this->add_control( 'subtitle_opacity_important_style',
            [
                'label' => esc_html__( 'Opacity', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'default' => '',
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .discount-text' => 'opacity: {{VALUE}} ;'],
				'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
            ]
        );
		
		$this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'subtitle_typo',
                'label' => esc_html__( 'Typography', 'bacola-core' ),
                'scheme' => Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .module-banner .banner-content .discount-text , {{WRAPPER}} .module-banner .banner-content .entry-subtitle.xlight',
				'condition' => ['banner_type' => ['type1' , 'select-type' , 'type3']]
            ]
        );
		
		$this->add_control( 'desc_heading',
            [
                'label' => esc_html__( 'DESCRIPTION', 'bacola-core' ),
                'type' => Controls_Manager::HEADING,
				'separator' => 'before'
            ]
        );
		
		$this->add_control( 'Desc_color',
           [
               'label' => esc_html__( 'Description Color', 'bacola-core' ),
               'type' => Controls_Manager::COLOR,
               'default' => '#9b9bb4',
               'selectors' => ['{{WRAPPER}} .module-banner .banner-content .entry-text' => 'color: {{VALUE}};']
           ]
        );
		
		$this->add_control( 'desc_size',
            [
                'label' => esc_html__( 'Description Size', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 100,
                'step' => 1,
                'default' => '',
                'selectors' => [ '{{WRAPPER}} .module-banner .banner-content .entry-text' => 'font-size: {{SIZE}}px;' ],
            ]
        );
		
		$this->add_responsive_control( 'desc_left',
            [
                'label' => esc_html__( 'Left', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .entry-text' => 'padding-left: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_responsive_control( 'desc_top',
            [
                'label' => esc_html__( 'Top', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .module-banner .banner-content .entry-text' => 'padding-top: {{SIZE}}{{UNIT}}',
                ]
            ]
        );
		
		$this->add_control( 'desc_opacity_important_style',
            [
                'label' => esc_html__( 'Opacity', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'default' => '',
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .entry-text' => 'opacity: {{VALUE}} ;'],
            ]
        );
		
		$this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'desc_typo',
                'label' => esc_html__( 'Typography', 'bacola-core' ),
                'scheme' => Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .module-banner .banner-content .entry-text'
            ]
        );
		
		$this->end_controls_tab();
        $this->end_controls_tabs();
        $this->end_controls_section();
     	
		/*****   END CONTROLS SECTION   ******/
        /*****   START CONTROLS SECTION   ******/
        $this->start_controls_section('btn_styling',
            [
                'label' => esc_html__( ' Button Style', 'bacola-core' ),
                'tab' => Controls_Manager::TAB_STYLE,
				
            ]
        );
		
		$this->add_responsive_control( 'btn_padding',
            [
                'label' => esc_html__( 'Padding', 'bacola-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => ['{{WRAPPER}}  .module-banner .banner-content .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],              
				
		    ]
        );
  	    
		$this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typo',
                'label' => esc_html__( 'Typography', 'bacola-core' ),
                'scheme' => Core\Schemes\Typography::TYPOGRAPHY_1,
                'selector' => '{{WRAPPER}} .module-banner .banner-content .button',
				
            ]
        );
        
		$this->add_responsive_control( 'btn_left',
            [
                'label' => esc_html__( 'Left', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}}  .module-banner .banner-content .button' => 'margin-left: {{SIZE}}{{UNIT}}',
                ],
				
            ]
        );
		
		$this->add_responsive_control( 'btn_top',
            [
                'label' => esc_html__( 'Top', 'bacola-core' ),
                'type' => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'vh' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 150
                    ],
                    'vh' => [
                        'min' => 0,
                        'max' => 50
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}}  .module-banner .banner-content .button' => 'margin-top: {{SIZE}}{{UNIT}}',
                ],
				
            ]
        );
		
		$this->add_control( 'btn_color',
            [
                'label' => esc_html__( 'Color', 'bacola-core' ),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .button ' => 'color: {{VALUE}};'],
				
            ]
        );
       
	    $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'btn_border',
                'label' => esc_html__( 'Border', 'bacola-core' ),
                'selector' => '{{WRAPPER}} .module-banner .banner-content .button',
				
            ]
        );
        
		$this->add_responsive_control( 'btn_border_radius',
            [
                'label' => esc_html__( 'Border Radius', 'bacola-core' ),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;'],
				
			]
        );
       
		$this->add_control( 'btn_bgclr',
           [
               'label' => esc_html__( 'Background Color', 'bacola-core' ),
               'type' => Controls_Manager::COLOR,
               'default' => '',
               'selectors' => [
					'{{WRAPPER}} .module-banner .banner-content .button' => 'background-color: {{VALUE}};'
               ],
			   
           ]
        );
		
		$this->add_control( 'btn_opacity_important_style',
            [
                'label' => esc_html__( 'Opacity', 'bacola-core' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 1,
                'step' => 0.1,
                'default' => '',
                'selectors' => ['{{WRAPPER}} .module-banner .banner-content .button' => 'opacity: {{VALUE}} ;'],
            ]
        );
       
		
		
		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$target = $settings['btn_link']['is_external'] ? ' target="_blank"' : '';
		$nofollow = $settings['btn_link']['nofollow'] ? ' rel="nofollow"' : '';
		
		$output = '';
	
		if($settings['banner_type'] == 'type3'){
			echo '<div class="site-module module-banner image align-right align-center">';
			echo '<div class="module-body">';
			echo '<div class="banner-wrapper">';
			echo '<div class="banner-content">';
			echo '<div class="content-main">';
			echo '<h4 class="entry-subtitle medium xlight color-text">'.bacola_sanitize_data($settings['subtitle']).'</h4>';
			echo '<h3 class="entry-title large color-text">'.esc_html($settings['title']).'</h3>';
			echo '<div class="entry-text color-info-dark">'.esc_html($settings['desc']).'</div>';
			echo '</div>';
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="button '.esc_attr($settings['button_type']).' rounded xsmall">'.esc_html($settings['btn_title']).'</a>';
			echo '</div>';
			echo '<div class="banner-thumbnail">';
			echo '<img src="'.esc_url($settings['image']['url']).'" alt="banner">';
			echo '</div>';
			if($settings['btn_link']['url']){
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="overlay-link"></a>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} elseif($settings['banner_type'] == 'type2'){
			echo '<div class="site-module module-banner image align-left align-center">';
			echo '<div class="module-body">';
			echo '<div class="banner-wrapper">';
			echo '<div class="banner-content">';
			echo '<div class="content-main">';
			echo '<h3 class="entry-title medium color-text-light">'.bacola_sanitize_data($settings['title']).'</h3>';
			echo '<div class="entry-text color-info-dark">'.esc_html($settings['subtitle']).'</div>';
			echo '</div>';
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="button '.esc_attr($settings['button_type']).' rounded xsmall">'.esc_html($settings['btn_title']).'</a>';
			echo '</div>';
			echo '<div class="banner-thumbnail">';
			echo '<img src="'.esc_url($settings['image']['url']).'" alt="banner">';
			echo '</div>';
			if($settings['btn_link']['url']){
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="overlay-link"></a>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		} else {
			echo '<div class="site-module module-banner image align-left align-center">';
			echo '<div class="module-body">';
			echo '<div class="banner-wrapper">';
			echo '<div class="banner-content">';
			echo '<div class="content-header">';
			echo '<div class="discount-text color-success">'.esc_html($settings['subtitle']).'</div>';
			echo '</div>';
			echo '<div class="content-main">';
			echo '<h3 class="entry-title color-text-light">'.bacola_sanitize_data($settings['title']).'</h3>';
			echo '<div class="entry-text color-info-dark">'.esc_html($settings['desc']).'</div>';
			echo '</div>';
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="button '.esc_attr($settings['button_type']).' rounded xsmall">'.esc_html($settings['btn_title']).'</a>';
			echo '</div>';
			echo '<div class="banner-thumbnail">';
			echo '<img src="'.esc_url($settings['image']['url']).'" alt="banner">';
			echo '</div>';
			if($settings['btn_link']['url']){
			echo '<a href="'.esc_url($settings['btn_link']['url']).'" '.esc_attr($target.$nofollow).' class="overlay-link"></a>';
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

	}

}
