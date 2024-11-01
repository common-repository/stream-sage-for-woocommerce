<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Management Extension.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_Management {
	/**
	 * @var array
	 *
	 * @link https://wordpress.org/support/article/roles-and-capabilities/#capabilities
	 * @link https://github.com/woocommerce/woocommerce/blob/c4e068be3b3d10a9242dbfed7e2d8916fe739e3a/plugins/woocommerce/includes/class-wc-install.php#L1535
	 */
	private static $role_caps = array(
		'read'                     => true,
		'view_admin_dashboard'     => true,
		'edit_posts'               => true,
		'install_plugins'          => true,
		'update_plugins'           => true,
		'activate_plugins'         => true,
		'delete_plugins'           => true,
		'edit_plugins'             => true,
		'upload_plugins'           => true,
		'view_woocommerce_reports' => true,
	);

	public static function ext_add_role(): void {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			// There is a problem with a WP_Roles!
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); //@codingStandardsIgnoreLine
		}

		add_role( 'streamsage', 'Stream Sage', self::$role_caps );
	}

	public static function ext_remove_role(): void {
		global $wp_roles;

		if ( ! class_exists( 'WP_Roles' ) ) {
			// There is a problem with a WP_Roles!
			return;
		}

		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); //@codingStandardsIgnoreLine
		}

		if ( $wp_roles->get_role( 'streamsage' ) === null ) {
			// Stream Sage role does not exists, ignore...
			return;
		}

		remove_role( 'streamsage' );
	}

	public function ext_init(): void {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			include ABSPATH . 'wp-includes/pluggable.php';
		}

		$user = wp_get_current_user();
		if ( is_user_logged_in() && $user ) {
			$roles = (array) $user->roles;

			if ( in_array( 'streamsage', $roles, true ) ) {
				add_action( 'admin_menu', array( $this, 'limit_dashboard_view' ), 71 );
			}
		}
	}

	public function limit_dashboard_view(): void {
		remove_menu_page( 'edit-comments.php' );
		remove_menu_page( 'edit.php' );
		remove_menu_page( 'wc-reports' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/overview' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/products' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/revenue' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/variations' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/categories' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/coupons' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/taxes' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/downloads' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/stock' );
		remove_submenu_page( 'wc-admin&path=/analytics/overview', 'wc-admin&path=/analytics/settings' );
		remove_menu_page( 'woocommerce-marketing' );
	}
}
