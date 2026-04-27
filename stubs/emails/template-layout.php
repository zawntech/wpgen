<?php
/**
 * Email layout wrapper. Receives the rendered body of a specific email
 * template via $content and wraps it with header / footer chrome.
 *
 * @var string $content  Rendered body from the specific email template.
 * @var string $subject
 * @var array  $data
 */
$site_name = get_bloginfo( 'name' );
$site_url  = home_url( '/' );
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $subject ); ?></title>
</head>
<body style="margin:0;padding:0;background-color:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;color:#424242;line-height:1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f6f6;padding:32px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #eaeaea;">
                    <tr>
                        <td style="background-color:#ffffff;padding:32px;text-align:center;border-bottom:1px solid #eaeaea;">
                            <a href="<?php echo esc_url( $site_url ); ?>" style="text-decoration:none;color:#202020;font-size:20px;font-weight:700;">
                                <?php echo esc_html( $site_name ); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;background-color:#ffffff;">
                            <?php echo $content; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;border-top:1px solid #eaeaea;background-color:#fafafa;text-align:center;font-size:12px;color:#606060;">
                            You received this email from <a href="<?php echo esc_url( $site_url ); ?>" style="color:#005499;text-decoration:none;"><?php echo esc_html( $site_name ); ?></a>.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
