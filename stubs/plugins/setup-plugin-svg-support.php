<?php
namespace {{ plugin_namespace }}\Setup;

use enshrined\svgSanitize\Sanitizer;

/**
 * Adds SVG upload support to the media library.
 *
 * SVGs are an XSS vector, so every uploaded file is sanitized (enshrined/svg-sanitize,
 * the same library used by the Safe SVG plugin) before it is stored. The component also
 * teaches WordPress to verify the file type and to preview SVGs in the admin.
 */
class SvgSupport
{
    public function __construct() {
        add_filter( 'upload_mimes', [ $this, 'allow_svg_mime' ] );
        add_filter( 'wp_check_filetype_and_ext', [ $this, 'fix_svg_filetype' ], 10, 4 );
        add_filter( 'wp_handle_upload_prefilter', [ $this, 'sanitize_svg_upload' ] );
        add_filter( 'wp_prepare_attachment_for_js', [ $this, 'prepare_svg_for_js' ], 10, 2 );
        add_action( 'admin_head', [ $this, 'admin_thumbnail_css' ] );
    }

    /**
     * Whitelist the SVG mime type for uploads.
     */
    public function allow_svg_mime( $mimes ) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    /**
     * WordPress can't reliably fingerprint an SVG's real mime type, so confirm the
     * type from the extension (against the allowed mimes) and let the upload through.
     */
    public function fix_svg_filetype( $data, $file, $filename, $mimes ) {
        $ext = isset( $data['ext'] ) ? $data['ext'] : '';

        if ( '' === $ext ) {
            $check = wp_check_filetype( $filename, $mimes );
            $ext   = $check['ext'];
        }

        if ( 'svg' === $ext ) {
            $data['ext']  = 'svg';
            $data['type'] = 'image/svg+xml';
        }

        return $data;
    }

    /**
     * Sanitize the SVG contents before the file is moved into uploads. A file that
     * can't be sanitized is rejected with an error.
     */
    public function sanitize_svg_upload( $file ) {
        if ( empty( $file['type'] ) || 'image/svg+xml' !== $file['type'] ) {
            return $file;
        }

        if ( ! current_user_can( 'upload_files' ) ) {
            $file['error'] = __( 'You are not allowed to upload SVG files.', {{ plugin_constants_prefix }}TEXT_DOMAIN );
            return $file;
        }

        if ( empty( $file['tmp_name'] ) || ! is_readable( $file['tmp_name'] ) ) {
            return $file;
        }

        $dirty = file_get_contents( $file['tmp_name'] );

        if ( false === $dirty ) {
            $file['error'] = __( 'The SVG file could not be read.', {{ plugin_constants_prefix }}TEXT_DOMAIN );
            return $file;
        }

        $sanitizer = new Sanitizer();
        $clean     = $sanitizer->sanitize( $dirty );

        if ( false === $clean ) {
            $file['error'] = __( 'This SVG could not be sanitized and was not uploaded.', {{ plugin_constants_prefix }}TEXT_DOMAIN );
            return $file;
        }

        file_put_contents( $file['tmp_name'], $clean );

        return $file;
    }

    /**
     * Give the media modal a usable preview + intrinsic dimensions for SVGs, which
     * otherwise show up blank because WordPress generates no image sub-sizes for them.
     */
    public function prepare_svg_for_js( $response, $attachment ) {
        if ( 'image/svg+xml' !== get_post_mime_type( $attachment ) ) {
            return $response;
        }

        $response['image'] = [ 'src' => $response['url'] ];
        $response['thumb'] = [ 'src' => $response['url'] ];

        $dimensions = $this->get_svg_dimensions( get_attached_file( $attachment->ID ) );

        if ( $dimensions ) {
            $response['width']       = $dimensions['width'];
            $response['height']      = $dimensions['height'];
            $response['orientation'] = $dimensions['width'] >= $dimensions['height'] ? 'landscape' : 'portrait';
        }

        return $response;
    }

    /**
     * Read intrinsic dimensions from an SVG's width/height, falling back to viewBox.
     */
    protected function get_svg_dimensions( $path ) {
        if ( ! $path || ! file_exists( $path ) ) {
            return null;
        }

        $xml = @simplexml_load_file( $path );

        if ( false === $xml ) {
            return null;
        }

        $attributes = $xml->attributes();
        $width      = isset( $attributes->width ) ? (float) $attributes->width : 0;
        $height     = isset( $attributes->height ) ? (float) $attributes->height : 0;

        if ( ( ! $width || ! $height ) && isset( $attributes->viewBox ) ) {
            $viewbox = preg_split( '/[\s,]+/', trim( (string) $attributes->viewBox ) );

            if ( is_array( $viewbox ) && count( $viewbox ) === 4 ) {
                $width  = $width ? $width : (float) $viewbox[2];
                $height = $height ? $height : (float) $viewbox[3];
            }
        }

        if ( ! $width || ! $height ) {
            return null;
        }

        return [
            'width'  => (int) round( $width ),
            'height' => (int) round( $height ),
        ];
    }

    /**
     * Constrain SVG thumbnails so they render at a sane size in the media grid/list.
     */
    public function admin_thumbnail_css() {
        echo '<style id="{{ plugin_text_domain }}-svg-admin">'
            . '.attachment .thumbnail img[src$=".svg"],'
            . '.media-icon img[src$=".svg"],'
            . 'td.media-icon img[src$=".svg"],'
            . 'img.attachment-thumbnail[src$=".svg"]{width:100%;height:auto;}'
            . '</style>';
    }
}
