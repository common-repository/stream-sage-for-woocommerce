<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Price helper.
 *
 * @version    1.1.1
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes/helpers
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Price_Helper {
	/**
	 * Formatting the WooCommerce price as a float
	 *
	 * @param string|null $wc_price The price to be formatted as a float.
	 *
	 * @return float|null
	 */
	public static function format_as_float( ?string $wc_price ): ?float {
		if ( empty( $wc_price ) ) {
			return null;
		}

		return (float) preg_replace( '#[^\d.]#', '', $wc_price );
	}

	/**
	 * Returns the price for a product with tax settings.
	 *
	 * @param WC_Product        $product The Product's instance.
	 * @param string|float|null $price The custom price to be used instead of `get_price()` from $product.
	 *
	 * @return string
	 */
	public static function get_taxed_price( WC_Product $product, $price = null ): string {
		$tax_settings               = 'yes' === get_option( 'woocommerce_calc_taxes' );
		$cart_tax_included          = 'incl' === get_option( 'woocommerce_tax_display_cart' );
		$product_price_tax_included = 'yes' === get_option( 'woocommerce_prices_include_tax' );

		// Taxes settings are disabled, ignore.
		if ( false === $tax_settings ) {
			if ( null === $price ) {
				return $product->get_price();
			}

			return (string) $price;
		}

		// The tax in the shopping cart is not displayed.
		// The product price doesn't include tax.
		if ( ! $cart_tax_included && ! $product_price_tax_included ) {
			if ( null === $price ) {
				return $product->get_price();
			}

			return (string) $price;
		}

		// The tax in the shopping cart is displayed.
		// The product price doesn't include tax.
		if ( $cart_tax_included && ! $product_price_tax_included ) {
			$args = array();
			if ( null !== $price ) {
				$args['price'] = $price;
			}

			return (string) wc_get_price_including_tax( $product, $args );
		}

		// The tax in the shopping cart is not displayed.
		// The product price includes tax.
		if ( ! $cart_tax_included && $product_price_tax_included ) {
			$args = array();
			if ( null !== $price ) {
				$args['price'] = $price;
			}

			return (string) wc_get_price_excluding_tax( $product, $args );
		}

		// The cart tax and product tax are valid.
		// $cart_tax_included && $product_price_tax_included.
		if ( null === $price ) {
			return $product->get_price();
		}

		return (string) $price;
	}
}
