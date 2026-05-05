<?php
/**
 * Settings init functions.
 *
 * @package eXtended WooCommerce
 * @subpackage Settings
 */

if ( ! function_exists( 'xwc_settings_init' ) && function_exists( 'add_action' ) ) :
    /**
     * Initializes the settings.
     */
    function xwc_settings_init(): void {
        static $xwc_settings_initialized;

        if ( isset( $xwc_settings_initialized ) || ! xwc_settings_should_init_admin_fields() ) {
            return;
        }

        \XWC\Admin\Image_Select_Field::instance();
        \XWC\Admin\Repeater_Text_Settings_Field::instance();

        $xwc_settings_initialized = true;
    }

    /**
     * Checks whether the custom admin fields should be registered for this request.
     */
    function xwc_settings_should_init_admin_fields(): bool {
        return \is_admin() &&
            ! xwc_settings_is_non_interactive_request() &&
            xwc_settings_is_wc_admin_settings_page() &&
            xwc_settings_has_wc_settings_dependencies();
    }

    /**
     * Checks whether this request should never register admin field hooks.
     */
    function xwc_settings_is_non_interactive_request(): bool {
        return xwc_settings_is_ajax_request() ||
            xwc_settings_is_cron_request() ||
            xwc_settings_is_cli_request() ||
            xwc_settings_is_rest_request();
    }

    /**
     * Checks whether WordPress is serving an AJAX request.
     */
    function xwc_settings_is_ajax_request(): bool {
        return \function_exists( 'wp_doing_ajax' ) && \wp_doing_ajax();
    }

    /**
     * Checks whether WordPress is serving a cron request.
     */
    function xwc_settings_is_cron_request(): bool {
        return \function_exists( 'wp_doing_cron' ) && \wp_doing_cron();
    }

    /**
     * Checks whether this request is running through WP-CLI.
     */
    function xwc_settings_is_cli_request(): bool {
        return \defined( 'WP_CLI' ) && \WP_CLI;
    }

    /**
     * Checks whether this admin request targets the WooCommerce settings page.
     */
    function xwc_settings_is_wc_admin_settings_page(): bool {
        return \function_exists( 'is_wc_admin_settings_page' ) && \is_wc_admin_settings_page();
    }

    /**
     * Checks whether WooCommerce settings dependencies are available.
     */
    function xwc_settings_has_wc_settings_dependencies(): bool {
        return \class_exists( 'WC_Admin_Settings', false ) &&
            \class_exists( 'WC_Settings_API', false ) &&
            \function_exists( 'wc_string_to_array' ) &&
            \function_exists( 'wc_implode_html_attributes' );
    }

    /**
     * Checks whether WordPress is serving a REST request.
     */
    function xwc_settings_is_rest_request(): bool {
        return ( \function_exists( 'wp_is_serving_rest_request' ) && \wp_is_serving_rest_request() ) ||
            ( \defined( 'REST_REQUEST' ) && \REST_REQUEST );
    }

    did_action( 'wp_loaded' ) && ! doing_action( 'wp_loaded' )
        ? xwc_settings_init()
        : add_action( 'wp_loaded', 'xwc_settings_init', 0, 0 );

endif;
