<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

use XWC\Mixins\Settings_API_Methods;

/**
 * Base class for extended WooCommerce integrations.
 */
abstract class XWC_Integration extends WC_Integration {
    use Settings_API_Methods;

    protected function get_hooks(): array {
        return array(
            "woocommerce_update_options_integration_{$this->id}" => array( 'action', 'process_admin_options', 10 ),
        );
    }

    protected function get_admin_vars(): array {
        return array(
            'page' => 'wc-settings',
			'rest' => '/integrations',
			'tab'  => 'integration',
        );
    }

    protected function get_base_defaults(): array {
        return array(
            'id'                 => '',
            'method_description' => '',
            'method_title'       => '',
            'plugin_id'          => '',
        );
    }
}
