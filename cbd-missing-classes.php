<?php
// includes/Core/I18n.php
namespace ContainerBlockDesigner\Core;

class I18n {
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'container-block-designer',
            false,
            dirname(CBD_PLUGIN_BASENAME) . '/languages/'
        );
    }
}

// includes/Core/Deactivator.php
namespace ContainerBlockDesigner\Core;

class Deactivator {
    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('cbd_daily_cleanup');
        wp_clear_scheduled_hook('cbd_cache_cleanup');
        
        // Flush cache
        wp_cache_flush();
        
        // Log deactivation
        update_option('cbd_deactivated', current_time('mysql'));
    }
}

// includes/Core/Uninstaller.php
namespace ContainerBlockDesigner\Core;

class Uninstaller {
    public static function uninstall() {
        // Check if we should remove data
        $settings = get_option('cbd_settings');
        if (empty($settings['remove_data_on_uninstall'])) {
            return;
        }
        
        // Remove capabilities
        self::remove_capabilities();
        
        // Drop tables
        require_once CBD_PLUGIN_DIR . 'includes/Database/Schema.php';
        $schema = new \ContainerBlockDesigner\Database\Schema();
        $schema->drop_tables();
        
        // Delete options
        delete_option('cbd_version');
        delete_option('cbd_db_version');
        delete_option('cbd_settings');
        delete_option('cbd_installed');
        delete_option('cbd_activation_log');
        delete_option('cbd_deactivated');
        
        // Clear cache
        wp_cache_flush();
    }
    
    private static function remove_capabilities() {
        $roles = ['administrator', 'editor'];
        $capabilities = [
            'cbd_manage_blocks',
            'cbd_create_blocks',
            'cbd_edit_blocks',
            'cbd_delete_blocks',
            'cbd_publish_blocks',
            'cbd_import_blocks',
            'cbd_export_blocks',
            'cbd_manage_settings',
            'cbd_delete_others_blocks',
        ];
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
}

// includes/Admin/Assets.php
namespace ContainerBlockDesigner\Admin;

class Assets {
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'container-block-designer') === false) {
            return;
        }
        
        // Admin styles
        wp_enqueue_style(
            'cbd-admin-style',
            CBD_PLUGIN_URL . 'build/admin.css',
            ['wp-components'],
            CBD_VERSION
        );
        
        // Admin scripts
        wp_enqueue_script(
            'cbd-admin-script',
            CBD_PLUGIN_URL . 'build/admin.js',
            ['wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-i18n'],
            CBD_VERSION,
            true
        );
        
        // Localization
        wp_localize_script('cbd-admin-script', 'cbdAdmin', [
            'apiUrl' => home_url('/wp-json/cbd/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'userId' => get_current_user_id(),
            'strings' => $this->get_localized_strings(),
        ]);
        
        wp_set_script_translations('cbd-admin-script', 'container-block-designer');
    }
    
    public function enqueue_block_editor_assets() {
        // Block editor scripts
        wp_enqueue_script(
            'cbd-blocks',
            CBD_PLUGIN_URL . 'build/blocks.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-i18n'],
            CBD_VERSION
        );
        
        // Block editor styles
        wp_enqueue_style(
            'cbd-blocks-editor',
            CBD_PLUGIN_URL . 'build/blocks-editor.css',
            ['wp-edit-blocks'],
            CBD_VERSION
        );
        
        wp_set_script_translations('cbd-blocks', 'container-block-designer');
    }
    
    public function enqueue_block_assets() {
        // Frontend block styles (loaded in editor and frontend)
        wp_enqueue_style(
            'cbd-blocks-style',
            CBD_PLUGIN_URL . 'build/style-blocks.css',
            [],
            CBD_VERSION
        );
    }
    
    public function enqueue_frontend_assets() {
        // Only enqueue if we have CBD blocks on the page
        if (!has_block('container-block-designer/container')) {
            return;
        }
        
        // Frontend styles
        wp_enqueue_style(
            'cbd-frontend',
            CBD_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            CBD_VERSION
        );
    }
    
    private function get_localized_strings() {
        return [
            'save' => __('Speichern', 'container-block-designer'),
            'cancel' => __('Abbrechen', 'container-block-designer'),
            'delete' => __('Löschen', 'container-block-designer'),
            'edit' => __('Bearbeiten', 'container-block-designer'),
            'duplicate' => __('Duplizieren', 'container-block-designer'),
            'confirmDelete' => __('Sind Sie sicher, dass Sie diesen Block löschen möchten?', 'container-block-designer'),
            'blockCreated' => __('Block erfolgreich erstellt!', 'container-block-designer'),
            'blockUpdated' => __('Block erfolgreich aktualisiert!', 'container-block-designer'),
            'blockDeleted' => __('Block erfolgreich gelöscht!', 'container-block-designer'),
            'error' => __('Ein Fehler ist aufgetreten.', 'container-block-designer'),
        ];
    }
}

