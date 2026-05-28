<?php
/**
 * {{ block_title }} - Server-side render
 *
 * Available variables:
 *   $attributes (array)  -- block attributes from block.json
 *   $content    (string) -- inner block content (post_content for this block)
 *   $block      (WP_Block) -- the parsed block instance
 *
 * If you convert this to a static block, delete this file and remove
 * the "render" field from block.json.
 */

$wrapper_attributes = get_block_wrapper_attributes();
$inner = isset( $attributes['content'] ) ? wp_kses_post( $attributes['content'] ) : '';
?>
<div <?php echo $wrapper_attributes; ?>>
    <p><?php echo $inner; ?></p>
</div>
