<?php
namespace {{ plugin_namespace }}\Abstract;

abstract class AbstractHttpClient
{
    /**
     * Return the base URL for the API.
     * @return string
     */
    abstract protected function get_api_base_url(): string;

    /**
     * Return base WP HTTP args (headers, auth, etc.).
     * @return array
     */
    abstract protected function get_http_args(): array;

    /**
     * Perform a GET request.
     *
     * @param string $endpoint
     * @param array  $params   Query parameters.
     * @return array|\WP_Error
     */
    public function get( $endpoint, $params = [] ) {
        $url = trailingslashit( $this->get_api_base_url() ) . $endpoint;

        if ( !empty( $params ) ) {
            $url .= '?' . http_build_query( $params );
        }

        return wp_remote_get( $url, $this->get_http_args() );
    }

    /**
     * Perform a POST request.
     *
     * @param string $endpoint
     * @param array  $params   Request body.
     * @return array|\WP_Error
     */
    public function post( $endpoint, $params = [] ) {
        $args = $this->get_http_args();
        $args['body'] = $params;

        return wp_remote_post( trailingslashit( $this->get_api_base_url() ) . $endpoint, $args );
    }
}
