<?php //phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
/**
 * Custom_Field class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Admin
 */

namespace XWC\Admin;

use XWP\Helper\Traits\Singleton_Ex;

/**
 * Base class for custom fields
 */
abstract class Custom_Field {
    use Singleton_Ex;

    /**
     * Did output flag
     *
     * @var bool
     */
    protected bool $did_output = false;

    /**
     * Constructor
     */
    private function __construct() {
        \add_filter( $this->get_hook(), array( $this, 'output_field' ), 99, $this->get_args() );
        \add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_option' ), 10, 3 );
        \add_filter( 'admin_footer', array( $this, 'output_css' ), 99, 0 );
        \add_filter( 'admin_footer', array( $this, 'output_js' ), 99, 0 );
    }

    /**
     * Get hook definitions.
     *
     * @return string
     */
    private function get_hook(): string {
        $hook = 'admin' === $this->get_type()
            ? 'woocommerce_generate_%s_html'
            : 'woocommerce_admin_field_%s';

        return \sprintf( $hook, $this->get_name() );
    }

    /**
     * Get hook arguments
     *
     * @return int
     */
    private function get_args(): int {
        return 'admin' === $this->get_type()
            ? 4
            : 1;
    }

    /**
     * Get field type
     *
     * @return string
     */
    abstract protected function get_name(): string;

    /**
     * Get field type
     *
     * @return 'admin'|'settings'
     */
    abstract protected function get_type(): string;

    /**
     * Get field css
     *
     * @return string
     */
    protected function get_css(): string {
        return '';
    }

    /**
     * Get field js
     *
     * @return string
     */
    protected function get_js(): string {
        return '';
    }

    /**
     * Set did output flag
     *
     * @return void
     */
    public function set_output(): void {
        $this->did_output = true;
    }

    /**
     * Adds custom styles
     */
    public function output_css() {
        if ( ! $this->did_output || ! $this->get_css() ) {
            return;
        }

        //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, Generic.Strings.UnnecessaryStringConcat
        \printf(
            '<' . '%1$s id="%3$s-field-css">%2$s</%1$s' . '>',
            'style',
            $this->get_css(),
            $this->get_name(),
        );
        //phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped, Generic.Strings.UnnecessaryStringConcat
    }

    /**
     * Adds custom scripts
     */
    public function output_js() {
        if ( ! $this->did_output || ! $this->get_js() ) {
            return;
        }

        //phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped, Generic.Strings.UnnecessaryStringConcat
        \printf(
            '<' . '%1$s id="%3$s-field-js">%2$s</%1$s' . '>',
            'script',
            $this->get_js(),
            $this->get_name(),
        );
        //phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped, Generic.Strings.UnnecessaryStringConcat
    }

    /**
     * Sanitize custom field callback wrapper
     *
     * @param  mixed               $value     The value to sanitize.
     * @param  array<string,mixed> $option    The field option.
     * @param  mixed               $raw_value The raw value.
     * @return mixed
     */
    public function sanitize_option( $value, $option, $raw_value ) {
        if ( $this->get_name() !== $option['type'] || ( $option['nosanitize'] ?? false ) ) {
            return $value;
        }

        return $this->sanitize( $value, $option, $raw_value );
    }

    /**
     * Sanitize the field value
     *
     * @param  mixed               $value     The value to sanitize.
     * @param  array<string,mixed> $option    The field option.
     * @param  mixed               $raw_value The raw value.
     * @return mixed
     */
    protected function sanitize( mixed $value, array $option, mixed $raw_value ): mixed {
        return $value;
    }
}
