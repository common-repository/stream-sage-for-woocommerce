<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Price {
	/**
	 * @var string The Currency's code
	 */
	public $currencyCode;

	/**
	 * @var float The amount of the Price
	 */
	public $amount;

	public function __construct( $price, ?string $currency_code = null ) {
		if ( is_string( $price ) ) {
			$price = StreamSage_WooCommerce_Price_Helper::format_as_float( $price );
		}

		$this->amount       = $price;
		$this->currencyCode = $currency_code ?? get_woocommerce_currency();

	}
}
