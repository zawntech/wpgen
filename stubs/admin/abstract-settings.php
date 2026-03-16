<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class SettingsAbstract
{
    /**
     * WordPress option key for storing settings.
     * Must be defined by the extending class.
     */
    const OPTION_KEY = '';

    /**
     * Prefix for filter hooks.
     * Must be defined by the extending class.
     */
    const PREFIX = '';

    /**
     * Constant used as the encryption salt.
     * Must be defined by the extending class if $encrypted_keys is not empty.
     */
    const ENCRYPTION_CONSTANT = '';

    /**
     * @var array Keys whose values should be encrypted at rest.
     */
    protected $encrypted_keys = [];

    /**
     * @var array An array of all options.
     */
    protected $all = [];

    /**
     * Settings constructor. Validates configuration and loads options.
     *
     * @throws \RuntimeException If required constants are not defined or encryption is misconfigured.
     */
    protected function __construct() {
        $this->validate();

        $options = get_option( static::OPTION_KEY );
        $this->all = wp_parse_args( $options, $this->get_defaults() );

        // Decrypt any encrypted values after loading.
        foreach ( $this->encrypted_keys as $key ) {
            if ( isset( $this->all[$key] ) && ! empty( $this->all[$key] ) ) {
                $this->all[$key] = $this->decrypt( $this->all[$key] );
            }
        }
    }

    /**
     * Validate that required constants and configuration are properly defined.
     *
     * @throws \RuntimeException If OPTION_KEY, PREFIX, or encryption key is not defined.
     */
    protected function validate() {
        if ( empty( static::OPTION_KEY ) ) {
            throw new \RuntimeException( static::class . '::OPTION_KEY must be defined.' );
        }

        if ( empty( static::PREFIX ) ) {
            throw new \RuntimeException( static::class . '::PREFIX must be defined.' );
        }

        if ( ! empty( $this->encrypted_keys ) && empty( static::ENCRYPTION_CONSTANT ) ) {
            throw new \RuntimeException( static::class . '::ENCRYPTION_CONSTANT must be defined when using encrypted keys.' );
        }
    }

    /**
     * @return static Get a new, preloaded instance of settings.
     */
    public static function get() {
        return new static;
    }

    /**
     * @return array The default settings values.
     */
    abstract protected function get_defaults();

    /**
     * @param array $values An array of new values to store.
     */
    public function set( $values = [] ) {
        if ( empty( $values ) ) {
            return;
        }

        foreach ( $values as $key => $value ) {
            $this->all[$key] = $value;
        }

        // Encrypt designated keys before persisting.
        $to_store = $this->all;
        foreach ( $this->encrypted_keys as $key ) {
            if ( isset( $to_store[$key] ) && ! empty( $to_store[$key] ) ) {
                $to_store[$key] = $this->encrypt( $to_store[$key] );
            }
        }

        update_option( static::OPTION_KEY, $to_store );
    }

    /**
     * @return array An array of all options.
     */
    public function all() {
        return $this->all;
    }

    /**
     * Get a filtered setting value.
     *
     * @param string $key Setting key.
     * @return mixed
     */
    protected function get_setting( $key ) {
        $value = $this->all[$key] ?? null;
        return apply_filters( static::PREFIX . 'get_setting', $value, $key );
    }

    /**
     * Get a setting value cast as an array.
     *
     * @param string $key
     * @return array
     */
    protected function get_setting_as_array( $key ) {
        $value = $this->get_setting( $key );
        if ( ! is_array( $value ) ) {
            return [];
        }
        return $value;
    }

    /**
     * Get the raw encryption salt.
     *
     * @return string
     */
    protected function get_encryption_salt() {
        return static::ENCRYPTION_CONSTANT;
    }

    /**
     * Get the encryption key derived from the salt.
     *
     * @return string
     */
    protected function get_encryption_key() {
        return hash( 'sha256', $this->get_encryption_salt() );
    }

    /**
     * Encrypt a value using AES-256-CBC.
     *
     * @param string $value
     * @return string Base64-encoded IV + ciphertext.
     */
    protected function encrypt( $value ) {
        $key = $this->get_encryption_key();
        $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'aes-256-cbc' ) );
        $encrypted = openssl_encrypt( $value, 'aes-256-cbc', $key, 0, $iv );

        return base64_encode( $iv . '::' . $encrypted );
    }

    /**
     * Decrypt an AES-256-CBC encrypted value.
     *
     * @param string $value Base64-encoded IV + ciphertext.
     * @return string|false The decrypted value, or false on failure.
     */
    protected function decrypt( $value ) {
        $key = $this->get_encryption_key();
        $decoded = base64_decode( $value );

        if ( false === $decoded || false === strpos( $decoded, '::' ) ) {
            return $value;
        }

        list( $iv, $encrypted ) = explode( '::', $decoded, 2 );

        return openssl_decrypt( $encrypted, 'aes-256-cbc', $key, 0, $iv );
    }
}
