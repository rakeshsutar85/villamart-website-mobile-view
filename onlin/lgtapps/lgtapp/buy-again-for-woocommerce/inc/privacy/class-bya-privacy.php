<?php
/*
 * BYA Compliance.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'BYA_Privacy' ) ) :

	/**
	 * BYA_Privacy class.
	 */
	class BYA_Privacy {

		/**
		 * BYA_Privacy constructor.
		 */
		public function __construct() {
			$this->init_hooks();
		}

		/**
		 * Register plugin.
		 */
		public function init_hooks() {
			// This hook registers Booking System privacy content.
			add_action( 'admin_init', array( __CLASS__, 'register_privacy_content' ), 20 );
		}

		/**
		 * Register Privacy Content.
		 */
		public static function register_privacy_content() {
			if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
				return;
			}

			$content = self::get_privacy_message();
			if ( $content ) {
				wp_add_privacy_policy_content( esc_html__( 'Buy Again for WooCommerce', 'buy-again-for-wooCommerce' ), $content );
			}
		}

		/**
		 * Prepare Privacy Content.
		 */
		public static function get_privacy_message() {

			return self::get_privacy_message_html();
		}

		/**
		 * Get Privacy Content.
		 */
		public static function get_privacy_message_html() {
			ob_start();
			?>
			<p><?php esc_html_e( 'This includes the basics of what personal data your store may collect, store & share. Depending on what settings are enabled furthermore which additional plugins used, the specific information shared by your store will vary.', 'buy-again-for-wooCommerce' ); ?></p>
			<h2><?php esc_html_e( 'What the Plugin Does?', 'buy-again-for-wooCommerce' ); ?></h2>
			<p><?php esc_html_e( 'This plugin allows your customers to quickly purchase the products which they had purchased earlier in your site.', 'buy-again-for-wooCommerce' ); ?> </p>
			<h2><?php esc_html_e( 'What We Collect and Store?', 'buy-again-for-wooCommerce' ); ?></h2>
			<h4><?php esc_html_e( 'First Name, Last Name, Username and Email ID', 'buy-again-for-wooCommerce' ); ?></h4>
			<ul>
				<li>
					<p><?php esc_html_e( 'We record the First Name, Last Name, Username and email ids of the logged-in users to identify the users who have purchased products using the Buy Now and Add to Cart buttons provided by the plugin.', 'buy-again-for-wooCommerce' ); ?></p>
				</li>
			</ul>
			<?php
			$contents = ob_get_contents();
			ob_end_clean();

			return $contents;
		}

	}

	new BYA_Privacy();

endif;
