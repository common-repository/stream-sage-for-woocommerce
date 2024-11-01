<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Orders API controller.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/api/v1
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Order_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'shopsage/v1';
		$this->rest_base = 'orders';

		add_filter(
			'woocommerce_order_data_store_cpt_get_orders_query',
			array(
				$this,
				'handle_order_cart_key_query',
			),
			10,
			2
		);
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes(): void {
		// GET /orders/by_cart/:cart_key
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/by_cart/(?P<cart_key>[\w-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Cart key', 'streamsage-woocommerce' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_order_by_cart' ),
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Extend current WC_Order_Query by a cart key property.
	 *
	 * @param array $query
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public function handle_order_cart_key_query( array $query, array $query_vars ): array {
		if ( ! empty( $query_vars['cart_key'] ) ) {
			$query['meta_query'][] = array(
				'key'   => '_order_cart_id',
				'value' => wc_clean( wp_unslash( $query_vars['cart_key'] ) ),
			);
		}

		return $query;
	}

	/**
	 * Check for an Order created by associated cart key.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_order_by_cart( WP_REST_Request $request ): WP_REST_Response {
		try {
			if ( ! $request->has_param( 'cart_key' ) ) {
				throw new Exception( 'Card key is required!', 400 );
			}

			$cart_key = wc_clean( wp_unslash( $request->get_param( 'cart_key' ) ) );
			$orders   = wc_get_orders(
				array(
					'cart_key' => $cart_key,
					'orderby'  => 'date',
					'order'    => 'DESC',
				)
			);

			if ( count( $orders ) < 1 ) {
				return new WP_REST_Response( null, 404 );
			}

			// To be sure about only a single Order, let's return the last one
			$order = end( $orders );

			return new WP_REST_Response(
				new StreamSage_WooCommerce_Order( $order ),
				200
			);

		} catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), $exception->getCode() );
		}
	}
}

