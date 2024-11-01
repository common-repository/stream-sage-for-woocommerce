<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Product API controller.
 *
 * @version    1.3.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/api/v1
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Product_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'shopsage/v1';
		$this->rest_base = 'products';

		add_filter(
			'woocommerce_product_data_store_cpt_get_products_query',
			array(
				$this,
				'search_like_name',
			),
			10,
			2
		);
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes(): void {
		// GET /products
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// GET /products/:id
		// GET /products/:slug
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
	public function get_collection_params(): array {
		$params = parent::get_collection_params();
		unset( $params['search'], $params['context'] );

		return $params;
	}

	/**
	 * @inheritDoc
	 */
	public function get_items( $request ): WP_REST_Response {
		$ids_param         = StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'ids' );
		$variant_ids_param = StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'variantIds' );
		$include_ids       = array();

		if ( null !== $ids_param || null !== $variant_ids_param ) {
			$include_ids = $this->parse_include_ids( $ids_param, $variant_ids_param );
			if ( null === $include_ids ) {
				return rest_ensure_response( array() );
			}
		}

		$products_data = wc_get_products(
			array(
				'limit'     => (int) StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'per_page', '10' ),
				'page'      => (int) StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'page', '1' ),
				'status'    => 'publish',
				'paginate'  => true,
				'return'    => 'objects',
				'type'      => array( 'simple', 'variable', 'grouped' ),
				'include'   => $include_ids,
				'orderby'   => null,
				'like_name' => StreamSage_WooCommerce_Sanitizer::sanitize_request_parameter( $request, 'search' ),
			)
		);

		$products = array();
		foreach ( $products_data->products as $raw_product ) {
			if ( $raw_product->is_type( 'grouped' ) ) {
				$group_products = $raw_product->get_children();
				$raw_product    = wc_get_product( $group_products[0] );
			}

			if ( ! $raw_product instanceof WC_Product ) {
				// If groupped products is empty, ignore it.
				continue;
			}

			$product_response = $this->prepare_item_for_response( $raw_product, $request );
			if ( null === $product_response ) {
				// If product is not available, don't return it.
				continue;
			}

			$products[] = $this->prepare_response_for_collection( $product_response );
		}

		$response = rest_ensure_response( $products );
		$response->header( 'X-WP-Total', $products_data->total );
		$response->header( 'X-WP-TotalPages', $products_data->max_num_pages );

		return $response;
	}

	/**
	 * @param $item WC_Product
	 */
	public function prepare_item_for_response( $item, $request ) {
		$product = new StreamSage_WooCommerce_Product( $item );
		if ( count( $product->variants ) < 1 ) {
			// If product don't have any variants, ignore it.
			return null;
		}

		return rest_ensure_response( $product );
	}

	/**
	 * @inheritDoc
	 */
	public function get_item( $request ): WP_REST_Response {
		try {
			$product_id = $request->get_param( 'id' ) ? wc_clean( wp_unslash( $request->get_param( 'id' ) ) ) : 0;
			if ( ! is_numeric( $product_id ) ) {
				/** @var WP_Post $product_slug_object */
				$product_slug_object = get_page_by_path( $product_id, OBJECT, 'product' );

				if ( null === $product_slug_object ) {
					throw new Exception( 'Product not found', 404 );
				}

				$product_sku_id = $product_slug_object->ID;

				if ( $product_sku_id && $product_sku_id > 0 ) {
					$product_id = $product_sku_id;
				} else {
					throw new Exception( 'Product not found', 404 );
				}
			}

			$product = wc_get_product( (int) $product_id );

			if ( ! $product || ! $product->is_type( array( 'simple', 'variable', 'grouped' ) ) ) {
				throw new Exception( 'Product not found', 404 );
			}

			if ( $product->is_type( 'grouped' ) ) {
				// If product is grouped, let's return its first item
				$product = wc_get_product( $product->get_children()[0] );
			}

			$response = $this->prepare_item_for_response( $product, $request );

			return rest_ensure_response( $response );

		} catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), $exception->getCode() );
		}
	}

	public function search_like_name( $query, $query_vars ) {
		if ( isset( $query_vars['like_name'] ) && ! empty( $query_vars['like_name'] ) ) {
			$query['s'] = esc_attr( $query_vars['like_name'] );
		}

		return $query;
	}

	private function parse_include_ids( ?string $ids_param, ?string $variant_ids_param ): ?array {
		$include_ids = array();

		if ( null !== $variant_ids_param ) {
			foreach ( explode( ',', $variant_ids_param ) as $id ) {
				$parent_id = wp_get_post_parent_id( (int) $id );
				$product   = wc_get_product( $parent_id );

				if ( ! $product ) {
					continue;
				}

				if ( $product->is_type( 'variable' ) ) {
					$include_ids[] = $product->get_id();
				}
			}
		}

		if ( null !== $ids_param ) {
			$ids         = array_map( 'intval', explode( ',', $ids_param ) );
			$include_ids = array_merge( $include_ids, $ids );
		}

		$include_ids = array_filter(
			$include_ids,
			static function ( $entry ) {
				return is_numeric( $entry ) && $entry > 0;
			}
		);

		if ( count( $include_ids ) < 1 ) {
			return null;
		}

		return array_unique( $include_ids );
	}
}
