<?php
/**
 * Layout functions
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'bya_select2_html' ) ) {

	/**
	 * Return or display Select2 HTML
	 *
	 * @return string
	 */
	function bya_select2_html( $args, $echo = true ) {
		$args = wp_parse_args(
			$args,
			array(
				'class'                   => '',
				'id'                      => '',
				'name'                    => '',
				'list_type'               => '',
				'action'                  => '',
				'placeholder'             => '',
				'exclude_global_variable' => 'no',
				'custom_attributes'       => array(),
				'multiple'                => true,
				'allow_clear'             => true,
				'selected'                => true,
				'options'                 => array(),
			)
		);

		$multiple = $args['multiple'] ? 'multiple="multiple"' : '';
		$name     = esc_attr( '' !== $args['name'] ? $args['name'] : $args['id'] ) . '[]';
		$options  = array_filter( bya_check_is_array( $args['options'] ) ? $args['options'] : array() );

		$allowed_html = array(
			'select' => array(
				'id'                           => array(),
				'class'                        => array(),
				'data-placeholder'             => array(),
				'data-allow_clear'             => array(),
				'data-exclude-global-variable' => array(),
				'data-action'                  => array(),
				'multiple'                     => array(),
				'name'                         => array(),
			),
			'option' => array(
				'value'    => array(),
				'selected' => array(),
			),
		);

		// Custom attribute handling.
		$custom_attributes = bya_format_custom_attributes( $args );

		ob_start();
		?><select <?php echo esc_attr( $multiple ); ?> 
			name="<?php echo esc_attr( $name ); ?>" 
			id="<?php echo esc_attr( $args['id'] ); ?>" 
			data-action="<?php echo esc_attr( $args['action'] ); ?>" 
			data-exclude-global-variable="<?php echo esc_attr( $args['exclude_global_variable'] ); ?>" 
			class="bya_select2_search <?php echo esc_attr( $args['class'] ); ?>" 
			data-placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" 
			<?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
			<?php echo $args['allow_clear'] ? 'data-allow_clear="true"' : ''; ?> >
				<?php
				if ( is_array( $args['options'] ) ) {
					foreach ( $args['options'] as $option_id ) {
						$option_value = '';
						switch ( $args['list_type'] ) {
							case 'post':
								$option_value = get_the_title( $option_id );
								break;
							case 'products':
								$option_value = get_the_title( $option_id ) . ' (#' . absint( $option_id ) . ')';
								break;
							case 'customers':
								$user = get_user_by( 'id', $option_id );
								if ( $user ) {
									$option_value = $user->display_name . '(#' . absint( $user->ID ) . ' &ndash; ' . $user->user_email . ')';
								}
								break;
						}

						if ( $option_value ) {
							?>
						<option value="<?php echo esc_attr( $option_id ); ?>" <?php echo $args['selected'] ? 'selected="selected"' : ''; // WPCS: XSS ok. ?>><?php echo esc_html( $option_value ); ?></option>
							<?php
						}
					}
				}
				?>
		</select>
		<?php
		$html = ob_get_clean();

		if ( $echo ) {
			echo wp_kses( $html, $allowed_html );
		}

		return $html;
	}
}

