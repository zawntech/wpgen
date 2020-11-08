<?php
/**
 * Dynamic CSS applied to a specific (this) {{ module_name }} module instances.
 *
 * @var $module \{{ plugin_namespace }}\BeaverBuilder\{{ module_class }}Module
 * @var $id string The module's node ID.
 * @var $settings object The modules settings
 */
?>

.fl-node-<?php echo $id; ?> {
    /* background-color: #'<?php echo $settings->bg_color; ?>'; */
}
