<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing

use XWC\Mixins\Settings_API_Methods;

/**
 * Base class for extended WooCommerce integrations.
 */
abstract class XWC_Integration extends WC_Integration {
    use Settings_API_Methods;

    protected function get_meta_type(): string {
        return 'integration';
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
