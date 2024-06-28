<?php
/**
 * Welcome (Docs & Support) Page
 *
 * @package     saved-addresses-for-woocommerce/includes/admin/
 * @since       2.4.0
 * @version     1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SAW_Admin_Welcome' ) ) {

	/**
	 * SAW_Admin_Welcome class
	 */
	class SAW_Admin_Welcome {

		/**
		 * Hook in tabs.
		 */
		public function __construct() {

			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_init', array( $this, 'saw_welcome' ) );
		}

		/**
		 * Add admin menus/screens.
		 */
		public function show_welcome_page() {
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			if ( empty( $get_page ) ) {
				return;
			}

			?>
			<script type="text/javascript">
				jQuery(function() {
					jQuery('#menu-pages').find('li.wp-first-item').addClass('current');
				});
			</script>
			<?php

			switch ( $get_page ) {
				case 'saw-about':
					$this->about_screen();
					break;
			}
		}

		/**
		 * Add styles just for this page, and remove dashboard page links.
		 */
		public function admin_head() {
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore

			if ( ! empty( $get_page ) && ( 'saw-about' === $get_page ) ) {
				?>
				<style type="text/css">
					.update-nag,
					.error,
					.updated,
					#wpfooter {
						display: none !important;
					}
					.about-wrap h3 {
						margin-right: 0em;
						margin-bottom: 0.1em;
						font-size: 1.25em;
						line-height: 1.3em;
					}
					.about-wrap .button-primary {
						margin-top: 18px;
					}
					.about-wrap p {
						margin-top: 0.6em;
						margin-bottom: 0.8em;
						line-height: 1.6em;
					}
					.about-wrap .feature-section {
						padding-bottom: 5px;
					}
					.saw-features {
						max-width: none !important;
						margin-left: unset !important;
					}
					.saw-features.note {
						font-weight: 500;
					}
					.about-wrap .has-2-columns,
					.about-wrap .has-3-columns {
						max-width: unset !important;
					}
				</style>
				<?php
			}
		}

		/**
		 * Intro text/links shown on all about pages.
		 */
		private function intro() {
			?>
			<h1><?php echo esc_html__( 'Thank you for installing Saved Addresses For WooCommerce!', 'saved-addresses-for-woocommerce' ); ?></h1>
			<h3><?php echo esc_html__( 'Glad to have you onboard. We hope Saved Addresses For WooCommerce adds to your desired success 🏆', 'saved-addresses-for-woocommerce' ); ?></h3>
			<?php
		}

		/**
		 * Output the about screen.
		 */
		public function about_screen() {
			?>
			<div class="wrap about-wrap" style="max-width: none !important;">

				<?php $this->intro(); ?>

				<div class="changelog">
					<div class="has-2-columns feature-section col two-col">
						<div class="column col">
						</div>
						<div class="column col last-feature">
							<p align="right">
								<a href="<?php echo esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' ); ?>" target="_blank"><?php echo esc_html__( 'Contact us', 'saved-addresses-for-woocommerce' ); ?></a> | 
								<a href="<?php echo esc_url( 'https://docs.woocommerce.com/document/saved-addresses-for-woocommerce/' ); ?>" target="_blank"><?php echo esc_html__( 'Docs', 'saved-addresses-for-woocommerce' ); ?></a>
							</p>
						</div>
					</div>

					<div class="feature-section">
						<p class="saw-features">
							<?php echo esc_html__( 'Saved Addresses plugin allows your users to add multiple billing and shipping addresses. Users can add, edit, delete an address from checkout or My Account > Addresses tab. Users can also set a default address from their account.', 'saved-addresses-for-woocommerce' ); ?>
						</p>
						<p class="saw-features note">
							<?php echo esc_html__( 'This plugin does not have any settings in the admin. You can start using the plugin after activating.', 'saved-addresses-for-woocommerce' ); ?>
						</p>
					</div>
					<div class="has-3-columns feature-section col three-col">
						<div class="column col">
							<h4><?php echo esc_html__( 'Checkout', 'saved-addresses-for-woocommerce' ); ?></h4>
							<p>
								<a target="_blank" href="https://docs.woocommerce.com/document/saved-addresses-for-woocommerce/#section-5">
									<?php echo esc_html__( 'Learn how it works on checkout', 'saved-addresses-for-woocommerce' ); ?>
								</a>
							</p>
						</div>
						<div class="column col">
							<h4><?php echo esc_html__( 'My Account', 'saved-addresses-for-woocommerce' ); ?></h4>
							<p>
								<a target="_blank" href="https://docs.woocommerce.com/document/saved-addresses-for-woocommerce/#section-6">
									<?php echo esc_html__( 'Learn how it works on My Account > Addresses', 'saved-addresses-for-woocommerce' ); ?>
								</a>
							</p>
						</div>
						<div class="column col last-feature">
							<h4><?php echo esc_html__( 'FAQ', 'saved-addresses-for-woocommerce' ); ?></h4>
							<p>
								<a target="_blank" href="https://docs.woocommerce.com/document/saved-addresses-for-woocommerce/#section-7">
									<?php echo esc_html__( 'Click here', 'saved-addresses-for-woocommerce' ); ?>
								</a>
							</p>
						</div>
					</div>
				</div>
				<div class="feature-section">
					<h3 style="text-align: center;"><?php echo esc_html__( 'Explore all plugins from StoreApps', 'saved-addresses-for-woocommerce' ); ?></h3>
					<p style="text-align: center;">
						<a class="button button-primary" href="https://woocommerce.com/vendor/storeapps/" target="_blank">
							<?php echo esc_html__( 'Yes, show me', 'saved-addresses-for-woocommerce' ); ?>
						</a>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * Sends user to the welcome page on activation.
		 */
		public function saw_welcome() {

			if ( ! get_transient( '_saw_activation_redirect' ) ) {
				return;
			}

			// Delete the redirect transient.
			delete_transient( '_saw_activation_redirect' );

			wp_safe_redirect( admin_url( 'edit.php?post_type=page&page=saw-about' ) );
			exit;

		}

	}

}

$GLOBALS['sa_saw_admin_welcome'] = new SAW_Admin_Welcome();
