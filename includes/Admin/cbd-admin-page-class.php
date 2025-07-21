<?php
/**
 * Admin Page Handler
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\Admin;

/**
 * Admin Page class
 */
class AdminPage {
    /**
     * Menu slug
     *
     * @var string
     */
    const MENU_SLUG = 'container-block-designer';
    
    /**
     * Add menu page
     *
     * @return void
     */
    public function add_menu_page(): void {
        add_menu_page(
            __('Container Block Designer', 'container-block-designer'),
            __('Container Blocks', 'container-block-designer'),
            'cbd_manage_blocks',
            self::MENU_SLUG,
            [$this, 'render_admin_page'],
            'dashicons-layout',
            30
        );
        
        // Submenu pages
        add_submenu_page(
            self::MENU_SLUG,
            __('Alle Blöcke', 'container-block-designer'),
            __('Alle Blöcke', 'container-block-designer'),
            'cbd_manage_blocks',
            self::MENU_SLUG,
            [$this, 'render_admin_page']
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            __('Neuer Block', 'container-block-designer'),
            __('Neuer Block', 'container-block-designer'),
            'cbd_create_blocks',
            self::MENU_SLUG . '-new',
            [$this, 'render_new_block_page']
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            __('Templates', 'container-block-designer'),
            __('Templates', 'container-block-designer'),
            'cbd_manage_blocks',
            self::MENU_SLUG . '-templates',
            [$this, 'render_templates_page']
        );
        
        add_submenu_page(
            self::MENU_SLUG,
            __('Einstellungen', 'container-block-designer'),
            __('Einstellungen', 'container-block-designer'),
            'cbd_manage_settings',
            self::MENU_SLUG . '-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Render main admin page
     *
     * @return void
     */
    public function render_admin_page(): void {
        if (!current_user_can('cbd_manage_blocks')) {
            wp_die(__('Sie haben nicht die erforderlichen Berechtigungen, um auf diese Seite zuzugreifen.', 'container-block-designer'));
        }
        
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <?php echo esc_html(get_admin_page_title()); ?>
            </h1>
            <a href="<?php echo esc_url(admin_url('admin.php?page=' . self::MENU_SLUG . '-new')); ?>" class="page-title-action">
                <?php _e('Neuer Block', 'container-block-designer'); ?>
            </a>
            
            <hr class="wp-header-end">
            
            <?php $this->display_notices(); ?>
            
            <div id="cbd-admin-app">
                <!-- React App wird hier gemountet -->
                <div class="cbd-loading">
                    <span class="spinner is-active"></span>
                    <p><?php _e('Lade Container Block Designer...', 'container-block-designer'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render new block page
     *
     * @return void
     */
    public function render_new_block_page(): void {
        if (!current_user_can('cbd_create_blocks')) {
            wp_die(__('Sie haben nicht die erforderlichen Berechtigungen, um auf diese Seite zuzugreifen.', 'container-block-designer'));
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Neuer Container Block', 'container-block-designer'); ?></h1>
            
            <div id="cbd-block-designer">
                <!-- React Block Designer wird hier gemountet -->
                <div class="cbd-loading">
                    <span class="spinner is-active"></span>
                    <p><?php _e('Lade Block Designer...', 'container-block-designer'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render templates page
     *
     * @return void
     */
    public function render_templates_page(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Block Templates', 'container-block-designer'); ?></h1>
            
            <div id="cbd-templates">
                <!-- Templates Verwaltung -->
                <div class="notice notice-info">
                    <p><?php _e('Template-Verwaltung wird in einer zukünftigen Version verfügbar sein.', 'container-block-designer'); ?></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void {
        if (!current_user_can('cbd_manage_settings')) {
            wp_die(__('Sie haben nicht die erforderlichen Berechtigungen, um auf diese Seite zuzugreifen.', 'container-block-designer'));
        }
        
        // Handle form submission
        if (isset($_POST['cbd_settings_nonce']) && wp_verify_nonce($_POST['cbd_settings_nonce'], 'cbd_save_settings')) {
            $this->save_settings();
        }
        
        $settings = get_option('cbd_settings', []);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('cbd_save_settings', 'cbd_settings_nonce'); ?>
                
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="enable_cache"><?php _e('Cache aktivieren', 'container-block-designer'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_cache" name="cbd_settings[enable_cache]" value="1" 
                                <?php checked(!empty($settings['enable_cache'])); ?>>
                            <p class="description">
                                <?php _e('Aktiviert das Caching für bessere Performance.', 'container-block-designer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cache_ttl"><?php _e('Cache-Dauer (Sekunden)', 'container-block-designer'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="cache_ttl" name="cbd_settings[cache_ttl]" 
                                value="<?php echo esc_attr($settings['cache_ttl'] ?? 3600); ?>" 
                                min="60" max="86400" step="60">
                            <p class="description">
                                <?php _e('Wie lange sollen Block-Daten gecacht werden? (60 - 86400 Sekunden)', 'container-block-designer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enable_debug"><?php _e('Debug-Modus', 'container-block-designer'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_debug" name="cbd_settings[enable_debug]" value="1" 
                                <?php checked(!empty($settings['enable_debug'])); ?>>
                            <p class="description">
                                <?php _e('Aktiviert erweiterte Debug-Informationen.', 'container-block-designer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="enable_import_export"><?php _e('Import/Export', 'container-block-designer'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enable_import_export" name="cbd_settings[enable_import_export]" value="1" 
                                <?php checked(!empty($settings['enable_import_export'])); ?>>
                            <p class="description">
                                <?php _e('Aktiviert Import/Export-Funktionalität für Blöcke.', 'container-block-designer'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_blocks_per_user"><?php _e('Max. Blöcke pro Benutzer', 'container-block-designer'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="max_blocks_per_user" name="cbd_settings[max_blocks_per_user]" 
                                value="<?php echo esc_attr($settings['max_blocks_per_user'] ?? 100); ?>" 
                                min="1" max="1000">
                            <p class="description">
                                <?php _e('Maximale Anzahl von Blöcken, die ein Benutzer erstellen kann.', 'container-block-designer'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Einstellungen speichern', 'container-block-designer')); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Save settings
     *
     * @return void
     */
    private function save_settings(): void {
        $settings = [];
        
        if (isset($_POST['cbd_settings'])) {
            $settings['enable_cache'] = !empty($_POST['cbd_settings']['enable_cache']);
            $settings['cache_ttl'] = absint($_POST['cbd_settings']['cache_ttl'] ?? 3600);
            $settings['enable_debug'] = !empty($_POST['cbd_settings']['enable_debug']);
            $settings['enable_import_export'] = !empty($_POST['cbd_settings']['enable_import_export']);
            $settings['max_blocks_per_user'] = absint($_POST['cbd_settings']['max_blocks_per_user'] ?? 100);
            $settings['enable_block_versioning'] = !empty($_POST['cbd_settings']['enable_block_versioning']);
            $settings['max_versions_per_block'] = absint($_POST['cbd_settings']['max_versions_per_block'] ?? 10);
        }
        
        update_option('cbd_settings', $settings);
        
        add_settings_error(
            'cbd_settings',
            'settings_updated',
            __('Einstellungen gespeichert.', 'container-block-designer'),
            'success'
        );
    }
    
    /**
     * Display admin notices
     *
     * @return void
     */
    private function display_notices(): void {
        settings_errors('cbd_settings');
        
        // Check for database updates
        $schema = new \ContainerBlockDesigner\Database\Schema();
        if ($schema->needs_update()) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e('Container Block Designer Datenbank muss aktualisiert werden.', 'container-block-designer'); ?>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=' . self::MENU_SLUG . '&action=update_db'), 'cbd_update_db')); ?>" class="button button-primary">
                        <?php _e('Datenbank aktualisieren', 'container-block-designer'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }
}