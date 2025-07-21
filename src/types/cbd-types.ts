/**
 * TypeScript Type Definitions
 */

// Block Configuration Types
export interface BlockConfig {
    styles: {
        desktop: StyleConfig;
        tablet?: Partial<StyleConfig>;
        mobile?: Partial<StyleConfig>;
    };
    allowedBlocks?: string[];
    template?: BlockTemplate;
    animations?: AnimationConfig;
}

export interface StyleConfig {
    padding: BoxSpacing;
    margin: BoxSpacing;
    backgroundColor: string;
    backgroundImage?: string;
    backgroundPosition?: string;
    backgroundSize?: string;
    borderWidth: number;
    borderColor: string;
    borderStyle?: 'solid' | 'dashed' | 'dotted' | 'none';
    borderRadius: number;
    boxShadow?: string;
    minHeight?: string;
    maxWidth?: string;
    display?: 'block' | 'flex' | 'grid';
    flexDirection?: 'row' | 'column' | 'row-reverse' | 'column-reverse';
    justifyContent?: 'start' | 'center' | 'end' | 'space-between' | 'space-around';
    alignItems?: 'start' | 'center' | 'end' | 'stretch';
    gap?: number;
}

export interface BoxSpacing {
    top: number;
    right: number;
    bottom: number;
    left: number;
}

export interface AnimationConfig {
    entrance?: 'none' | 'fadeIn' | 'slideIn' | 'zoomIn';
    duration?: string;
    delay?: string;
}

export type BlockTemplate = Array<[string, Record<string, any>?, BlockTemplate?]>;

// Block Data Types
export interface BlockData {
    id: number;
    name: string;
    title: string;
    description?: string;
    icon: string;
    category: string;
    keywords: string[];
    config: BlockConfig;
    status: 'active' | 'inactive' | 'draft' | 'trash';
    version: number;
    author: number;
    created_at: string;
    updated_at: string;
    _embedded?: {
        author?: {
            id: number;
            name: string;
            avatar: string;
        };
    };
}

// Filter Types
export interface BlockFilters {
    search?: string;
    status?: string;
    author?: string;
    page?: number;
    per_page?: number;
    orderby?: 'id' | 'name' | 'title' | 'created_at' | 'updated_at';
    order?: 'asc' | 'desc';
}

// API Response Types
export interface ApiResponse<T> {
    data: T;
    headers: {
        'X-WP-Total': string;
        'X-WP-TotalPages': string;
    };
}

export interface ApiError {
    code: string;
    message: string;
    data?: {
        status: number;
        params?: Record<string, string>;
    };
}

// Settings Types
export interface PluginSettings {
    enable_cache: boolean;
    cache_ttl: number;
    enable_debug: boolean;
    enable_import_export: boolean;
    max_blocks_per_user: number;
    enable_block_versioning: boolean;
    max_versions_per_block: number;
    default_block_category: string;
}

// Component Props Types
export interface BlockDesignerProps {
    blockId?: number;
    onSave?: (block: BlockData) => void;
    onCancel?: () => void;
}

export interface BlockListProps {
    blocks: BlockData[];
    onBlockDeleted: (id: number) => void;
    onBlockDuplicated?: (id: number) => void;
}

export interface BlockFiltersProps {
    filters: BlockFilters;
    onChange: (filters: BlockFilters) => void;
}

// WordPress Types Extensions
export interface WPBlockType {
    name: string;
    title: string;
    description?: string;
    category: string;
    icon: string | { src: string };
    keywords?: string[];
    attributes?: Record<string, any>;
    supports?: Record<string, any>;
    edit: React.ComponentType<any>;
    save: React.ComponentType<any> | null;
}

// Global Window Extensions
declare global {
    interface Window {
        cbdAdmin: {
            apiUrl: string;
            nonce: string;
            userId: number;
            strings: Record<string, string>;
        };
        cbdBlocks?: Record<number, BlockData>;
    }
}