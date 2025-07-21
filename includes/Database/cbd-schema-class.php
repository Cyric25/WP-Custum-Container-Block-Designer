<?php
/**
 * Database Schema
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\Database;

/**
 * Database Schema class
 */
class Schema {
    /**
     * Create database tables
     *
     * @return void
     */
    public function create_tables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Blocks table
        $blocks_table = $wpdb->prefix . 'cbd_blocks';
        
        $blocks_sql = "CREATE TABLE {$blocks_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            block_name varchar(100) NOT NULL,
            block_title varchar(255) NOT NULL,
            block_description text,
            block_icon varchar(100) DEFAULT 'layout',
            block_category varchar(100) DEFAULT 'container-blocks',
            block_keywords text,
            block_config longtext NOT NULL,
            block_styles longtext,
            block_scripts longtext,
            allowed_blocks text,
            template_structure longtext,
            cache_key varchar(32),
            version int(11) DEFAULT 1,
            status enum('active','inactive','draft','trash') DEFAULT 'draft',
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY block_name (block_name),
            KEY status (status),
            KEY created_by (created_by),
            KEY cache_key (cache_key),
            FULLTEXT KEY search (block_title,block_description,block_keywords)
        ) $charset_collate;";
        
        // Block versions table
        $versions_table = $wpdb->prefix . 'cbd_block_versions';
        
        $versions_sql = "CREATE TABLE {$versions_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            block_id bigint(20) unsigned NOT NULL,
            version_number int(11) NOT NULL,
            config_snapshot longtext NOT NULL,
            styles_snapshot longtext,
            change_notes text,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY block_id (block_id),
            KEY version_lookup (block_id,version_number)
        ) $charset_collate;";
        
        // Audit log table
        $audit_table = $wpdb->prefix . 'cbd_audit_log';
        
        $audit_sql = "CREATE TABLE {$audit_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            block_id bigint(20) unsigned,
            user_id bigint(20) unsigned NOT NULL,
            action varchar(50) NOT NULL,
            details text,
            ip_address varchar(45),
            user_agent varchar(255),
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY block_id (block_id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Templates table (for future use)
        $templates_table = $wpdb->prefix . 'cbd_templates';
        
        $templates_sql = "CREATE TABLE {$templates_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            template_name varchar(100) NOT NULL,
            template_title varchar(255) NOT NULL,
            template_description text,
            template_category varchar(100),
            template_config longtext NOT NULL,
            template_screenshot varchar(255),
            is_premium tinyint(1) DEFAULT 0,
            downloads int(11) DEFAULT 0,
            rating decimal(3,2) DEFAULT 0.00,
            created_by bigint(20) unsigned NOT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY template_name (template_name),
            KEY template_category (template_category),
            KEY is_premium (is_premium),
            KEY downloads (downloads),
            KEY rating (rating)
        ) $charset_collate;";
        
        // Execute table creation
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($blocks_sql);
        dbDelta($versions_sql);
        dbDelta($audit_sql);
        dbDelta($templates_sql);
        
        // Update database version
        update_option('cbd_db_version', '1.0.0');
    }
    
    /**
     * Drop all plugin tables
     *
     * @return void
     */
    public function drop_tables(): void {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'cbd_blocks',
            $wpdb->prefix . 'cbd_block_versions',
            $wpdb->prefix . 'cbd_audit_log',
            $wpdb->prefix . 'cbd_templates',
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
        
        delete_option('cbd_db_version');
    }
    
    /**
     * Check if tables exist
     *
     * @return bool
     */
    public function tables_exist(): bool {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'cbd_blocks';
        $query = $wpdb->prepare(
            'SHOW TABLES LIKE %s',
            $wpdb->esc_like($table_name)
        );
        
        return $wpdb->get_var($query) === $table_name;
    }
    
    /**
     * Get current database version
     *
     * @return string
     */
    public function get_version(): string {
        return get_option('cbd_db_version', '0.0.0');
    }
    
    /**
     * Check if database needs update
     *
     * @return bool
     */
    public function needs_update(): bool {
        return version_compare($this->get_version(), '1.0.0', '<');
    }
}