<?php
namespace {{ plugin_namespace }}\API;

abstract class AbstractApiController
{
    protected $namespace = '{{ api_namespace }}/v1';

    public function __construct() {
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    public function get_base_url() {
        return rest_url( $this->namespace . '/' );
    }

    public function check_permissions() {
        return true;
    }

    public static function base_url() {
        return (new static)->get_base_url();
    }
}