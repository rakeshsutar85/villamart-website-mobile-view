<?php
/**
 * Class to add compatibility with the WooCommerce Subscriptions extension.
 *
 * @package WC_OD
 * @since   1.3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_OD_Subscriptions
 */
class WC_OD_Subscriptions {

	/**
	 * Constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		$this->includes();

		add_action( 'woocommerce_subscription_activation_next_payment_not_recalculated', array( $this, 'subscription_activated_next_payment_not_recalculated' ), 10, 3 );
		add_action( 'woocommerce_subscription_date_updated', array( $this, 'subscription_date_updated' ), 10, 2 );
		add_action( 'woocommerce_subscription_date_deleted', array( $this, 'subscription_date_deleted' ), 10, 2 );
		add_action( 'wc_od_subscription_delivery_date_not_found', array( $this, 'delivery_date_not_found' ) );

		add_filter( 'wcs_new_order_created', array( $this, 'order_created' ), 10, 2 );

		// Priority 5. Before send the emails.
		add_action( 'woocommerce_order_status_pending_to_processing', array( $this, 'process_order' ), 5 );
		add_action( 'woocommerce_order_status_failed_to_processing', array( $this, 'process_order' ), 5 );
	}

	/**
	 * Includes the necessary files.
	 *
	 * @since 1.3.0
	 */
	public function includes() {
		include_once 'wc-od-subscriptions-functions.php';
		include_once 'class-wc-od-subscription-delivery-details.php';
		include_once 'class-wc-od-subscriptions-data-copier.php';
		include_once 'class-wc-od-subscriptions-checkout.php';
		include_once 'class-wc-od-subscriptions-emails.php';
		include_once 'class-wc-od-subscriptions-settings.php';
		include_once 'class-wc-od-subscription-delivery.php';

		if ( is_admin() ) {
			include_once 'class-wc-od-subscription-admin.php';
		}
	}

	/**
	 * Processes new subscriptions and their renewals.
	 *
	 * @since 1.5.0
	 *
	 * @param WC_Subscription $subscription The subscription instance.
	 */
	public function process_subscription( $subscription ) {
		/*
		 * Skip if the subscription is being updated manually by the merchant.
		 * Processed in `WC_OD_Subscription_Admin->subscription_date_updated()`.
		 */
		if ( wc_od_is_save_request_for_order( $subscription->get_id() ) ) {
			return;
		}

		/*
		 * The subscription data is not ready at this point after creating the subscription in the checkout form.
		 * Processed in `WC_OD_Subscriptions_Checkout->subscription_created()`.
		 */
		if ( defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			$cart_item = wcs_cart_contains_renewal();

			if ( ! $cart_item || $subscription->get_id() !== $cart_item['subscription_renewal']['subscription_id'] ) {
				return;
			}
		}

		$delivery_date = $subscription->get_meta( '_delivery_date' );

		// It's a valid date.
		if ( $delivery_date && wc_od_validate_subscription_delivery_date( $subscription, $delivery_date ) ) {
			return;
		}

		wc_od_update_subscription_delivery_date( $subscription );
		wc_od_update_subscription_delivery_time_frame( $subscription );
	}

	/**
	 * The subscription was activated but there wasn't need to update the next payment date.
	 *
	 * @since 1.5.5
	 *
	 * @param int             $next_payment A timestamp representing the next payment date.
	 * @param string          $old_status   The old status of the subscription.
	 * @param WC_Subscription $subscription The subscription object.
	 */
	public function subscription_activated_next_payment_not_recalculated( $next_payment, $old_status, $subscription ) {
		$this->process_subscription( $subscription );
	}

	/**
	 * Updates the subscription delivery when the next payment date is updated.
	 *
	 * @since 1.5.5
	 *
	 * @param WC_Subscription $subscription The subscription instance.
	 * @param string          $date_type    The date type.
	 */
	public function subscription_date_updated( $subscription, $date_type ) {
		if ( 'next_payment' !== $date_type ) {
			return;
		}

		$this->process_subscription( $subscription );
	}

