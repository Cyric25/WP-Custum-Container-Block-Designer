# Technisches Pflichtenheft: Container Block Designer für WordPress

**Version:** 1.0  
**Datum:** 19. Juli 2025  
**Status:** Entwurf  
**Referenz:** Lastenheft v1.0

---

## 1. Systemarchitektur

### 1.1 Architekturübersicht

```
┌─────────────────────────────────────────────────────────────┐
│                     WordPress Core                           │
├─────────────────────────────────────────────────────────────┤
│                   Container Block Designer                   │
├──────────────┬──────────────┬──────────────┬───────────────┤
│   Frontend   │   Backend    │  REST API    │   Database    │
│  Components  │   Modules    │  Endpoints   │   Handler     │
├──────────────┼──────────────┼──────────────┼───────────────┤
│   Gutenberg  │    Admin     │    JSON      │    MySQL      │
│   Blocks     │  Interface   │  Controllers │   Tables      │
└──────────────┴──────────────┴──────────────┴───────────────┘
```

### 1.2 Technologie-Stack

#### Backend
- **PHP**: 8.0+ mit Namespace-Unterstützung
- **WordPress**: 6.0+ mit Gutenberg Block API v2
- **Composer**: Dependency Management
- **PHPUnit**: 9.5+ für Unit-Tests

#### Frontend
- **React**: 18.2+ (WordPress-Bundle)
- **TypeScript**: 4.9+ für Type-Safety
- **Webpack**: 5+ (via @wordpress/scripts)
- **SCSS**: Für modulares Styling
- **Jest**: Für JavaScript-Tests

#### Build-Tools
- **Node.js**: 18.x LTS
- **npm**: 9.x
- **@wordpress/scripts**: Latest
- **@wordpress/env**: Für lokale Entwicklung

### 1.3 Verzeichnisstruktur (Detailliert)

```
container-block-designer/
├── .github/
│   └── workflows/
│       ├── tests.yml              # CI/CD Pipeline
│       └── deploy.yml             # Deployment Workflow
├── assets/
│   ├── css/
│   │   └── frontend.css          # Frontend Styles
│   ├── images/
│   └── icons/
├── src/                          # Entwicklungs-Quellcode
│   ├── admin/
│   │   ├── components/           # React-Komponenten
│   │   │   ├── BlockDesigner.tsx
│   │   │   ├── BlockList.tsx
│   │   │   ├── StylePanel.tsx
│   │   │   └── PreviewPane.tsx
│   │   ├── styles/
│   │   │   └── admin.scss
│   │   └── index.tsx            # Admin Entry Point
│   ├── blocks/
│   │   ├── container/
│   │   │   ├── block.json
│   │   │   ├── index.tsx
│   │   │   ├── edit.tsx
│   │   │   ├── save.tsx
│   │   │   └── style.scss
│   │   └── shared/
│   │       ├── components/
│   │       └── hooks/
│   └── utils/
│       ├── api.ts
│       ├── validators.ts
│       └── helpers.ts
├── includes/                     # PHP-Klassen
│   ├── Core/
│   │   ├── Plugin.php
│   │   ├── Loader.php
│   │   └── I18n.php
│   ├── Admin/
│   │   ├── AdminPage.php
│   │   ├── Settings.php
│   │   └── Assets.php
│   ├── Blocks/
│   │   ├── BlockRegistry.php
│   │   ├── BlockRenderer.php
│   │   └── BlockFactory.php
│   ├── Database/
│   │   ├── Schema.php
│   │   ├── Migration.php
│   │   └── Repository.php
│   ├── API/
│   │   ├── RestController.php
│   │   ├── Endpoints/
│   │   │   ├── BlocksEndpoint.php
│   │   │   └── TemplatesEndpoint.php
│   │   └── Validators/
│   └── Utils/
│       ├── Cache.php
│       ├── Security.php
│       └── Logger.php
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
├── build/                        # Kompilierte Dateien
├── languages/
├── composer.json
├── package.json
├── webpack.config.js
├── tsconfig.json
├── phpunit.xml
└── container-block-designer.php  # Haupt-Plugin-Datei
```

---

## 2. Datenbank-Design

### 2.1 Tabellen-Schema

#### Haupttabelle: `{prefix}_cbd_blocks`

