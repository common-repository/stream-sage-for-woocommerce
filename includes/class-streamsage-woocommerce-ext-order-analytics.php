<?php

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit( 1 );

/**
 * The Stream Sage Order analytics Extension.
 *
 * @version    1.0.0
 * @package    StreamSage_WooCommerce
 * @subpackage StreamSage_WooCommerce/includes
 * @author     Stream Sage Inc. <contact@streamsage.io>
 */
class StreamSage_WooCommerce_Ext_Order_Analytics {

	public function ext_init(): void {
		add_action( 'init', array( $this, 'ext_order_analytics_dropdown_options' ) );

		add_filter( 'woocommerce_analytics_orders_query_args', array( $this, 'ext_order_analytics_handle' ) );
		add_filter( 'woocommerce_analytics_orders_stats_query_args', array( $this, 'ext_order_analytics_handle' ) );

		add_filter(
			'woocommerce_analytics_clauses_join_orders_subquery',
			array( $this, 'ext_order_analytics_query_join' )
		);
		add_filter(
			'woocommerce_analytics_clauses_join_orders_stats_total',
			array( $this, 'ext_order_analytics_query_join' )
		);
		add_filter(
			'woocommerce_analytics_clauses_join_orders_stats_interval',
			array( $this, 'ext_order_analytics_query_join' )
		);

		add_filter(
			'woocommerce_analytics_clauses_where_orders_subquery',
			array( $this, 'ext_order_analytics_query_where' )
		);
		add_filter(
			'woocommerce_analytics_clauses_where_orders_stats_total',
			array( $this, 'ext_order_analytics_query_where' )
		);
		add_filter(
			'woocommerce_analytics_clauses_where_orders_stats_interval',
			array( $this, 'ext_order_analytics_query_where' )
		);

		add_filter(
			'woocommerce_analytics_clauses_select_orders_subquery',
			array( $this, 'ext_order_analytics_query_select_subquery' )
		);
		add_filter(
			'woocommerce_analytics_clauses_select_orders_stats_total',
			array( $this, 'ext_order_analytics_query_select_subquery' )
		);
		add_filter(
			'woocommerce_analytics_clauses_select_orders_stats_interval',
			array( $this, 'ext_order_analytics_query_select_subquery' )
		);
	}

	/**
	 * Add an additional JOIN query to access a postmeta from the Order
	 *
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function ext_order_analytics_query_join( array $clauses ): array {
		global $wpdb;
		$selected_source = $this->get_order_source_param();

		if ( $selected_source ) {
			$clauses[] = "JOIN {$wpdb->postmeta} os_postmeta ON {$wpdb->prefix}wc_order_stats.order_id = os_postmeta.post_id";
		}

		return $clauses;
	}

	/**
	 * Add an additional WHERE condition to filter Orders by an 'order_source'
	 *
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function ext_order_analytics_query_where( array $clauses ): array {
		$selected_source = $this->get_order_source_param();

		if ( ! $selected_source || 'all' === wc_strtolower( $selected_source ) ) {
			return $clauses;
		}

		$value_condition = "(os_postmeta.meta_value = 'streamsage' OR os_postmeta.meta_value = 'shop_sage')";
		if ( 'streamsage' === wc_strtolower( $selected_source ) ) {
			$clauses[] = "AND os_postmeta.meta_key = '_order_source' AND ${value_condition}";
		} elseif ( 'direct' === wc_strtolower( $selected_source ) ) {
			$clauses[] = "AND os_postmeta.meta_key = '_order_source' AND NOT ${value_condition}";
		}

		return $clauses;
	}

	/**
	 * Add an additional SELECT state to access an 'order_source' from the Order
	 *
	 * @param array $clauses
	 *
	 * @return array
	 */
	public function ext_order_analytics_query_select_subquery( array $clauses ): array {
		$selected_source = $this->get_order_source_param();

		if ( $selected_source ) {
			$clauses[] = ', os_postmeta.meta_value AS order_source';
		}

		return $clauses;
	}

	/**
	 * Add new sub-collection with an additional dropdown options
	 *
	 * @return void
	 * @throws Exception
	 */
	public function ext_order_analytics_dropdown_options(): void {
		$sources = array(
			array(
				'label' => __( 'All sources', 'streamsage-woocommerce' ),
				'value' => 'all',
			),
			array(
				'label' => __( 'Direct', 'streamsage-woocommerce' ),
				'value' => 'direct',
			),
			array(
				'label' => __( 'Stream Sage', 'streamsage-woocommerce' ),
				'value' => 'streamsage',
			),
		);

		$data_registry = Automattic\WooCommerce\Blocks\Package::container()->get(
			Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class
		);

		$data_registry->add( 'orderSource', $sources );
	}

	/**
	 * Pass an additional filter property
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function ext_order_analytics_handle( array $args ): array {
		$selected_source = $this->get_order_source_param();

		if ( $selected_source && 'all' !== $selected_source ) {
			$args['order_source'] = $selected_source;
		}

		return $args;
	}

	/**
	 * Get the query's parameter for the orders filtering
	 *
	 * @return string|null
	 */
	private function get_order_source_param(): ?string {
		if ( isset( $_GET['order_source'] ) && ! empty( $_GET['order_source'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return sanitize_text_field( wp_unslash( $_GET['order_source'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}

		return null;
	}
}
