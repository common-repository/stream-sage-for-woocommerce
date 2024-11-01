<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * Fired during plugin deactivation.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Deactivator {

	/**
	 * @version     1.0.0
	 */
	public static function deactivate(): void {
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-management.php';
		StreamSage_WooCommerce_Ext_Management::ext_remove_role();
	}
}
