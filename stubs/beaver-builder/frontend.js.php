<?php
/**
 * Javascript applied to specific (this) {{ module_name }} module instances.
 *
 * @var $module \{{ plugin_namespace }}\BeaverBuilder\{{ module_class }}Module
 * @var $id string The module's node ID.
 * @var $settings object The modules settings
 */
?>

(function($){

    console.log('Module ID: <?php echo $id; ?>');
    console.log('Settings:', <?= json_encode( $settings ); ?>);

})(jQuery);