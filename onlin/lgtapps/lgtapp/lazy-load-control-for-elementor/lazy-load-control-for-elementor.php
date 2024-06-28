<?php
/*
Plugin Name: Lazy Load Control For Elementor
Description: Remove the Lazy Load attribute from specific images in Elementor.
Author: Jose Mortellaro
Author URI: https://josemortellaro.com
Domain Path: /languages/
Text Domain: lazy-load-control-for-elementor
Version: 0.0.3
Elementor tested up to: 3.18.5
*/
/*  This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

add_action( 'elementor/element/image/section_image/before_section_end', function( $element, $args ) {
	$element->start_injection( [
		'at' => 'after',
		'of' => 'link',
	] );
	$element->add_control(
		'eos_image_lazy_loading',
		[
			'label' => __( 'Lazy Loading','lazy-load-control-for-elementor' ),
			'type' => \Elementor\Controls_Manager::SELECT,
      'default' => 'lazy',
			'options' => [
				'lazy' => __( 'Lazy load','lazy-load-control-for-elementor' ),
				'no_lazy' => __( 'Do not lazy load','lazy-load-control-for-elementor' ),
			],
		]
	);
	$element->end_injection();
}, 10, 2 );

add_action( 'elementor/widget/render_content', function( $content, $widget ){
  if( $widget->get_name() === 'image' ){
    $settings = $widget->get_settings();
	$content = str_replace( ' loading="lazy"','',$content );
	if( ! isset( $settings['eos_image_lazy_loading'] ) || 'lazy' === sanitize_text_field( $settings['eos_image_lazy_loading'] ) ) {
		$content = str_replace( 
			array( 
				'<img'
			),
			array( 
				'<img loading="lazy"'
			), $content );
	}
  }
  return $content;
}, 10, 2 );