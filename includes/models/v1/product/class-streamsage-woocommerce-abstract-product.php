<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

abstract class StreamSage_WooCommerce_Abstract_Product {
	private const DEFAULT_IMAGE_SIZE = 'medium_large';

	/**
	 * @var int The Product's ID
	 */
	public $id;

	/**
	 * @var string The Product's slug (from URL)
	 */
	public $slug;

	/**
	 * @var string The Product's SKU
	 */
	public $sku;

	/**
	 * @var string|string[] The Product's name
	 */
	public $name;

	/**
	 * @var string[] The Product's image gallery as an array of the URLs (featured image + gallery)
	 */
	public $images;

	/**
	 * @var string[] Attributes objects
	 */
	public $attributes = array();

	public function __construct( WC_Product $product ) {
		$this->id         = $product->get_id();
		$this->name       = is_array( $product->get_name() )
			? $product->get_name()
			: array( $product->get_name() );
		$this->slug       = $product->get_slug();
		$this->sku        = ! empty( $product->get_sku() )
			? $product->get_sku()
			: null;
		$this->images     = $this->get_images( $product );
		$this->attributes = StreamSage_WooCommerce_Product_Attributes::get_attributes_map( $product );
	}

	protected function get_images( WC_Product $product ): array {
		$image_size = self::DEFAULT_IMAGE_SIZE;
		if ( ! array_key_exists( self::DEFAULT_IMAGE_SIZE, wp_get_registered_image_subsizes() ) ) {
			$image_size = 'full';
		}

		$product_gallery = array();
		foreach ( $product->get_gallery_image_ids() as $image_id ) {
			$product_gallery[] = wp_get_attachment_image_url( $image_id, $image_size );
		}

		if ( count( $product_gallery ) < 1 && get_post_thumbnail_id( $product->get_id() ) === 0 ) {
			return array();
		}

		return array_merge( array( wp_get_attachment_image_url( get_post_thumbnail_id( $product->get_id() ), $image_size ) ), $product_gallery );
	}
}
