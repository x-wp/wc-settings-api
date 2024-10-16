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

		$xwc_settings_initialized = \XWC\Admin\Image_Select_Field::instance();
	}

    did_action( 'init' ) && ! doing_action( 'init' )
        ? xwc_settings_init()
        : add_action( 'init', 'xwc_settings_init', 99, 0 );

endif;
