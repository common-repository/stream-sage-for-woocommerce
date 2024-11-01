<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage CoCart Extension.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_CoCart {
	private $cart_days_ttl = 30;

	public function ext_init(): void {
		add_filter( 'cocart_cart_expiring', array( $this, 'ext_cocart_cart_expiring' ) );
		add_filter( 'cocart_cart_expiration', array( $this, 'ext_cocart_cart_expiration' ) );
	}

	/**
	 * Return expiring time of the cart.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function ext_cocart_cart_expiring(): int {
		return DAY_IN_SECONDS * ( $this->cart_days_ttl > 3 ? $this->cart_days_ttl - 3 : 1 );
	}

	/**
	 * Return expration time of the cart.
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function ext_cocart_cart_expiration(): int {
		return DAY_IN_SECONDS * $this->cart_days_ttl;
	}
}
