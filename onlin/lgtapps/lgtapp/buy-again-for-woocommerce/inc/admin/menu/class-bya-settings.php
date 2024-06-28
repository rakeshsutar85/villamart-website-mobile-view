<?php
/**
 * Admin Settings Class
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Settings' ) ) {

	/**
	 * BYA_Settings Class
	 */
	class BYA_Settings {

		/**
		 * Setting pages.
		 */
		private static $settings = array();

		/**
		 * Error messages.
		 */
		private static $errors = array();

		/**
		 * Plugin slug.
		 */
		private static $plugin_slug = 'bya';

		/**
		 * Update messages.
		 */
		private static $messages = array();

		/**
		 * Include the settings page classes.
		 */
		public static function get_settings_pages() {
			if ( ! empty( self::$settings ) ) {
				return self::$settings;
			}

			include_once BYA_PLUGIN_PATH . '/inc/abstracts/class-bya-settings-page.php';

			$settings = array();
			$tabs     = self::settings_page_tabs();

			foreach ( $tabs as $tab_name ) {
				$settings[ sanitize_key( $tab_name ) ] = include 'tabs/' . sanitize_key( $tab_name ) . '.php';
			}

			/**
			 * Filter to Buy Again Settings Page.
			 *
			 * @since 1.0
			 * */
			self::$settings = apply_filters( sanitize_key( self::$plugin_slug . '_get_settings_pages' ), $settings );

			return self::$settings;
		}

		/**
		 * Add a message.
		 */
		public static function add_message( $text ) {
			self::$messages[] = $text;
		}

		/**
		 * Add an error.
		 */
		public static function add_error( $text ) {
			self::$errors[] = $text;
		}

		/**
		 * Output messages + errors.
		 */
		public static function show_messages() {
			if ( count( self::$errors ) > 0 ) {
				foreach ( self::$errors as $error ) {
					self::error_message( $error );
				}
			} elseif ( count( self::$messages ) > 0 ) {
				foreach ( self::$messages as $message ) {
					self::success_message( $message );
				}
			}
		}

		/**
		 * Show an success message.
		 */
		public static function success_message( $text, $echo = true ) {
			ob_start();
			$contents = '<div id="message " class="updated inline ' . esc_html( self::$plugin_slug ) . '_save_msg"><p><strong>' . esc_html( $text ) . '</strong></p></div>';
			ob_end_clean();

			if ( $echo ) {
				$allowed_html = array(
					'div'    => array(
						'class' => array(),
					),
					'p'      => array(),
					'i'      => array(
						'class'       => array(),
						'aria-hidden' => array(),
					),
					'strong' => array(),
				);

				echo wp_kses( $contents, $allowed_html );
			} else {
				return $contents;
			}
		}

		/**
		 * Show an error message.
		 */
		public static function error_message( $text, $echo = true ) {
			ob_start();
			$contents = '<div id="message" class="error inline"><p><strong>' . esc_html( $text ) . '</strong></p></div>';
			ob_end_clean();

			if ( $echo ) {
				$allowed_html = array(
					'div'    => array(
						'class' => array(),
					),
					'p'      => array(),
					'i'      => array(
						'class'       => array(),
						'aria-hidden' => array(),
					),
					'strong' => array(),
				);

				echo wp_kses( $contents, $allowed_html );
			} else {
				return $contents;
			}
		}

		/**
		 * Settings page tabs
		 */
		public static function settings_page_tabs() {

			return array(
				'general',
				'advanced',
				'localization',
				'shortcodes',
			);
		}

		/**
		 * Handles the display of the settings page in admin.
		 */
		public static function output() {
			global $current_section, $current_tab;

			/**
			 * Action hook to adjust Settings Start.
			 *
			 * @since 1.0
			 */
			do_action( sanitize_key( self::$plugin_slug . '_settings_start' ) );

			$tabs = bya_get_allowed_setting_tabs();

			/* Include admin html settings */
			include_once 'views/html-settings.php';
		}

		/**
		 * Handles the display of the settings page buttons in page.
		 */
		public static function output_buttons( $reset = true ) {
			global $current_tab;

			if ( 'shortcodes' != $current_tab ) {
				/* Include admin html settings buttons */
				include_once 'views/html-settings-buttons.php';
			}
		}

		/**
		 * Output admin fields.
		 */
		public static function output_fields( $value ) {

			if ( ! isset( $value['type'] ) || 'bya_custom_fields' != $value['type'] ) {
				return;
			}

			$value['id']                = isset( $value['id'] ) ? $value['id'] : '';
			$value['css']               = isset( $value['css'] ) ? $value['css'] : '';
			$value['desc']              = isset( $value['desc'] ) ? $value['desc'] : '';
			$value['title']             = isset( $value['title'] ) ? $value['title'] : '';
			$value['class']             = isset( $value['class'] ) ? $value['class'] : '';
			$value['default']           = isset( $value['default'] ) ? $value['default'] : '';
			$value['name']              = isset( $value['name'] ) ? $value['name'] : $value['id'];
			$value['placeholder']       = isset( $value['placeholder'] ) ? $value['placeholder'] : '';
			$value['without_label']     = isset( $value['without_label'] ) ? $value['without_label'] : false;
			$value['custom_attributes'] = isset( $value['custom_attributes'] ) ? $value['custom_attributes'] : '';

			// Custom attribute handling.
			$custom_attributes = bya_format_custom_attributes( $value );
			$allowed_html      = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'strong' => array(),
				'style'  => array(),
				'min'    => array(),
				'max'    => array(),
				'span'   => array(
					'class'    => array(),
					'data-tip' => array(),
				),
			);
			// Description handling.
			$field_description = WC_Admin_Settings::get_field_description( $value );
			$description       = $field_description['description'];
			$tooltip_html      = $field_description['tooltip_html'];

			// Switch based on type
			switch ( $value['bya_field'] ) {

				case 'subtitle':
					?>
					<tr valign="top" >
						<th scope="row" colspan="2">
							<?php echo esc_html( $value['title'] ); ?><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							<p><?php echo wp_kses( $description, $allowed_html ); ?></p>
						</th>
					</tr>
					<?php
					break;

				case 'button':
					?>
					<tr valign="top">
						<?php if ( ! $value['without_label'] ) : ?>
							<th scope="row">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							</th>
						<?php endif; ?>
						<td>
							<button
								id="<?php echo esc_attr( $value['id'] ); ?>"
								type="<?php echo esc_attr( $value['bya_field'] ); ?>"
								class="<?php echo esc_attr( $value['class'] ); ?>"
								<?php echo wp_kses( implode( ' ', $custom_attributes ), $allowed_html ); ?>
								><?php echo esc_html( $value['default'] ); ?> </button>
								<?php echo wp_kses( $description, $allowed_html ); ?>
						</td>
					</tr>
					<?php
					break;

				case 'ajaxmultiselect':
					$option_value = get_option( $value['id'], $value['default'] );
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
						</th>
						<td>
							<?php
							$value['options'] = $option_value;
							bya_select2_html( $value );
							echo wp_kses( $description, $allowed_html );
							?>
						</td>
					</tr>
					<?php
					break;

				case 'datepicker':
					$value['value'] = get_option( $value['id'], $value['default'] );
					if ( ! isset( $value['datepickergroup'] ) || 'start' == $value['datepickergroup'] ) {
						?>
						<tr valign="top">
							<th scope="row">
								<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
							</th>
							<td>
								<fieldset>
									<?php
					}
								echo isset( $value['label'] ) ? esc_html( $value['label'] ) : '';
								bya_get_datepicker_html( $value );
								echo wp_kses( $description, $allowed_html );

					if ( ! isset( $value['datepickergroup'] ) || 'end' == $value['datepickergroup'] ) {
						?>
								</fieldset>
							</td>
						</tr>
					<?php } ?>
					<?php
					break;
				case 'wpeditor':
					$option_value = get_option( $value['id'], $value['default'] );
					?>
					<tr valign="top">
						<th scope="row">
							<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label><?php echo wp_kses( $tooltip_html, $allowed_html ); ?>
						</th>
						<td>
							<?php
							wp_editor(
								$option_value,
								$value['id'],
								array(
									'media_buttons' => false,
									'editor_class'  => esc_attr( $value['class'] ),
								)
							);

							echo wp_kses( $description, $allowed_html );
							?>
						</td>
					</tr>
					<?php
					break;
			}
		}

		/**
		 * Save admin fields.
		 */
		public static function save_fields( $value, $option, $raw_value ) {

			if ( ! isset( $option['type'] ) || 'bya_custom_fields' != $option['type'] ) {
				return $value;
			}

			$value = null;

			// Format the value based on option type.
			switch ( $option['bya_field'] ) {
				case 'ajaxmultiselect':
					$value = array_filter( (array) $raw_value );
					break;
				case 'wpeditor':
				case 'datepicker':
					$value = wc_clean( $raw_value );
					break;
			}

			return $value;
		}

		/**
		 * Reset admin fields.
		 */
		public static function reset_fields( $options ) {
			if ( ! is_array( $options ) ) {
				return false;
			}

			// Loop options and get values to reset.
			foreach ( $options as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['type'] ) || ! isset( $option['default'] ) ) {
					continue;
				}

				update_option( $option['id'], $option['default'] );
			}
			return true;
		}

	}

}
