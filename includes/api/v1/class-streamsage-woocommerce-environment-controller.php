<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The WordPress php environment informations.
 *
 * @version    1.2.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/api/v1
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Environment_Controller extends WP_REST_Controller {
	public function __construct() {
		$this->namespace = 'shopsage/v1';
		$this->rest_base = 'environment';
	}

	/**
	 * @inheritDoc
	 */
	public function register_routes(): void {
		// GET /environment/info
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/info',
			array(
				'args'   => array(),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_environment_info' ),
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
	 * Get php environment info.
	 *
	 * @return WP_REST_Response
	 */
	public function get_environment_info(): WP_REST_Response {
		try {
			$details = array(
				'met'      => ini_get('max_execution_time'),
				'sl'	   => sys_getloadavg(),
			);

			return new WP_REST_Response( $details, 200 );

		} catch ( Exception $exception ) {
			return new WP_REST_Response( $exception->getMessage(), $exception->getCode() );
		}
	}
}

