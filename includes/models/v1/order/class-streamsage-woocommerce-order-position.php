<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Order_Position {
	/**
	 * @var StreamSage_WooCommerce_Cart_Product
	 */
	public $item;

	/**
	 * @var StreamSage_WooCommerce_Product_Variant
	 */
	public $variant;

	/**
	 * @var int
	 */
	public $quantity;

	public function __construct( WC_Order_Item $order_item ) {
		$this->item     = new StreamSage_WooCommerce_Cart_Product( $order_item->get_product() );
		$this->variant  = new StreamSage_WooCommerce_Product_Variant( $order_item->get_product() );
		$this->quantity = $order_item->get_quantity();
	}
}
