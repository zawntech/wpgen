<?php

namespace WPGen\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WPGen\Commands\Traits\CheckWorkingDirectory;
use WPGen\Commands\Traits\LoadOptions;
use WPGen\Commands\Traits\QueryOptions;
use WPGen\Commands\Traits\RegisterComponentInMainClass;
use WPGen\Commands\Traits\ProcessStubFiles;
use WPGen\Config;

class CreateAdminCommand extends Command
{
    use LoadOptions,
        ProcessStubFiles,
        RegisterComponentInMainClass,
        CheckWorkingDirectory,
        QueryOptions;

    protected $options = [];

    /**
     * @var bool Whether the Admin component already exists (subsequent run).
     */
    protected $adminComponentExists = false;

    protected static $defaultName = 'create:admin';

    protected function configure() {
        $this
            ->setDescription( 'Add a settings page and helper class to plugin.' )
            ->setHelp( 'Creates a plugin settings page and Settings helper class. Run from your plugin root.' );
        $this->loadPluginOptions();
    }

    protected function interact( InputInterface $input, OutputInterface $output ) {

        // Verify we're in a plugin directory.
        $this->isWPGenPluginDirectory( $input, $output );

        // Query admin options (settings page name, top/sub level placement).
        $options = Config::get()->adminOptions();
        $this->mergeOptions( $options );
        $this->querySecondaryOptions( $input, $output, $options );

        // Derive settings page slug and namespace from the settings page name.
        $slug = strtolower( $this->options['settings_page_name']['value'] );
        $slug = str_replace( ' ', '-', $slug );
        $this->options['settings_page_slug'] = ['value' => $slug];

        // PascalCase namespace segment from slug (e.g. "tpp-groups" => "TppGroups").
        $namespace = str_replace( '-', ' ', $slug );
        $namespace = ucwords( $namespace );
        $namespace = str_replace( ' ', '', $namespace );
        $this->options['settings_page_namespace'] = ['value' => $namespace];

        // Resolve parent slug for sub-level pages.
        $this->options['parent_slug'] = ['value' => ''];
        if ( ! $this->options['top_level_admin_page']['value'] ) {
            $label = $this->options['sub_level_admin_page']['value'];
            $parent_slug = $this->resolveSelectValue( $options, 'sub_level_admin_page', $label );
            $this->options['parent_slug'] = ['value' => $parent_slug];
        }

        // Check if the Admin component already exists.
        $this->adminComponentExists = file_exists( getcwd() . '/src/Admin/AdminComponent.php' );

        // Register in main class only on first run.
        if ( ! $this->adminComponentExists ) {
            $component = 'Admin\AdminComponent';
            $this->registerComponentInMainClass( $input, $output, $component );
        }
    }

    /**
     * Look up the actual value for a select option given its displayed label.
     */
    protected function resolveSelectValue( $options, $key, $label ) {
        foreach ( $options as $option ) {
            if ( $option['key'] !== $key || ! isset( $option['options'] ) ) {
                continue;
            }
            foreach ( $option['options'] as $choice ) {
                if ( $choice['label'] === $label ) {
                    return $choice['value'];
                }
            }
        }
        return '';
    }

    /**
     * Process and copy stub files to target directory.
     *
     * Fires after the interact function completes.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute( InputInterface $input, OutputInterface $output ) {

        // Output path.
        $component_path = getcwd()  . '/src/Admin/';
        if ( ! is_dir( $component_path ) ) {
            mkdir( $component_path );
        }

        $abstract_path = $component_path . '/Abstract/';
        if ( ! is_dir( $abstract_path ) ) {
            mkdir( $abstract_path, 0755, true );
        }

        $tab_namespace = $this->options['settings_page_namespace']['value'];
        $tab_path = $component_path . '/Tabs/' . $tab_namespace . '/';
        if ( ! is_dir( $tab_path ) ) {
            mkdir( $tab_path, 0755, true );
        }

        $stub_path = APP_ROOT . 'stubs/admin/';

        if ( $this->adminComponentExists ) {
            // Subsequent run: only generate the new tab file.
            $files = [
                [
                    'source' => 'admin-main-settings-tab.php',
                    'target' => 'Tabs/' . $tab_namespace . '/MainSettingsTab.php',
                ]
            ];

            $this->processFiles( $stub_path, $component_path, $files );

            // Append settings page registration to existing AdminComponent.
            $this->appendSettingsPageToAdminComponent( $output, $component_path );
        } else {
            // First run: generate all files.
            $files = [
                [
                    'source' => 'admin-component.php',
                    'target' => 'AdminComponent.php'
                ],
                [
                    'source' => 'admin-settings-page.php',
                    'target' => 'Abstract/AdminSettingsPageContainer.php',
                ],
                [
                    'source' => 'admin-settings-page-tab-abstract.php',
                    'target' => 'Abstract/AdminSettingsPageTabAbstract.php',
                ],
                [
                    'source' => 'admin-main-settings-tab.php',
                    'target' => 'Tabs/' . $tab_namespace . '/MainSettingsTab.php',
                ],
                [
                    'source' => 'abstract-settings.php',
                    'target' => 'Abstract/AbstractSettings.php',
                ],
                [
                    'source' => 'settings.php',
                    'target' => 'Settings.php',
                ]
            ];

            $this->processFiles( $stub_path, $component_path, $files );
        }

        return 0;
    }

    /**
     * Append a new AdminSettingsPageContainer block to the existing
     * AdminComponent::init() method.
     */
    protected function appendSettingsPageToAdminComponent( OutputInterface $output, $component_path ) {

        $file_path = $component_path . 'AdminComponent.php';
        $contents = file_get_contents( $file_path );

        $settings_page_name = $this->options['settings_page_name']['value'];
        $settings_page_slug = $this->options['settings_page_slug']['value'];
        $settings_page_namespace = $this->options['settings_page_namespace']['value'];
        $parent_slug = $this->options['parent_slug']['value'];

        // Check if this settings page is already registered.
        if ( false !== strpos( $contents, "'{$settings_page_slug}'" ) ) {
            $output->writeln( "<info>Settings page '{$settings_page_slug}' is already registered in AdminComponent.</info>" );
            return;
        }

        // Build the new settings page block.
        $block = <<<PHP

        // Register {$settings_page_name} settings page.
        new AdminSettingsPageContainer([
            'title' => '{$settings_page_name}',
            'slug' => '{$settings_page_slug}',
            'parent_slug' => '{$parent_slug}',
            'tabs' => [
                new Tabs\\{$settings_page_namespace}\\MainSettingsTab,
            ],
        ]);
PHP;

        // Find the init() method and its closing brace.
        $start = strpos( $contents, 'init() {' );
        if ( false === $start ) {
            $output->writeln( '<error>Error: Unable to parse AdminComponent, no init() method found.</error>' );
            return;
        }

        // Find the closing brace of init() by matching braces.
        $brace_pos = strpos( $contents, '{', $start );
        $depth = 1;
        $pos = $brace_pos + 1;
        $len = strlen( $contents );

        while ( $pos < $len && $depth > 0 ) {
            if ( $contents[$pos] === '{' ) {
                $depth++;
            } elseif ( $contents[$pos] === '}' ) {
                $depth--;
            }
            $pos++;
        }

        // $pos is now just past the closing brace. Insert before it.
        $closing_brace_pos = $pos - 1;
        $contents = substr( $contents, 0, $closing_brace_pos ) . $block . "\n    " . substr( $contents, $closing_brace_pos );

        file_put_contents( $file_path, $contents );

        $output->writeln( "<info>Settings page '{$settings_page_name}' has been registered in AdminComponent.</info>" );
    }
}
