<?php
/**
 * Settings_API_Methods trait file.
 *
 * @package eXtended WooCommerce
 * @subpackage Settings
 */

namespace XWC\Mixins;

use Automattic\Jetpack\Constants;

/**
 * Shared methods for Settings_API classes.
 */
trait Settings_API_Methods {
    /**
     * Flag to indicate if the settings have been saved.
     *
     * @var bool
     */
    protected bool $did_save = false;

    /**
     * Array of field types which are only for display.
     *
     * @var array<int,string>
     */
    protected array $display_fields = array(
        'title',
		'tbody_open',
		'tbody_close',
		'table_end',
    );

    /**
     * Magic getter for our object.
     *
     * @param string $name Property to get.
     * @return mixed
     */
    public function __get( string $name ): mixed {
        $value = $this->$name ?? $this->settings[ $name ] ?? null;

        return 'no' === $value || 'yes' === $value
            ? \wc_string_to_bool( $value )
            : $value;
    }

    /**
     * Returns the instance type.
     *
     * @return 'payment_gateway'|'shipping_method'|'integration'|'email'|'other'
     */
    abstract protected function get_meta_type(): string;

    /**
     * Get base props needed for gateway functioning.
     *
     * Base props are: id, 'method_title', 'method_description', 'has_fields', 'supports'
     *
     * @return array<string,mixed>
     */
    abstract protected function get_base_props(): array;

    /**
     * Get base props default values.
     *
     * @return array<string,mixed>
     */
    abstract protected function get_base_defaults(): array;

    /**
     * Get raw form fields.
     *
     * @return array
     */
    abstract protected function get_raw_form_fields(): array;

    /**
     * Get the instance API Meta config.
     *
     * @return array{
     *   page: string,
     *   rest: string,
     *   tab: string,
     *   hooks: array<string, array{
     *     0: 'filter'|'action',
     *     1: string
     *     2: int
     *   }>
     * }
     */
    protected function get_api_meta(): array {
        $instance_cfg = array(
            'integration'     => array(
                'hooks' => array(
                    "woocommerce_update_options_integration_{$this->id}" => array( 'action', 'process_admin_options', 10 ),
                ),
                'page'  => 'wc-settings',
                'rest'  => '/integrations',
                'tab'   => 'integration',
            ),
            'payment_gateway' => array(
                'hooks' => array(
                    "woocommerce_update_options_payment_gateways_{$this->id}" => array( 'action', 'process_admin_options', 10 ),
                    'wc_payment_gateways_initialized' => array( 'action', 'init_gateway', 100 ),
                ),
                'page'  => 'wc-settings',
                'rest'  => '/payment_gateways',
                'tab'   => 'checkout',
            ),
            'shipping_method' => array(
                'hooks' => array(
                    "woocommerce_update_options_shipping_{$this->id}" => array( 'action', 'process_admin_options', 10 ),
                ),
                'page'  => 'wc-settings',
                'rest'  => '/shipping_methods',
                'tab'   => 'shipping',
            ),
        );

        return $instance_cfg[ $this->get_meta_type() ];
    }

    /**
     * Get the option key.
     *
     * Modifies the option key to follow the format: PLUGIN_ID_settings_api_ID
     */
    public function get_option_key() {
        return \sprintf(
            '%s_api_settings_%s',
            \rtrim( $this->plugin_id, '_' ),
            $this->id,
        );
    }

    /**
	 * Prefix key for settings.
	 *
	 * @param  string $key Field key.
	 * @return string
	 */
    public function get_field_key( $key ) {
        return \sprintf(
            '%s_%s_%s',
            \rtrim( $this->plugin_id, '_' ),
            $this->id,
            $key,
		);
    }

    /**
     * Initializes base props needed for class functionality.
     */
    protected function init_base_props(): void {
        $props = \wp_parse_args(
            $this->get_base_props(),
            $this->get_base_defaults(),
        );

        foreach ( $props as $key => $value ) {
            $this->$key = $value;
        }
    }

    /**
	 * Initialise settings form fields.
	 *
	 * Add an array of fields to be displayed on the instance settings screen.
	 *
	 * @since  1.0.0
	 */
    public function init_form_fields() {
        $this->form_fields = $this->is_accessing_settings()
            ? $this->process_form_fields()
            : $this->load_form_fields();
    }

    /**
	 * Initialize Settings.
	 *
	 * Store all settings in a single database entry
	 * and make sure the $settings array is either the default
	 * or the settings stored in the database.
	 *
	 * @uses `get_option`, `add_option`
	 */
    public function init_settings() {
        $this->settings = \get_option( $this->get_option_key(), null ) ?? $this->load_settings();

        $this->on_init_settings();
    }

    /**
     * Initialize hooks.
     */
    public function init_hooks(): void {
        foreach ( $this->get_api_meta()['hooks'] as $tag => [ $cb, $method, $prio ] ) {
            ( "add_{$cb}" )( $tag, array( $this, $method ), $prio );
        }
    }

    /**
	 * Checks to see whether or not the admin settings are being accessed by the current request.
	 *
	 * @return bool
	 */
	protected function is_accessing_settings() {
        $val = $this->get_api_meta();
        $req = \wp_parse_args(
            \xwp_array_slice_assoc( \xwp_req_arr(), 'page', 'tab', 'section' ),
            array(
                'page'    => '',
                'section' => '',
                'tab'     => '',
			),
        );

        if ( ! \is_admin() && ! Constants::is_true( 'REST_REQUEST' ) ) {
            return false;
        }

        return ( $req['page'] === $val['page'] && $req['tab'] === $val['tab'] && $this->id === $req['section'] ) ||
            ( \str_contains( $GLOBALS['wp']->query_vars['rest_route'] ?? '', $val['rest'] ) );
	}

    /**
     * Processes callbacks in form fields.
     *
     * @return array
     */
    protected function process_form_fields(): array {
        $fields = $this->load_form_fields();

        foreach ( $fields as &$field ) {
            $field = \array_map(
                static fn( $f ) => $f instanceof \Closure ? $f() : $f,
                $field,
            );

        }

        return $fields;
    }

    /**
     * Loads the default settings and returns them.
     *
     * @return array<string,mixed>
     */
    protected function load_settings(): array {
        $defaults = array();

        foreach ( $this->get_form_fields() as $key => $field ) {
            if ( ! $this->is_option_field( $key, $field ) ) {
                continue;
            }

            $defaults[ $key ] = $field['default'] ?? '';
        }

        return $defaults;
    }

    /**
     * Is the field a display field?
     *
     * @param  string $key   Field key.
     * @param  array  $field Field data.
     * @return bool
     */
    protected function is_option_field( string $key, array $field ): bool {
        foreach ( $this->display_fields as $field ) {
            if ( \str_contains( $key, $field ) ) {
                return false;
            }
        }

        return ! ( $field['form_only'] ?? false );
    }

    /**
     * Fires when the settings are initialized.
     */
    protected function on_init_settings(): void {
        // Noop.
    }

    /**
     * Loads the form fields.
     *
     * @return array
     */
    public function load_form_fields(): array {
        return \array_merge(
            $this->get_meta_form_fields(),
            $this->get_raw_form_fields(),
        );
    }

    /**
     * Get the meta form fields.
     *
     * These are form fields which are needed for the instance to function.
     */
    protected function get_meta_form_fields(): array {
        return array();
    }
}
