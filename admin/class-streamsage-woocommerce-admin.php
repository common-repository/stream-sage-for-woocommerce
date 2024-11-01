<?php

declare( strict_types=1 );

/**
 * The admin-specific functionality of the plugin.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/admin
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @version  1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @version  1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @version  1.0.0
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style( $this->plugin_name, STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR_URL . 'admin/css/streamsage-woocommerce-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @version  1.0.0
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			$this->plugin_name,
			STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR_URL . 'admin/js/ext-order-analytics.js',
			array( 'jquery', 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-block-editor' ),
			$this->version,
			false
		);
	}

}
