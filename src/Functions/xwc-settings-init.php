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

        if ( ! is_admin() || isset( $xwc_settings_initialized ) ) {
            return;
        }

        \XWC\Admin\Image_Select_Field::instance();
        \XWC\Admin\Repeater_Text_Settings_Field::instance();

        $xwc_settings_initialized = true;
    }

    did_action( 'plugins_loaded' ) && ! doing_action( 'plugins_loaded' )
        ? xwc_settings_init()
        : add_action( 'plugins_loaded', 'xwc_settings_init', 9999, 0 );

endif;
