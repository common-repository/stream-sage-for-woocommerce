<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Order {
	/**
	 * @var int The Order's ID
	 */
	public $transactionId;

	/**
	 * @var StreamSage_WooCommerce_Price The Order's total price BEFORE checkout discount
	 */
	public $prevTotalPrice;

	/**
	 * @var StreamSage_WooCommerce_Price The Order's total price AFTER checkout discount
	 */
	public $totalPrice;

	/**
	 * @var StreamSage_WooCommerce_Order_Position[] The Order's items
	 */
	public $items;

	public function __construct( WC_Order $order ) {
		$this->transactionId  = $order->get_id();
		$this->prevTotalPrice = $order->get_discount_total() > 0
			? new StreamSage_WooCommerce_Price( bcadd( $order->get_discount_total(), (string) $order->get_total() ), $order->get_currency() )
			: null;
		$this->totalPrice     = new StreamSage_WooCommerce_Price( $order->get_total(), $order->get_currency() );
		$this->items          = array_values(
			array_map(
				static function ( $order_item ) {
					return new StreamSage_WooCommerce_Order_Position( $order_item );
				},
				$order->get_items()
			)
		);
	}
}
