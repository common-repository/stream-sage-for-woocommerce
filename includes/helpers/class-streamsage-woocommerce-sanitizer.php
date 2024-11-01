<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage sanitanization helper.
 *
 * @version    1.3.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/helpers
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Sanitizer {
	/**
	 * Sanitizing the WP_REST_Request parameter.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @param string          $parameter Name of the request's parameter to be sanitized.
	 * @param mixed           $default Default value that will be returned if there is no provided parameter.
	 *
	 * @return mixed|string|null
	 */
	public static function sanitize_request_parameter( WP_REST_Request $request, string $parameter, $default = null ) {
		if ( $request->has_param( $parameter ) ) {
			return sanitize_text_field( $request->get_param( $parameter ) );
		}

		return $default;
	}
}
