<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Order source Extension.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_Order_Source {
	private $order_source_param       = 'source';
	private $order_source_key         = 'order_source';
	private $order_source_session_key = 'streamsage_order_source';
	private $order_source_default     = 'direct';

	public function ext_init(): void {
		add_action( 'init', array( $this, 'ext_order_source_handle' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'ext_order_source_attach' ) );
		add_action(
			'woocommerce_admin_order_data_after_billing_address',
			array( $this, 'ext_order_source_order_details_view' ),
			10,
			1
		);
		add_filter( 'manage_edit-shop_order_columns', array( $this, 'ext_order_source_order_list_column' ) );
		add_action( 'manage_shop_order_posts_custom_column', array( $this, 'ext_order_source_order_list_view' ) );
	}

	/**
	 * Fetch the "source" parameter's value and save it into the customer's session
	 */
	public function ext_order_source_handle(): void {
		// Early initialize customer session.
		if ( isset( WC()->session ) && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}

		if ( isset( $_GET[ $this->order_source_param ] ) && ! empty( $_GET[ $this->order_source_param ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$order_source = sanitize_text_field( wp_unslash( $_GET[ $this->order_source_param ] ) ); // phpcs:ignore WordPress.Security.NonceVerification

			// Set the session data.
			WC()->session->set( $this->order_source_session_key, $order_source );
		}
	}

	/**
	 * Attach the "source" parameter's value to the Order
	 *
	 * @param WC_Order $order
	 */
	public function ext_order_source_attach( $order ): void {
		$data = WC()->session->get( $this->order_source_session_key ); // Get order source from session.

		if ( null === $data ) {
			$data = $this->order_source_default; // Set source as Direct if not available.
		}

		$order->update_meta_data( sprintf( '_%s', $this->order_source_key ), sanitize_text_field( $data ) );

		WC()->session->__unset( $this->order_source_session_key ); // Remove session variable.
	}

	/**
	 * Display the Order's source on it's details.
	 *
	 * @param WC_Order $order
	 */
	public function ext_order_source_order_details_view( WC_Order $order ): void {
		$order_source = $order->get_id();

		printf(
			'<p><strong>%s:</strong> %s</p>',
			esc_attr__( 'Order source', 'streamsage-woocommerce' ),
			wp_kses_post(
				get_post_meta( $order_source, sprintf( '_%s', $this->order_source_key ), true )
			)
		);
	}

	/**
	 * Add custom column for the Orders source.
	 *
	 * @param array $columns
	 *
	 * @return array
	 * @since    1.0.0
	 */
	public function ext_order_source_order_list_column( array $columns ): array {
		$columns[ $this->order_source_key ] = __( 'Order source', 'streamsage-woocommerce' );

		return $columns;
	}

	/**
	 * Load the Order's source value into 'Source' column.
	 *
	 * @param string $column
	 */
	public function ext_order_source_order_list_view( string $column ): void {
		global $post;

		if ( $this->order_source_key === $column ) {
			$order        = wc_get_order( $post->ID );
			$order_source = $order->get_meta( sprintf( '_%s', $this->order_source_key ) );

			echo wp_kses_post( sprintf( '<mark style="background-color: transparent"><span>%s</span></mark>', $order_source ) );
		}
	}
}
