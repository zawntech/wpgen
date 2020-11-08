<?php
namespace {{ plugin_namespace }}\Admin;

class AdminComponent
{
    public function __construct() {
        add_action( 'init', [$this, 'init'] );
    }

    public function init() {

        // Register settings page container.
        new AdminSettingsPageContainer;

        // Register individual pages.
        new Tabs\MainSettingsTab;
    }
}