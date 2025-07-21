<?php
/**
 * Fired during plugin activation
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\Core;

use ContainerBlockDesigner\Database\Schema;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {
    /**
     * Plugin activation handler
     *
     * @return void
     */
    public static function activate(): void {
        // Version checks
        self::check_requirements();
        
        // Create database tables
        self::create_tables();
        
        // Set default options
        self::set_default_options();
        
        // Create default capabilities
        self::add_capabilities();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule cron events
        self::schedule_events();
        
        // Log activation
        self::log_activation();
    }
    
    /**
     * Check minimum requirements
     *
     * @return void
     */
    private static function check_requirements(): void {
        // PHP Version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            deactivate_plugins(CBD_PLUGIN_BASENAME);
            wp_die(
                __('Container Block Designer benötigt PHP 8.0 oder höher.', 'container-block-designer'),
                __('Plugin Aktivierung fehlgeschlagen', 'container-block-designer'),
                ['back_link' => true]
            );
        }
        
        // WordPress Version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            deactivate_plugins(CBD_PLUGIN_BASENAME);
            wp_die(
                __('Container Block Designer benötigt WordPress 6.0 oder höher.', 'container-block-designer'),
                __('Plugin Aktivierung fehlgeschlagen', 'container-block-designer'),
                ['back_link' => true]
            );
        }
        
        // Check for Gutenberg
        if (!function_exists('register_block_type')) {
            deactivate_plugins(CBD_PLUGIN_BASENAME);
            wp_die(
                __('Container Block Designer benötigt den Gutenberg Block Editor.', 'container-block-designer'),
                __('Plugin Aktivierung fehlgeschlagen', 'container-block-designer'),
                ['back_link' => true]
            );
        }
    }
    
    /**
     * Create database tables
     *
     * @return void
     */
    private static function create_tables(): void {
        require_once CBD_PLUGIN_DIR . 'includes/Database/Schema.php';
        
        $schema = new Schema();
        $schema->create_tables();
    }
    
    /**
     * Set default plugin options
     *
     * @return void
     */
    private static function set_default_options(): void {
        // Plugin version
        add_option('cbd_version', CBD_VERSION);
        
        // Database version
        add_option('cbd_db_version', '1.0.0');
        
        // Default settings
        $default_settings = [
            'enable_cache' => true,
            'cache_ttl' => 3600,
            'enable_debug' => false,
            'default_block_category' => 'container-blocks',
            'enable_import_export' => true,
            'max_blocks_per_user' => 100,
            'enable_block_versioning' => true,
            'max_versions_per_block' => 10,
        ];
        
        add_option('cbd_settings', $default_settings);
        
        // Installation timestamp
        add_option('cbd_installed', current_time('mysql'));
    }
    
    /**
     * Add plugin capabilities
     *
     * @return void
     */
    private static function add_capabilities(): void {
        $roles = ['administrator', 'editor'];
        
        $capabilities = [
            'cbd_manage_blocks',
            'cbd_create_blocks',
            'cbd_edit_blocks',
            'cbd_delete_blocks',
            'cbd_publish_blocks',
            'cbd_import_blocks',
            'cbd_export_blocks',
        ];
        
        // Admin gets all capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
            
            // Admin-only capabilities
            $admin_role->add_cap('cbd_manage_settings');
            $admin_role->add_cap('cbd_delete_others_blocks');
        }
        
        // Editor gets limited capabilities
        $editor_role = get_role('editor');
        if ($editor_role) {
            $editor_caps = [
                'cbd_create_blocks',
                'cbd_edit_blocks',
                'cbd_publish_blocks',
            ];
            
            foreach ($editor_caps as $cap) {
                $editor_role->add_cap($cap);
            }
        }
    }
    
    /**
     * Schedule cron events
     *
     * @return void
     */
    private static function schedule_events(): void {
        // Schedule daily cleanup
        if (!wp_next_scheduled('cbd_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'cbd_daily_cleanup');
        }
        
        // Schedule cache cleanup
        if (!wp_next_scheduled('cbd_cache_cleanup')) {
            wp_schedule_event(time(), 'hourly', 'cbd_cache_cleanup');
        }
    }
    
    /**
     * Log plugin activation
     *
     * @return void
     */
    private static function log_activation(): void {
        $log_data = [
            'version' => CBD_VERSION,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
        ];
        
        update_option('cbd_activation_log', $log_data);
    }
}