<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Cart_Product {
	/**
	 * @var int The Product's ID
	 */
	public $id;

	/**
	 * @var string The Product's name
	 */
	public $name;

	/**
	 * @var string The Product's type. Available: "simple", variable as "multiple"
	 */
	public $type;

	public function __construct( WC_Product $product ) {
		$this->id   = $product->get_id();
		$this->name = $product->get_name();
		$this->type = $product->get_type() === 'variation' ? 'multiple' : $product->get_type();
	}
}
