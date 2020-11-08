<?php
namespace {{ plugin_namespace }}\Setup;

class SetupComponent
{
    public function __construct() {
        new EnqueueAssets;
        new ActivatePlugin;
        new DeactivatePlugin;
    }
}