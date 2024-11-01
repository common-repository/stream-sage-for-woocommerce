<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      StreamSage_WooCommerce_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * An external plugins required by Stream Sage WooCommerce.
	 *
	 * @since   1.0.0
	 * @access  protected
	 * @var     string[][] $plugin_dependencies Required external plugins by our.
	 */
	protected $plugin_dependencies = array(
		'woocommerce/woocommerce.php'                                     => array(
			'title'          => 'WooCommerce',
			'slug'           => 'woocommerce',
			'repository_url' => 'https://wordpress.org/plugins/woocommerce/',
		),
		'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php' => array(
			'title'          => 'CoCart',
			'slug'           => 'cart-rest-api-for-woocommerce',
			'repository_url' => 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/',
		),
	);

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'STREAMSAGE_WOOCOMMERCE_VERSION' ) ) {
			$this->version = STREAMSAGE_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'streamsage-woocommerce';
	}

	public function init(): void {
		$this->load_dependencies();
		$this->load_rest_api();
		$this->load_extends();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies(): void {
		/**
		 * The helper classes
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/helpers/class-streamsage-woocommerce-price-helper.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/helpers/class-streamsage-woocommerce-product-attributes.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/helpers/class-streamsage-woocommerce-sanitizer.php';

		/**
		 * The models classes
		 */
		// Product
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-price.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-abstract-product.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-product-collection.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-product-collection-category.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-product.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/product/class-streamsage-woocommerce-product-variant.php';
		// Order
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/order/class-streamsage-woocommerce-order.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/order/class-streamsage-woocommerce-order-position.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/models/v1/order/class-streamsage-woocommerce-order-product.php';

		/**
		 * The classes responsible for the custom API namespace.
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/api/v1/class-streamsage-woocommerce-product-controller.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/api/v1/class-streamsage-woocommerce-order-controller.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/api/v1/class-streamsage-woocommerce-product-collection-controller.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/api/v1/class-streamsage-woocommerce-details-controller.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/api/v1/class-streamsage-woocommerce-environment-controller.php';

		/**
		 * The classes responsible for extending already existing functionalities.
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-order-source.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-order-analytics.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-order-cart.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-order-organization.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-cocart.php';
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-ext-management.php';
	}

	/**
	 * Initialize every API controller
	 *
	 * @since    1.0.0
	 */
	private function load_rest_api(): void {
		$controllers = array(
			new StreamSage_WooCommerce_Product_Controller(),
			new StreamSage_WooCommerce_Order_Controller(),
			new StreamSage_WooCommerce_Product_Collection_Controller(),
			new StreamSage_WooCommerce_Details_Controller(),
			new StreamSage_WooCommerce_Environment_Controller(),
		);

		foreach ( $controllers as $controller ) {
			$this->loader->add_action( 'rest_api_init', $controller, 'register_routes' );
		}
	}

	/**
	 * Load extends for existing functionalities.
	 *
	 * @since    1.0.0
	 */
	private function load_extends(): void {
		$extends = array(
			new StreamSage_WooCommerce_Ext_Order_Source(),
			new StreamSage_WooCommerce_Ext_Order_Analytics(),
			new StreamSage_WooCommerce_Ext_Order_Cart(),
			new StreamSage_WooCommerce_Ext_Order_Organization(),
			new StreamSage_WooCommerce_Ext_CoCart(),
			new StreamSage_WooCommerce_Ext_Management(),
		);

		foreach ( $extends as $ext ) {
			$ext->ext_init();
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * Check a required dependencies and notify about it.
	 *
	 * @return   bool    Pass or not pass a requirements check.
	 * @since    1.0.0
	 */
	public function check_dependencies(): bool {
		$this->load_core_dependencies();

		$active_plugins       = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
		$missing_dependencies = array();

		foreach ( $this->plugin_dependencies as $plugin => $details ) {
			if ( ! in_array( $plugin, $active_plugins, true ) ) {
				$missing_dependencies[ $plugin ] = $details;
			}
		}

		if (
			count( $missing_dependencies ) > 0
			&& in_array( plugin_basename( STREAMSAGE_WOOCOMMERCE_PLUGIN_FILE ), $active_plugins, true )
		) {
			deactivate_plugins( plugin_basename( STREAMSAGE_WOOCOMMERCE_PLUGIN_FILE ) );

			new StreamSage_WooCommerce_Admin_Notice_Dependencies( $this->get_plugin_name(), $this->get_version(), $missing_dependencies );

			return false;
		}

		return true;
	}

	private function load_core_dependencies(): void {
		/**
		 * The core WordPress classes required by the rest of functionalities.
		 */
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'admin/class-streamsage-woocommerce-admin.php';

		/**
		 * The classes responsible for rendering plugin notices.
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'admin/notices/class-streamsage-woocommerce-admin-notice-dependencies.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once STREAMSAGE_WOOCOMMERCE_PLUGIN_DIR . 'includes/class-streamsage-woocommerce-loader.php';

		$this->loader = new StreamSage_WooCommerce_Loader();

		$this->define_admin_hooks();
	}

	/**
	 * Register all of the hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks(): void {
		$plugin_admin = new StreamSage_WooCommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name(): string {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    StreamSage_WooCommerce_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader(): StreamSage_WooCommerce_Loader {
		return $this->loader;
	}
}
