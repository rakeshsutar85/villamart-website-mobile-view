<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://woocommerce.com/
 * @since      1.0.0
 *
 * @package    Coupon_Referral_Program
 * @subpackage Coupon_Referral_Program/emails
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Referral progam emails.
 *
 * @package    Coupon_Referral_Program
 * @subpackage Coupon_Referral_Program/emails
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Coupon_Referral_Email' ) ) {
	/**
	 * This is referral email class.
	 */
	class Coupon_Referral_Program_Emails extends WC_Email {
		/**
		 *  Coupon Code.
		 *
		 * @var string $coupon_code
		 */
		public $coupon_code;
		/**
		 *  Coupon Expiry.
		 *
		 * @var string $coupon_expiry
		 */
		public $coupon_expiry;
		/**
		 *  Coupon Amount.
		 *
		 * @var string $coupon_amount
		 */
		public $coupon_amount = 0;
		/** Constructor */
		public function __construct() {
			$this->id             = 'crp_signup_email';
			$this->title          = __( 'Sign up discount coupon', 'coupon-referral-program' );
			$this->customer_email = true;
			$this->description    = __( 'This coupon is sent to those customers who create their account on your site via a referred user.', 'coupon-referral-program' );
			$this->template_html  = 'crp-email-template.php';
			$this->template_plain = 'plain/crp-email-template.php';
			$this->template_base  = COUPON_REFERRAL_PROGRAM_DIR_PATH . 'emails/templates/';
			$this->placeholders   = array(
				'{site_title}'    => $this->get_blogname(),
				'{coupon_code}'   => '',
				'{coupon_amount}' => '',
				'{coupon_expiry}' => '',
			);

			// Call parent constructor .
			parent::__construct();
		}

		/**
		 * Get email subject.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_subject() {
			return __( 'Signup Discount Coupon {site_title}', 'coupon-referral-program' );
		}

		/**
		 * Get email heading.
		 *
		 * @since  3.1.0
		 * @return string
		 */
		public function get_default_heading() {
			return __( 'Thank You for your Registration', 'coupon-referral-program' );
		}

		/**
		 * Trigger the sending of this email.
		 *
		 * @param int   $user_id .
		 * @param mixed $coupon_code .
		 * @param mixed $coupon_amount .
		 * @param mixed $coupon_expiry .
		 */
		public function trigger( $user_id, $coupon_code, $coupon_amount, $coupon_expiry ) {
			if ( $user_id ) {
				$this->setup_locale();
				$user      = new WP_User( $user_id );
				$user_info = get_userdata( $user_id );
				$coupon    = new WC_Coupon( $coupon_code );
				if ( is_a( $user, 'WP_User' ) ) {
					$this->object                          = $user;
					$this->coupon_code                     = $coupon_code;
					$this->coupon_amount                   = $coupon_amount;
					$this->coupon_expiry                   = $coupon_expiry;
					$this->recipient                       = $user_info->user_email;
					$this->placeholders['{coupon_code}']   = $coupon_code;
					$this->placeholders['{coupon_amount}'] = $coupon_amount;
					$this->placeholders['{coupon_expiry}'] = $coupon_expiry;

					if ( $this->is_enabled() && $this->get_recipient() ) {
						$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
					}
				}
				$this->restore_locale();
			}

		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'user'          => $this->object,
					'coupon_code'   => $this->coupon_code,
					'coupon_amount' => $this->coupon_amount,
					'coupon_expiry' => $this->coupon_expiry,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => false,
					'email'         => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'user'          => $this->object,
					'coupon_code'   => $this->coupon_code,
					'coupon_amount' => $this->coupon_amount,
					'coupon_expiry' => $this->coupon_expiry,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => false,
					'plain_text'    => true,
					'email'         => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Initialise settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'enabled'    => array(
					'title'   => __( 'Enable/Disable', 'coupon-referral-program' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable this email notification', 'coupon-referral-program' ),
					'default' => 'yes',
				),
				'subject'    => array(
					'title'       => __( 'Subject', 'coupon-referral-program' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'coupon-referral-program' ), '<code>{site_title}, {coupon_code},{coupon_amount},{coupon_code}</code>' ),
					'placeholder' => $this->get_default_subject(),
					'default'     => '',
				),
				'heading'    => array(
					'title'       => __( 'Email heading', 'coupon-referral-program' ),
					'type'        => 'text',
					'desc_tip'    => true,
					/* translators: %s: list of placeholders */
					'description' => sprintf( __( 'Available placeholders: %s', 'coupon-referral-program' ), '<code>{site_title}, {coupon_code},{coupon_amount},{coupon_code}</code>' ),
					'placeholder' => $this->get_default_heading(),
					'default'     => '',
				),
				'email_type' => array(
					'title'       => __( 'Email type', 'coupon-referral-program' ),
					'type'        => 'select',
					'description' => __( 'Choose which format of email to send.', 'coupon-referral-program' ),
					'default'     => 'html',
					'class'       => 'email_type wc-enhanced-select',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true,
				),
			);
		}

	}

}

return new Coupon_Referral_Program_Emails();
