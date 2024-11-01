<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Details Store API controller.
 *
 * @version    1.1.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/api/v1
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Details_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'shopsage/v1';
		$this->rest_base = 'details';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes(): void {
		// GET /details/store
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/store',
			array(
				'args'   => array(),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_store_details' ),
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
	 * Get store details.
	 *
	 * @return WP_REST_Response
	 */
	public function get_store_details(): WP_REST_Response {
		try {
			$taxes = null;
			if ( 'yes' === get_option( 'woocommerce_calc_taxes' ) ) {
				// If taxes control is enabled, re-calculate prices if needed.
				$taxes = array(
					'display_shop_included' => 'incl' === get_option( 'woocommerce_tax_display_shop' ),
					'display_cart_included' => 'incl' === get_option( 'woocommerce_tax_display_cart' ),
				);
			}

			$details = array(
				'name'             => get_bloginfo( 'title' ),
				'currency'         => get_woocommerce_currency(),
				'currencySymbol'   => get_woocommerce_currency_symbol(),
				'priceFormat'      => get_woocommerce_price_format(),
				'url'              => get_bloginfo( 'url' ),
				'checkoutEndpoint' => str_replace( get_bloginfo( 'url' ), '', wc_get_checkout_url() ),
				'taxes'            => $taxes,
			);

			return new WP_REST_Response( $details, 200 );

		} catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), $exception->getCode() );
		}
	}
}