// includes/Blocks/BlockRegistry.php
namespace ContainerBlockDesigner\Blocks;

use ContainerBlockDesigner\Database\BlockRepository;

class BlockRegistry {
    private $repository;
    
    public function __construct() {
        $this->repository = new BlockRepository();
    }
    
    public function register_blocks() {
        // Register main container block
        register_block_type(CBD_PLUGIN_DIR . 'build/blocks/container');
        
        // Register dynamic blocks from database
        $this->register_dynamic_blocks();
    }
    
    public function add_block_category($categories, $post) {
        return array_merge(
            $categories,
            [
                [
                    'slug' => 'container-blocks',
                    'title' => __('Container Blöcke', 'container-block-designer'),
                    'icon' => 'layout',
                ],
            ]
        );
    }
    
    private function register_dynamic_blocks() {
        $active_blocks = $this->repository->find_by_status('active');
        
        foreach ($active_blocks as $block) {
            $this->register_dynamic_block($block);
        }
    }
    
    private function register_dynamic_block($block) {
        // This will be implemented to register block variations
        // For now, we'll just ensure the data is available
        add_action('enqueue_block_editor_assets', function() use ($block) {
            wp_add_inline_script(
                'cbd-blocks',
                sprintf(
                    'window.cbdBlocks = window.cbdBlocks || {}; window.cbdBlocks[%d] = %s;',
                    $block['id'],
                    wp_json_encode([
                        'id' => $block['id'],
                        'name' => $block['block_name'],
                        'title' => $block['block_title'],
                        'description' => $block['block_description'],
                        'icon' => $block['block_icon'],
                        'config' => json_decode($block['block_config'], true),
                    ])
                ),
                'before'
            );
        });
    }
}

// includes/Database/Database.php
namespace ContainerBlockDesigner\Database;

class Database {
    public function __construct() {
        // Check if database needs update on admin init
        add_action('admin_init', [$this, 'maybe_update_database']);
    }
    
    public function maybe_update_database() {
        $schema = new Schema();
        
        if (!$schema->tables_exist() || $schema->needs_update()) {
            $schema->create_tables();
        }
    }
}

// includes/API/Endpoints/TemplatesEndpoint.php
namespace ContainerBlockDesigner\API\Endpoints;

class TemplatesEndpoint extends \WP_REST_Controller {
    protected $namespace = 'cbd/v1';
    protected $rest_base = 'templates';
    
    public function register_routes() {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
            ],
        ]);
    }
    
    public function get_items($request) {
        // Placeholder for template functionality
        return new \WP_REST_Response([], 200);
    }
    
    public function get_items_permissions_check($request) {
        return current_user_can('edit_posts');
    }
}

// includes/Security/InputValidator.php
namespace ContainerBlockDesigner\Security;

