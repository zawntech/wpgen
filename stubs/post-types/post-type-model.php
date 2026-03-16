<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractPostTypeModel;

class {{ post_type_plural }} extends AbstractPostTypeModel
{
    const POST_TYPE_KEY = {{ post_type_singular }}PostType::KEY;
}
