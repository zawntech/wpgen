<?php
namespace {{ plugin_namespace }}\API;

class ExampleController extends AbstractApiController
{
    public function register_routes() {

        // POST {$base}/example-action
        register_rest_route( $this->namespace, 'example-action', [
            'methods' => 'POST',
            'callback' => [$this, 'example_action'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );
    }

    public function example_action( \WP_REST_Request $request ) {
        $some_param = $request->get_param('some_param');
        return rest_ensure_response('Great job!');
    }
}