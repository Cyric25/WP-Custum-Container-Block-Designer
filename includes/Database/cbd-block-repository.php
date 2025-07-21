<?php
/**
 * Block Repository
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\Database;

/**
 * Block Repository class
 */
class BlockRepository {
    /**
     * Table name
     *
     * @var string
     */
    private $table_name;
    
    /**
     * Cache group
     *
     * @var string
     */
    private $cache_group = 'cbd_blocks';
    
    /**
     * Cache TTL
     *
     * @var int
     */
    private $cache_ttl = 3600;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cbd_blocks';
    }
    
    /**
     * Find block by ID
     *
     * @param int $id Block ID
     * @return array|null
     */
    public function find($id) {
        $cache_key = "block_{$id}";
        $cached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        global $wpdb;
        $block = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d AND deleted_at IS NULL",
                $id
            ),
            ARRAY_A
        );
        
        if ($block) {
            wp_cache_set($cache_key, $block, $this->cache_group, $this->cache_ttl);
        }
        
        return $block;
    }
    
    /**
     * Find all blocks
     *
     * @param array $args Query arguments
     * @return array
     */
    public function find_all($args = []) {
        global $wpdb;
        
        $defaults = [
            'status' => null,
            'search' => null,
            'author' => null,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'limit' => 10,
            'offset' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $where = ['deleted_at IS NULL'];
        $values = [];
        
        if ($args['status']) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        
        if ($args['author']) {
            $where[] = 'created_by = %d';
            $values[] = $args['author'];
        }
        
        if ($args['search']) {
            $where[] = '(block_title LIKE %s OR block_description LIKE %s OR block_keywords LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        $order_clause = sprintf('%s %s', esc_sql($args['orderby']), esc_sql($args['order']));
        
        $query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$order_clause} LIMIT %d OFFSET %d";
        $values[] = $args['limit'];
        $values[] = $args['offset'];
        
        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }
        
        return $wpdb->get_results($query, ARRAY_A);
    }
    
    /**
     * Count blocks
     *
     * @param array $args Query arguments
     * @return int
     */
    public function count($args = []) {
        global $wpdb;
        
        $where = ['deleted_at IS NULL'];
        $values = [];
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $values[] = $args['status'];
        }
        
        if (!empty($args['author'])) {
            $where[] = 'created_by = %d';
            $values[] = $args['author'];
        }
        
        if (!empty($args['search'])) {
            $where[] = '(block_title LIKE %s OR block_description LIKE %s OR block_keywords LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
            $values[] = $search_term;
        }
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        
        if (!empty($values)) {
            $query = $wpdb->prepare($query, $values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    /**
     * Create new block
     *
     * @param array $data Block data
     * @return int|false Block ID or false on failure
     */
    public function create($data) {
        global $wpdb;
        
        $insert_data = [
            'block_name' => $data['name'],
            'block_title' => $data['title'],
            'block_description' => $data['description'] ?? '',
            'block_icon' => $data['icon'] ?? 'layout',
            'block_category' => $data['category'] ?? 'container-blocks',
            'block_keywords' => isset($data['keywords']) ? implode(',', $data['keywords']) : '',
            'block_config' => wp_json_encode($data['config'] ?? []),
            'block_styles' => $this->generate_styles($data['config'] ?? []),
            'allowed_blocks' => wp_json_encode($data['config']['allowedBlocks'] ?? []),
            'template_structure' => wp_json_encode($data['config']['template'] ?? []),
            'cache_key' => $this->generate_cache_key(),
            'status' => $data['status'] ?? 'draft',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ];
        
        $result = $wpdb->insert($this->table_name, $insert_data);
        
        if ($result) {
            $this->clear_cache();
            do_action('cbd_block_created', $wpdb->insert_id, $insert_data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update block
     *
     * @param int $id Block ID
     * @param array $data Block data
     * @return bool
     */
    public function update($id, $data) {
        global $wpdb;
        
        $update_data = [];
        
        if (isset($data['name'])) {
            $update_data['block_name'] = $data['name'];
        }
        
        if (isset($data['title'])) {
            $update_data['block_title'] = $data['title'];
        }
        
        if (isset($data['description'])) {
            $update_data['block_description'] = $data['description'];
        }
        
        if (isset($data['icon'])) {
            $update_data['block_icon'] = $data['icon'];
        }
        
        if (isset($data['category'])) {
            $update_data['block_category'] = $data['category'];
        }
        
        if (isset($data['keywords'])) {
            $update_data['block_keywords'] = is_array($data['keywords']) ? implode(',', $data['keywords']) : $data['keywords'];
        }
        
        if (isset($data['config'])) {
            $update_data['block_config'] = wp_json_encode($data['config']);
            $update_data['block_styles'] = $this->generate_styles($data['config']);
            
            if (isset($data['config']['allowedBlocks'])) {
                $update_data['allowed_blocks'] = wp_json_encode($data['config']['allowedBlocks']);
            }
            
            if (isset($data['config']['template'])) {
                $update_data['template_structure'] = wp_json_encode($data['config']['template']);
            }
        }
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
        }
        
        $update_data['updated_at'] = current_time('mysql');
        $update_data['cache_key'] = $this->generate_cache_key();
        
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            ['id' => $id]
        );
        
        if ($result !== false) {
            $this->clear_cache();
            wp_cache_delete("block_{$id}", $this->cache_group);
            do_action('cbd_block_updated', $id, $update_data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete block (soft delete)
     *
     * @param int $id Block ID
     * @return bool
     */
    public function trash($id) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->table_name,
            [
                'status' => 'trash',
                'deleted_at' => current_time('mysql'),
            ],
            ['id' => $id]
        );
        
        if ($result) {
            $this->clear_cache();
            wp_cache_delete("block_{$id}", $this->cache_group);
            do_action('cbd_block_trashed', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete block permanently
     *
     * @param int $id Block ID
     * @return bool
     */
    public function delete($id) {
        global $wpdb;
        
        $result = $wpdb->delete($this->table_name, ['id' => $id]);
        
        if ($result) {
            $this->clear_cache();
            wp_cache_delete("block_{$id}", $this->cache_group);
            do_action('cbd_block_deleted', $id);
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if block name exists
     *
     * @param string $name Block name
     * @return bool
     */
    public function name_exists($name) {
        global $wpdb;
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE block_name = %s AND deleted_at IS NULL",
                $name
            )
        );
        
        return $count > 0;
    }
    
    /**
     * Count user blocks
     *
     * @param int $user_id User ID
     * @return int
     */
    public function count_user_blocks($user_id) {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE created_by = %d AND deleted_at IS NULL",
                $user_id
            )
        );
    }
    
    /**
     * Find blocks by status
     *
     * @param string $status Status
     * @return array
     */
    public function find_by_status($status) {
        return $this->find_all(['status' => $status]);
    }
    
    /**
     * Duplicate block
     *
     * @param int $id Block ID
     * @param array $overrides Override data
     * @return int|false New block ID or false
     */
    public function duplicate($id, $overrides = []) {
        $block = $this->find($id);
        
        if (!$block) {
            return false;
        }
        
        $new_data = [
            'name' => $overrides['name'] ?? $block['block_name'] . '-copy',
            'title' => $overrides['title'] ?? $block['block_title'] . ' (Copy)',
            'description' => $block['block_description'],
            'icon' => $block['block_icon'],
            'category' => $block['block_category'],
            'keywords' => explode(',', $block['block_keywords']),
            'config' => json_decode($block['block_config'], true),
            'status' => $overrides['status'] ?? 'draft',
        ];
        
        return $this->create($new_data);
    }
    
    /**
     * Generate styles from config
     *
     * @param array $config Block config
     * @return string
     */
    private function generate_styles($config) {
        if (!isset($config['styles'])) {
            return '';
        }
        
        // This would be implemented by the StyleGenerator class
        // For now, return empty string
        return '';
    }
    
    /**
     * Generate cache key
     *
     * @return string
     */
    private function generate_cache_key() {
        return md5(uniqid('cbd_', true));
    }
    
    /**
     * Clear all cache
     *
     * @return void
     */
    private function clear_cache() {
        wp_cache_flush_group($this->cache_group);
    }
}