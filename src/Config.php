<?php
namespace WPGen;

/**
 * Class Config
 *
 * @package WPGen
 */
class Config
{
    /**
     * @var array Loaded options.
     */
    protected $loaded = [];

    /**
     * @var static
     */
    protected static $instance;

    /**
     * @return Config
     */
    public static function get() {
        if ( !static::$instance ) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Load a configuration file from the /config directory.
     *
     * @param string $name
     * @return mixed
     */
    protected function loadConfigFile( $name = '' ) {
        $path = APP_ROOT . 'config/' . $name . '.php';
        if ( isset( $this->loaded[$path] ) ) {
            return $this->loaded[$path];
        }
        $this->loaded[$path] = include $path;
        return $this->loaded[$path];
    }

    /**
     * @return mixed string Get application logo.
     */
    public function logo() {
        return $this->loadConfigFile( 'logo' );
    }

    /**
     * @return array Plugin options.
     */
    public function pluginOptions() {
        return $this->loadConfigFile( 'plugin-options' );
    }

    /**
     * @return array Module options.
     */
    public function bbModuleOptions() {
        return $this->loadConfigFile( 'beaver-builder-module-options' );
    }

    /**
     * @return array Module options.
     */
    public function bbFormSettingsOptions() {
        return $this->loadConfigFile( 'beaver-builder-settings-form-options' );
    }


    /**
     * @return array Component options.
     */
    public function componentOptions() {
        return $this->loadConfigFile( 'component-options' );
    }

    /**
     * @return array Post type options.
     */
    public function postTypeOptions() {
        return $this->loadConfigFile( 'post-type-options' );
    }

    public function metaBoxOptions() {
        return $this->loadConfigFile( 'meta-box-options' );
    }

    public function postTypeListTableOptions() {
        return $this->loadConfigFile( 'post-type-list-table-options' );
    }

    /**
     * @return array Taxonomy options.
     */
    public function taxonomyOptions() {
        return $this->loadConfigFile( 'taxonomy-options' );
    }

    public function taxonomyCustomFieldsOptions() {
        return $this->loadConfigFile( 'taxonomy-custom-fields-options' );
    }

    /**
     * @return array Admin options.
     */
    public function adminOptions() {
        return $this->loadConfigFile( 'admin-options' );
    }
}