class InputValidator {
    public function validate_block($data, $is_update = false) {
        $errors = [];
        
        // Name validation (required for new blocks)
        if (!$is_update || isset($data['name'])) {
            if (empty($data['name'])) {
                $errors['name'] = __('Block-Name ist erforderlich.', 'container-block-designer');
            } elseif (!preg_match('/^[a-z0-9-]+$/', $data['name'])) {
                $errors['name'] = __('Block-Name darf nur Kleinbuchstaben, Zahlen und Bindestriche enthalten.', 'container-block-designer');
            }
        }
        
        // Title validation (required)
        if (!$is_update || isset($data['title'])) {
            if (empty($data['title'])) {
                $errors['title'] = __('Block-Titel ist erforderlich.', 'container-block-designer');
            }
        }
        
        // Config validation (required)
        if (!$is_update || isset($data['config'])) {
            if (empty($data['config'])) {
                $errors['config'] = __('Block-Konfiguration ist erforderlich.', 'container-block-designer');
            }
        }
        
        if (!empty($errors)) {
            return new \WP_Error('validation_failed', __('Validierung fehlgeschlagen.', 'container-block-designer'), $errors);
        }
        
        return true;
    }
}

// includes/Security/OutputSanitizer.php
namespace ContainerBlockDesigner\Security;

class OutputSanitizer {
    public static function sanitize_block_data($data) {
        $sanitized = [];
        
        if (isset($data['name'])) {
            $sanitized['name'] = sanitize_key($data['name']);
        }
        
        if (isset($data['title'])) {
            $sanitized['title'] = sanitize_text_field($data['title']);
        }
        
        if (isset($data['description'])) {
            $sanitized['description'] = sanitize_textarea_field($data['description']);
        }
        
        if (isset($data['icon'])) {
            $sanitized['icon'] = sanitize_text_field($data['icon']);
        }
        
        if (isset($data['category'])) {
            $sanitized['category'] = sanitize_text_field($data['category']);
        }
        
        if (isset($data['keywords']) && is_array($data['keywords'])) {
            $sanitized['keywords'] = array_map('sanitize_text_field', $data['keywords']);
        }
        
        if (isset($data['config'])) {
            $sanitized['config'] = self::sanitize_config($data['config']);
        }
        
        if (isset($data['status'])) {
            $allowed_statuses = ['active', 'inactive', 'draft', 'trash'];
            $sanitized['status'] = in_array($data['status'], $allowed_statuses) ? $data['status'] : 'draft';
        }
        
        return $sanitized;
    }
    
    private static function sanitize_config($config) {
        if (!is_array($config)) {
            return [];
        }
        
        $sanitized = [];
        
        if (isset($config['styles'])) {
            $sanitized['styles'] = self::sanitize_styles($config['styles']);
        }
        
        if (isset($config['allowedBlocks']) && is_array($config['allowedBlocks'])) {
            $sanitized['allowedBlocks'] = array_map('sanitize_text_field', $config['allowedBlocks']);
        }
        
        if (isset($config['template'])) {
            $sanitized['template'] = $config['template']; // Complex structure, needs careful handling
        }
        
        return $sanitized;
    }
    
    private static function sanitize_styles($styles) {
        if (!is_array($styles)) {
            return [];
        }
        
        $sanitized = [];
        
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            if (isset($styles[$device])) {
                $sanitized[$device] = self::sanitize_device_styles($styles[$device]);
            }
        }
        
        return $sanitized;
    }
    
    private static function sanitize_device_styles($styles) {
        $sanitized = [];
        
        // Spacing
        foreach (['padding', 'margin'] as $property) {
            if (isset($styles[$property]) && is_array($styles[$property])) {
                $sanitized[$property] = [
                    'top' => absint($styles[$property]['top'] ?? 0),
                    'right' => absint($styles[$property]['right'] ?? 0),
                    'bottom' => absint($styles[$property]['bottom'] ?? 0),
                    'left' => absint($styles[$property]['left'] ?? 0),
                ];
            }
        }
        
        // Colors
        if (isset($styles['backgroundColor'])) {
            $sanitized['backgroundColor'] = sanitize_hex_color($styles['backgroundColor']);
        }
        
        if (isset($styles['borderColor'])) {
            $sanitized['borderColor'] = sanitize_hex_color($styles['borderColor']);
        }
        
        // Numeric values
        $numeric_props = ['borderWidth', 'borderRadius'];
        foreach ($numeric_props as $prop) {
            if (isset($styles[$prop])) {
                $sanitized[$prop] = absint($styles[$prop]);
            }
        }
        
        return $sanitized;
    }
}