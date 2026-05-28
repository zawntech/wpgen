<?php
namespace {{ plugin_namespace }}\Gutenberg;

/**
 * Registers every pattern file under PATTERNS_DIR.
 *
 * Each pattern file declares its metadata via WordPress-style file-header
 * comments (Title, Slug, Categories, etc.) and its body is the block
 * markup, captured via output buffering so inline PHP (i18n, escaping)
 * still runs at registration time.
 *
 * Drop a new .php file under patterns/ with the right headers and it
 * will register on the next page load -- no edits here.
 *
 * Also handles two pieces of plugin-wide discoverability:
 *
 *   - Registers the plugin's custom pattern category (init priority 9
 *     so it exists before the patterns themselves register at priority
 *     10) and prepends it to every pattern's categories list.
 *   - Injects GutenbergComponent::SEARCH_KEYWORD into every pattern's
 *     keywords list so one inserter search surfaces all of them.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_pattern/
 */
class RegisterPatterns
{
    /**
     * Relative path (from the plugin root) to pattern PHP files. Patterns
     * live outside assets/ because they have no build step -- they are
     * pure declarative PHP read by register_block_pattern().
     */
    const PATTERNS_DIR = 'patterns';

    /**
     * Header keys read from each pattern file via get_file_data(). The
     * left-hand keys map to register_block_pattern() args; the right-hand
     * strings are the comment-header labels the user writes in the file.
     */
    const HEADERS = [
        'title'         => 'Title',
        'slug'          => 'Slug',
        'description'   => 'Description',
        'categories'    => 'Categories',
        'keywords'      => 'Keywords',
        'blockTypes'    => 'Block Types',
        'postTypes'     => 'Post Types',
        'templateTypes' => 'Template Types',
        'viewportWidth' => 'Viewport Width',
        'inserter'      => 'Inserter',
    ];

    public function __construct() {
        add_action( 'init', [$this, 'register_category'], 9 );
        add_action( 'init', [$this, 'register'] );
    }

    /**
     * Register the plugin's custom pattern category. Fires before
     * register() (priority 9 vs 10) so the category exists by the time
     * we attach it to each pattern.
     */
    public function register_category() {
        register_block_pattern_category( GutenbergComponent::CATEGORY_SLUG, [
            'label' => GutenbergComponent::CATEGORY_TITLE,
        ] );
    }

    public function register() {
        $patterns_dir = {{ plugin_constants_prefix }}DIR . self::PATTERNS_DIR;
        if ( ! is_dir( $patterns_dir ) ) {
            return;
        }

        foreach ( glob( $patterns_dir . '/*.php' ) as $file ) {
            $data = get_file_data( $file, self::HEADERS );

            // A pattern without a Title is treated as a draft / disabled.
            if ( empty( $data['title'] ) ) {
                continue;
            }

            $slug = ! empty( $data['slug'] )
                ? $data['slug']
                : '{{ plugin_text_domain }}/' . basename( $file, '.php' );

            // CSV headers get expanded to arrays; empty ones default to
            // an empty array (rather than being unset) for the two we
            // unconditionally inject into below.
            foreach ( ['categories', 'keywords', 'blockTypes', 'postTypes', 'templateTypes'] as $key ) {
                if ( ! empty( $data[$key] ) ) {
                    $data[$key] = array_values( array_filter( array_map( 'trim', explode( ',', $data[$key] ) ) ) );
                } elseif ( in_array( $key, ['categories', 'keywords'], true ) ) {
                    $data[$key] = [];
                } else {
                    unset( $data[$key] );
                }
            }

            // Prepend the plugin's custom pattern category so every
            // pattern lands in our section of the inserter, regardless
            // of what the Categories: header declares.
            if ( ! in_array( GutenbergComponent::CATEGORY_SLUG, $data['categories'], true ) ) {
                array_unshift( $data['categories'], GutenbergComponent::CATEGORY_SLUG );
            }

            // Append the plugin's search keyword so all patterns are
            // findable via a single inserter search term.
            if ( ! in_array( GutenbergComponent::SEARCH_KEYWORD, $data['keywords'], true ) ) {
                $data['keywords'][] = GutenbergComponent::SEARCH_KEYWORD;
            }

            if ( ! empty( $data['viewportWidth'] ) ) {
                $data['viewportWidth'] = (int) $data['viewportWidth'];
            } else {
                unset( $data['viewportWidth'] );
            }

            // "Inserter: no" hides the pattern from the inserter UI but
            // still allows programmatic insertion.
            if ( isset( $data['inserter'] ) && $data['inserter'] !== '' ) {
                $data['inserter'] = ! in_array( strtolower( $data['inserter'] ), ['no', 'false', '0'], true );
            } else {
                unset( $data['inserter'] );
            }

            // Drop the slug from the args array -- it is passed as the first
            // arg to register_block_pattern() and would be ignored anyway.
            unset( $data['slug'] );

            ob_start();
            require $file;
            $data['content'] = ob_get_clean();

            register_block_pattern( $slug, $data );
        }
    }
}
