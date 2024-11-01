<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Product_Collection_Category {
	/**
	 * @var int The Category's ID
	 */
	public $id;

	/**
	 * @var string The Category's name
	 */
	public $name;

	/**
	 * @var string The Category's slug (from URL)
	 */
	public $slug;

	/**
	 * @var StreamSage_WooCommerce_Product[] Products of the collection
	 */
	public $items = array();

	/**
	 * @var array
	 */
	public $headers = array();

	public function __construct( WP_Term $term, int $limit = 1, int $page = 10 ) {
		$this->id   = $term->term_id;
		$this->name = $term->name;
		$this->slug = $term->slug;

		$term_products = wc_get_products(
			array(
				'category' => $term->slug,
				'status'   => 'publish',
				'limit'    => $limit,
				'page'     => $page,
				'paginate' => true,
				'orderby'  => null,
				'return'   => 'objects',
			)
		);
		$this->headers = array(
			'total_items' => $term_products->total,
			'total_pages' => $term_products->max_num_pages,
		);
		$this->items   = array_map(
			function ( WC_Product $product ) {
				return $this->get_product_by_type( $product );
			},
			$term_products->products
		);

	}

	private function get_product_by_type( WC_Product $product ): StreamSage_WooCommerce_Abstract_Product {
		if ( in_array( $product->get_type(), array( 'simple', 'variable' ), true ) ) {
			return new StreamSage_WooCommerce_Product( $product );
		}

		if ( 'grouped' === $product->get_type() ) {
			$group_products = $product->get_children();
			return new StreamSage_WooCommerce_Product( wc_get_product( $group_products[0] ) );
		}
	}
}
