<?php
/**
 * REST API Controller
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\API;

use ContainerBlockDesigner\API\Endpoints\BlocksEndpoint;
use ContainerBlockDesigner\API\Endpoints\TemplatesEndpoint;

/**
 * REST API Controller class
 */
class RestController {
    /**
     * API namespace
     *
     * @var string
     */
    const NAMESPACE = 'cbd/v1';
    
    /**
     * Endpoints
     *
     * @var array
     */
    private $endpoints = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->endpoints = [
            new BlocksEndpoint(),
            new TemplatesEndpoint(),
        ];
    }
    
    /**
     * Register REST API routes
     *
     * @return void
     */
    public function register_routes(): void {
        foreach ($this->endpoints as $endpoint) {
            $endpoint->register_routes();
        }
        
        // Register custom routes
        $this->register_custom_routes();
    }
    
    /**
     * Register custom routes
     *
     * @return void
     */
    private function register_custom_routes(): void {
        // Health check endpoint
        register_rest_route(self::NAMESPACE, '/health', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'health_check'],
            'permission_callback' => '__return_true',
        ]);
        
        // Version endpoint
        register_rest_route(self::NAMESPACE, '/version', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'get_version'],
            'permission_callback' => '__return_true',
        ]);
        
        // Settings endpoint
        register_rest_route(self::NAMESPACE, '/settings', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_settings'],
                'permission_callback' => [$this, 'check_admin_permission'],
            ],
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'check_admin_permission'],
                'args' => $this->get_settings_schema(),
            ],
        ]);
    }
    
    /**
     * Health check callback
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function health_check($request) {
        $health = [
            'status' => 'ok',
            'timestamp' => current_time('c'),
            'version' => CBD_VERSION,
            'database' => $this->check_database_health(),
            'cache' => $this->check_cache_health(),
        ];
        
        return new \WP_REST_Response($health, 200);
    }
    
    /**
     * Get version callback
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_version($request) {
        return new \WP_REST_Response([
            'version' => CBD_VERSION,
            'api_version' => '1.0',
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
        ], 200);
    }
    
    /**
     * Get settings callback
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function get_settings($request) {
        $settings = get_option('cbd_settings', []);
        
        return new \WP_REST_Response($settings, 200);
    }
    
    /**
     * Update settings callback
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function update_settings($request) {
        $settings = $request->get_json_params();
        
        // Sanitize settings
        $sanitized = $this->sanitize_settings($settings);
        
        // Update settings
        update_option('cbd_settings', $sanitized);
        
        // Clear cache if needed
        if (isset($settings['enable_cache']) && !$settings['enable_cache']) {
            $this->clear_all_cache();
        }
        
        return new \WP_REST_Response($sanitized, 200);
    }
    
    /**
     * Check admin permission
     *
     * @param \WP_REST_Request $request Request object
     * @return bool
     */
    public function check_admin_permission($request) {
        return current_user_can('cbd_manage_settings');
    }
    
    /**
     * Get settings schema
     *
     * @return array
     */
    private function get_settings_schema() {
        return [
            'enable_cache' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'cache_ttl' => [
                'type' => 'integer',
                'minimum' => 60,
                'maximum' => 86400,
                'default' => 3600,
            ],
            'enable_debug' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'enable_import_export' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'max_blocks_per_user' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 1000,
                'default' => 100,
            ],
            'enable_block_versioning' => [
                'type' => 'boolean',
                'default' => true,
            ],
            'max_versions_per_block' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 100,
                'default' => 10,
            ],
        ];
    }
    
    /**
     * Sanitize settings
     *
     * @param array $settings Settings to sanitize
     * @return array
     */
    private function sanitize_settings($settings) {
        $schema = $this->get_settings_schema();
        $sanitized = [];
        
        foreach ($schema as $key => $rules) {
            if (!isset($settings[$key])) {
                $sanitized[$key] = $rules['default'] ?? null;
                continue;
            }
            
            $value = $settings[$key];
            
            switch ($rules['type']) {
                case 'boolean':
                    $sanitized[$key] = (bool) $value;
                    break;
                    
                case 'integer':
                    $value = absint($value);
                    if (isset($rules['minimum'])) {
                        $value = max($value, $rules['minimum']);
                    }
                    if (isset($rules['maximum'])) {
                        $value = min($value, $rules['maximum']);
                    }
                    $sanitized[$key] = $value;
                    break;
                    
                case 'string':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
                    
                default:
                    $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Check database health
     *
     * @return array
     */
    private function check_database_health() {
        global $wpdb;
        
        $tables = [
            'blocks' => $wpdb->prefix . 'cbd_blocks',
            'versions' => $wpdb->prefix . 'cbd_block_versions',
            'audit_log' => $wpdb->prefix . 'cbd_audit_log',
            'templates' => $wpdb->prefix . 'cbd_templates',
        ];
        
        $health = [
            'status' => 'ok',
            'tables' => [],
        ];
        
        foreach ($tables as $name => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
            $health['tables'][$name] = $exists;
            
            if (!$exists) {
                $health['status'] = 'error';
            }
        }
        
        return $health;
    }
    
    /**
     * Check cache health
     *
     * @return array
     */
    private function check_cache_health() {
        $test_key = 'cbd_cache_health_check';
        $test_value = time();
        
        // Test cache
        wp_cache_set($test_key, $test_value, 'cbd_blocks', 60);
        $retrieved = wp_cache_get($test_key, 'cbd_blocks');
        
        return [
            'status' => $retrieved === $test_value ? 'ok' : 'error',
            'type' => $this->get_cache_type(),
        ];
    }
    
    /**
     * Get cache type
     *
     * @return string
     */
    private function get_cache_type() {
        if (wp_using_ext_object_cache()) {
            if (class_exists('Redis')) {
                return 'redis';
            }
            if (class_exists('Memcached')) {
                return 'memcached';
            }
            return 'external';
        }
        
        return 'transient';
    }
    
    /**
     * Clear all cache
     *
     * @return void
     */
    private function clear_all_cache() {
        wp_cache_flush_group('cbd_blocks');
        
        // Clear transients
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_cbd_%' 
            OR option_name LIKE '_transient_timeout_cbd_%'"
        );
    }
}