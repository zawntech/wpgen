<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractTaxonomy;

class {{ taxonomy_singular }}Taxonomy extends AbstractTaxonomy
{
    const KEY = '{{ taxonomy_key }}';
    const SINGULAR = '{{ taxonomy_singular }}';
    const PLURAL = '{{ taxonomy_plural }}';

    protected $post_types = ['{{ post_type }}'];
}
