/**
 * {{ block_title }} - Editor component
 */
const { useBlockProps, RichText } = wp.blockEditor;
const { __ } = wp.i18n;

export default function Edit( { attributes, setAttributes } ) {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps }>
            <RichText
                tagName="p"
                value={ attributes.content }
                onChange={ ( content ) => setAttributes( { content } ) }
                placeholder={ __( '{{ block_title }} content...', '{{ plugin_text_domain }}' ) }
            />
        </div>
    );
}
