<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

class StreamSage_WooCommerce_Product extends StreamSage_WooCommerce_Abstract_Product {
	/**
	 * @var string The Product's category name. If Product is attached to the multiple categories, only first will be returned
	 */
	public $category;

	/**
	 * @var string Product's type. ["simple", "multiple" if product has a variants]
	 */
	public $type;

	/**
	 * @var string The Product's description
	 */
	public $description;

	/**
	 * @var StreamSage_WooCommerce_Product_Variant[]|void The Product's variants
	 */
	public $variants;

	public function __construct( WC_Product $product ) {
		parent::__construct( $product );

		$this->category = array_map(
			static function ( $term ) {
				return $term->name;
			},
			get_the_terms( $product->get_id(), 'product_cat' )
		)[0]; // return only the first category
		$this->type     = $product->get_type() === 'variation' ? 'multiple' : $product->get_type();
		$this->variants = $this->get_product_variants( $product );

		if ( ! empty( $product->get_description() ) ) {
			$this->description = $product->get_description();
		} elseif ( ! empty( $product->get_short_description() ) ) {
			$this->description = $product->get_short_description();
		} else {
			$this->description = null;
		}
	}

	/**
	 * @param WC_Product|WC_Product_Variable $product
	 */
	private function get_product_variants( $product ): array {
		if ( $product->is_type( 'simple' ) ) {
			return array( new StreamSage_WooCommerce_Product_Variant( $product ) );
		}

		return array_map(
			static function ( $product_id ) {
				return new StreamSage_WooCommerce_Product_Variant( wc_get_product( $product_id ) );
			},
			$product->get_children()
		);
	}
}
