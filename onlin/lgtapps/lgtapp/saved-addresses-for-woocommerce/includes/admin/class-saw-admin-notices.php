<?php
/**
 * Class to handle display of Saved Addresses admin notices
 *
 * @package     saved-addresses-for-woocommerce/includes/admin/
 * @since       2.0.1
 * @version     1.1.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'SAW_Admin_Notices' ) ) {

	/**
	 * Class to handle display of Saved Addresses review notice.
	 */
	class SAW_Admin_Notices {

		/**
		 * Constructor
		 */
		public function __construct() {

			add_action( 'admin_notices', array( $this, 'admin_notice_saw_needs_wc_37_above' ) );

			add_action( 'admin_menu', array( $this, 'saw_add_admin_submenu' ) );
			add_action( 'admin_head', array( $this, 'saw_remove_admin_submenu' ) );

			add_filter( 'plugin_action_links_' . plugin_basename( SAW_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );

			add_action( 'admin_notices', array( $this, 'saw_show_admin_notices' ) );
			add_action( 'admin_init', array( $this, 'saw_dismiss_admin_notice' ) );

		}

		/**
		 * Function to handle WC compatibility related function call from appropriate class.
		 *
		 * @param string $function_name Function to call.
		 * @param array  $arguments Array of arguments passed while calling $function_name.
		 * @return mixed Result of function call.
		 */
		public function __call( $function_name, $arguments = array() ) {

			if ( ! is_callable( 'SA_WC_Compatibility_4_0', $function_name ) ) {
				return;
			}

			if ( ! empty( $arguments ) ) {
				return call_user_func_array( 'SA_WC_Compatibility_4_0::' . $function_name, $arguments );
			} else {
				return call_user_func( 'SA_WC_Compatibility_4_0::' . $function_name );
			}
		}

		/**
		 * Function to show admin notice that Saved Addresses For WooCommerce works with WC 3.7+
		 */
		public function admin_notice_saw_needs_wc_37_above() {
			if ( ! $this->is_wc_gte_37() ) {
				?>
				<div class="updated error">
					<p>
					<?php
						printf(
							'<strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a>',
							esc_html__( 'Important - ', 'saved-addresses-for-woocommerce' ),
							esc_html__( 'Saved Addresses For WooCommerce plugin is active but it will only work with WooCommerce 3.7+.', 'saved-addresses-for-woocommerce' ),
							esc_url( admin_url( 'plugins.php?plugin_status=upgrade' ), 'saved-addresses-for-woocommerce' ),
							esc_html__( 'Please update WooCommerce to the latest version', 'saved-addresses-for-woocommerce' )
						);
					?>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Add admin submenus for welcome/landing pages.
		 */
		public function saw_add_admin_submenu() {
			$get_page = ( ! empty( $_GET['page'] ) ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore
			if ( 'saw-about' === $get_page ) {
				add_submenu_page( 'edit.php?post_type=page', __( 'Getting started', 'saved-addresses-for-woocommerce' ), __( 'Getting started', 'saved-addresses-for-woocommerce' ), 'manage_woocommerce', 'saw-about', array( $GLOBALS['sa_saw_admin_welcome'], 'show_welcome_page' ) );
			}
		}


		/**
		 * Remove unnecessary submenus.
		 */
		public function saw_remove_admin_submenu() {
			remove_submenu_page( 'edit.php?post_type=page', 'saw-about' );
		}

		/**
		 * Function to add more action on plugins page
		 *
		 * @param array $links Existing links.
		 * @return array $links
		 */
		public function plugin_action_links( $links ) {
			$getting_started_link = add_query_arg( array( 'page' => 'saw-about' ), admin_url( 'edit.php?post_type=page' ) );

			$action_links = array(
				'getting-started' => '<a href="' . esc_url( $getting_started_link ) . '">' . __( 'Getting started', 'saved-addresses-for-woocommerce' ) . '</a>',
				'docs'            => '<a target="_blank" href="' . esc_url( 'https://docs.woocommerce.com/document/saved-addresses-for-woocommerce/' ) . '">' . __( 'Docs', 'saved-addresses-for-woocommerce' ) . '</a>',
				'support'         => '<a target="_blank" href="' . esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' ) . '">' . __( 'Support', 'saved-addresses-for-woocommerce' ) . '</a>',
				'review'          => '<a target="_blank" href="' . esc_url( 'https://woocommerce.com/products/saved-addresses-for-woocommerce/#comments' ) . '">' . __( 'Review', 'saved-addresses-for-woocommerce' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/**
		 * Function to show admin notice in the Saved Addresses.
		 */
		public function saw_show_admin_notices() {
			$saw_notice_status = get_option( 'sa_checkout_adresss_design_updated_saved_addresses', 'no' );
			if ( 'yes' === $saw_notice_status ) {
				return;
			}
			?>
			<style type="text/css" class="saw-admin-notice">
				.saw-notice {
					padding: 1.5em 1em !important;
				}
				.saw-main-headline {
					font-size: 2em;
					font-weight: bold;
					padding-bottom: 0.4em;
					color: #5850EC;
				}
				.saw-feature {
					font-size: 1.5em;
					line-height: 1em;
					padding: 1em 0;
				}
				.saw-sub-content {
					font-size: 1.2em;
					line-height: 1.1em;
					padding-bottom: 1em;
				}
				.saw-review {
					font-size: 1.2em;
					line-height: 1.1em;
				}
				a.saw-dismiss-cta {
					text-decoration: none;
					color: #aeb3b5;
					float: right;
					font-size: small;
					margin-top: -1em;
				}
			</style>
			<div class="updated success saw-notice">
				<div class="saw-main-headline"><?php echo esc_html__( 'Welcome to Saved Addresses for WooCommerce 2.5.1!', 'saved-addresses-for-woocommerce' ); ?></div>
				<div class="saw-feature"><?php echo esc_html__( 'Major update - Display of addresses on the checkout page.', 'saved-addresses-for-woocommerce' ); ?></div>
				<div class="saw-sub-content"><?php echo esc_html__( 'Earlier, all saved addresses used to show up. Now by default, only two addresses will be displayed. Customers can choose from other saved addresses using the select box.', 'saved-addresses-for-woocommerce' ); ?></div>
				<div class="saw-review">
					<?php
						/* translators: 1. start of a tag. 2. end of a tag */
						echo sprintf( esc_html__( 'Kindly review your checkout page to know more. Reach out to us from %1$1shere%2$2s for any queries.', 'saved-addresses-for-woocommerce' ), '<a target="_blank" href="' . esc_url( 'https://woocommerce.com/my-account/create-a-ticket/' ) . '">', '</a>' );
					?>
				</div>
				<a class="saw-dismiss-cta" href="?saw_dismiss_admin_notice=1&option_name=sa_checkout_adresss_design_updated&set=yes"><?php echo esc_html__( 'Hide this', 'saved-addresses-for-woocommerce' ); ?></a>
			</div>
			<?php
		}

		/**
		 * Function to dismiss any admin notice in Saved Addresses.
		 */
		public function saw_dismiss_admin_notice() {

			$saw_dismiss_admin_notice = ( ! empty( $_GET['saw_dismiss_admin_notice'] ) ) ? wc_clean( wp_unslash( $_GET['saw_dismiss_admin_notice'] ) ) : ''; // phpcs:ignore
			$saw_option_name          = ( ! empty( $_GET['option_name'] ) ) ? wc_clean( wp_unslash( $_GET['option_name'] ) ) : ''; // phpcs:ignore
			$saw_option_value         = ( ! empty( $_GET['set'] ) ) ? wc_clean( wp_unslash( $_GET['set'] ) ) : 'yes'; // phpcs:ignore

			if ( ! empty( $saw_dismiss_admin_notice ) && '1' === $saw_dismiss_admin_notice && ! empty( $saw_option_name ) ) {
				// Whether to set option value as yes or no. Pass 'set' in dismiss call to decide. Defaults to yes.
				update_option( $saw_option_name . '_saved_addresses', $saw_option_value, 'no' );

				$referer = wp_get_referer();
				wp_safe_redirect( $referer );
				exit();
			}

		}

	}
}

new SAW_Admin_Notices();
