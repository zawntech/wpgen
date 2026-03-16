<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractHttpClient;

class {{ api_class }}Api extends AbstractHttpClient
{
    protected function get_api_base_url(): string {
        return 'https://example.com/api/';
    }

    protected function get_http_args(): array {
        return [
            'headers' => [
                'Authorization' => 'Bearer your-api-key',
            ],
        ];
    }

    /**
     * Example GET request.
     *
     * @param array $params
     * @return array|false
     * @throws \Exception
     */
    public function get_something( $params = [] ) {
        $response = $this->get( 'something', $params );

        if ( is_wp_error( $response ) ) {
            error_log( print_r( $response, true ) );
            return false;
        }

        if ( 200 !== $response['response']['code'] ) {
            error_log( print_r( $response, true ) );
            throw new \Exception( $response['response']['message'] );
        }

        return json_decode( $response['body'], true );
    }

    /**
     * Example POST request.
     *
     * @param array $data
     * @return array|false
     */
    public function post_something( $data = [] ) {
        $response = $this->post( 'something', $data );

        if ( is_wp_error( $response ) ) {
            error_log( print_r( $response, true ) );
            return false;
        }

        if ( 200 !== $response['response']['code'] ) {
            error_log( print_r( $response, true ) );
            return false;
        }

        return json_decode( $response['body'], true );
    }
}
