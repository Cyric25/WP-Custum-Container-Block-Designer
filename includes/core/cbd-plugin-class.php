<?php
/**
 * Main Plugin Class
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\Core;

use ContainerBlockDesigner\Admin\AdminPage;
use ContainerBlockDesigner\Admin\Assets;
use ContainerBlockDesigner\API\RestController;
use ContainerBlockDesigner\Blocks\BlockRegistry;
use ContainerBlockDesigner\Database\Database;

/**
 * Main plugin class
 */
class Plugin {
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = CBD_VERSION;
    
    /**
     * Singleton instance
     *
     * @var Plugin|null
     */
    private static $instance = null;
    
    /**
     * Plugin modules
     *
     * @var array
     */
    private $modules = [];
    
    /**
     * Get singleton instance
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load plugin dependencies
     *
     * @return void
     */
    private function load_dependencies(): void {
        // Core Module
        $this->modules['loader'] = new Loader();
        $this->modules['i18n'] = new I18n();
        
        // Database
        $this->modules['database'] = new Database();
        
        // Admin Module
        $this->modules['admin_page'] = new AdminPage();
        $this->modules['assets'] = new Assets();
        
        // Blocks
        $this->modules['block_registry'] = new BlockRegistry();
        
        // API
        $this->modules['rest_controller'] = new RestController();
    }
    
    /**
     * Initialize plugin hooks
     *
     * @return void
     */
    private function init_hooks(): void {
        $loader = $this->modules['loader'];
        
        // Internationalization
        $loader->add_action('plugins_loaded', $this->modules['i18n'], 'load_plugin_textdomain');
        
        // Admin
        $loader->add_action('admin_menu', $this->modules['admin_page'], 'add_menu_page');
        $loader->add_action('admin_enqueue_scripts', $this->modules['assets'], 'enqueue_admin_assets');
        
        // Blocks
        $loader->add_action('init', $this->modules['block_registry'], 'register_blocks');
        $loader->add_action('enqueue_block_editor_assets', $this->modules['assets'], 'enqueue_block_editor_assets');
        $loader->add_action('enqueue_block_assets', $this->modules['assets'], 'enqueue_block_assets');
        $loader->add_filter('block_categories_all', $this->modules['block_registry'], 'add_block_category', 10, 2);
        
        // REST API
        $loader->add_action('rest_api_init', $this->modules['rest_controller'], 'register_routes');
        
        // Frontend
        $loader->add_action('wp_enqueue_scripts', $this->modules['assets'], 'enqueue_frontend_assets');
        
        // Run hooks
        $loader->run();
    }
    
    /**
     * Get a specific module
     *
     * @param string $name Module name
     * @return object|null
     */
    public function get_module(string $name) {
        return $this->modules[$name] ?? null;
    }
    
    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version(): string {
        return self::VERSION;
    }
    
    /**
     * Get plugin directory path
     *
     * @return string
     */
    public function get_plugin_dir(): string {
        return CBD_PLUGIN_DIR;
    }
    
    /**
     * Get plugin URL
     *
     * @return string
     */
    public function get_plugin_url(): string {
        return CBD_PLUGIN_URL;
    }
}