```sql
CREATE TABLE `{prefix}_cbd_blocks` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_name` VARCHAR(100) NOT NULL,
    `block_title` VARCHAR(255) NOT NULL,
    `block_description` TEXT,
    `block_icon` VARCHAR(100) DEFAULT 'layout',
    `block_category` VARCHAR(100) DEFAULT 'container-blocks',
    `block_keywords` TEXT,
    `block_config` LONGTEXT NOT NULL COMMENT 'JSON configuration',
    `block_styles` LONGTEXT COMMENT 'Generated CSS',
    `block_scripts` LONGTEXT COMMENT 'Custom JavaScript',
    `allowed_blocks` TEXT COMMENT 'JSON array of allowed inner blocks',
    `template_structure` LONGTEXT COMMENT 'JSON template structure',
    `cache_key` VARCHAR(32),
    `version` INT(11) DEFAULT 1,
    `status` ENUM('active','inactive','draft','trash') DEFAULT 'draft',
    `created_by` BIGINT(20) UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `block_name` (`block_name`),
    KEY `status` (`status`),
    KEY `created_by` (`created_by`),
    KEY `cache_key` (`cache_key`),
    FULLTEXT KEY `search` (`block_title`,`block_description`,`block_keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

#### Versionstabelle: `{prefix}_cbd_block_versions`

```sql
CREATE TABLE `{prefix}_cbd_block_versions` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_id` BIGINT(20) UNSIGNED NOT NULL,
    `version_number` INT(11) NOT NULL,
    `config_snapshot` LONGTEXT NOT NULL,
    `styles_snapshot` LONGTEXT,
    `change_notes` TEXT,
    `created_by` BIGINT(20) UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `block_id` (`block_id`),
    KEY `version_lookup` (`block_id`, `version_number`),
    CONSTRAINT `fk_block_versions` FOREIGN KEY (`block_id`) 
        REFERENCES `{prefix}_cbd_blocks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

#### Audit-Log: `{prefix}_cbd_audit_log`

```sql
CREATE TABLE `{prefix}_cbd_audit_log` (
    `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `block_id` BIGINT(20) UNSIGNED,
    `user_id` BIGINT(20) UNSIGNED NOT NULL,
    `action` VARCHAR(50) NOT NULL,
    `details` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` VARCHAR(255),
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `block_id` (`block_id`),
    KEY `user_id` (`user_id`),
    KEY `action` (`action`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
```

### 2.2 Datenbank-Zugriffschicht

```php
namespace ContainerBlockDesigner\Database;

class BlockRepository {
    private $table_name;
    private $cache_group = 'cbd_blocks';
    private $cache_ttl = 3600; // 1 Stunde
    
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
            $block['config'] = json_decode($block['block_config'], true);
            wp_cache_set($cache_key, $block, $this->cache_group, $this->cache_ttl);
        }
        
        return $block;
    }
    
    public function create(array $data) {
        global $wpdb;
        
        // Validierung
        $validated = $this->validate($data);
        
        // Vorbereitung der Daten
        $insert_data = [
            'block_name' => $validated['name'],
            'block_title' => $validated['title'],
            'block_description' => $validated['description'] ?? '',
            'block_config' => wp_json_encode($validated['config']),
            'block_styles' => $this->generateStyles($validated['config']),
            'cache_key' => $this->generateCacheKey(),
            'created_by' => get_current_user_id(),
            'status' => 'draft'
        ];
        
        $wpdb->insert($this->table_name, $insert_data);
        
        if ($wpdb->insert_id) {
            $this->clearCache();
            do_action('cbd_block_created', $wpdb->insert_id, $insert_data);
            return $wpdb->insert_id;
        }
        
        return false;
    }
}
```

---

## 3. REST API Spezifikation

### 3.1 Authentifizierung

```php
namespace ContainerBlockDesigner\API;

class Authentication {
    public static function init() {
        add_filter('rest_authentication_errors', [__CLASS__, 'check_authentication']);
    }
    
    public static function check_authentication($error) {
        if (!empty($error)) {
            return $error;
        }
        
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
                __('Keine ausreichenden Berechtigungen.', 'container-block-designer'),
                ['status' => 403]
            );
        }
        
        return null;
    }
}
```

### 3.2 API Endpoints Implementation

```php
namespace ContainerBlockDesigner\API\Endpoints;

class BlocksEndpoint extends \WP_REST_Controller {
    protected $namespace = 'cbd/v1';
    protected $rest_base = 'blocks';
    
    public function register_routes() {
        // GET /cbd/v1/blocks
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
        
        // GET, PUT, DELETE /cbd/v1/blocks/{id}
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
            ],
        ]);
        
        // POST /cbd/v1/blocks/{id}/duplicate
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/duplicate', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'duplicate_item'],
            'permission_callback' => [$this, 'create_item_permissions_check'],
        ]);
    }
    
    public function get_item_schema() {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'block',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'description' => 'Unique identifier for the block.',
                    'type' => 'integer',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
                'name' => [
                    'description' => 'The block name (slug).',
                    'type' => 'string',
                    'required' => true,
                    'pattern' => '^[a-z0-9-]+$',
                    'context' => ['view', 'edit'],
                ],
                'title' => [
                    'description' => 'The block title.',
                    'type' => 'string',
                    'required' => true,
                    'context' => ['view', 'edit'],
                ],
                'description' => [
                    'description' => 'The block description.',
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
                'config' => [
                    'description' => 'The block configuration.',
                    'type' => 'object',
                    'required' => true,
                    'context' => ['view', 'edit'],
                    'properties' => [
                        'styles' => [
                            'type' => 'object',
                            'properties' => [
                                'desktop' => ['type' => 'object'],
                                'tablet' => ['type' => 'object'],
                                'mobile' => ['type' => 'object'],
                            ],
                        ],
                        'allowedBlocks' => [
                            'type' => 'array',
                            'items' => ['type' => 'string'],
                        ],
                        'template' => [
                            'type' => 'array',
                        ],
                    ],
                ],
                'status' => [
                    'description' => 'The block status.',
                    'type' => 'string',
                    'enum' => ['active', 'inactive', 'draft', 'trash'],
                    'context' => ['view', 'edit'],
                ],
                'created_at' => [
                    'description' => 'The date the block was created.',
                    'type' => 'string',
                    'format' => 'date-time',
                    'context' => ['view', 'edit'],
                    'readonly' => true,
                ],
            ],
        ];
    }
}
```

### 3.3 Request/Response Beispiele

#### Block erstellen
```http
POST /wp-json/cbd/v1/blocks
Content-Type: application/json
X-WP-Nonce: {nonce}

{
    "name": "hero-section",
    "title": "Hero Section",
    "description": "Ein großer Hero-Bereich",
    "config": {
        "styles": {
            "desktop": {
                "padding": {"top": 60, "right": 20, "bottom": 60, "left": 20},
                "backgroundColor": "#f8f9fa"
            }
        },
        "allowedBlocks": ["core/heading", "core/paragraph", "core/buttons"]
    }
}
```

#### Response
```json
{
    "id": 123,
    "name": "hero-section",
    "title": "Hero Section",
    "description": "Ein großer Hero-Bereich",
    "config": {
        "styles": {
            "desktop": {
                "padding": {"top": 60, "right": 20, "bottom": 60, "left": 20},
                "backgroundColor": "#f8f9fa"
            }
        },
        "allowedBlocks": ["core/heading", "core/paragraph", "core/buttons"]
    },
    "status": "draft",
    "created_at": "2025-07-19T10:30:00Z",
    "_links": {
        "self": [{
            "href": "https://example.com/wp-json/cbd/v1/blocks/123"
        }],
        "collection": [{
            "href": "https://example.com/wp-json/cbd/v1/blocks"
        }]
    }
}
```

---

## 4. Frontend-Architektur

### 4.1 React-Komponenten-Struktur

```typescript
// src/admin/components/BlockDesigner.tsx
import React, { useState, useEffect, useCallback } from 'react';
import { Panel, PanelBody, Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

interface BlockDesignerProps {
    blockId?: number;
    onSave: (data: BlockConfig) => void;
}

interface BlockConfig {
    name: string;
    title: string;
    description: string;
    styles: {
        desktop: StyleConfig;
        tablet?: Partial<StyleConfig>;
        mobile?: Partial<StyleConfig>;
    };
    allowedBlocks: string[];
    template: any[];
}

interface StyleConfig {
    padding: BoxSpacing;
    margin: BoxSpacing;
    backgroundColor: string;
    borderWidth: number;
    borderColor: string;
    borderRadius: number;
    minHeight?: string;
    maxWidth?: string;
}

interface BoxSpacing {
    top: number;
    right: number;
    bottom: number;
    left: number;
}

const BlockDesigner: React.FC<BlockDesignerProps> = ({ blockId, onSave }) => {
    const [config, setConfig] = useState<BlockConfig>(getDefaultConfig());
    const [activeDevice, setActiveDevice] = useState<'desktop' | 'tablet' | 'mobile'>('desktop');
    const [isDirty, setIsDirty] = useState(false);
    
    // WordPress Data Layer
    const { saveBlock, updateBlock } = useDispatch('container-block-designer');
    const existingBlock = useSelect(
        (select) => blockId ? select('container-block-designer').getBlock(blockId) : null,
        [blockId]
    );
    
    // Lade existierende Block-Daten
    useEffect(() => {
        if (existingBlock) {
            setConfig(existingBlock.config);
        }
    }, [existingBlock]);
    
    // Style-Update Handler
    const updateStyle = useCallback((property: keyof StyleConfig, value: any) => {
        setConfig(prev => ({
            ...prev,
            styles: {
                ...prev.styles,
                [activeDevice]: {
                    ...prev.styles[activeDevice],
                    [property]: value
                }
            }
        }));
        setIsDirty(true);
    }, [activeDevice]);
    
    // Speichern Handler
    const handleSave = async () => {
        try {
            if (blockId) {
                await updateBlock(blockId, config);
            } else {
                await saveBlock(config);
            }
            setIsDirty(false);
            onSave(config);
        } catch (error) {
            console.error('Fehler beim Speichern:', error);
        }
    };
    
    return (
        <div className="cbd-block-designer">
            <div className="cbd-designer-header">
                <DeviceSelector 
                    activeDevice={activeDevice}
                    onChange={setActiveDevice}
                />
                <Button
                    variant="primary"
                    onClick={handleSave}
                    disabled={!isDirty}
                >
                    {__('Speichern', 'container-block-designer')}
                </Button>
            </div>
            
            <div className="cbd-designer-content">
                <div className="cbd-designer-controls">
                    <Panel>
                        <PanelBody title={__('Grundeinstellungen', 'container-block-designer')}>
                            <TextControl
                                label={__('Block Name', 'container-block-designer')}
                                value={config.name}
                                onChange={(name) => {
                                    setConfig(prev => ({ ...prev, name }));
                                    setIsDirty(true);
                                }}
                            />
                            {/* Weitere Controls... */}
                        </PanelBody>
                        
                        <PanelBody title={__('Spacing', 'container-block-designer')}>
                            <SpacingControl
                                label={__('Padding', 'container-block-designer')}
                                value={config.styles[activeDevice]?.padding}
                                onChange={(padding) => updateStyle('padding', padding)}
                            />
                            {/* Weitere Spacing Controls... */}
                        </PanelBody>
                    </Panel>
                </div>
                
                <div className="cbd-designer-preview">
                    <BlockPreview 
                        config={config}
                        device={activeDevice}
                    />
                </div>
            </div>
        </div>
    );
};
```

### 4.2 State Management

```typescript
// src/store/index.ts
import { createReduxStore, register } from '@wordpress/data';

interface BlockState {
    blocks: Record<number, BlockData>;
    isLoading: boolean;
    error: string | null;
}

const DEFAULT_STATE: BlockState = {
    blocks: {},
    isLoading: false,
    error: null,
};

const actions = {
    setBlocks(blocks: BlockData[]) {
        return {
            type: 'SET_BLOCKS',
            blocks,
        };
    },
    
    saveBlock(config: BlockConfig) {
        return async ({ dispatch }) => {
            dispatch({ type: 'SET_LOADING', isLoading: true });
            
            try {
                const response = await apiFetch({
                    path: '/cbd/v1/blocks',
                    method: 'POST',
                    data: config,
                });
                
                dispatch({
                    type: 'ADD_BLOCK',
                    block: response,
                });
                
                return response;
            } catch (error) {
                dispatch({
                    type: 'SET_ERROR',
                    error: error.message,
                });
                throw error;
            } finally {
                dispatch({ type: 'SET_LOADING', isLoading: false });
            }
        };
    },
};

const reducer = (state = DEFAULT_STATE, action: any) => {
    switch (action.type) {
        case 'SET_BLOCKS':
            const blocksMap = {};
            action.blocks.forEach(block => {
                blocksMap[block.id] = block;
            });
            return {
                ...state,
                blocks: blocksMap,
            };
            
        case 'ADD_BLOCK':
            return {
                ...state,
                blocks: {
                    ...state.blocks,
                    [action.block.id]: action.block,
                },
            };
            
        case 'SET_LOADING':
            return {
                ...state,
                isLoading: action.isLoading,
            };
            
        case 'SET_ERROR':
            return {
                ...state,
                error: action.error,
            };
            
        default:
            return state;
    }
};

const selectors = {
    getBlocks(state: BlockState) {
        return Object.values(state.blocks);
    },
    
    getBlock(state: BlockState, id: number) {
        return state.blocks[id] || null;
    },
    
    isLoading(state: BlockState) {
        return state.isLoading;
    },
};

export const store = createReduxStore('container-block-designer', {
    reducer,
    actions,
    selectors,
});

register(store);
```

---

## 5. Block-Rendering-Engine

### 5.1 Dynamic Block Registration

```php
namespace ContainerBlockDesigner\Blocks;

class BlockRegistry {
    private $blocks = [];
    private $repository;
    
    public function __construct(BlockRepository $repository) {
        $this->repository = $repository;
    }
    
    public function init() {
        add_action('init', [$this, 'register_blocks'], 20);
        add_filter('block_categories_all', [$this, 'add_block_category']);
    }
    
    public function register_blocks() {
        // Registriere Basis Container Block
        register_block_type(__DIR__ . '/../../build/blocks/container', [
            'render_callback' => [$this, 'render_container_block'],
        ]);
        
        // Lade alle aktiven Blöcke aus der Datenbank
        $active_blocks = $this->repository->findByStatus('active');
        
        foreach ($active_blocks as $block) {
            $this->register_dynamic_variation($block);
        }
    }
    
    private function register_dynamic_variation($block) {
        // Registriere Block-Variation für jeden gespeicherten Block
        wp_register_script(
            "cbd-variation-{$block['id']}",
            false,
            [],
            CBD_VERSION,
            true
        );
        
        wp_add_inline_script(
            "cbd-variation-{$block['id']}",
            $this->generate_variation_script($block),
            'after'
        );
        
        wp_enqueue_script("cbd-variation-{$block['id']}");
    }
    
    private function generate_variation_script($block) {
        $config = json_encode([
            'name' => "cbd-{$block['block_name']}",
            'title' => $block['block_title'],
            'description' => $block['block_description'],
            'icon' => $block['block_icon'],
            'attributes' => [
                'blockId' => $block['id'],
            ],
            'scope' => ['inserter'],
            'isActive' => ['blockId'],
        ]);
        
        return "
        wp.blocks.registerBlockVariation(
            'container-block-designer/container',
            {$config}
        );
        ";
    }
    
    public function render_container_block($attributes, $content, $block) {
        $renderer = new BlockRenderer();
        return $renderer->render($attributes, $content, $block);
    }
}
```

### 5.2 CSS Generation Engine

```php
namespace ContainerBlockDesigner\Styles;

class StyleGenerator {
    private $breakpoints = [
        'desktop' => null,
        'tablet' => 1024,
        'mobile' => 600,
    ];
    
    public function generateCSS($block_id, $config) {
        $css = '';
        $selector = ".cbd-container-{$block_id}";
        
        // Desktop Styles (Basis)
        if (isset($config['styles']['desktop'])) {
            $css .= $this->generateDeviceCSS(
                $selector,
                $config['styles']['desktop'],
                'desktop'
            );
        }
        
        // Tablet Styles
        if (isset($config['styles']['tablet']) && $config['styles']['tablet']) {
            $css .= $this->generateDeviceCSS(
                $selector,
                array_merge(
                    $config['styles']['desktop'] ?? [],
                    $config['styles']['tablet']
                ),
                'tablet'
            );
        }
        
        // Mobile Styles
        if (isset($config['styles']['mobile']) && $config['styles']['mobile']) {
            $css .= $this->generateDeviceCSS(
                $selector,
                array_merge(
                    $config['styles']['desktop'] ?? [],
                    $config['styles']['tablet'] ?? [],
                    $config['styles']['mobile']
                ),
                'mobile'
            );
        }
        
        // Minifizieren
        return $this->minifyCSS($css);
    }
    
    private function generateDeviceCSS($selector, $styles, $device) {
        $rules = [];
        
        // Padding
        if (isset($styles['padding'])) {
            $rules[] = sprintf(
                'padding: %spx %spx %spx %spx',
                $styles['padding']['top'],
                $styles['padding']['right'],
                $styles['padding']['bottom'],
                $styles['padding']['left']
            );
        }
        
        // Margin
        if (isset($styles['margin'])) {
            $rules[] = sprintf(
                'margin: %spx %spx %spx %spx',
                $styles['margin']['top'],
                $styles['margin']['right'],
                $styles['margin']['bottom'],
                $styles['margin']['left']
            );
        }
        
        // Background
        if (isset($styles['backgroundColor'])) {
            $rules[] = 'background-color: ' . $styles['backgroundColor'];
        }
        
        // Border
        if (isset($styles['borderWidth']) && $styles['borderWidth'] > 0) {
            $rules[] = sprintf(
                'border: %dpx solid %s',
                $styles['borderWidth'],
                $styles['borderColor'] ?? '#ddd'
            );
        }
        
        // Border Radius
        if (isset($styles['borderRadius'])) {
            $rules[] = 'border-radius: ' . $styles['borderRadius'] . 'px';
        }
        
        // Min/Max Dimensions
        if (isset($styles['minHeight'])) {
            $rules[] = 'min-height: ' . $styles['minHeight'];
        }
        
        if (isset($styles['maxWidth'])) {
            $rules[] = 'max-width: ' . $styles['maxWidth'];
            $rules[] = 'margin-left: auto';
            $rules[] = 'margin-right: auto';
        }
        
        // Flexbox
        if (isset($styles['display']) && $styles['display'] === 'flex') {
            $rules[] = 'display: flex';
            
            if (isset($styles['flexDirection'])) {
                $rules[] = 'flex-direction: ' . $styles['flexDirection'];
            }
            
            if (isset($styles['justifyContent'])) {
                $rules[] = 'justify-content: ' . $styles['justifyContent'];
            }
            
            if (isset($styles['alignItems'])) {
                $rules[] = 'align-items: ' . $styles['alignItems'];
            }
        }
        
        // CSS generieren
        if (empty($rules)) {
            return '';
        }
        
        $css = "{$selector} { " . implode('; ', $rules) . "; }\n";
        
        // Media Query wrappen wenn nicht Desktop
        if ($device !== 'desktop' && isset($this->breakpoints[$device])) {
            $css = "@media (max-width: {$this->breakpoints[$device]}px) { {$css} }\n";
        }
        
        return $css;
    }
    
    private function minifyCSS($css) {
        // Entferne Kommentare
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Entferne Whitespace
        $css = str_replace(["\r\n", "\r", "\n", "\t", '  ', '    '], '', $css);
        // Entferne Leerzeichen um Selektoren
        $css = preg_replace('/\s*([{}|:;,])\s+/', '$1', $css);
        
        return $css;
    }
}
```

---

## 6. Sicherheitskonzept

### 6.1 Input Validation

```php
namespace ContainerBlockDesigner\Security;

class InputValidator {
    private $rules = [
        'block_name' => [
            'type' => 'string',
            'pattern' => '/^[a-z0-9-]+$/',
            'min_length' => 3,
            'max_length' => 50,
        ],
        'block_title' => [
            'type' => 'string',
            'min_length' => 3,
            'max_length' => 100,
        ],
        'padding' => [
            'type' => 'object',
            'properties' => [
                'top' => ['type' => 'integer', 'min' => 0, 'max' => 500],
                'right' => ['type' => 'integer', 'min' => 0, 'max' => 500],
                'bottom' => ['type' => 'integer', 'min' => 0, 'max' => 500],
                'left' => ['type' => 'integer', 'min' => 0, 'max' => 500],
            ],
        ],
        'backgroundColor' => [
            'type' => 'string',
            'pattern' => '/^#[0-9A-Fa-f]{6}$|^rgba?\([\d\s,]+\)$/',
        ],
    ];
    
    public function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field]) && isset($rule['required']) && $rule['required']) {
                $errors[$field] = 'Field is required';
                continue;
            }
            
            if (isset($data[$field])) {
                $value = $data[$field];
                
                // Type validation
                if (isset($rule['type']) && gettype($value) !== $rule['type']) {
                    $errors[$field] = "Field must be of type {$rule['type']}";
                    continue;
                }
                
                // Pattern validation
                if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                    $errors[$field] = 'Field format is invalid';
                }
                
                // Length validation
                if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                    $errors[$field] = "Field must be at least {$rule['min_length']} characters";
                }
                
                if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                    $errors[$field] = "Field must not exceed {$rule['max_length']} characters";
                }
                
                // Numeric validation
                if (isset($rule['min']) && $value < $rule['min']) {
                    $errors[$field] = "Field must be at least {$rule['min']}";
                }
                
                if (isset($rule['max']) && $value > $rule['max']) {
                    $errors[$field] = "Field must not exceed {$rule['max']}";
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }
}
```

### 6.2 Output Sanitization

```php
namespace ContainerBlockDesigner\Security;

class OutputSanitizer {
    public static function sanitizeBlockConfig($config) {
        $sanitized = [];
        
        // Name sanitization
        if (isset($config['name'])) {
            $sanitized['name'] = sanitize_key($config['name']);
        }
        
        // Title sanitization
        if (isset($config['title'])) {
            $sanitized['title'] = sanitize_text_field($config['title']);
        }
        
        // Description sanitization
        if (isset($config['description'])) {
            $sanitized['description'] = sanitize_textarea_field($config['description']);
        }
        
        // Styles sanitization
        if (isset($config['styles'])) {
            $sanitized['styles'] = self::sanitizeStyles($config['styles']);
        }
        
        // Allowed blocks sanitization
        if (isset($config['allowedBlocks']) && is_array($config['allowedBlocks'])) {
            $sanitized['allowedBlocks'] = array_map('sanitize_text_field', $config['allowedBlocks']);
        }
        
        return $sanitized;
    }
    
    private static function sanitizeStyles($styles) {
        $sanitized = [];
        
        foreach (['desktop', 'tablet', 'mobile'] as $device) {
            if (isset($styles[$device])) {
                $sanitized[$device] = self::sanitizeDeviceStyles($styles[$device]);
            }
        }
        
        return $sanitized;
    }
    
    private static function sanitizeDeviceStyles($styles) {
        $sanitized = [];
        
        // Spacing
        foreach (['padding', 'margin'] as $property) {
            if (isset($styles[$property])) {
                $sanitized[$property] = array_map('intval', $styles[$property]);
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
                $sanitized[$prop] = intval($styles[$prop]);
            }
        }
        
        // Text values
        $text_props = ['display', 'flexDirection', 'justifyContent', 'alignItems'];
        foreach ($text_props as $prop) {
            if (isset($styles[$prop])) {
                $sanitized[$prop] = sanitize_text_field($styles[$prop]);
            }
        }
        
        return $sanitized;
    }
}
```

### 6.3 Permission System

```php
namespace ContainerBlockDesigner\Security;

class Permissions {
    const CAPABILITY_PREFIX = 'cbd_';
    
    public static function init() {
        add_action('init', [__CLASS__, 'register_capabilities']);
    }
    
    public static function register_capabilities() {
        $admin_role = get_role('administrator');
        $editor_role = get_role('editor');
        
        // Admin capabilities
        $admin_caps = [
            'cbd_manage_blocks',
            'cbd_create_blocks',
            'cbd_edit_blocks',
            'cbd_delete_blocks',
            'cbd_publish_blocks',
            'cbd_import_blocks',
            'cbd_export_blocks',
            'cbd_manage_settings',
        ];
        
        foreach ($admin_caps as $cap) {
            $admin_role->add_cap($cap);
        }
        
        // Editor capabilities
        $editor_caps = [
            'cbd_create_blocks',
            'cbd_edit_blocks',
            'cbd_publish_blocks',
        ];
        
        foreach ($editor_caps as $cap) {
            $editor_role->add_cap($cap);
        }
    }
    
    public static function can_manage_blocks() {
        return current_user_can('cbd_manage_blocks');
    }
    
    public static function can_edit_block($block_id) {
        $block = get_block($block_id);
        
        if (!$block) {
            return false;
        }
        
        // Admins können alle Blöcke bearbeiten
        if (current_user_can('cbd_manage_blocks')) {
            return true;
        }
        
        // Eigene Blöcke können bearbeitet werden
        if ($block['created_by'] == get_current_user_id()) {
            return current_user_can('cbd_edit_blocks');
        }
        
        return false;
    }
}
```

---

## 7. Performance-Optimierung

### 7.1 Caching-Strategie

```php
namespace ContainerBlockDesigner\Performance;

class CacheManager {
    private $cache_group = 'cbd_blocks';
    private $cache_ttl = HOUR_IN_SECONDS;
    
    public function get($key) {
        // Versuche aus Object Cache
        $cached = wp_cache_get($key, $this->cache_group);
        
        if (false !== $cached) {
            return $cached;
        }
        
        // Versuche aus Transient (Fallback)
        $transient = get_transient("cbd_{$key}");
        
        if (false !== $transient) {
            // Füge zu Object Cache hinzu
            wp_cache_set($key, $transient, $this->cache_group, $this->cache_ttl);
            return $transient;
        }
        
        return false;
    }
    
    public function set($key, $value, $ttl = null) {
        if (null === $ttl) {
            $ttl = $this->cache_ttl;
        }
        
        // Object Cache
        wp_cache_set($key, $value, $this->cache_group, $ttl);
        
        // Transient als Fallback
        set_transient("cbd_{$key}", $value, $ttl);
        
        return true;
    }
    
    public function flush($key = null) {
        if ($key) {
            wp_cache_delete($key, $this->cache_group);
            delete_transient("cbd_{$key}");
        } else {
            // Flush entire group
            wp_cache_flush_group($this->cache_group);
            
            // Lösche alle CBD Transients
            global $wpdb;
            $wpdb->query(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE '_transient_cbd_%' 
                OR option_name LIKE '_transient_timeout_cbd_%'"
            );
        }
    }
}
```

### 7.2 Asset-Optimierung

```php
namespace ContainerBlockDesigner\Performance;

class AssetOptimizer {
    private $inline_styles = [];
    private $critical_css = '';
    
    public function init() {
        add_action('init', [$this, 'register_assets']);
        add_action('wp_enqueue_scripts', [$this, 'optimize_frontend_assets']);
        add_action('wp_head', [$this, 'print_critical_css'], 5);
        add_action('wp_footer', [$this, 'print_deferred_styles'], 20);
    }
    
    public function register_assets() {
        // Registriere minifizierte Assets
        wp_register_style(
            'cbd-frontend',
            CBD_PLUGIN_URL . 'build/frontend.min.css',
            [],
            CBD_VERSION
        );
        
        wp_register_script(
            'cbd-frontend',
            CBD_PLUGIN_URL . 'build/frontend.min.js',
            [],
            CBD_VERSION,
            true
        );
    }
    
    public function optimize_frontend_assets() {
        if (!$this->has_cbd_blocks()) {
            return;
        }
        
        // Lade nur benötigte Block-Styles
        $used_blocks = $this->get_used_blocks();
        
        foreach ($used_blocks as $block_id) {
            $styles = $this->get_block_styles($block_id);
            
            if (strlen($styles) < 1000) {
                // Kleine Styles inline
                $this->inline_styles[$block_id] = $styles;
            } else {
                // Große Styles als separate Datei
                wp_enqueue_style(
                    "cbd-block-{$block_id}",
                    $this->get_block_style_url($block_id),
                    ['cbd-frontend'],
                    $this->get_block_version($block_id)
                );
            }
        }
        
        // Enqueue Frontend JS nur wenn nötig
        if ($this->needs_frontend_js()) {
            wp_enqueue_script('cbd-frontend');
        }
    }
    
    public function print_critical_css() {
        if (empty($this->critical_css)) {
            return;
        }
        
        echo '<style id="cbd-critical-css">';
        echo $this->critical_css;
        echo '</style>';
    }
    
    private function has_cbd_blocks() {
        global $post;
        
        if (!$post) {
            return false;
        }
        
        return has_block('container-block-designer/container', $post);
    }
}
```

### 7.3 Database Query Optimierung

```php
namespace ContainerBlockDesigner\Performance;

class QueryOptimizer {
    private $query_cache = [];
    
    public function get_blocks_batch($ids) {
        // Sortiere IDs für Cache-Key
        sort($ids);
        $cache_key = 'batch_' . md5(implode(',', $ids));
        
        if (isset($this->query_cache[$cache_key])) {
            return $this->query_cache[$cache_key];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'cbd_blocks';
        
        // Prepared statement mit IN clause
        $placeholders = array_fill(0, count($ids), '%d');
        $sql = $wpdb->prepare(
            "SELECT * FROM {$table} 
            WHERE id IN (" . implode(',', $placeholders) . ") 
            AND status = 'active' 
            AND deleted_at IS NULL",
            $ids
        );
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        // Index by ID
        $blocks = [];
        foreach ($results as $row) {
            $blocks[$row['id']] = $row;
        }
        
        $this->query_cache[$cache_key] = $blocks;
        
        return $blocks;
    }
    
    public function search_blocks($search_term, $limit = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'cbd_blocks';
        
        // Fulltext search mit Relevanz
        $sql = $wpdb->prepare(
            "SELECT *, 
            MATCH(block_title, block_description, block_keywords) 
            AGAINST(%s IN NATURAL LANGUAGE MODE) AS relevance
            FROM {$table}
            WHERE status = 'active'
            AND deleted_at IS NULL
            AND MATCH(block_title, block_description, block_keywords) 
            AGAINST(%s IN NATURAL LANGUAGE MODE)
            ORDER BY relevance DESC
            LIMIT %d OFFSET %d",
            $search_term,
            $search_term,
            $limit,
            $offset
        );
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
}
```

---

## 8. Testing-Strategie

### 8.1 Unit Tests (PHPUnit)

```php
// tests/unit/BlockRepositoryTest.php
namespace ContainerBlockDesigner\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ContainerBlockDesigner\Database\BlockRepository;

class BlockRepositoryTest extends TestCase {
    private $repository;
    
    protected function setUp(): void {
        parent::setUp();
        $this->repository = new BlockRepository();
    }
    
    public function test_create_block_with_valid_data() {
        $data = [
            'name' => 'test-block',
            'title' => 'Test Block',
            'config' => [
                'styles' => [
                    'desktop' => [
                        'padding' => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20]
                    ]
                ]
            ]
        ];
        
        $block_id = $this->repository->create($data);
        
        $this->assertIsInt($block_id);
        $this->assertGreaterThan(0, $block_id);
    }
    
    public function test_create_block_with_invalid_name() {
        $this->expectException(\InvalidArgumentException::class);
        
        $data = [
            'name' => 'Test Block!', // Invalid characters
            'title' => 'Test Block',
            'config' => []
        ];
        
        $this->repository->create($data);
    }
    
    public function test_find_existing_block() {
        // Erstelle Test-Block
        $block_id = $this->repository->create([
            'name' => 'find-test',
            'title' => 'Find Test',
            'config' => []
        ]);
        
        $block = $this->repository->find($block_id);
        
        $this->assertIsArray($block);
        $this->assertEquals('find-test', $block['block_name']);
    }
}
```

### 8.2 Integration Tests

```php
// tests/integration/RestApiTest.php
namespace ContainerBlockDesigner\Tests\Integration;

use WP_UnitTestCase;
use WP_REST_Request;

class RestApiTest extends WP_UnitTestCase {
    private $server;
    
    public function setUp(): void {
        parent::setUp();
        
        global $wp_rest_server;
        $this->server = $wp_rest_server = new \WP_REST_Server();
        do_action('rest_api_init');
        
        // Create test user
        $this->user_id = $this->factory->user->create([
            'role' => 'administrator'
        ]);
        wp_set_current_user($this->user_id);
    }
    
    public function test_get_blocks_endpoint() {
        $request = new WP_REST_Request('GET', '/cbd/v1/blocks');
        $response = $this->server->dispatch($request);
        
        $this->assertEquals(200, $response->get_status());
        $this->assertIsArray($response->get_data());
    }
    
    public function test_create_block_endpoint() {
        $request = new WP_REST_Request('POST', '/cbd/v1/blocks');
        $request->set_body_params([
            'name' => 'api-test-block',
            'title' => 'API Test Block',
            'config' => [
                'styles' => [
                    'desktop' => [
                        'backgroundColor' => '#ffffff'
                    ]
                ]
            ]
        ]);
        
        $response = $this->server->dispatch($request);
        
        $this->assertEquals(201, $response->get_status());
        $data = $response->get_data();
        $this->assertEquals('api-test-block', $data['name']);
    }
    
    public function test_unauthorized_access() {
        wp_set_current_user(0); // Logout
        
        $request = new WP_REST_Request('GET', '/cbd/v1/blocks');
        $response = $this->server->dispatch($request);
        
        $this->assertEquals(401, $response->get_status());
    }
}
```

### 8.3 E2E Tests (Playwright)

```typescript
// tests/e2e/block-designer.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Block Designer', () => {
    test.beforeEach(async ({ page }) => {
        // Login
        await page.goto('/wp-login.php');
        await page.fill('#user_login', 'admin');
        await page.fill('#user_pass', 'password');
        await page.click('#wp-submit');
        
        // Navigate to Block Designer
        await page.goto('/wp-admin/admin.php?page=container-block-designer');
    });
    
    test('should create a new block', async ({ page }) => {
        // Click new block button
        await page.click('button:has-text("Neuer Block")');
        
        // Fill form
        await page.fill('input[label="Block Name"]', 'e2e-test-block');
        await page.fill('input[label="Block Titel"]', 'E2E Test Block');
        
        // Adjust padding
        await page.click('button:has-text("Spacing")');
        await page.fill('input[name="padding-top"]', '40');
        
        // Save
        await page.click('button:has-text("Speichern")');
        
        // Verify success message
        await expect(page.locator('.notice-success')).toContainText('erfolgreich gespeichert');
        
        // Verify block appears in list
        await expect(page.locator('.cbd-block-item')).toContainText('E2E Test Block');
    });
    
    test('should use block in Gutenberg editor', async ({ page }) => {
        // Create new post
        await page.goto('/wp-admin/post-new.php');
        
        // Open block inserter
        await page.click('button[aria-label="Block hinzufügen"]');
        
        // Search for our block
        await page.fill('input[placeholder="Suche nach einem Block"]', 'Container');
        
        // Insert block
        await page.click('.block-editor-block-types-list__item:has-text("Container Block")');
        
        // Select block type
        await page.selectOption('select[label="Container-Block Typ"]', 'hero-section');
        
        // Verify block is inserted
        await expect(page.locator('.cbd-container')).toBeVisible();
    });
});
```

---

## 9. Deployment & CI/CD

### 9.1 Build-Prozess

```json
// package.json
{
    "name": "container-block-designer",
    "version": "1.0.0",
    "scripts": {
        "start": "wp-scripts start",
        "build": "wp-scripts build",
        "build:production": "npm run clean && npm run build -- --mode production && npm run bundle",
        "clean": "rm -rf build dist",
        "bundle": "npm run create-zip",
        "create-zip": "node scripts/create-zip.js",
        "lint:js": "wp-scripts lint-js",
        "lint:css": "wp-scripts lint-style",
        "lint:php": "composer run-script phpcs",
        "test:unit": "wp-scripts test-unit-js",
        "test:e2e": "wp-scripts test-e2e",
        "test:php": "composer run-script phpunit",
        "test": "npm run test:unit && npm run test:php",
        "format": "wp-scripts format"
    },
    "devDependencies": {
        "@playwright/test": "^1.40.0",
        "@types/wordpress__blocks": "^12.0.0",
        "@types/wordpress__components": "^23.0.0",
        "@types/wordpress__data": "^6.0.0",
        "@typescript-eslint/eslint-plugin": "^6.0.0",
        "@typescript-eslint/parser": "^6.0.0",
        "@wordpress/scripts": "^26.0.0",
        "typescript": "^5.0.0"
    }
}
```

### 9.2 GitHub Actions Workflow

```yaml
# .github/workflows/ci.yml
name: CI/CD Pipeline

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
      
      - name: Install dependencies
        run: npm ci
      
      - name: Run JS linting
        run: npm run lint:js
      
      - name: Run CSS linting
        run: npm run lint:css
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          tools: composer:v2
      
      - name: Install Composer dependencies
        run: composer install --no-progress --prefer-dist
      
      - name: Run PHP linting
        run: npm run lint:php

  test:
    runs-on: ubuntu-latest
    needs: lint
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: wordpress_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
          extensions: mysql
          tools: composer:v2
      
      - name: Install dependencies
        run: |
          npm ci
          composer install --no-progress --prefer-dist
      
      - name: Setup WordPress Test Suite
        run: |
          bash bin/install-wp-tests.sh wordpress_test root root localhost latest
      
      - name: Run PHP Unit Tests
        run: npm run test:php
      
      - name: Run JS Unit Tests
        run: npm run test:unit
      
      - name: Setup Playwright
        run: npx playwright install --with-deps
      
      - name: Run E2E Tests
        run: npm run test:e2e
        env:
          WP_BASE_URL: http://localhost:8889

  build:
    runs-on: ubuntu-latest
    needs: test
    if: github.ref == 'refs/heads/main'
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '18'
          cache: 'npm'
      
      - name: Install dependencies
        run: npm ci
      
      - name: Build production bundle
        run: npm run build:production
      
      - name: Upload artifact
        uses: actions/upload-artifact@v3
        with:
          name: container-block-designer
          path: dist/container-block-designer.zip
          
      - name: Create Release
        if: startsWith(github.ref, 'refs/tags/')
        uses: softprops/action-gh-release@v1
        with:
          files: dist/container-block-designer.zip
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

### 9.3 Deployment-Script

```javascript
// scripts/create-zip.js
const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const PLUGIN_SLUG = 'container-block-designer';
const BUILD_DIR = path.join(__dirname, '../dist');
const ZIP_FILE = path.join(BUILD_DIR, `${PLUGIN_SLUG}.zip`);

// Files und Ordner die eingeschlossen werden sollen
const INCLUDE_FILES = [
    'container-block-designer.php',
    'readme.txt',
    'LICENSE',
    'languages/**/*',
    'includes/**/*.php',
    'build/**/*',
    'assets/**/*',
];

// Files und Ordner die ausgeschlossen werden sollen
const EXCLUDE_PATTERNS = [
    'node_modules',
    'src',
    'tests',
    '.git',
    '.github',
    '*.log',
    '*.lock',
    '.DS_Store',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'webpack.config.js',
    'tsconfig.json',
    'phpunit.xml',
    '.eslintrc',
    '.prettierrc',
];

async function createZip() {
    // Erstelle dist Verzeichnis
    if (!fs.existsSync(BUILD_DIR)) {
        fs.mkdirSync(BUILD_DIR, { recursive: true });
    }
    
    // Erstelle ZIP
    const output = fs.createWriteStream(ZIP_FILE);
    const archive = archiver('zip', {
        zlib: { level: 9 }
    });
    
    output.on('close', () => {
        console.log(`✅ Plugin ZIP erstellt: ${ZIP_FILE}`);
        console.log(`📦 Größe: ${(archive.pointer() / 1024 / 1024).toFixed(2)} MB`);
    });
    
    archive.on('error', (err) => {
        throw err;
    });
    
    archive.pipe(output);
    
    // Füge Dateien hinzu
    INCLUDE_FILES.forEach(pattern => {
        if (pattern.includes('*')) {
            archive.glob(pattern, {
                cwd: path.join(__dirname, '..'),
                ignore: EXCLUDE_PATTERNS,
            });
        } else {
            const filePath = path.join(__dirname, '..', pattern);
            if (fs.existsSync(filePath)) {
                archive.file(filePath, { name: pattern });
            }
        }
    });
    
    await archive.finalize();
}

createZip().catch(console.error);
```

---

## 10. Dokumentation

### 10.1 Code-Dokumentation (PHPDoc)

```php
/**
 * Container Block Designer
 *
 * @package     ContainerBlockDesigner
 * @author      Your Name
 * @copyright   2025 Your Company
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Container Block Designer
 * Plugin URI:  https://example.com/container-block-designer
 * Description: Visueller Designer für custom Container-Blöcke im Gutenberg Editor
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: container-block-designer
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

namespace ContainerBlockDesigner;

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class Plugin {
    /**
     * Plugin version
     *
     * @var string
     */
    const VERSION = '1.0.0';
    
    /**
     * Singleton instance
     *
     * @var Plugin|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     *
     * @since 1.0.0
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
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Define plugin constants
     *
     * @since 1.0.0
     * @return void
     */
    private function define_constants(): void {
        define('CBD_VERSION', self::VERSION);
        define('CBD_PLUGIN_FILE', __FILE__);
        define('CBD_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('CBD_PLUGIN_URL', plugin_dir_url(__FILE__));
    }
}
```

### 10.2 API-Dokumentation (OpenAPI)

```yaml
openapi: 3.0.0
info:
  title: Container Block Designer API
  version: 1.0.0
  description: REST API für Container Block Designer WordPress Plugin

servers:
  - url: https://example.com/wp-json/cbd/v1
    description: Production server

paths:
  /blocks:
    get:
      summary: Liste aller Blocks
      parameters:
        - name: status
          in: query
          schema:
            type: string
            enum: [active, inactive, draft, trash]
        - name: page
          in: query
          schema:
            type: integer
            default: 1
        - name: per_page
          in: query
          schema:
            type: integer
            default: 10
            maximum: 100
      responses:
        '200':
          description: Successful response
          content:
            application/json:
              schema:
                type: array
                items:
                  $ref: '#/components/schemas/Block'
                  
    post:
      summary: Neuen Block erstellen
      requestBody:
        required: true
        content:
          application/json:
            schema:
              $ref: '#/components/schemas/BlockInput'
      responses:
        '201':
          description: Block created
          content:
            application/json:
              schema:
                $ref: '#/components/schemas/Block'

components:
  schemas:
    Block:
      type: object
      properties:
        id:
          type: integer
        name:
          type: string
          pattern: '^[a-z0-9-]+$'
        title:
          type: string
        description:
          type: string
        config:
          type: object
          properties:
            styles:
              type: object
            allowedBlocks:
              type: array
              items:
                type: string
        status:
          type: string
          enum: [active, inactive, draft, trash]
        created_at:
          type: string
          format: date-time
          
    BlockInput:
      type: object
      required:
        - name
        - title
        - config
      properties:
        name:
          type: string
          pattern: '^[a-z0-9-]+$'
        title:
          type: string
        description:
          type: string
        config:
          type: object
```

---

## 11. Anhang

### 11.1 Konfigurationsdateien

#### composer.json
```json
{
    "name": "yourcompany/container-block-designer",
    "description": "Visual designer for custom container blocks in Gutenberg",
    "type": "wordpress-plugin",
    "license": "GPL-2.0-or-later",
    "authors": [
        {
            "name": "Your Name",
            "email": "email@example.com"
        }
    ],
    "require": {
        "php": ">=8.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "squizlabs/php_codesniffer": "^3.7",
        "wp-coding-standards/wpcs": "^3.0",
        "phpstan/phpstan": "^1.10",
        "brain/monkey": "^2.6"
    },
    "autoload": {
        "psr-4": {
            "ContainerBlockDesigner\\": "includes/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "ContainerBlockDesigner\\Tests\\": "tests/"
        }
    },
    "scripts": {
        "phpcs": "phpcs --standard=WordPress .",
        "phpcbf": "phpcbf --standard=WordPress .",
        "phpstan": "phpstan analyse",
        "phpunit": "phpunit",
        "test": ["@phpcs", "@phpstan", "@phpunit"]
    }
}
```

#### tsconfig.json
```json
{
    "compilerOptions": {
        "target": "ES2020",
        "module": "ESNext",
        "lib": ["ES2020", "DOM"],
        "jsx": "react",
        "strict": true,
        "esModuleInterop": true,
        "skipLibCheck": true,
        "forceConsistentCasingInFileNames": true,
        "moduleResolution": "node",
        "resolveJsonModule": true,
        "isolatedModules": true,
        "noEmit": true,
        "types": [
            "@types/wordpress__blocks",
            "@types/wordpress__components",
            "@types/wordpress__data",
            "@types/wordpress__element"
        ]
    },
    "include": ["src/**/*"],
    "exclude": ["node_modules", "build", "dist"]
}
```

### 11.2 Migrations

```php
// includes/Database/Migrations/Migration_1_0_0.php
namespace ContainerBlockDesigner\Database\Migrations;

class Migration_1_0_0 {
    public function up() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Haupttabelle
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}cbd_blocks (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            block_name VARCHAR(100) NOT NULL,
            block_title VARCHAR(255) NOT NULL,
            block_description TEXT,
            block_icon VARCHAR(100) DEFAULT 'layout',
            block_category VARCHAR(100) DEFAULT 'container-blocks',
            block_keywords TEXT,
            block_config LONGTEXT NOT NULL,
            block_styles LONGTEXT,
            block_scripts LONGTEXT,
            allowed_blocks TEXT,
            template_structure LONGTEXT,
            cache_key VARCHAR(32),
            version INT(11) DEFAULT 1,
            status ENUM('active','inactive','draft','trash') DEFAULT 'draft',
            created_by BIGINT(20) UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at DATETIME DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY block_name (block_name),
            KEY status (status),
            KEY created_by (created_by),
            KEY cache_key (cache_key),
            FULLTEXT KEY search (block_title, block_description, block_keywords)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Version tracking
        add_option('cbd_db_version', '1.0.0');
    }
    
    public function down() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}cbd_blocks");
        delete_option('cbd_db_version');
    }
}
```

---

**Ende des technischen Pflichtenhefts**