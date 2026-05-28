<?php
namespace {{ plugin_namespace }}\Gutenberg;

/**
 * Registers every block found under BLOCKS_DIR by pointing
 * register_block_type() at each block.json file.
 *
 * block.json's `editorScript`, `script`, `style`, and `editorStyle`
 * fields are resolved relative to the block.json's own directory --
 * point them at compiled webpack output (e.g. ./build/index.js).
 *
 * Drop a new directory containing a block.json under assets/blocks/
 * and it will register on the next page load -- no edits here.
 *
 * Also handles two pieces of plugin-wide discoverability:
 *
 *   - Registers the plugin's custom inserter category (via the
 *     block_categories_all filter) so new blocks scaffolded with the
 *     default category land in their own section of the inserter.
 *   - Injects GutenbergComponent::SEARCH_KEYWORD into every block's
 *     keywords array (scoped to blocks in our namespace, so we do not
 *     touch core or third-party blocks).
 */
class RegisterBlocks
{
    /**
     * Relative path (from the plugin root) to per-block source directories,
     * each containing a block.json.
     */
    const BLOCKS_DIR = 'assets/blocks';

    public function __construct() {
        add_action( 'init', [$this, 'register'] );
        add_filter( 'block_categories_all', [$this, 'register_category'] );
        add_filter( 'register_block_type_args', [$this, 'inject_keyword'], 10, 2 );
    }

    public function register() {
        $blocks_dir = {{ plugin_constants_prefix }}DIR . self::BLOCKS_DIR;
        if ( ! is_dir( $blocks_dir ) ) {
            return;
        }

        foreach ( glob( $blocks_dir . '/*/block.json' ) as $block_json ) {
            register_block_type( dirname( $block_json ) );
        }
    }

    /**
     * Register the plugin's custom block category. Prepended so it lands
     * at the top of the inserter sidebar.
     */
    public function register_category( $categories ) {
        array_unshift( $categories, [
            'slug'  => GutenbergComponent::CATEGORY_SLUG,
            'title' => GutenbergComponent::CATEGORY_TITLE,
        ] );
        return $categories;
    }

    /**
     * Append GutenbergComponent::SEARCH_KEYWORD to the keywords array
     * of every block this plugin registers, so users can find them all
     * by searching for one term in the inserter.
     */
    public function inject_keyword( $args, $name ) {
        if ( strpos( $name, GutenbergComponent::BLOCK_NAMESPACE . '/' ) !== 0 ) {
            return $args;
        }

        $keywords = isset( $args['keywords'] ) && is_array( $args['keywords'] ) ? $args['keywords'] : [];

        if ( ! in_array( GutenbergComponent::SEARCH_KEYWORD, $keywords, true ) ) {
            $keywords[] = GutenbergComponent::SEARCH_KEYWORD;
        }

        $args['keywords'] = $keywords;
        return $args;
    }
}
