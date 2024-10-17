<?php
/**
 * Config_Repository interface file.
 *
 * @package eXtended WooCommerce
 * @subpackage Settings
 */

namespace XWC\Interfaces;

/**
 * Interface describing a config repository
 */
interface Config_Repository {
    /**
     * Reload the config array
     *
     * @return static
     */
    public function reload(): static;

    /**
     * Get the entire config array
     *
     * @return array
     */
    public function all(): array;

    /**
     * Check if a key exists in the config array
     *
     * @param string $key Key to check. Optionally dot separated for nested values.
     * @return bool
     */
    public function has( string $key ): bool;

    /**
     * Get a value from the config array
     *
     * @param string $key Key to get. Optionally dot separated for nested values.
     * @param mixed  $def Default value to return if key is not found.
     * @return mixed
     */
    public function get( string $key, mixed $def = null ): mixed;

    /**
     * Set a value in the config array
     *
     * @param string $key Key to set. Optionally dot separated for nested values.
     * @param mixed  $val Value to set.
     */
    public function set( string $key, mixed $val ): void;
}
