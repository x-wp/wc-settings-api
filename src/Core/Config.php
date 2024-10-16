<?php
/**
 * XWC_Config class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Settings
 */

use XWC\Interfaces\Config_Repository;

/**
 * Config service class
 *
 * @template T of array
 */
class XWC_Config implements Config_Repository {
    /**
     * DB option key format
     *
     * @var array<string,string>
     */
    protected array $formats = array(
        'api'  => '%s_api_settings_',
        'page' => '%s_settings_',
    );

    /**
     * Settings array
     *
     * @var T
     */
    protected array $settings = array();

    /**
     * Constructor
     *
     * @param array{page?: string, api?:string} $args The arguments to pass to the constructor.
     * @param T                                 $defaults The default settings.
     *
     * @throws \InvalidArgumentException If no settings are provided.
     */
    public function __construct( array $args, protected array $defaults = array() ) {
        if ( ! isset( $args['api'] ) && ! isset( $args['page'] ) ) {
            throw new \InvalidArgumentException(
                'You must provide either an WC Settings_API or a WC_Settings_Page configuration',
            );
        }

        foreach ( $args as $type => $prefix ) {
            $this->parse_settings( $type, $prefix );
        }
    }

    /**
     * Load the settings.
     *
     * @param 'page'|'api' $type Database option key format.
     * @param string       $prefix The settings prefix.
     */
    protected function parse_settings( string $type, string $prefix ) {
        $key = \sprintf( $this->formats[ $type ], $prefix );

        foreach ( $this->load_settings( $key ) as $section => $fields ) {
            $this->parse_fields( $this->settings, $section, $fields );
        }
    }

    /**
     * Load the settings from the database
     *
     * @param  string $option_key The option key base.
     * @return array<string, mixed>
     */
    protected function load_settings( string $option_key ): array {
        $unparsed = $this->get_settings_from_db( $option_key );
        $parsed   = array();

        foreach ( $unparsed as [ 'section' => $section, 'options' => $options ] ) {
            $opts = &$parsed;
            $subs = \explode( '--', $section );

            foreach ( $subs as $sub ) {
                $opts[ $sub ] ??= array();

                $opts = &$opts[ $sub ];
            }

            $opts = \maybe_unserialize( $options );

        }

        return $parsed;
    }

    /**
     * Get the options from the database
     *
     * @param  string $option_key The option key base.
     * @return array<int,array{section:string,options:string}>()
     */
	protected function get_settings_from_db( string $option_key ) {
		global $wpdb;

		return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT REPLACE(option_name, %s, %s) as section, option_value as options FROM %i WHERE option_name LIKE %s',
                $option_key,
                '',
                $wpdb->options,
                $wpdb->esc_like( $option_key ) . '%',
            ),
            \ARRAY_A,
		);
	}

    /**
     * Parse the fields
     *
     * @param  array  $parsed The parsed fields.
     * @param  string $key    The key to parse.
     * @param  mixed  $fields The fields to parse.
     * @param  int    $level  The nesting level.
     */
    protected function parse_fields( &$parsed, string $key, mixed $fields, int $level = 0 ) {
        if ( $this->has_subfields( $fields ) ) {
            $parsed[ $key ] ??= array();

            foreach ( $fields as $field_key => $field_val ) {
                $this->parse_fields( $parsed[ $key ], $field_key, $field_val, $level + 1 );
            }

            return;
        }

        if ( $this->is_split( $key, $level ) ) {
            [ $first, $rest ]   = $this->parse_subkeys( $key );
            $parsed[ $first ] ??= array();

            $this->parse_fields( $parsed[ $first ], $rest, $fields, $level + 1 );

            return;
        }

        $val = $this->parse_options( $fields );

        $parsed[ $key ] = $val;
    }

    /**
     * Check if the value has subfields
     *
     * @param  mixed $value The value to check.
     * @return bool         True if the value has subfields, false otherwise.
     */
    protected function has_subfields( mixed $value ): bool {
        return \is_array( $value ) && ! \is_numeric( \key( $value ) );
    }

    /**
     * Check if the key should be split into subkeys.
     *
     * @param  string $key   The key to check.
     * @param  int    $level The nesting level.
     * @return bool          True if the key is split, false otherwise.
     */
    protected function is_split( string $key, int $level ): bool {
        return \preg_match( '/^([a-z]+)_?-_?([a-z_]+)$/', $key ) && $level > 0;
    }

    /**
     * Parse the subgroups
     *
     * @param  string $key    The group to parse.
     * @return array
     */
    protected function parse_subkeys( string $key ): array {
        $expl  = \array_map( static fn( $v ) => \trim( $v, '_' ), \explode( '-', $key ) );
        $first = \array_shift( $expl );
        $rest  = \implode( '_-_', $expl );

        return array( $first, $rest );
    }

    /**
     * Parse the option value
     *
     * @param  mixed $opts The options to parse.
     * @return mixed       The parsed options.
     */
    protected function parse_options( $opts ) {
        if ( ! \is_scalar( $opts ) && ! \is_null( $opts ) ) {
            return $opts;
        }

        $opts ??= '';

        $lopt = \is_string( $opts ) ? \strtolower( \trim( $opts ) ) : $opts;
        $yes  = array( '1', 'yes', 'true', 'on' );
        $no   = array( '0', 'no', 'false', 'off' );

        return match ( true ) {
            \in_array( $lopt, $yes, true ) => true,
            \in_array( $lopt, $no, true )  => false,
            \xwp_is_int_str( $lopt )       => \intval( $lopt ),
            \xwp_is_float_str( $lopt )     => \floatval( $lopt ),
            default                        => $opts,
        };
    }

    /**
     * Get the settings array
     *
     * @param  string ...$keys The keys to get.
     * @return array
     */
    protected function get_sub( string ...$keys ): mixed {
        $opts = $this->settings;

        foreach ( $keys as $k ) {
            if ( ! isset( $opts[ $k ] ) ) {
                return null;
            }

            $opts = $opts[ $k ];
        }

        return $opts;
    }

    /**
     * Get the settings array
     *
     * @return array
     */
    public function all(): array {
        return $this->settings;
    }

    /**
     * Get the settings array
     *
     * @param  string $key The setting to get.
     * @param  mixed  $def The default value to return.
     * @return mixed       The settings array.
     */
	public function get( string $key, mixed $def = null ): mixed {
        return $this->get_sub( ...\array_filter( \explode( '.', $key ) ) ) ?? $def;
	}

    /**
     * Check if a key exists in the settings array
     *
     * @param  string $key Key to check. Optionally dot separated for nested values.
     * @return bool
     */
    public function has( string $key ): bool {
        $uniq = \uniqid( 'key_', true );
        return $uniq !== $this->get( $key, $uniq );
    }

    /**
     * Set a value in the settings array
     *
     * @param string $key Key to set. Optionally dot separated for nested values.
     * @param mixed  $val Value to set.
     */
    public function set( string $key, mixed $val ): void {
        $key  = \array_filter( \explode( '.', $key ) );
        $opts = &$this->settings;

        foreach ( $key as $k ) {
            $opts[ $k ] ??= array();
            $opts         = &$opts[ $k ];
        }

		$opts = $val;
    }
}
