<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractPostType;

class {{ post_type_singular }}PostType extends AbstractPostType
{
    const KEY = '{{ post_type_key }}';
    const SINGULAR = '{{ post_type_singular }}';
    const PLURAL = '{{ post_type_plural }}';
    const SLUG = '{{ post_type_slug }}';
}
