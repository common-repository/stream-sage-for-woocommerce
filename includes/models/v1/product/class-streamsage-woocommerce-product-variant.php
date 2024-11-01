<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Product_Variant extends StreamSage_WooCommerce_Abstract_Product {
	/**
	 * @var float The Product's active price.
	 */
	public $price;

	/**
	 * @var float The Product's regular price (if is not on SALE).
	 */
	public $prevPrice;

	/**
	 * @var boolean The Product's availability status
	 */
	public $available;

	public function __construct( WC_Product $product ) {
		parent::__construct( $product );
		if ( $product->is_type( 'variation' ) ) {
			/** @var WC_Product_Variation $product */
			$this->name = array_values( $product->get_attributes() );
		}

		$this->price     = new StreamSage_WooCommerce_Price(
			StreamSage_WooCommerce_Price_Helper::get_taxed_price( $product, $product->get_price() ),
			get_woocommerce_currency()
		);
		$this->prevPrice = $product->is_on_sale()
			? new StreamSage_WooCommerce_Price(
				StreamSage_WooCommerce_Price_Helper::get_taxed_price( $product, $product->get_regular_price() ),
				get_woocommerce_currency()
			)
			: null;
		$this->available = $product->get_stock_status() === 'instock';
	}
}
