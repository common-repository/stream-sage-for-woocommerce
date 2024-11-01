<?php

declare( strict_types=1 );

/**
 * @link              https://streamsage.io
 * @version           1.0.0
 * @package           StreamSage_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Stream Sage for WooCommerce
 * Description:       Turn your content into a sales channel. Native integration with Stream Sage that enables shopping directly from your content.
 * Version:           1.3.0
 * Author:            Stream Sage Inc.
 * Author URI:        https://streamsage.io
 * Requires at least: 5.6
 * Requires PHP:      7.3
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       streamsage-woocommerce
 * Domain Path:       /languages
 */

defined( 'WPINC' ) || exit( 1 );
define( 'STREAMSAGE_WOOCOMMERCE_VERSION', '1.3.0' );
define( 'STREAMSAGE_WOOCOMMERCE_PLUGIN_FILE', __FILE__ );
define( 'STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_streamsage_woocommerce() {
	require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-activator.php';
	StreamSage_WooCommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_streamsage_woocommerce() {
	require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-deactivator.php';
	StreamSage_WooCommerce_Deactivator::deactivate();
}

/**
 * The code that runs after the plugin has been updated.
 */
function update_streamsage_woocommerce( $upgrader, $options ) {
	$woo_plugin = plugin_basename( __FILE__ );

	if ( 'plugin' === $options['type'] && isset( $options['plugins'] ) ) {
		foreach ( $options['plugins'] as $plugin ) {
			if ( $plugin === $woo_plugin ) {
				require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-updater.php';

				StreamSage_WooCommerce_Updater::update();
			}
		}
	}
}

register_activation_hook( STREAMSAGE_WOOCOMMERCE_PLUGIN_FILE, 'activate_streamsage_woocommerce' );
register_deactivation_hook( STREAMSAGE_WOOCOMMERCE_PLUGIN_FILE, 'deactivate_streamsage_woocommerce' );
add_action( 'upgrader_process_complete', 'update_streamsage_woocommerce', 10, 2 );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * @version     1.0.0
 */
function run_streamsage_woocommerce() {
	$plugin = new StreamSage_WooCommerce();

	if ( $plugin->check_dependencies() ) {
		$plugin->init();
		$plugin->run();
	}
}

run_streamsage_woocommerce();
