<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * The plugin's dependency notification.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/admin/notices
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Admin_Notice_Dependencies {
	private $missing_plugins;

	public function __construct( $plugin_name, $version, $missing_plugins ) {
		$this->missing_plugins = $missing_plugins;

		add_action( 'admin_notices', array( $this, 'render' ) );

		$plugin_admin = new StreamSage_WooCommerce_Admin( $plugin_name, $version );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ) );
	}

	public function render(): void {
		?>
        <div class="notice notice-warning shopsage-notice">
			<span class="shopsage-notice-icon">
				<img src="<?php echo esc_url( STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR_URL . 'admin/images/streamsage-for-woocommerce.svg' ); ?>"
                     alt="Stream Sage for WooCommerce Logo" width="250">
			</span>
            <div class="shopsage-notice-content">
                <h2>Stream Sage for WooCommerce</h2>
                <p>Sorry, but you need to have the following plugins enabled to use <i>Stream Sage for WooCommerce</i>:
                </p>
                <ul>
					<?php

					foreach ( $this->missing_plugins as $plugin => $details ) {
						if ( current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' ) ) {
							if ( ! is_plugin_active( $plugin ) && file_exists( sprintf( '%s/%s', WP_PLUGIN_DIR, $plugin ) ) ) {
								// User can activate the plugin
								$action = 'Activate';
								$url    = wp_nonce_url(
									self_admin_url(
										sprintf( 'plugins.php?action=activate&plugin=%s&plugin_status=all', $plugin )
									),
									sprintf( 'activate-plugin_%s', $plugin )
								);
							} else {
								// User can install the plugin
								$action = 'Install';
								$url    = wp_nonce_url(
									self_admin_url(
										sprintf( 'update.php?action=install-plugin&plugin=%s', $details['slug'] )
									),
									sprintf( 'install-plugin_%s', $details['slug'] )
								);
							}
						} else {
							// User cannot install or activate plugins
							$action = 'Check';
							$url    = $details['repository_url'];

							echo esc_html__( 'Unfortunately you do not have sufficient permissions to install or activate plugins. Ask the site administrator to do it for you.', 'streamsage-woocommerce' );
						}

						printf(
							'<li><a href="%1$s" class="button button-%4$s" aria-label="%2$s %3$s">%2$s %3$s</a></li>',
							esc_url( $url ),
							$action,
							$details['title'],
							'Activate' === $action ? 'primary' : 'secondary'
						);
					}

					?>
                </ul>
            </div>
            <div class="shopsage-notice-reference">
                <a href="https://streamsage.io" target="_blank">
                    <img src="<?php echo esc_url( STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR_URL . 'admin/images/streamsage.svg' ); ?>"
                         alt="Stream Sage Logo">
                </a>
            </div>
        </div>
		<?php
	}
}
