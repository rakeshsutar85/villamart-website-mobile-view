<?php
/**
 * Email Verification for WooCommerce - Pro Class.
 *
 * @version 2.2.7
 * @since   1.1.0
 * @author  WPFactory
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Email_Verification_Pro' ) ) :

class Alg_WC_Email_Verification_Pro {

	/**
	 * @version 2.0.7
	 * @since   2.0.7
	 *
	 * @var bool
	 */
	protected $auto_verify_from_3rd_party = false;

	/**
	 * Constructor.
	 *
	 * @version 2.2.6
	 * @since   1.1.0
	 * @todo    [next] Block thank you (`maybe_redirect_to_myaccount`?): Cancelled order (also block login)
	 * @todo    (maybe) email verification + order statuses?
	 * @todo    (maybe) emails whitelist
	 */
	function __construct() {
		add_filter( 'alg_wc_ev_settings', array( $this, 'settings' ), 10, 3 );
		add_filter( 'alg_wc_ev_core_loaded', array( $this, 'core_loaded' ) );
		add_filter( 'alg_wc_ev_email_content', array( $this, 'email_content' ) );
		add_filter( 'alg_wc_ev_email_content_final', array( $this, 'maybe_wrap_in_wc_email_template' ), 10, 2 );
		add_filter( 'alg_wc_ev_email_subject', array( $this, 'email_subject' ) );
		add_action( 'alg_wc_ev_user_account_activated', array( $this, 'maybe_send_admin_email' ) );
		add_action( 'alg_wc_ev_after_thankyou_logout', array( $this, 'maybe_redirect_to_myaccount_action' ) );
		add_filter( 'alg_wc_ev_redirect_after_checkout', array( $this, 'maybe_redirect_to_myaccount_filter' ), 10, 2 );
		add_filter( 'alg_wc_ev_verify_email', array( $this, 'validate_blacklisted_emails' ), 10, 2 );
		add_action( 'alg_wc_ev_verify_email_error', array( $this, 'output_blacklisted_email_notice' ), 10, 2 );
		add_filter( 'alg_wc_ev_verify_email', array( $this, 'validate_activation_code_time' ), 10, 2 );
		add_action( 'alg_wc_ev_verify_email_error', array( $this, 'output_activation_code_expired_notice' ), 10, 3 );
		add_filter( 'alg_wc_ev_delete_unverified_users_loop_args', array( $this, 'add_activation_code_time_meta_query' ), 10, 3 );
		add_filter( 'alg_wc_ev_send_mail_message', array( $this, 'maybe_add_wc_email_style' ), 10, 2 );
		add_filter( 'woocommerce_checkout_process', array( $this, 'maybe_block_unverified_checkout_process' ), PHP_INT_MAX );
		$this->handle_compatibility();
		// Unverify email changing
		add_action( 'profile_update', array( $this, 'unverify_email_changing' ), 10, 2 );
		// Blocks content for unverified users
		add_action( 'template_redirect', array( $this, 'block_content_for_unverified_users_by_conditionals' ) );
		add_action( 'template_redirect', array( $this, 'block_products_for_unverified_users' ) );
		// Verify the user on password reset
		add_action( 'woocommerce_customer_reset_password', array( $this, 'verify_account_on_password_reset' ), 100, 1 );
		// Paying customers
		add_action( 'woocommerce_order_status_changed', array( $this, 'verify_account_on_order_paid' ), 10, 3 );
		require_once( 'class-alg-wc-ev-rest-api-pro.php' );
	}

	/**
	 * verify_account_on_order_paid.
	 *
	 * @version 2.2.6
	 * @since   2.2.4
	 *
	 * @param $order_id
	 * @param $from
	 * @param $to
	 */
	function verify_account_on_order_paid( $order_id, $from, $to ) {
		if (
			'yes' === get_option( 'alg_wc_ev_auto_verify_paying_user', 'no' ) &&
			! empty( $statuses = wc_get_is_paid_statuses() ) &&
			in_array( $to, $statuses ) &&
			! empty( $order = wc_get_order( $order_id ) ) &&
			! empty( $order->get_subtotal() ) &&
			! empty( $customer_id = $order->get_customer_id() ) &&
			! alg_wc_ev_is_user_verified_by_user_id( $customer_id )
		) {
			$this->core->activate_user( array(
				'user_id'  => $customer_id,
				'directly' => false
			) );
		}
	}

	/**
	 * verify_account_on_password_reset.
	 *
	 * @version 2.1.4
	 * @since   2.1.4
	 */
	function verify_account_on_password_reset( $user ) {
		if ( 'yes' === get_option( 'alg_wc_ev_verify_account_on_password_reset', 'no' ) ) {
			if ( $user && ! is_wp_error( $user ) ) {
				$user_id = $user->ID;
				if ( ! $this->core->is_user_verified( $user ) ) {
					update_user_meta( $user_id, 'alg_wc_ev_is_activated', '1' );
					wp_safe_redirect(
						add_query_arg(
							array(
								'alg_wc_ev_success_activation_message' => 1,
								'password-reset'                       => 'true',
							),
							wc_get_page_permalink( 'myaccount' )
						)
					);
					exit;
				}
			}
		}
	}

	/**
	 * block_products_for_unverified_users.
	 *
	 * @version 2.1.1
	 * @since   2.1.1
	 */
	function block_products_for_unverified_users() {
		global $post;
		if (
			! is_admin()
			&& ! empty( $blocked_products = get_option( 'alg_wc_ev_blocked_products', array() ) )
			&& is_product()
			&& $post
			&& ( $product = wc_get_product( $post->ID ) )
			&& in_array( $product->get_id(), $blocked_products )
			&&
			(
				! is_user_logged_in()
				|| ! alg_wc_ev_is_user_verified_by_user_id( get_current_user_id() )
			)
		) {
			$redirect_url = add_query_arg( array(
				'alg_wc_ev_blocked_content' => true
			), get_option( 'alg_wc_ev_block_content_redirect', home_url() ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * block_content_for_unverified_users_by_conditionals.
	 *
	 * @version 2.1.1
	 * @since   2.1.1
	 */
	function block_content_for_unverified_users_by_conditionals() {
		if (
			! is_admin()
			&& ! empty( $conditionals = get_option( 'alg_wc_ev_blocked_conditionals', array() ) )
			&& ( $result = array_map( function ( $v ) {
				return function_exists( $v ) && call_user_func( $v );
			}, $conditionals ) )
			&& in_array( true, $result )
			&&
			(
				! is_user_logged_in()
				|| ! alg_wc_ev_is_user_verified_by_user_id( get_current_user_id() )
			)
		) {
			$redirect_url = add_query_arg( array(
				'alg_wc_ev_blocked_content' => true
			), get_option( 'alg_wc_ev_block_content_redirect', home_url() ) );
			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	/**
	 * Unverify email changing.
	 *
	 * @version 2.0.3
	 * @since   2.0.3
	 *
	 * @param $user_id
	 * @param $old_user_data
	 */
	function unverify_email_changing( $user_id, $old_user_data ) {
		if (
			'no' === get_option( 'alg_wc_ev_unverify_email_changing', 'no' )
			|| empty( $old_user_email = $old_user_data->user_email )
			|| ! ( $user = get_user_by( 'ID', $user_id ) )
			|| $user->user_email == $old_user_email
		) {
			return;
		}
		// Logout
		wp_destroy_current_session();
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );
		do_action( 'wp_logout', $user_id );
		// Unverify
		update_user_meta( $user_id, 'alg_wc_ev_is_activated', '0' );
		delete_user_meta( $user_id, 'alg_wc_ev_customer_new_account_email_sent' );
		delete_user_meta( $user_id, 'alg_wc_ev_admin_email_sent' );
		// Resend activation email
		alg_wc_ev()->core->emails->reset_and_mail_activation_link( $user_id );
	}

	/**
	 * handle_compatibility.
	 *
	 * @version 2.2.7
	 * @since   2.0.2
	 */
	function handle_compatibility() {
		add_filter( 'alg_wc_ev_is_user_verified', array( $this, 'accept_verification_by_3rd_party' ), 10, 2 );
		// Nextend Social Login
		add_action( 'nsl_login', array( $this, 'verify_nsl_user' ), 10 );
		add_action( 'nsl_register_new_user', array( $this, 'verify_nsl_user' ), 10 );
		// Social Login (Skyverge)
		add_action( 'wc_social_login_user_authenticated', array( $this, 'verify_social_login_skyverge_user' ) );
		add_action( 'wc_social_login_before_user_login', array( $this, 'verify_social_login_skyverge_user' ) );
		add_action( 'wc_social_login_before_create_user', array( $this, 'wc_social_login_before_create_user' ) );
		// WooMail - https://codecanyon.net/item/email-customizer-for-woocommerce-with-drag-drop-builder-woo-email-editor/22400984
		add_filter( 'do_shortcode_tag', array( $this, 'create_woomail_shortcode_parameter' ), 10, 3 );
		// Compatibility with Email Customizer.
		add_action( 'alg_wc_ev_ec_email_content', array( $this, 'get_email_from_email_customizer_plugin_and_display_activation_email_content' ), 10, 2 );
	}

	/**
	 * get_email_from_email_customizer_plugin_and_display_activation_email_content.
	 *
	 * @see https://help.themehigh.com/hc/en-us/articles/4405390768025-Add-New-Email-Template#h_01FDS804XTGFN0S9F2W736GKD6.
	 *
	 * @version 2.2.7
	 * @since   2.2.7
	 *
	 * @param $param1
	 * @param $param2
	 */
	function get_email_from_email_customizer_plugin_and_display_activation_email_content( $param1, $param2 ) {
		if (
			'yes' === get_option( 'alg_wc_ev_email_customizer_hook_enabled', 'no' ) &&
			! empty( $user_email = $param2->user_email )
		) {
			$activation_email_content = alg_wc_ev()->core->emails->alg_wc_ev_email_content_placeholder( array(
				'user_email' => $user_email
			) );
			echo $activation_email_content;
		}
	}

	/**
	 * create_woomail_shortcode_parameter.
	 *
	 * @see https://codecanyon.net/item/email-customizer-for-woocommerce-with-drag-drop-builder-woo-email-editor/22400984
	 * @see https://emailcustomizer.com/2019/10/22/how-to-create-custom-shortcode-in-email-customizer-for-woocommerce/
	 *
	 * @version 2.0.8
	 * @since   2.0.8
	 *
	 * @param $output
	 * @param $tag
	 * @param $attr
	 *
	 * @return string
	 */
	function create_woomail_shortcode_parameter( $output, $tag, $attr ) {
		if (
			'ec_woo_custom_code' == $tag
			&& 'yes' === get_option( 'alg_wc_ev_woomail', 'no' )
			&& $attr['type'] == 'alg_wc_ev_activation_email'
			&& ! empty( $user_email = do_shortcode( '[ec_woo_user_email]' ) )
		) {
			$user = get_user_by( 'email', $user_email );
			ob_start();
			do_action( "alg_wc_ev_activation_email_content_placeholder", $user );
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		return $output;
	}

	/**
	 * wc_social_login_before_create_user.
	 *
	 * @version 2.0.7
	 * @since   2.0.7
	 *
	 * @param $profile
	 */
	function wc_social_login_before_create_user( $profile ) {
		if ( 'yes' === get_option( 'alg_wc_ev_accept_social_login_skyverge', 'no' ) ) {
			$this->auto_verify_from_3rd_party = true;
		}
	}

	/**
	 * Activates user from Social Login (Skyverge)
	 *
	 * @version 2.0.7
	 * @since   1.9.7
	 *
	 * @see https://docs.woocommerce.com/document/woocommerce-social-login-developer-docs/
	 *
	 * @param $user_id
	 */
	function verify_social_login_skyverge_user( $user_id ) {
		if ( 'yes' === get_option( 'alg_wc_ev_accept_social_login_skyverge', 'no' ) ) {
			update_user_meta( $user_id, 'alg_wc_ev_is_activated', '1' );
		}
	}

	/**
	 * verify_nsl_user.
	 *
	 * @see https://nextendweb.com/nextend-social-login-docs/backend-developer/
	 *
	 * @version 1.9.7
	 * @since   1.9.7
	 *
	 * @param $user_id
	 */
	function verify_nsl_user( $user_id ) {
		if (
			! empty( $nextend_verify = get_option( 'alg_wc_ev_nextend_verify', array() ) )
			&& in_array( current_action(), $nextend_verify )
		) {
			update_user_meta( $user_id, 'alg_wc_ev_is_activated', '1' );
		}
	}

	/**
	 * core_loaded.
	 *
	 * @version 2.0.8
	 * @since   1.5.0
	 */
	function core_loaded( $core ) {
		$this->core = $core;
		// Block order emails
		if ( 'yes' === get_option( 'alg_wc_ev_block_customer_order_emails', 'no' ) ) {
			foreach ( get_option( 'alg_wc_ev_block_customer_order_emails_email_ids', array( 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order' ) ) as $email_id ) {
				add_filter( 'woocommerce_email_enabled_' . $email_id, array( $this, 'block_customer_order_emails' ), PHP_INT_MAX, 3 );
			}
			add_action( 'alg_wc_ev_user_account_activated', array( $this, 'send_blocked_emails' ) );
		}
		// Block guests from adding products to the cart
		if ( 'yes' === get_option( 'alg_wc_ev_block_guest_add_to_cart', 'no' ) ) {
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'block_guest_add_to_cart_validation' ), PHP_INT_MAX, 3 );
			add_action( 'woocommerce_init',                   array( $this, 'block_guest_add_to_cart_ajax_error' ), PHP_INT_MAX );
		}
	}

	/**
	 * Unblocks emails by sending the emails that have been blocked to the users who have just verified accounts.
	 *
	 * Sends only the email related to the current order status or/and the 'new_order' email.
	 *
	 * send_blocked_emails.
	 *
	 * @version 2.0.8
	 * @since   2.0.8
	 *
	 * @param $user_id
	 */
	function send_blocked_emails( $user_id ) {
		if ( 'yes' !== get_option( 'alg_wc_ev_block_customer_order_emails_unblock', 'yes' ) ) {
			return;
		}
		$order_ids = wc_get_orders( array(
			'customer_id' => $user_id,
			'return' => 'ids',
		) );
		$email_ids = get_option( 'alg_wc_ev_block_customer_order_emails_email_ids', array( 'customer_on_hold_order', 'customer_processing_order', 'customer_completed_order' ) );
		foreach ( WC()->mailer()->get_emails() as $wc_mail ) {
			if ( in_array( $wc_mail->id, $email_ids ) ) {
				foreach ( $order_ids as $order_id ) {
					$order = wc_get_order( $order_id );
					if (
						'new_order' === $wc_mail->id
						|| false !== strpos( str_replace( '-', '_', $wc_mail->id ), str_replace( '-', '_', $order->get_status() ) )
					) {
						$wc_mail->trigger( $order_id );
					}
				}
			}
		}
	}

	/**
	 * maybe_block_unverified_checkout_process.
	 *
	 * @version 1.8.0
	 * @since   1.8.0
	 * @todo    [next] (maybe) `alg_wc_ev_block_checkout_process_notice`: better default value
	 * @todo    (maybe) `alg_wc_ev_block_checkout_process_notice`: add placeholders (e.g. `%login_url%`)
	 */
	function maybe_block_unverified_checkout_process() {
		if ( 'yes' === get_option( 'alg_wc_ev_block_checkout_process', 'no' ) ) {
			if ( ! $this->core->is_user_verified( wp_get_current_user() ) ) {
				$message = do_shortcode( get_option( 'alg_wc_ev_block_checkout_process_notice',
					__( 'You need to log in and verify your email to place an order.', 'emails-verification-for-woocommerce' ) ) );
				alg_wc_ev_add_notice( $message, 'error' );
			}
		}
	}

	/**
	 * add_activation_code_time_meta_query.
	 *
	 * @version 1.9.8
	 * @since   1.7.0
	 */
	function add_activation_code_time_meta_query( $args, $current_user_id, $is_cron ) {
		if ( 0 != ( $expiration_time = alg_wc_ev_get_expiration_time() ) ) {
			$meta_query = array(
				'key'     => 'alg_wc_ev_activation_code_time',
				'value'   => ( time() - $expiration_time ),
				'compare' => '<',
			);
			if ( isset( $args['meta_query']['relation'] ) ) {
				$_meta_query = $args['meta_query'];
				$args['meta_query'] = array( $_meta_query );
			}
			$args['meta_query']['relation'] = 'AND';
			$args['meta_query'][] = $meta_query;
		}
		return $args;
	}

	/**
	 * output_activation_code_expired_notice.
	 *
	 * @version 2.2.6
	 * @since   1.7.0
	 */
	function output_activation_code_expired_notice( $user_id, $args ) {
		if ( ! $this->validate_activation_code_time( true, $user_id ) && $args['directly'] ) {
			alg_wc_ev_add_notice( $this->get_activation_code_expired_message( $user_id ), 'error' );
		}
	}

	/**
	 * get_activation_code_expired_message.
	 *
	 * @version 1.8.0
	 * @since   1.7.0
	 */
	function get_activation_code_expired_message( $user_id ) {
		$notice = do_shortcode( get_option( 'alg_wc_ev_activation_code_expired_message',
			__( 'Link has expired. You can resend the email with verification link by clicking <a href="%resend_verification_url%">here</a>.', 'emails-verification-for-woocommerce' ) ) );
		return str_replace( '%resend_verification_url%', $this->core->messages->get_resend_verification_url( $user_id ), $notice );
	}

	/**
	 * validate_activation_code_time.
	 *
	 * @version 1.9.8
	 * @since   1.7.0
	 */
	function validate_activation_code_time( $is_valid, $user_id ) {
		if ( $is_valid && $user_id && 0 != ( $expiration_time = alg_wc_ev_get_expiration_time() ) ) {
			$activation_code_time = get_user_meta( $user_id, 'alg_wc_ev_activation_code_time', true );
			if ( ! $activation_code_time ) {
				$activation_code_time = 0;
			}
			if ( ( time() - $activation_code_time ) > $expiration_time ) {
				return false;
			}
		}
		return $is_valid;
	}

	/**
	 * output_blacklisted_email_notice.
	 *
	 * @version 2.2.6
	 * @since   1.6.0
	 */
	function output_blacklisted_email_notice( $user_id, $args ) {
		if ( ! $this->validate_blacklisted_emails( true, $user_id ) && $args['directly'] ) {
			alg_wc_ev_add_notice( $this->get_blacklisted_message(), 'error' );
		}
	}

	/**
	 * get_blacklisted_message.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_blacklisted_message() {
		return do_shortcode( get_option( 'alg_wc_ev_blacklisted_message', __( 'Your email is denied.', 'emails-verification-for-woocommerce' ) ) );
	}

	/**
	 * wildcard_match.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function wildcard_match( $pattern, $subject ) {
		$pattern = strtr( $pattern, array(
			'*' => '.*?', // 0 or more (lazy) - asterisk (*)
			'?' => '.',   // 1 character - question mark (?)
		) );
		return preg_match( "/$pattern/", $subject );
	}

	/**
	 * validate_blacklisted_emails.
	 *
	 * @version 1.8.0
	 * @since   1.6.0
	 * @todo    (maybe) check for this earlier, i.e. not on verification link click
	 */
	function validate_blacklisted_emails( $is_valid, $user_id ) {
		if ( $is_valid && $user_id && '' != ( $blacklist = get_option( 'alg_wc_ev_email_blacklist', '' ) ) ) {
			$user      = new WP_User( $user_id );
			$blacklist = str_replace( PHP_EOL, ',', $blacklist );
			$blacklist = array_map( 'trim', explode( ',', $blacklist ) );
			foreach ( $blacklist as $email ) {
				if ( $email === $user->user_email || $this->wildcard_match( $email, $user->user_email ) ) {
					return false;
				}
			}
		}
		return $is_valid;
	}

	/**
	 * accept_verification_by_3rd_party.
	 *
	 * @version 2.1.1
	 * @since   1.6.0
	 * @see     https://codecanyon.net/item/woocommerce-social-login-wordpress-plugin/8495883 (WooCommerce Social Login - WordPress Plugin)
	 * @todo    [next] https://wordpress.org/plugins/yith-woocommerce-social-login/ (YITH WooCommerce Social Login)
	 */
	function accept_verification_by_3rd_party( $is_user_verified, $user_id ) {
		if ( 'yes' === get_option( 'alg_wc_ev_accept_social_login', 'no' ) && ! empty( $user_id ) ) {
			// WooCommerce Social Login (SkyVerge)
			if ( defined( 'WOO_SLG_USER_META_PREFIX' ) ) {
				$wooslg_by_social_login = get_user_meta( $user_id, WOO_SLG_USER_META_PREFIX . 'by_social_login', true );
				if ( 'true' === $wooslg_by_social_login ) {
					return true;
				}
			}
		} elseif (
			// Super Socializer
			'yes' === get_option( 'alg_wc_ev_super_socializer_login', 'no' )
			&& defined( 'THE_CHAMP_SS_VERSION' )
			&& ! empty( $user_id )
			&& ! empty( get_user_meta( $user_id, 'thechamp_current_id', true ) )
		) {
			return true;
		} elseif (
			// Social Login from My Listing theme
			'yes' === get_option( 'alg_wc_ev_my_listing_social_login', 'no' )
			&& ! empty( $user_id )
			&&
			(
				! empty( get_user_meta( $user_id, 'mylisting_google_account_id', true ) )
				|| ! empty( get_user_meta( $user_id, 'mylisting_facebook_account_id', true ) )
			)
		) {
			return true;
		} elseif ( $this->auto_verify_from_3rd_party ) {
			return true;
		}
		return $is_user_verified;
	}

	/**
	 * maybe_redirect_to_myaccount_filter.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function maybe_redirect_to_myaccount_filter( $redirect_to, $user_id ) {
		return ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_checkout_block_thankyou', 'no' ) ? wc_get_page_permalink( 'myaccount' ) : $redirect_to );
	}

	/**
	 * maybe_redirect_to_myaccount_action.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function maybe_redirect_to_myaccount_action( $user_id ) {
		if ( 'yes' === get_option( 'alg_wc_ev_prevent_login_after_checkout_block_thankyou', 'no' ) ) {
			wp_safe_redirect( add_query_arg( 'alg_wc_ev_activate_account_message', $user_id, wc_get_page_permalink( 'myaccount' ) ) );
			exit;
		}
	}

	/**
	 * block_customer_order_emails.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 * @todo    (maybe) optional `guest`
	 * @todo    (maybe) delay (i.e. not block)
	 */
	function block_customer_order_emails( $is_enabled, $order, $email ) {
		return ( is_a( $order, 'WC_Order' ) && ( $user_id = $order->get_customer_id() ) && $this->core->is_user_verified_by_user_id( $user_id ) ? $is_enabled : false );
	}

	/**
	 * get_block_guest_add_to_cart_notice.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 */
	function get_block_guest_add_to_cart_notice() {
		$notice = do_shortcode( get_option( 'alg_wc_ev_block_guest_add_to_cart_notice',
			__( 'You need to <a href="%myaccount_url%" target="_blank">register</a> and verify your email before adding products to the cart.', 'emails-verification-for-woocommerce' ) ) );
		$placeholders = array(
			'%myaccount_url%' => wc_get_page_permalink( 'myaccount' ),
		);
		return str_replace( array_keys( $placeholders ), $placeholders, $notice );
	}

	/**
	 * block_guest_add_to_cart_validation.
	 *
	 * @version 2.0.3
	 * @since   1.5.0
	 */
	function block_guest_add_to_cart_validation( $passed, $product_id, $quantity ) {
		if ( ! is_user_logged_in() ) {
			if ( ! wp_doing_ajax() ) {
				$custom_url   = get_option( 'alg_wc_ev_block_guest_add_to_cart_custom_redirect_url', '' );
				$redirect_url = add_query_arg( array( 'alg_wc_ev_guest' => true ), $custom_url );
				wp_redirect( $redirect_url );
				exit;
			} else {
				add_filter( 'woocommerce_cart_redirect_after_error', array( $this, 'block_guest_add_to_cart_ajax_redirect' ), PHP_INT_MAX, 2 );
			}
			return false;
		}
		return $passed;
	}

	/**
	 * block_guest_add_to_cart_ajax_redirect.
	 *
	 * @version 2.0.3
	 * @since   1.5.0
	 *
	 * @param $url
	 * @param $product_id
	 *
	 * @return string
	 */
	function block_guest_add_to_cart_ajax_redirect( $url, $product_id ) {
		$url = empty( $custom_url = get_option( 'alg_wc_ev_block_guest_add_to_cart_custom_redirect_url', '' ) ) ? $url : $custom_url;
		return add_query_arg( 'alg_wc_ev_guest', true, $url );
	}

	/**
	 * block_guest_add_to_cart_ajax_error.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function block_guest_add_to_cart_ajax_error() {
		if ( isset( $_GET['alg_wc_ev_guest'] ) ) {
			alg_wc_ev_add_notice( $this->get_block_guest_add_to_cart_notice(), 'error' );
		}
	}

	/**
	 * maybe_wrap_in_wrap_in_wc_email_template.
	 *
	 * @version 2.1.8
	 * @since   1.5.0
	 */
	function maybe_wrap_in_wc_email_template( $email_content, $args ) {
		if (
			'wc' === ( $email_template = get_option( 'alg_wc_ev_email_template', 'plain' ) ) ||
			( 'smart' === $email_template && 'separate_email' === $args['context'] )
		) {
			if ( 'manual' === ( $wrap_method = get_option( 'alg_wc_ev_email_template_wc_wrap_method', 'manual' ) ) ) {
				$email_content = $this->wrap_in_wc_email_template( $email_content,
					do_shortcode( get_option( 'alg_wc_ev_email_template_wc_heading', __( 'Activate your account', 'emails-verification-for-woocommerce' ) ) ) );
			} elseif ( 'native' === $wrap_method ) {
				$mailer        = WC()->mailer();
				$email_content = $mailer->wrap_message( do_shortcode( get_option( 'alg_wc_ev_email_template_wc_heading', __( 'Activate your account', 'emails-verification-for-woocommerce' ) ) ), $email_content );
			}
		}
		return $email_content;
	}

	/**
	 * wrap_in_wc_email_template.
	 *
	 * @version 1.9.0
	 * @since   1.0.0
	 */
	function wrap_in_wc_email_template( $content, $email_heading = '' ) {
		$header = $this->get_wc_email_part( 'header', $email_heading );
		$footer = $this->get_wc_email_part( 'footer' );
		if ( class_exists( 'WC_Emails' ) && method_exists( 'WC_Emails', 'instance' ) && method_exists( 'WC_Emails', 'replace_placeholders' ) ) {
			$emails = WC_Emails::instance();
			$footer = $emails->replace_placeholders( $footer );
		} else {
			$footer = $this->replace_placeholders( $footer );
		}
		return $header . $content . $footer;
	}

	/**
	 * Replace placeholder text in strings.
	 *
	 * @version 1.9.0
	 * @since   1.9.0
	 * @param   string $string Email footer text.
	 * @return  string         Email footer text with any replacements done.
	 * @see     /woocommerce/includes/class-wc-emails.php
	 */
	function replace_placeholders( $string ) {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		return str_replace(
			array(
				'{site_title}',
				'{site_address}',
				'{site_url}',
				'{woocommerce}',
				'{WooCommerce}',
			),
			array(
				wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
				$domain,
				$domain,
				'<a href="https://woocommerce.com">WooCommerce</a>',
				'<a href="https://woocommerce.com">WooCommerce</a>',
			),
			$string
		);
	}

	/**
	 * get_wc_email_part.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_wc_email_part( $part, $email_heading = '' ) {
		ob_start();
		switch ( $part ) {
			case 'header':
				wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
				break;
			case 'footer':
				wc_get_template( 'emails/email-footer.php' );
				break;
		}
		return ob_get_clean();
	}

	/**
	 * maybe_add_wc_email_style.
	 *
	 * @version 1.9.2
	 * @since   1.9.2
	 * @todo    [test] if it's required in case of `mail` function
	 * @todo    (maybe) add style only if are sure that it's not plain text message
	 */
	function maybe_add_wc_email_style( $message, $func ) {
		if ( in_array( $func, array( 'mail', 'wp_mail' ) ) ) {
			$email   = new WC_Email();
			$message = $email->style_inline( $message );
		}
		return $message;
	}

	/**
	 * maybe_send_admin_email.
	 *
	 * @version 1.9.7
	 * @since   1.5.0
	 * @todo    [next] *optional* WC template
	 */
	function maybe_send_admin_email( $user_id ) {
		if (
			'yes' === get_option( 'alg_wc_ev_admin_email', 'no' ) &&
			'' == get_user_meta( $user_id, 'alg_wc_ev_admin_email_sent', true )
		) {
			$recipient    = get_option( 'alg_wc_ev_admin_email_recipient', '' );
			if ( '' === $recipient ) {
				$recipient = get_bloginfo( 'admin_email' );
			}
			$user         = new WP_User( $user_id );
			$placeholders = array(
				'%user_id%'                => $user_id,
				'%user_login%'             => $user->user_login,
				'%user_nicename%'          => $user->user_nicename,
				'%user_email%'             => $user->user_email,
				'%user_url%'               => $user->user_url,
				'%user_registered%'        => $user->user_registered,
				'%user_display_name%'      => $user->display_name,
				'%user_roles%'             => implode( ', ', $user->roles ),
				'%user_first_name%'        => $user->first_name,
				'%user_last_name%'         => $user->last_name,
				'%admin_user_profile_url%' => admin_url( 'user-edit.php?user_id=' . $user_id ),
			);
			$subject = str_replace( array_keys( $placeholders ), $placeholders, get_option( 'alg_wc_ev_admin_email_subject', __( 'User email has been verified', 'emails-verification-for-woocommerce' ) ) );
			$heading = str_replace( array_keys( $placeholders ), $placeholders, get_option( 'alg_wc_ev_admin_email_heading', __( 'User account has been activated', 'emails-verification-for-woocommerce' ) ) );
			$content = wpautop( str_replace( array_keys( $placeholders ), $placeholders, get_option( 'alg_wc_ev_admin_email_content',
				sprintf( __( 'User %s has just verified his email (%s).', 'emails-verification-for-woocommerce' ),
					'<a href="%admin_user_profile_url%">%user_login%</a>', '%user_email%' ) ) ) );
			$content      = $this->wrap_in_wc_email_template( $content, $heading );
			$this->core->emails->send_mail( $recipient, $subject, $content );
			update_user_meta( $user_id, 'alg_wc_ev_admin_email_sent', time() );
		}
	}

	/**
	 * email_subject.
	 *
	 * @version 1.1.1
	 * @since   1.1.0
	 */
	function email_subject( $subject ) {
		return get_option( 'alg_wc_ev_email_subject', __( 'Please activate your account', 'emails-verification-for-woocommerce' ) );
	}

	/**
	 * email_content.
	 *
	 * @version 2.1.8
	 * @since   1.1.0
	 */
	function email_content( $content ) {
		return get_option( 'alg_wc_ev_email_content',
			__( '<p>Please <a href="%verification_url%" target="_blank">click here</a> to verify your email.</p>', 'emails-verification-for-woocommerce' ) );
	}

	/**
	 * settings.
	 *
	 * @version 1.7.0
	 * @since   1.1.0
	 */
	function settings( $value, $type = '', $args = array() ) {
		if ( 'min' === $type ) {
			return array( 'min' => $args[0] );
		}
		return '';
	}

}

endif;

return new Alg_WC_Email_Verification_Pro();
