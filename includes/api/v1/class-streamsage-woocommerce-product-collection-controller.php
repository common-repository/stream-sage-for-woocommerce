<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Product Collection API controller.
 *
 * @version    1.3.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/api/v1
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Product_Collection_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'shopsage/v1';
		$this->rest_base = 'product_collections';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes(): void {
		// GET /product_collections/:slug
		// GET /product_collections/:id
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\w-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the product.', 'streamsage-woocommerce' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
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
	 * @inheritDoc
	 */
	public function get_item( $request ): WP_REST_Response {
		try {
			$id = $request->get_param( 'id' ) ? wc_clean( wp_unslash( $request->get_param( 'id' ) ) ) : 0;

			if ( ! is_numeric( $id ) ) {
				$category = get_term_by( 'slug', $id, 'product_cat' );
			} else {
				$category = get_term_by( 'id', $id, 'product_cat' );
			}

			if ( false === $category ) {
				throw new Exception( 'Collection not found', 404 );
			}

			$limit = (int) StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'per_page', '10' );
			$page  = (int) StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'page', '1' );

			$products_data = new StreamSage_WooCommerce_Product_Collection_Category( $category, $limit, $page );
			$response      = new WP_REST_Response();
			// Set the headers first.
			$response->header( 'X-WP-Total', $products_data->headers['total_items'] );
			$response->header( 'X-WP-TotalPages', $products_data->headers['total_pages'] );
			// Remove unnecessary properties from response.
			unset( $products_data->headers );

			$response->set_data( $products_data );

			return $response;

		} catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), $exception->getCode() );
		}
	}
}

