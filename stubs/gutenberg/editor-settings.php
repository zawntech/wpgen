<?php
namespace {{ plugin_namespace }}\Gutenberg;

/**
 * Block editor settings tweaks.
 *
 * Currently disables WP 7.0+'s "auto content-only" editing for unsynced
 * pattern instances. By default WP 7.0 server-side adds
 * `metadata.patternName` to the outer block of any single-root pattern
 * during REST resolution -- and the editor client then auto-applies
 * content-only editing to anything with `patternName`, marking inner
 * structural blocks as "disabled" and hiding them from List View
 * (their children get hoisted up to the nearest enabled ancestor).
 *
 * That UX is helpful when shipping patterns to end-content-editors
 * (they cannot accidentally break layout) but it is confusing for the
 * developers building the patterns -- inner groups need to be
 * navigable. Setting `disableContentOnlyForUnsyncedPatterns` to true
 * is the WP-provided opt-out: inserted patterns behave like any other
 * block tree (fully editable, fully visible in List View).
 *
 * If a specific pattern later needs to be content-only, add an
 * explicit `"templateLock":"contentOnly"` to its outer block.
 */
class EditorSettings
{
    public function __construct() {
        add_filter( 'block_editor_settings_all', [$this, 'filter_settings'] );
    }

    public function filter_settings( $settings ) {
        $settings['disableContentOnlyForUnsyncedPatterns'] = true;
        return $settings;
    }
}
