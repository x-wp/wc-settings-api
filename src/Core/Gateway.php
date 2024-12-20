<?php //phpcs:disable WordPress.WP.I18n.TextDomainMismatch
/**
 * Extended_Payment_Gateway class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Settings API
 */

use XWC\Mixins\Settings_API_Methods;

/**
 * Extended Payment Gateway which enables easy setting up of payment gateways.
 */
abstract class XWC_Payment_Gateway extends WC_Payment_Gateway {
    use Settings_API_Methods;

    /**
     * Whether or not logging is enabled
     *
     * @var array<string,bool>
     */
    private static array $can_log = array();

    /**
     * Logger instance
     *
     * @var WC_Logger_Interface
     */
    private static WC_Logger_Interface $logger;

    /**
     * Default title for the gateway which users will see.
     *
     * @var string
     */
    protected string $user_title;

    /**
     * Default description for the gateway which users will see.
     *
     * @var string
     */
    protected string $user_description;

    /**
     * Constructor for the gateway.
     */
    public function __construct() {
        $this->init_base_props();
        $this->init_form_fields();
        $this->init_settings();
        $this->init_hooks();
    }

    /**
     * Get base props needed for gateway functioning.
     *
     * @return array{
     *   id: string,
     *   plugin_id: string,
     *   method_title: string,
     *   method_description: string,
     *   user_title: string,
     *   user_description: string,
     *   has_fields?: bool,
     *   icon?: string,
     *   order_button_text?: string,
     *   supports?: array<int,string>
     * }
     */
    abstract protected function get_base_props(): array;

    /**
     * Get raw form fields.
     *
     * @return array
     */
    abstract protected function get_raw_form_fields(): array;

    /**
     * {@inheritDoc}
     */
    protected function get_hooks(): array {
        return array(
            "woocommerce_update_options_payment_gateways_{$this->id}" => array( 'action', 'process_admin_options', 10 ),
            'wc_payment_gateways_initialized' => array( 'action', 'init_gateway', 100 ),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function get_admin_vars(): array {
        return array(
            'page' => 'wc-settings',
            'rest' => '/payment_gateways',
            'tab'  => 'checkout',
        );
    }

    /**
     * Get the default values for base props.
     *
     * @return array
     */
    protected function get_base_defaults(): array {
        return array(
            'has_fields'        => false,
            'icon'              => null,
            'order_button_text' => null,
            'supports'          => array( 'products' ),
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function get_base_form_fields(): array {
        //phpcs:disable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
        return array(
            'enabled'     => array(
                'title'   => __( 'Enable/Disable', 'woocommerce' ),
                'type'    => 'checkbox',
                'label'   => fn() => sprintf(
                    '%s %s %s',
                    __( 'Enable', 'woocommerce' ),
                    $this->method_title,
                    __( 'Payment gateway', 'woocommerce' ),
                ),
                'default' => 'no',
            ),
            'title'       => array(
                'title'       => __( 'Title', 'woocommerce' ),
                'type'        => 'safe_text',
                'description' => __(
                    'This controls the title which the user sees during checkout.',
                    'woocommerce',
                ),
                'default'     => $this->user_title,
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'woocommerce' ),
                'type'        => 'textarea',
                'description' => __(
                    'Payment method description that the customer will see on your checkout.',
                    'woocommerce',
                ),
                'default'     => $this->user_description,
                'desc_tip'    => true,
            ),
        );
        //phpcs:enable SlevomatCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder
    }

    /**
     * Get gateway options.
     *
     * @return array
     */
    public function get_options(): array {
        return array_combine(
            array_keys( $this->settings ),
            array_map( fn( $v ) => $this->$v, array_keys( $this->settings ) ),
        );
    }

    /**
     * Loads settings from the database.
     */
    protected function on_init_settings(): void {
        $this->enabled     = wc_bool_to_string( $this->get_option( 'enabled' ) );
        $this->title       = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );

        self::$can_log[ $this->id ] = $this->debug ?? false;
    }

    /**
     * Initializes the gateway.
     *
     * Hooked to `wc_payment_gateways_initialized`.
     */
    public function init_gateway(): void {
        // Noop.
    }

    /**
     * Checks if the gateway is available for use.
     *
     * @return WP_Error|bool
     */
    public function is_valid_for_use(): \WP_Error|bool {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function admin_options() {
        $is_available = $this->is_valid_for_use();

        if ( ! is_wp_error( $is_available ) ) {
            return parent::admin_options();
        }

        ?>
        <div class="inline error">
            <p>
                <strong>
                    <?php esc_html_e( 'Gateway disabled', 'woocommerce' ); ?>
                </strong>:
                <?php echo esc_html( $is_available->get_error_message() ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Logging method.
     *
     * @param string $message Log message.
     * @param string $level Optional. Default 'info'. Possible values:
     *                      emergency|alert|critical|error|warning|notice|info|debug.
     *
     * @return static
     */
    final public function log( $message, $level = 'info' ): static {
        if ( self::$can_log[ $this->id ] ) {
            $this
            ->logger()
            ->log( $level, $message, array( 'source' => $this->id ) );
        }

        return $this;
    }

    /**
     * Get logger instance.
     *
     * @return WC_Logger_Interface
     */
    final public function logger(): WC_Logger_Interface {
        return self::$logger ??= wc_get_logger();
    }
}
