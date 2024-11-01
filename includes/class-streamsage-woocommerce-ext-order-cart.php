<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Order cart Extension.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_Order_Cart {
	private $order_cart_param          = 'cocart-load-cart';
	private $order_cart_key            = 'order_cart_id';
	private $order_cart_id_session_key = 'streamsage_order_cart_id';

	public function ext_init(): void {
		add_action( 'init', array( $this, 'ext_order_cart_handle' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'ext_order_cart_attach' ) );

		add_action( 'template_redirect', array( $this, 'ext_order_cart_load_cart_redirect' ), 10, 1 );

		// Let's hook our logic to detach the "cart_key" from the Order before remove it.
		add_action( 'cocart_cleanup_carts', array( $this, 'ext_order_cart_remove_cart_key' ), 1, 0 );
	}

	/**
	 * Remove the cart's ID from the order
	 *
	 * @throws Exception
	 */
	public function ext_order_cart_remove_cart_key(): void {
		global $wpdb;
		$r = $wpdb->get_results( $wpdb->prepare( "SELECT cart_key FROM {$wpdb->prefix}cocart_carts WHERE cart_expiry < %d", time() ) );

		foreach ( $r as $key => $v ) {
			$cart_key = $v->cart_key;
			$orders   = wc_get_orders(
				array(
					'meta_key'     => sprintf( '_%s', $this->order_cart_key ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					'meta_value'   => $cart_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					'meta_compare' => '=',
				)
			);

			foreach ( $orders as $order ) {
				$order_id = $order->get_id();
				// Clear `order_cart_id` from the Order.
				update_post_meta(
					$order_id,
					sprintf( '_%s', $this->order_cart_key ),
					sanitize_text_field( '' )
				); // phpcs:ignore WordPress.Security.NonceVerification
			}
		}
	}

	/**
	 * Fetch the "cart ID" parameter's value and save it into the customer's session
	 */
	public function ext_order_cart_handle(): void {
		// Early initialize customer session.
		if ( isset( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( isset( $_GET[ $this->order_cart_param ] ) && ! empty( $_GET[ $this->order_cart_param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$order_source = sanitize_text_field( wp_unslash( $_GET[ $this->order_cart_param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			// Set the session data.
			WC()->session->set( $this->order_cart_id_session_key, $order_source );
		}
	}

	/**
	 * Attach the "cart ID" parameter's value to the Order
	 *
	 * @param WC_Order $order Order to be modified.
	 */
	public function ext_order_cart_attach( $order ): void {
		$data = WC()->session->get( $this->order_cart_id_session_key ); // Get order source from session.

		$order->update_meta_data( sprintf( '_%s', $this->order_cart_key ), sanitize_text_field( $data ) );

		WC()->session->__unset( $this->order_cart_id_session_key ); // Clean session variable.
	}

	/**
	 * Redirect an our custom query param into the defaults of the CoCart
	 */
	public function ext_order_cart_load_cart_redirect(): void {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['load-cart'], $_SERVER['REQUEST_URI'] ) && ! empty( $_GET['load-cart'] ) ) {
			$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$request_uri = preg_replace( '/(\?|&)(load-cart)/i', '$1cocart-load-cart', $request_uri );
			wp_safe_redirect( home_url() . $request_uri, 301 );
			exit;
		}
	}
}
