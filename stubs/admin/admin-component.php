<?php
namespace {{ plugin_namespace }}\Admin;

use {{ plugin_namespace }}\Admin\AdminSettingsPageContainer;

class AdminComponent
{
    public function __construct() {
        add_action( 'init', [$this, 'init'] );
    }

    public function init() {

        // Register {{ settings_page_name }} settings page.
        new AdminSettingsPageContainer([
            'title' => '{{ settings_page_name }}',
            'slug' => '{{ settings_page_slug }}',
            'parent_slug' => '{{ parent_slug }}',
            'tabs' => [
                new Tabs\{{ settings_page_namespace }}\MainSettingsTab,
            ],
        ]);
    }
}