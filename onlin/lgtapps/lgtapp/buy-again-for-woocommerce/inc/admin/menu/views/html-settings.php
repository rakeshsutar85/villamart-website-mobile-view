<?php
/* Admin HTML Settings */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class = "wrap <?php echo esc_attr( self::$plugin_slug ); ?>_wrapper_cover woocommerce">
	<form method = "post" enctype = "multipart/form-data">
		<div class = "<?php echo esc_attr( self::$plugin_slug ); ?>_wrapper">
			<nav class = "nav-tab-wrapper woo-nav-tab-wrapper <?php echo esc_attr( self::$plugin_slug ); ?>_tab_ul">
				<?php foreach ( $tabs as $name => $label ) { ?>
					<a href="<?php echo esc_url( bya_get_settings_page_url( array( 'tab' => $name ) ) ); ?>" class="nav-tab <?php echo esc_html( self::$plugin_slug ); ?>_tab_a <?php echo esc_attr( $name ) . '_a ' . ( $current_tab == $name ? 'nav-tab-active' : '' ); ?>">
						<span><?php echo esc_html( $label ); ?></span>
					</a>
				<?php } ?>
			</nav>
			<div class="<?php echo esc_attr( self::$plugin_slug ); ?>_tab_content bya_<?php echo esc_attr( $current_tab ); ?>_tab_content_wrapper">
				<?php
				/**
				 * Action hook to adjust Settings.
				 *
				 * @since 1.0
				 */
				do_action( sanitize_key( self::$plugin_slug . '_sections_' . $current_tab ) );
				?>
				<div class="<?php echo esc_attr( self::$plugin_slug ); ?>_tab_inner_content bya_<?php echo esc_attr( $current_tab ); ?>_tab_inner_content">
					<?php
					/**
					 * Action hook to adjust Before Settings Tab Section.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_before_tab_sections' ) );

					/* Display Error or Warning Messages */
					self::show_messages();

					/**
					 * Action hook to adjust Settings Tab.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_settings_' . $current_tab ) );

					/**
					 * Action hook to adjust Settings Button.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_settings_buttons_' . $current_tab ) );

					/**
					 * Action hook to adjust after Settings Button.
					 *
					 * @since 1.0
					 */
					do_action( sanitize_key( self::$plugin_slug . '_after_setting_buttons_' . $current_tab ) );
					?>
				</div>
			</div>
		</div>
	</form>
	<?php
	/**
	 * Action hook to adjust Settings Section End.
	 *
	 * @since 1.0
	 */
	do_action( sanitize_key( self::$plugin_slug . '_' . $current_tab . '_' . $current_section . '_setting_end' ) );

	/**
	 * Action hook to adjust Settings End.
	 *
	 * @since 1.0
	 */
	do_action( sanitize_key( self::$plugin_slug . '_settings_end' ) );
	?>
</div>
<?php
