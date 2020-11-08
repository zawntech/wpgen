<?php
namespace {{ plugin_namespace }}\Setup;

class DeactivatePlugin
{
    public function __construct() {
        $this->deactivate_plugin();
    }

    public function deactivate_plugin() {
        // Deactivate plugin...
    }
}