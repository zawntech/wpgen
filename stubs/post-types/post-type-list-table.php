<?php
namespace {{ plugin_namespace }}\{{ component_name }};

use {{ plugin_namespace }}\Abstract\AbstractPostTypeListTable;

class {{ post_type_singular }}PostTypeListTableFilter extends AbstractPostTypeListTable
{
    protected $columns = [];

    protected $unset_columns = [];

    protected $sortable_columns = [];

    protected $post_types = [{{ post_type_singular }}PostType::KEY];
    
}
