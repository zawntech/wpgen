<?php
namespace {{ plugin_namespace }}\Gutenberg;

/**
 * Container component for everything Gutenberg-related in this plugin.
 *
 * Delegates the actual registration work to sibling classes so each
 * concern (blocks vs patterns) stays in its own file:
 *
 *   - RegisterBlocks    discovers assets/blocks/{slug}/block.json files
 *                       and registers them via register_block_type().
 *   - RegisterPatterns  discovers patterns/*.php files (with WP-style
 *                       header comments) and registers them via
 *                       register_block_pattern().
 *   - EditorSettings    block_editor_settings_all filter that opts our
 *                       patterns out of WP 7.0's auto-content-only
 *                       behavior so inner groups stay visible in List
 *                       View.
 *
 * Constants below define the plugin-wide identifiers used by both
 * sibling classes for discoverability:
 *
 *   - BLOCK_NAMESPACE   the namespace prefix used in block.json's `name`
 *                       field (e.g. "{plugin}/my-block"). Used by
 *                       RegisterBlocks to scope filter callbacks so we
 *                       only touch our own blocks, not core/third-party.
 *   - CATEGORY_SLUG     slug for the custom inserter category. Used as
 *                       both the block category (default in block.json
 *                       stubs) and the pattern category (auto-added to
 *                       every pattern we register).
 *   - CATEGORY_TITLE    human-readable category title shown in the
 *                       inserter sidebar.
 *   - SEARCH_KEYWORD    injected into every block's and pattern's
 *                       keywords array so typing this string in the
 *                       inserter search surfaces everything this plugin
 *                       ships.
 */
class GutenbergComponent
{
    const BLOCK_NAMESPACE = '{{ plugin_text_domain }}';
    const CATEGORY_SLUG   = '{{ plugin_text_domain }}';
    const CATEGORY_TITLE  = '{{ plugin_name }}';
    const SEARCH_KEYWORD  = '{{ plugin_text_domain }}';

    public function __construct() {
        new RegisterBlocks;
        new RegisterPatterns;
        new EditorSettings;
    }
}
