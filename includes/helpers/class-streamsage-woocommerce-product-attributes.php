<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Product Attributes helper.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/helpers
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Product_Attributes {
	public static function get_attributes_map( WC_Product $product ): array {
		if ( $product->is_type( 'variation' ) ) {
			return self::get_variation_attributes( $product );
		}

		$attributes = array();
		foreach ( $product->get_attributes() as $attribute ) {
			$attribute_id = 'attribute_' . str_replace( ' ', '-', strtolower( $attribute['name'] ) );

			if ( $attribute['is_variation'] ) { // Do not return attributes that are not used for variations
				$attributes[] = array(
					'name'   => self::get_attribute_taxonomy_name( $attribute['name'], $product ),
					'key'    => $attribute_id,
					'values' => self::get_attribute_options( $product->get_id(), $attribute ),
				);
			}
		}

		return $attributes;
	}

	public static function get_variation_attributes( WC_Product_Variation $product_variation ): array {
		$_product   = wc_get_product( $product_variation->get_parent_id() );
		$attributes = array();

		foreach ( $product_variation->get_variation_attributes() as $attribute_name => $attribute ) {
			$name = str_replace( 'attribute_', '', $attribute_name );

			if ( ! $attribute ) {
				continue;
			}

			if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
				$option_term = get_term_by( 'slug', $attribute, $name );

				$attributes[] = array(
					'name'   => self::get_attribute_taxonomy_name( $name, $_product ),
					'key'    => $attribute_name,
					'values' => $option_term && ! is_wp_error( $option_term )
						? array( $option_term->slug => $option_term->name )
						: array( $attribute => $attribute ),
				);
			} else {
				$attributes[] = array(
					'name'   => self::get_attribute_taxonomy_name( $name, $_product ),
					'key'    => $attribute_name,
					'values' => array( $attribute => $attribute ),
				);
			}
		}

		return $attributes;
	}

	private static function get_attribute_taxonomy_name( $slug, $product ) {
		$attributes = $product->get_attributes();

		if ( ! isset( $attributes[ $slug ] ) ) {
			return str_replace( 'pa_', '', $slug );
		}

		$attribute = $attributes[ $slug ];

		// Taxonomy attribute name.
		if ( $attribute->is_taxonomy() ) {
			return $attribute->get_taxonomy_object()->attribute_label;
		}

		// Custom product attribute name.
		return $attribute->get_name();
	}

	private static function get_attribute_options( $product_id, $attribute ): array {
		$attributes = array();

		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
			$terms = wc_get_product_terms(
				$product_id,
				$attribute['name'],
				array( 'fields' => 'all' )
			);

			foreach ( $terms as $term ) {
				$attributes[ $term->slug ] = $term->name;
			}
		} elseif ( isset( $attribute['value'] ) ) {
			$options = explode( '|', $attribute['value'] );

			foreach ( $options as $attribute2 ) {
				$slug                = trim( $attribute2 );
				$attributes[ $slug ] = trim( $attribute2 );
			}
		}

		return $attributes;
	}
}
