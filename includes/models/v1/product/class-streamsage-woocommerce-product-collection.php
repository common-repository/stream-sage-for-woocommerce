<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Product_Collection {
	/**
	 * @var int The Product's ID
	 */
	public $id;

	/**
	 * @var string The Product's name
	 */
	public $name;

	/**
	 * @var string The Product's slug (from URL)
	 */
	public $slug;

	/**
	 * @var StreamSage_WooCommerce_Product[] Products of the collection
	 */
	public $items;

	public function __construct( WC_Product $product_grouped ) {
		$this->id    = $product_grouped->get_id();
		$this->name  = $product_grouped->get_name();
		$this->slug  = $product_grouped->get_slug();
		$this->items = array_map(
			static function ( string $product_id ) {
				$raw_product = wc_get_product( (int) $product_id );

				return new StreamSage_WooCommerce_Product( $raw_product );
			},
			$product_grouped->get_children()
		);
	}
}
