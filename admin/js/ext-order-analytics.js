(function (wp) {
	const addOrderSourceFilter = (filters) => {
		return [
			{
				label: wp.i18n.__('Source', 'streamsage-woocommerce'),
				staticParams: [],
				param: 'order_source',
				showFilters: () => true,
				defaultValue: 'all',
				filters: [...(wcSettings.orderSource || [])],
			},
			...filters,
		];
	};

	wp.hooks.addFilter('woocommerce_admin_orders_report_filters', 'streamsage-woocommerce', addOrderSourceFilter);

})(window.wp );
