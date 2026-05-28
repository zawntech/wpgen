/**
 * {{ block_title }} - Block registration
 *
 * If you keep render.php (dynamic block), the `save` function returns null
 * and PHP renders the frontend. To switch to a static block, delete
 * render.php, remove the `render` field from block.json, and have save.js
 * return JSX instead of null.
 */
import edit from './edit';
import save from './save';
import metadata from './block.json';

const { registerBlockType } = wp.blocks;

registerBlockType( metadata.name, {
    edit,
    save,
} );
