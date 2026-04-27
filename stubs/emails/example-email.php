<?php
namespace {{ plugin_namespace }}\Emails;

use {{ plugin_namespace }}\Abstract\AbstractEmail;

/**
 * Example transactional email.
 *
 * Usage:
 *   ( new ExampleEmail( $user->user_email, [
 *       'display_name' => $user->display_name,
 *       'cta_url'      => 'https://example.com',
 *   ] ) )->send();
 *
 * To add a new email: copy this file, rename the class, set the subject
 * and template name, and add a matching template under
 * assets/templates/emails/.
 */
class ExampleEmail extends AbstractEmail
{
    protected function subject(): string {
        /* translators: %s is the site name */
        return sprintf( __( 'A message from %s', {{ plugin_constants_prefix }}TEXT_DOMAIN ), get_bloginfo( 'name' ) );
    }

    protected function template(): string {
        return 'example';
    }
}