	/**
	 * Deletes the subscription delivery when the next payment date is deleted.
	 *
	 * @since 1.5.5
	 *
	 * @param WC_Subscription $subscription The subscription instance.
	 * @param string          $date_type    The date type.
	 */
	public function subscription_date_deleted( $subscription, $date_type ) {
		if ( 'next_payment' !== $date_type ) {
			return;
		}

		// Disable the save process of the meta box 'woocommerce-subscription-delivery' to avoid overwrite the value.
		if ( wc_od_is_save_request_for_order( $subscription->get_id() ) ) {
			remove_action( 'woocommerce_process_shop_order_meta', 'WC_OD_Meta_Box_Subscription_Delivery::save', 20 );
		}

		// Delete the next delivery date and time frame.
		wc_od_delete_order_meta( $subscription, '_delivery_date', true );
		wc_od_delete_order_meta( $subscription, '_delivery_time_frame', true );
	}

	/**
	 * Adds a note to the subscription when a delivery date for the next order is not found.
	 *
	 * @since 1.3.0
	 *
	 * @param WC_Subscription $subscription The subscription instance.
	 */
	public function delivery_date_not_found( $subscription ) {
		wc_od_add_order_note( $subscription, __( 'Delivery date not found for the next order.', 'woocommerce-order-delivery' ) );
	}

	/**
	 * Filters the metadata that will be copied from a subscription to an order and vice-versa.
	 *
	 * @since 1.3.0
	 * @since 1.5.5 Also supports the copy from an order to a subscription.
	 * @deprecated 2.5.0
	 *
	 * @param array    $meta       The metadata to copy to the order.
	 * @param WC_Order $to_order   The order to copy the metadata.
	 * @param WC_Order $from_order The order from which the metadata is copied.
	 * @return array An array with the order metadata.
	 */
	public function copy_order_meta( $meta, $to_order, $from_order ) {
		wc_deprecated_function( __FUNCTION__, '2.5.0', 'WC_OD_Subscriptions_Data_Copier::copy_meta_filter()' );

		return WC_OD_Subscriptions_Data_Copier::copy_meta_filter( $meta, $to_order, $from_order );
	}

	/**
	 * Processes the order created from a subscription.
	 *
	 * @since 1.5.0
	 *
	 * @param WC_Order        $order        The order instance.
	 * @param WC_Subscription $subscription The subscription instance.
	 * @return WC_Order
	 */
	public function order_created( $order, $subscription ) {
		$this->fix_order_shipping_method( $order, $subscription );
		$this->update_order_time_frame( $order );

		return $order;
	}

	/**
	 * Fix for WooCommerce Subscriptions: Set the missing order-item meta 'instance_id'.
	 *
	 * @since 1.5.0
	 *
	 * @param WC_Order        $order        The order instance.
	 * @param WC_Subscription $subscription The subscription instance.
	 */
	public function fix_order_shipping_method( $order, $subscription ) {
		$order_shippings = $order->get_shipping_methods();
		$order_shipping  = reset( $order_shippings );

		if ( $order_shipping && ! $order_shipping['instance_id'] ) {
			$subscription_shippings = $subscription->get_shipping_methods();
			$subscription_shipping  = reset( $subscription_shippings );

			if ( $subscription_shipping && '' !== $subscription_shipping['instance_id'] ) {
				wc_update_order_item_meta( $order_shipping->get_id(), 'instance_id', $subscription_shipping['instance_id'] );
			}
		}
	}

	/**
	 * Replaces the time frame ID by its data.
	 *
	 * @since 1.5.0
	 *
	 * @param WC_Order $order The order instance.
	 */
	public function update_order_time_frame( $order ) {
		$delivery_date = $order->get_meta( '_delivery_date' );

		if ( ! $delivery_date ) {
			return;
		}

		$time_frame_id = $order->get_meta( '_delivery_time_frame' );

		if ( $time_frame_id ) {
			$time_frame = wc_od_get_time_frame_for_date( $delivery_date, $time_frame_id );

			if ( $time_frame ) {
				wc_od_update_order_meta( $order, '_delivery_time_frame', wc_od_time_frame_to_order( $time_frame ) );
			}
		}
	}