if ( ! function_exists( 'bya_format_custom_attributes' ) ) {

	/**
	 * Format Custom Attributes
	 *
	 * @return array
	 */
	function bya_format_custom_attributes( $value ) {
		$custom_attributes = array();

		if ( ! empty( $value['custom_attributes'] ) && is_array( $value['custom_attributes'] ) ) {
			foreach ( $value['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '=' . esc_attr( $attribute_value ) . '';
			}
		}

		return $custom_attributes;
	}
}

if ( ! function_exists( 'bya_get_datepicker_html' ) ) {

	/**
	 * Return or display Datepicker HTML
	 *
	 * @return string
	 */
	function bya_get_datepicker_html( $args, $echo = true ) {
		$args = wp_parse_args(
			$args,
			array(
				'class'             => '',
				'id'                => '',
				'name'              => '',
				'placeholder'       => '',
				'custom_attributes' => array(),
				'value'             => '',
				'wp_zone'           => true,
			)
		);

		$name = ( '' !== $args['name'] ) ? $args['name'] : $args['id'];

		$allowed_html = array(
			'input' => array(
				'id'          => array(),
				'type'        => array(),
				'placeholder' => array(),
				'class'       => array(),
				'value'       => array(),
				'name'        => array(),
				'min'         => array(),
				'max'         => array(),
				'style'       => array(),
			),
		);

		// Custom attribute handling.
		$custom_attributes = bya_format_custom_attributes( $args );
		$value             = ( ! empty( $args['value'] ) && ! isset( $args['date_format'] ) ) ? BYA_Date_Time::get_wp_format_datetime( $args['value'], 'date', $args['wp_zone'] ) : $args['value'];
		ob_start();
		?>
		<input type = "text" 
			   id="<?php echo esc_attr( $args['id'] ); ?>"
			   value = "<?php echo esc_attr( $value ); ?>"
			   class="bya_datepicker <?php echo esc_attr( $args['class'] ); ?>" 
			   placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>" 
			   <?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
			   />

		<input type = "hidden" 
			   class="bya_alter_datepicker_value" 
			   name="<?php echo esc_attr( $name ); ?>"
			   value = "<?php echo esc_attr( $args['value'] ); ?>"
			   /> 
		<?php
		$html = ob_get_clean();

		if ( $echo ) {
			echo wp_kses( $html, $allowed_html );
		}

		return $html;
	}
}

if ( ! function_exists( 'bya_display_action' ) ) {

	/**
	 * Display Action
	 *
	 * @return string
	 */
	function bya_display_action( $status, $id, $current_url, $action = false ) {
		switch ( $status ) {
			case 'edit':
				$status_name = esc_html__( 'Edit', 'buy-again-for-woocommerce' );
				break;
			case 'active':
				$status_name = esc_html__( 'Activate', 'buy-again-for-woocommerce' );
				break;
			case 'inactive':
				$status_name = esc_html__( 'Deactivate', 'buy-again-for-woocommerce' );
				break;
			default:
				$status_name = esc_html__( 'Delete Permanently', 'buy-again-for-woocommerce' );
				break;
		}

		$section_name = 'section';
		if ( $action ) {
			$section_name = 'action';
		}

		if ( 'edit' == $status ) {
			return '<a href="' . esc_url(
				add_query_arg(
					array(
						$section_name => $status,
						'id'          => $id,
					),
					$current_url
				)
			) . '">' . $status_name . '</a>';
		} elseif ( 'delete' == $status ) {
			return '<a class="bya_delete_data" href="' . esc_url(
				add_query_arg(
					array(
						'action' => $status,
						'id'     => $id,
					),
					$current_url
				)
			) . '">' . $status_name . '</a>';
		} else {
			return '<a href="' . esc_url(
				add_query_arg(
					array(
						'action' => $status,
						'id'     => $id,
					),
					$current_url
				)
			) . '">' . $status_name . '</a>';
		}
	}
}

if ( ! function_exists( 'bya_display_status' ) ) {

	/**
	 * Display formatted status
	 *
	 * @return string
	 */
	function bya_display_status( $status, $html = true ) {

		$status_object = get_post_status_object( $status );

		if ( ! isset( $status_object ) ) {
			return '';
		}

		return $html ? '<mark class="bya_status_label ' . esc_attr( $status ) . '_status"><span >' . esc_html( $status_object->label ) . '</span></mark>' : esc_html( $status_object->label );
	}
}

if ( ! function_exists( 'bya_get_template' ) ) {

	/**
	 *  Get other templates from themes
	 */
	function bya_get_template( $template_name, $args = array() ) {

		wc_get_template( $template_name, $args, 'buy-again-for-woocommerce/', bya()->templates() );
	}
}

if ( ! function_exists( 'bya_get_template_html' ) ) {

	/**
	 *  Like bya_get_template, but returns the HTML instead of outputting.
	 *
	 *  @return string
	 */
	function bya_get_template_html( $template_name, $args = array() ) {

		ob_start();
		bya_get_template( $template_name, $args );
		return ob_get_clean();
	}
}

if ( ! function_exists( 'bya_wc_help_tip' ) ) {

	/**
	 *  Display tool help based on WC help tip
	 *
	 *  @return string
	 */
	function bya_wc_help_tip( $tip, $allow_html = false, $echo = true ) {

		$formatted_tip = wc_help_tip( $tip, $allow_html );

		$allowed_html = array(
			'a'      => array(
				'href'  => array(),
				'title' => array(),
			),
			'br'     => array(),
			'em'     => array(),
			'strong' => array(),
			'span'   => array(
				'data-tip' => array(),
				'class'    => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( $formatted_tip, $allowed_html );
		}

		return $formatted_tip;
	}
}

if ( ! function_exists( 'bya_render_product_image' ) ) {

	/**
	 *  Display Product image
	 *
	 *  @return string
	 */
	function bya_render_product_image( $product, $echo = true ) {

		$allowed_html = array(
			'a'   => array(
				'href' => array(),
			),
			'img' => array(
				'class'  => array(),
				'src'    => array(),
				'alt'    => array(),
				'srcset' => array(),
				'sizes'  => array(),
				'width'  => array(),
				'height' => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( $product->get_image(), $allowed_html );
		}

		return $product->get_image();
	}
}


if ( ! function_exists( 'bya_price' ) ) {

	/**
	 *  Display Price based wc_price function
	 *
	 *  @return string
	 */
	function bya_price( $price, $echo = true ) {

		$allowed_html = array(
			'span' => array(
				'class' => array(),
			),
		);

		if ( $echo ) {
			echo wp_kses( wc_price( $price ), $allowed_html );
		}

		return wc_price( $price );
	}
}

