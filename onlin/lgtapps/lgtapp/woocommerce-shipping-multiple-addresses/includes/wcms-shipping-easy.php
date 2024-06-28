<?php

if (! defined('ABSPATH')) {
	exit;
}

class WC_MS_Shipping_Easy {

	public function __construct() {
		add_action( 'woocommerce_order_action_se_send_to_shippingeasy', array( $this, 'process_manual_export' ), 1 );
		add_action( 'woocommerce_order_action_shippingeasy_export', array( $this, 'process_manual_export' ), 1 );
		add_action( 'woocommerce_thankyou', array( $this, 'send_shipments' ), 1 );
		add_action( 'woocommerce_payment_complete', array( $this, 'send_shipments' ), 1 );

        add_filter( 'se_order_values', array( $this, 'mark_as_gift' ), 9 );
        add_filter( 'se_order_values', array( $this, 'order_id_replacement' ) );
		add_action( 'se_shipment_response', array( $this, 'log_shipment_response' ) );
	}

	public function process_manual_export( $order ) {
		$order_id = WC_MS_Compatibility::get_order_prop( $order, 'id' );

		$this->send_shipments( $order_id, true );
		if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
			update_post_meta( $order_id, 'se_order_created', true );
		} else {
			$order->update_meta_data( 'se_order_created', true );
		}
	}

	public function send_shipments( $parent_order_id, $backend_order = false ) {
		$parent_order = wc_get_order( $parent_order_id );

		if ( ! $parent_order ) {
			return;
		}

		if ( 'yes' == $parent_order->get_meta( '_multiple_shipping' ) ) {
			$shipment_ids = get_posts(array(
				'nopaging'      => true,
				'post_type'     => 'order_shipment',
				'post_parent'   => $parent_order_id,
				'post_status'   => 'any',
				'fields'        => 'ids',
			));

			if ( class_exists( 'WC_ShippingEasy_Integration' ) ) {
				$se = new WC_ShippingEasy_Integration();

				foreach ( $shipment_ids as $shipment_id ) {
					$se->shipping_place_order( $shipment_id, $backend_order );
				}
			} else {
				foreach ( $shipment_ids as $shipment_id ) {
					shipping_place_order( $shipment_id, $backend_order );
				}
			}



			$parent_order->update_meta_data( 'se_order_created', true );
			$parent_order->save();
		}
	}

	public function order_id_replacement( $values ) {
		$post = get_post( $values['external_order_identifier'] );

		if ( $post && 'order_shipment' === $post->post_type ) {
			$values['external_order_identifier'] = $post->post_parent . '-' . $post->ID;
		}

		return $values;
	}

    public function mark_as_gift( $values ) {
        $post = get_post( $values['external_order_identifier'] );

        if ( $post && 'order_shipment' === $post->post_type ) {
            if ( 1 == get_post_meta( $post->ID, '_gift', true ) ) {
                $values['notes'] = __('This is a gift!', 'wc_shipping_multiple_address') . "\n\n" . $values['notes'];
            }
        }

        return $values;
    }

	public function log_shipment_response( $response ) {

        $order_id = $response['shipment']['orders'][0]['ext_order_reference_id'];

		if ( strpos( $order_id, '_' ) !== false ) {
			$parts          = explode( '_', $order_id );
			$order_id       = $parts[0];
		}


		if ( get_post_type( $order_id ) != 'order_shipment' ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( $order ) {
			//Store the values of shipped order which we are getting from ShippingEasy.
			$tracking_number = $response['shipment']['tracking_number'];
			$carrier_key = $response['shipment']['carrier_key'];
			$carrier_service_key = $response['shipment']['carrier_service_key'];
			$shipment_cost_cents = $response['shipment']['shipment_cost'];
			$shipment_cost = ($shipment_cost_cents / 100);

			/* translators: 1: tracking number 2: carrier key 3: carrier service key 4: cost */
			$comment_update = sprintf( __( 'Shipping Tracking Number: %s<br/>Carrier Key: %s<br/>Carrier Service Key: %s<br/>Cost: %s', 'wc_shipping_multiple_address' ), $tracking_number, $carrier_key, $carrier_service_key, $shipment_cost );

			$order->add_order_note( $comment_update );
		}
	}

}

new WC_MS_Shipping_Easy();