	/**
	 * Processes the order created from a subscription renewal after the payment success.
	 *
	 * @since 1.5.0
	 *
	 * @param int $order_id The order Id.
	 */
	public function process_order( $order_id ) {
		// No renewals in the order.
		if ( ! wcs_order_contains_renewal( $order_id ) ) {
			return;
		}

		$this->validate_order_delivery_date( $order_id );
		$this->validate_order_delivery_time_frame( $order_id );
		$this->add_order_shipping_date( $order_id );
	}

	/**
	 * Validates and updates if necessary the delivery date of the renewal order.
	 *
	 * @since 1.3.0
	 *
	 * @param int $order_id The order Id.
	 */
	public function validate_order_delivery_date( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$delivery_date = $order->get_meta( '_delivery_date' );

		// Delivery date not found during the subscription renewal or removed manually by the merchant.
		if ( ! $delivery_date ) {
			return;
		}

		// The 'next_payment' date is not up to date at this point, so we cannot use the 'end_date' parameter here.
		$args = array(
			'order_id'           => $order_id,
			'shipping_method'    => wc_od_get_order_shipping_method( $order ),
			'disabled_days_args' => array(
				'type'     => 'delivery',
				'country'  => $order->get_shipping_country(),
				'state'    => $order->get_shipping_state(),
				'order_id' => $order_id,
			),
		);

		// Get the first delivery date since payment.
		$first_delivery_date = wc_od_get_first_delivery_date( $args, 'renewal-order' );

		// No delivery date available.
		if ( ! $first_delivery_date ) {
			wc_od_delete_order_meta( $order_id, '_delivery_date', true );
			return;
		}

		// The minimum date for delivery.
		$args['start_date'] = date( 'Y-m-d', $first_delivery_date );

		// If the current date is not valid, change it for the first delivery date.
		if ( ! wc_od_validate_delivery_date( $delivery_date, $args, 'renewal-order' ) ) {
			wc_od_update_order_meta( $order_id, '_delivery_date', $args['start_date'], true );
		}
	}

	/**
	 * Validates and updates if necessary the delivery time frame of the renewal order.
	 *
	 * @since 1.5.0
	 *
	 * @param int $order_id The order Id.
	 */
	public function validate_order_delivery_time_frame( $order_id ) {
		$delivery_date = wc_od_get_order_meta( $order_id, '_delivery_date' );

		if ( ! $delivery_date ) {
			wc_od_delete_order_meta( $order_id, '_delivery_time_frame', true );
			return;
		}

		$time_frames = wc_od_get_time_frames_for_date(
			$delivery_date,
			array(
				'shipping_method' => wc_od_get_order_shipping_method( $order_id ),
			),
			'renewal-order'
		);

		if ( $time_frames->is_empty() ) {
			wc_od_delete_order_meta( $order_id, '_delivery_time_frame', true );
		} elseif ( 1 === count( $time_frames ) ) {
			$time_frame = $time_frames->first();

			wc_od_update_order_meta( $order_id, '_delivery_time_frame', wc_od_time_frame_to_order( $time_frame ), true );
		} else {
			$time_frame = wc_od_get_order_meta( $order_id, '_delivery_time_frame' );

			if ( $time_frame ) {
				$params = array_intersect_key( $time_frame, array_flip( array( 'time_from', 'time_to' ) ) );

				// Time frame not available for the current delivery date.
				if ( false === wc_od_search_time_frame( $time_frames, $params ) ) {
					wc_od_delete_order_meta( $order_id, '_delivery_time_frame', true );
				}
			}
		}
	}

	/**
	 * Adds the shipping date to the renewal order after the delivery date validation.
	 *
	 * @since 1.4.1
	 *
	 * @param int $order_id The order Id.
	 */
	public function add_order_shipping_date( $order_id ) {
		$shipping_timestamp = wc_od_get_order_last_shipping_date( $order_id, 'renewal-order' );

		if ( $shipping_timestamp ) {
			// Stores the date in the ISO 8601 format.
			$shipping_date = wc_od_localize_date( $shipping_timestamp, 'Y-m-d' );
			wc_od_update_order_meta( $order_id, '_shipping_date', $shipping_date, true );
		}
	}
}

return new WC_OD_Subscriptions();
