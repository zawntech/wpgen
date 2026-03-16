<?php
namespace {{ plugin_namespace }}\Admin;

use {{ plugin_namespace }}\Abstract\SettingsAbstract;

/**
 * {{ plugin_name }} Settings.
 *
 * Class Settings
 */
class Settings extends SettingsAbstract
{
    const OPTION_KEY = '{{ plugin_constants_prefix }}SETTINGS';

    const PREFIX = '{{ plugin_filter_prefix }}';

    /**
     * @var array Keys whose values should be encrypted at rest.
     */
    protected $encrypted_keys = [];

    /**
     * @return array The plugin defaults.
     */
    protected function get_defaults() {
        $settings = [
            'example_option' => '',
        ];
        return apply_filters( static::PREFIX . 'default_settings', $settings );
    }

    /**
     * @return string
     */
    public function example_option() {
        return $this->get_setting( 'example_option' );
    }
}
