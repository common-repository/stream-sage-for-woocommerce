<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Order organization Extension.
 *
 * @version    1.3.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_Order_Organization {
	private $order_organization_param       = 'ssorgid';
	private $order_organization_key         = '_streamsage'; // The final key will match the `__{key}` requirements. 
	private $order_organization_session_key = 'streamsage_order_organization_id';

	public function ext_init(): void {
		add_action( 'init', array( $this, 'ext_order_organization_handle' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'ext_order_organization_attach' ) );
	}

	/**
	 * Fetch the "organization ID" value and save it into the customer's session
	 */
	public function ext_order_organization_handle(): void {
		// Early initialize customer session.
		if ( isset( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( isset( $_GET[ $this->order_organization_param ] ) && ! empty( $_GET[ $this->order_organization_param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$order_source = sanitize_text_field( wp_unslash( $_GET[ $this->order_organization_param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			// Set the session data.
			WC()->session->set( $this->order_organization_session_key, $order_source );
		}
	}

	/**
	 * Attach the "organization ID" value to the Order
	 *
	 * @param WC_Order $order Order to be modified.
	 */
	public function ext_order_organization_attach( $order ): void {
		$data = WC()->session->get( $this->order_organization_session_key ); // Get order source from session.

		$order->update_meta_data( sprintf( '_%s', $this->order_organization_key ), sanitize_text_field( $data ) );

		WC()->session->__unset( $this->order_organization_session_key ); // Clean session variable.
	}
}
