<?php
/**
 * Example email body template. Wrapped by _layout.php.
 *
 * @var array $data  Expected keys: display_name, cta_url.
 */
$greeting_name = ! empty( $data['display_name'] ) ? $data['display_name'] : __( 'there', {{ plugin_constants_prefix }}TEXT_DOMAIN );
$cta_url       = ! empty( $data['cta_url'] ) ? $data['cta_url'] : '';
?>
<h2 style="margin:0 0 16px;color:#202020;font-size:22px;font-weight:700;">Hello from <?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>

<p style="margin:0 0 16px;font-size:15px;color:#424242;">Hi <?php echo esc_html( $greeting_name ); ?>,</p>

<p style="margin:0 0 16px;font-size:15px;color:#424242;">
    This is an example email body. Replace this template at
    <code>assets/templates/emails/example.php</code> and create new emails by
    extending <code>AbstractEmail</code>.
</p>

<?php if ( ! empty( $cta_url ) ) : ?>
<p style="margin:24px 0;">
    <a href="<?php echo esc_url( $cta_url ); ?>" style="display:inline-block;padding:14px 32px;background-color:#005499;color:#ffffff;font-weight:600;text-decoration:none;border-radius:4px;">Take Action</a>
</p>

<p style="margin:0 0 16px;font-size:14px;color:#606060;">
    Or copy and paste this link into your browser:<br>
    <a href="<?php echo esc_url( $cta_url ); ?>" style="color:#005499;word-break:break-all;"><?php echo esc_html( $cta_url ); ?></a>
</p>
<?php endif; ?>
