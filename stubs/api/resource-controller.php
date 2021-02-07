<?php
namespace {{ plugin_namespace }}\API;

class {{ resource-plural-class-name }}Controller extends AbstractApiController
{
    public function __construct() {
        add_action( 'rest_api_init', [$this, 'register_routes'] );
    }

    public function register_routes() {

        // Get the resource index...
        // GET {base-url}/{{ resource-plural }}
        register_rest_route( $this->namespace, '{{ resource-plural }}', [
            'methods' => 'GET',
            'callback' => [$this, 'index'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // Get a specific item by ID...
        // GET {base-url}/{{ resource-plural }}/{id}
        register_rest_route( $this->namespace, '{{ resource-plural }}/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // Create an item...
        // POST {base-url}/{{ resource-plural }}
        register_rest_route( $this->namespace, '{{ resource-plural }}', [
            'methods' => 'POST',
            'callback' => [$this, 'create'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // Update an item...
        // PUT {base-url}/{{ resource-plural }}/ID
        register_rest_route( $this->namespace, '{{ resource-plural }}/(?P<id>\d+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'update'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );

        // Delete an item...
        // DELETE {base-url}/{{ resource-plural }}/ID
        register_rest_route( $this->namespace, '{{ resource-plural }}/(?P<id>\d+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'delete'],
            'permission_callback' => [$this, 'check_permissions'],
        ] );
    }

    public function index( \WP_REST_Request $request ) {
        // Get all (or paginated) items and/or paginate request.
        return rest_ensure_response([]);
    }

    public function get( \WP_Rest_Request $request ) {
        $resource_id = $request->id;
        return rest_ensure_response([]);
    }

    public function create( \WP_Rest_Request $request ) {
        return rest_ensure_response('created');
    }

    public function update( \WP_Rest_Request $request ) {
        $resource_id = $request->id;
        return rest_ensure_response('updated');
    }

    public function delete( \WP_REST_Request $request ) {
        $resource_id = $request->id;
        return rest_ensure_response('deleted');
    }
}