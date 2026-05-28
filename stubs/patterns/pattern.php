<?php
/**
 * Title: {{ pattern_title }}
 * Slug: {{ plugin_text_domain }}/{{ pattern_slug }}
 * Description: {{ pattern_description }}
 * Categories: {{ pattern_categories }}
 * Keywords: {{ pattern_keywords }}
 * Block Types: {{ pattern_block_types }}
 * Viewport Width: 1400
 */
?>
<!-- wp:group {"align":"full"} -->
<div class="wp-block-group alignfull">
    <!-- wp:heading -->
    <h2><?php echo esc_html__( '{{ pattern_title }}', '{{ plugin_text_domain }}' ); ?></h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p><?php echo esc_html__( 'Replace this body with your pattern markup. Tip: build the layout in the block editor, then use Options -> Copy to grab the wp:* block comment markup and paste it here.', '{{ plugin_text_domain }}' ); ?></p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
