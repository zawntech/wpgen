<?php
namespace {{ plugin_namespace }}\Abstract;

/**
 * Base class for transactional emails.
 *
 * Concrete emails extend this class, declare a subject and a template name,
 * then are sent like:
 *
 *   ( new MyEmail( $to_address, [ 'foo' => 'bar' ] ) )->send();
 *
 * Templates live under assets/templates/emails/ and are wrapped by _layout.php.
 */
abstract class AbstractEmail
{
    protected string $to;
    protected array $data = [];

    public function __construct( string $to, array $data = [] ) {
        $this->to   = $to;
        $this->data = $data;
    }

    abstract protected function subject(): string;

    /**
     * Template file name (without .php) under assets/templates/emails/.
     */
    abstract protected function template(): string;

    protected function from_name(): string {
        return get_bloginfo( 'name' );
    }

    protected function from_address(): string {
        $host = wp_parse_url( home_url(), PHP_URL_HOST );
        return 'noreply@' . ( $host ?: 'localhost' );
    }

    protected function headers(): array {
        return [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $this->from_name(), $this->from_address() ),
        ];
    }

    public function send(): bool {
        $body = $this->render();
        if ( '' === $body ) {
            return false;
        }
        return wp_mail( $this->to, $this->subject(), $body, $this->headers() );
    }

    protected function render(): string {
        $template_file = {{ plugin_constants_prefix }}DIR . 'assets/templates/emails/' . $this->template() . '.php';
        if ( ! file_exists( $template_file ) ) {
            return '';
        }

        $data    = $this->data;
        $subject = $this->subject();

        ob_start();
        include $template_file;
        $content = ob_get_clean();

        $layout_file = {{ plugin_constants_prefix }}DIR . 'assets/templates/emails/_layout.php';
        if ( ! file_exists( $layout_file ) ) {
            return $content;
        }

        ob_start();
        include $layout_file;
        return ob_get_clean();
    }
}
