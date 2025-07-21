<?php
/**
 * Blocks REST API Endpoint
 *
 * @package ContainerBlockDesigner
 * @since 1.0.0
 */

namespace ContainerBlockDesigner\API\Endpoints;

use ContainerBlockDesigner\Database\BlockRepository;
use ContainerBlockDesigner\Security\InputValidator;
use ContainerBlockDesigner\Security\OutputSanitizer;

/**
 * Blocks endpoint class
 */
class BlocksEndpoint extends \WP_REST_Controller {
    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'cbd/v1';
    
    /**
     * Route base
     *
     * @var string
     */
    protected $rest_base = 'blocks';
    
    /**
     * Block repository
     *
     * @var BlockRepository
     */
    private $repository;
    
    /**
     * Input validator
     *
     * @var InputValidator
     */
    private $validator;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->repository = new BlockRepository();
        $this->validator = new InputValidator();
    }
    
    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes() {
        // Collection endpoints
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
            ],
        ]);
        
        // Single item endpoints
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'id' => [
                        'validate_callback' => function($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
            ],
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::EDITABLE),
            ],
            [
                'methods' => \WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args' => [
                    'force' => [
                        'default' => false,
                        'type' => 'boolean',
                        'description' => __('Ob der Block permanent gelöscht werden soll.', 'container-block-designer'),
                    ],
                ],
            ],
        ]);
        
        // Special endpoints
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/duplicate', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'duplicate_item'],
            'permission_callback' => [$this, 'create_item_permissions_check'],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/export', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$this, 'export_item'],
            'permission_callback' => [$this, 'get_item_permissions_check'],
        ]);
        
        register_rest_route($this->namespace, '/' . $this->rest_base . '/import', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'import_item'],
            'permission_callback' => [$this, 'create_item_permissions_check'],
        ]);
    }
    
    /**
     * Get collection of blocks
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items($request) {
        $params = $request->get_query_params();
        
        // Pagination
        $page = absint($params['page'] ?? 1);
        $per_page = absint($params['per_page'] ?? 10);
        $offset = ($page - 1) * $per_page;
        
        // Filters
        $args = [
            'status' => $params['status'] ?? null,
            'search' => $params['search'] ?? null,
            'orderby' => $params['orderby'] ?? 'created_at',
            'order' => $params['order'] ?? 'DESC',
            'author' => $params['author'] ?? null,
            'limit' => $per_page,
            'offset' => $offset,
        ];
        
        // Get blocks
        $blocks = $this->repository->find_all($args);
        $total = $this->repository->count($args);
        
        // Prepare response
        $data = [];
        foreach ($blocks as $block) {
            $data[] = $this->prepare_item_for_response($block, $request);
        }
        
        $response = rest_ensure_response($data);
        
        // Add pagination headers
        $response->header('X-WP-Total', $total);
        $response->header('X-WP-TotalPages', ceil($total / $per_page));
        
        return $response;
    }
    
    /**
     * Get single block
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_item($request) {
        $id = absint($request['id']);
        $block = $this->repository->find($id);
        
        if (!$block) {
            return new \WP_Error(
                'rest_block_not_found',
                __('Block nicht gefunden.', 'container-block-designer'),
                ['status' => 404]
            );
        }
        
        $data = $this->prepare_item_for_response($block, $request);
        
        return rest_ensure_response($data);
    }
    
    /**
     * Create block
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function create_item($request) {
        $data = $request->get_json_params();
        
        // Validate input
        $validation = $this->validator->validate_block($data);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Check if block name already exists
        if ($this->repository->name_exists($data['name'])) {
            return new \WP_Error(
                'rest_block_exists',
                __('Ein Block mit diesem Namen existiert bereits.', 'container-block-designer'),
                ['status' => 400]
            );
        }
        
        // Check user limit
        $user_blocks = $this->repository->count_user_blocks(get_current_user_id());
        $max_blocks = get_option('cbd_settings')['max_blocks_per_user'] ?? 100;
        
        if ($user_blocks >= $max_blocks) {
            return new \WP_Error(
                'rest_block_limit',
                sprintf(__('Sie haben das Limit von %d Blöcken erreicht.', 'container-block-designer'), $max_blocks),
                ['status' => 403]
            );
        }
        
        // Sanitize data
        $sanitized = OutputSanitizer::sanitize_block_data($data);
        
        // Create block
        $block_id = $this->repository->create($sanitized);
        
        if (!$block_id) {
            return new \WP_Error(
                'rest_block_create_failed',
                __('Block konnte nicht erstellt werden.', 'container-block-designer'),
                ['status' => 500]
            );
        }
        
        // Get created block
        $block = $this->repository->find($block_id);
        
        $response = $this->prepare_item_for_response($block, $request);
        $response->set_status(201);
        $response->header('Location', rest_url($this->namespace . '/' . $this->rest_base . '/' . $block_id));
        
        return $response;
    }
    
    /**
     * Update block
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_item($request) {
        $id = absint($request['id']);
        $block = $this->repository->find($id);
        
        if (!$block) {
            return new \WP_Error(
                'rest_block_not_found',
                __('Block nicht gefunden.', 'container-block-designer'),
                ['status' => 404]
            );
        }
        
        // Check permissions
        if (!$this->can_edit_block($block)) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, diesen Block zu bearbeiten.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        $data = $request->get_json_params();
        
        // Validate input
        $validation = $this->validator->validate_block($data, true);
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Check if name change conflicts
        if (isset($data['name']) && $data['name'] !== $block['block_name']) {
            if ($this->repository->name_exists($data['name'])) {
                return new \WP_Error(
                    'rest_block_exists',
                    __('Ein Block mit diesem Namen existiert bereits.', 'container-block-designer'),
                    ['status' => 400]
                );
            }
        }
        
        // Sanitize data
        $sanitized = OutputSanitizer::sanitize_block_data($data);
        
        // Update block
        $updated = $this->repository->update($id, $sanitized);
        
        if (!$updated) {
            return new \WP_Error(
                'rest_block_update_failed',
                __('Block konnte nicht aktualisiert werden.', 'container-block-designer'),
                ['status' => 500]
            );
        }
        
        // Get updated block
        $block = $this->repository->find($id);
        
        return $this->prepare_item_for_response($block, $request);
    }
    
    /**
     * Delete block
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item($request) {
        $id = absint($request['id']);
        $force = $request['force'] ?? false;
        
        $block = $this->repository->find($id);
        
        if (!$block) {
            return new \WP_Error(
                'rest_block_not_found',
                __('Block nicht gefunden.', 'container-block-designer'),
                ['status' => 404]
            );
        }
        
        // Check permissions
        if (!$this->can_delete_block($block)) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, diesen Block zu löschen.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        // Prepare response before deletion
        $previous = $this->prepare_item_for_response($block, $request);
        
        // Delete or trash
        if ($force) {
            $deleted = $this->repository->delete($id);
        } else {
            $deleted = $this->repository->trash($id);
        }
        
        if (!$deleted) {
            return new \WP_Error(
                'rest_block_delete_failed',
                __('Block konnte nicht gelöscht werden.', 'container-block-designer'),
                ['status' => 500]
            );
        }
        
        $response = new \WP_REST_Response();
        $response->set_data([
            'deleted' => true,
            'previous' => $previous->get_data(),
        ]);
        
        return $response;
    }
    
    /**
     * Duplicate block
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function duplicate_item($request) {
        $id = absint($request['id']);
        $block = $this->repository->find($id);
        
        if (!$block) {
            return new \WP_Error(
                'rest_block_not_found',
                __('Block nicht gefunden.', 'container-block-designer'),
                ['status' => 404]
            );
        }
        
        // Generate new name
        $new_name = $this->generate_unique_name($block['block_name']);
        
        // Duplicate
        $new_id = $this->repository->duplicate($id, [
            'name' => $new_name,
            'title' => $block['block_title'] . ' ' . __('(Kopie)', 'container-block-designer'),
            'status' => 'draft',
        ]);
        
        if (!$new_id) {
            return new \WP_Error(
                'rest_block_duplicate_failed',
                __('Block konnte nicht dupliziert werden.', 'container-block-designer'),
                ['status' => 500]
            );
        }
        
        // Get new block
        $new_block = $this->repository->find($new_id);
        
        $response = $this->prepare_item_for_response($new_block, $request);
        $response->set_status(201);
        
        return $response;
    }
    
    /**
     * Prepare item for response
     *
     * @param array $item Block data
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function prepare_item_for_response($item, $request) {
        $data = [
            'id' => absint($item['id']),
            'name' => $item['block_name'],
            'title' => $item['block_title'],
            'description' => $item['block_description'],
            'icon' => $item['block_icon'],
            'category' => $item['block_category'],
            'keywords' => $item['block_keywords'] ? explode(',', $item['block_keywords']) : [],
            'config' => json_decode($item['block_config'], true),
            'status' => $item['status'],
            'version' => absint($item['version']),
            'author' => absint($item['created_by']),
            'created_at' => mysql_to_rfc3339($item['created_at']),
            'updated_at' => mysql_to_rfc3339($item['updated_at']),
        ];
        
        // Add author details if requested
        if ($request->get_param('_embed')) {
            $user = get_userdata($item['created_by']);
            if ($user) {
                $data['_embedded']['author'] = [
                    'id' => $user->ID,
                    'name' => $user->display_name,
                    'avatar' => get_avatar_url($user->ID),
                ];
            }
        }
        
        // Wrap the data in a response object
        $response = rest_ensure_response($data);
        
        // Add links
        $response->add_links($this->prepare_links($item));
        
        return $response;
    }
    
    /**
     * Prepare links for the request
     *
     * @param array $block Block data
     * @return array
     */
    protected function prepare_links($block) {
        $base = sprintf('%s/%s', $this->namespace, $this->rest_base);
        
        return [
            'self' => [
                'href' => rest_url(trailingslashit($base) . $block['id']),
            ],
            'collection' => [
                'href' => rest_url($base),
            ],
            'author' => [
                'href' => rest_url('wp/v2/users/' . $block['created_by']),
                'embeddable' => true,
            ],
        ];
    }
    
    /**
     * Get item schema
     *
     * @return array
     */
    public function get_item_schema() {
        if ($this->schema) {
            return $this->add_additional_fields_schema($this->schema);
        }
        
        $this->schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'block',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'description' => __('Eindeutige ID des Blocks.', 'container-block-designer'),
                    'type' => 'integer',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'name' => [
                    'description' => __('Der Block-Name (Slug).', 'container-block-designer'),
                    'type' => 'string',
                    'required' => true,
                    'pattern' => '^[a-z0-9-]+$',
                    'context' => ['view', 'edit'],
                ],
                'title' => [
                    'description' => __('Der Block-Titel.', 'container-block-designer'),
                    'type' => 'string',
                    'required' => true,
                    'context' => ['view', 'edit'],
                ],
                'description' => [
                    'description' => __('Die Block-Beschreibung.', 'container-block-designer'),
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'icon' => [
                    'description' => __('Das Block-Icon.', 'container-block-designer'),
                    'type' => 'string',
                    'default' => 'layout',
                    'context' => ['view', 'edit'],
                ],
                'category' => [
                    'description' => __('Die Block-Kategorie.', 'container-block-designer'),
                    'type' => 'string',
                    'default' => 'container-blocks',
                    'context' => ['view', 'edit'],
                ],
                'keywords' => [
                    'description' => __('Schlagwörter für die Suche.', 'container-block-designer'),
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                    'context' => ['view', 'edit'],
                ],
                'config' => [
                    'description' => __('Die Block-Konfiguration.', 'container-block-designer'),
                    'type' => 'object',
                    'required' => true,
                    'context' => ['view', 'edit'],
                ],
                'status' => [
                    'description' => __('Der Block-Status.', 'container-block-designer'),
                    'type' => 'string',
                    'enum' => ['active', 'inactive', 'draft', 'trash'],
                    'default' => 'draft',
                    'context' => ['view', 'edit'],
                ],
                'version' => [
                    'description' => __('Die Block-Version.', 'container-block-designer'),
                    'type' => 'integer',
                    'context' => ['view'],
                    'readonly' => true,
                ],
                'author' => [
                    'description' => __('Die Benutzer-ID des Block-Erstellers.', 'container-block-designer'),
                    'type' => 'integer',
                    'context' => ['view'],
                    'readonly' => true,
                ],
                'created_at' => [
                    'description' => __('Das Erstellungsdatum des Blocks.', 'container-block-designer'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view'],
                    'readonly' => true,
                ],
                'updated_at' => [
                    'description' => __('Das letzte Änderungsdatum des Blocks.', 'container-block-designer'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view'],
                    'readonly' => true,
                ],
            ],
        ];
        
        return $this->add_additional_fields_schema($this->schema);
    }
    
    /**
     * Get collection params
     *
     * @return array
     */
    public function get_collection_params() {
        return [
            'context' => $this->get_context_param(['default' => 'view']),
            'page' => [
                'description' => __('Aktuelle Seite der Sammlung.', 'container-block-designer'),
                'type' => 'integer',
                'default' => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum' => 1,
            ],
            'per_page' => [
                'description' => __('Maximale Anzahl von Elementen pro Seite.', 'container-block-designer'),
                'type' => 'integer',
                'default' => 10,
                'minimum' => 1,
                'maximum' => 100,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'search' => [
                'description' => __('Suche in Titel, Beschreibung und Schlagwörtern.', 'container-block-designer'),
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'status' => [
                'description' => __('Filtere nach Status.', 'container-block-designer'),
                'type' => 'string',
                'enum' => ['active', 'inactive', 'draft', 'trash'],
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'author' => [
                'description' => __('Filtere nach Autor.', 'container-block-designer'),
                'type' => 'integer',
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'orderby' => [
                'description' => __('Sortiere Sammlung nach Attribut.', 'container-block-designer'),
                'type' => 'string',
                'default' => 'created_at',
                'enum' => ['id', 'name', 'title', 'created_at', 'updated_at'],
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order' => [
                'description' => __('Sortierreihenfolge.', 'container-block-designer'),
                'type' => 'string',
                'default' => 'desc',
                'enum' => ['asc', 'desc'],
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }
    
    /**
     * Check permissions for getting items
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function get_items_permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie müssen angemeldet sein.', 'container-block-designer'),
                ['status' => 401]
            );
        }
        
        if (!current_user_can('edit_posts')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, Blöcke anzuzeigen.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        return true;
    }
    
    /**
     * Check permissions for getting single item
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function get_item_permissions_check($request) {
        return $this->get_items_permissions_check($request);
    }
    
    /**
     * Check permissions for creating items
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function create_item_permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie müssen angemeldet sein.', 'container-block-designer'),
                ['status' => 401]
            );
        }
        
        if (!current_user_can('cbd_create_blocks')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, Blöcke zu erstellen.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        return true;
    }
    
    /**
     * Check permissions for updating items
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function update_item_permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie müssen angemeldet sein.', 'container-block-designer'),
                ['status' => 401]
            );
        }
        
        if (!current_user_can('cbd_edit_blocks')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, Blöcke zu bearbeiten.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        return true;
    }
    
    /**
     * Check permissions for deleting items
     *
     * @param \WP_REST_Request $request Request object
     * @return bool|\WP_Error
     */
    public function delete_item_permissions_check($request) {
        if (!is_user_logged_in()) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie müssen angemeldet sein.', 'container-block-designer'),
                ['status' => 401]
            );
        }
        
        if (!current_user_can('cbd_delete_blocks')) {
            return new \WP_Error(
                'rest_forbidden',
                __('Sie haben keine Berechtigung, Blöcke zu löschen.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        return true;
    }
    
    /**
     * Check if user can edit specific block
     *
     * @param array $block Block data
     * @return bool
     */
    private function can_edit_block($block) {
        // Admins can edit all blocks
        if (current_user_can('cbd_manage_blocks')) {
            return true;
        }
        
        // Users can edit their own blocks
        if ($block['created_by'] == get_current_user_id()) {
            return current_user_can('cbd_edit_blocks');
        }
        
        return false;
    }
    
    /**
     * Check if user can delete specific block
     *
     * @param array $block Block data
     * @return bool
     */
    private function can_delete_block($block) {
        // Admins can delete all blocks
        if (current_user_can('cbd_delete_others_blocks')) {
            return true;
        }
        
        // Users can delete their own blocks
        if ($block['created_by'] == get_current_user_id()) {
            return current_user_can('cbd_delete_blocks');
        }
        
        return false;
    }
    
    /**
     * Generate unique block name
     *
     * @param string $base_name Base name
     * @return string
     */
    private function generate_unique_name($base_name) {
        $name = $base_name;
        $counter = 1;
        
        while ($this->repository->name_exists($name)) {
            $name = $base_name . '-' . $counter;
            $counter++;
        }
        
        return $name;
    }
}