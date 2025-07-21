/**
 * WordPress Data Store for Container Block Designer
 */
import apiFetch from '@wordpress/api-fetch';
import { createReduxStore } from '@wordpress/data';
import { BlockData, BlockFilters } from '../../types';

// Types
interface State {
    blocks: Record<number, BlockData>;
    blockIds: number[];
    isLoading: boolean;
    error: string | null;
    filters: BlockFilters;
    totalPages: number;
    currentPage: number;
}

// Default State
const DEFAULT_STATE: State = {
    blocks: {},
    blockIds: [],
    isLoading: false,
    error: null,
    filters: {
        search: '',
        status: '',
        author: '',
        page: 1,
        per_page: 20,
    },
    totalPages: 1,
    currentPage: 1,
};

// Actions
const actions = {
    setBlocks(blocks: BlockData[]) {
        return {
            type: 'SET_BLOCKS' as const,
            blocks,
        };
    },
    
    addBlock(block: BlockData) {
        return {
            type: 'ADD_BLOCK' as const,
            block,
        };
    },
    
    updateBlock(id: number, updates: Partial<BlockData>) {
        return {
            type: 'UPDATE_BLOCK' as const,
            id,
            updates,
        };
    },
    
    removeBlock(id: number) {
        return {
            type: 'REMOVE_BLOCK' as const,
            id,
        };
    },
    
    setLoading(isLoading: boolean) {
        return {
            type: 'SET_LOADING' as const,
            isLoading,
        };
    },
    
    setError(error: string | null) {
        return {
            type: 'SET_ERROR' as const,
            error,
        };
    },
    
    setPagination(currentPage: number, totalPages: number) {
        return {
            type: 'SET_PAGINATION' as const,
            currentPage,
            totalPages,
        };
    },
    
    // Async actions
    fetchBlocks(filters: BlockFilters = {}) {
        return async ({ dispatch }: any) => {
            dispatch.setLoading(true);
            dispatch.setError(null);
            
            try {
                const queryParams = new URLSearchParams();
                Object.entries(filters).forEach(([key, value]) => {
                    if (value) {
                        queryParams.append(key, String(value));
                    }
                });
                
                const response = await apiFetch({
                    path: `/cbd/v1/blocks?${queryParams}`,
                    parse: false,
                });
                
                const blocks = await response.json();
                const totalPages = parseInt(response.headers.get('X-WP-TotalPages') || '1');
                const currentPage = filters.page || 1;
                
                dispatch.setBlocks(blocks);
                dispatch.setPagination(currentPage, totalPages);
            } catch (error) {
                dispatch.setError((error as Error).message);
            } finally {
                dispatch.setLoading(false);
            }
        };
    },
    
    saveBlock(blockData: Partial<BlockData>) {
        return async ({ dispatch }: any) => {
            dispatch.setLoading(true);
            dispatch.setError(null);
            
            try {
                const isUpdate = !!blockData.id;
                const path = isUpdate 
                    ? `/cbd/v1/blocks/${blockData.id}`
                    : '/cbd/v1/blocks';
                
                const response = await apiFetch({
                    path,
                    method: isUpdate ? 'PUT' : 'POST',
                    data: blockData,
                });
                
                if (isUpdate) {
                    dispatch.updateBlock(blockData.id!, response as BlockData);
                } else {
                    dispatch.addBlock(response as BlockData);
                }
                
                return response;
            } catch (error) {
                dispatch.setError((error as Error).message);
                throw error;
            } finally {
                dispatch.setLoading(false);
            }
        };
    },
    
    deleteBlock(id: number, force: boolean = false) {
        return async ({ dispatch }: any) => {
            dispatch.setLoading(true);
            dispatch.setError(null);
            
            try {
                await apiFetch({
                    path: `/cbd/v1/blocks/${id}`,
                    method: 'DELETE',
                    data: { force },
                });
                
                dispatch.removeBlock(id);
            } catch (error) {
                dispatch.setError((error as Error).message);
                throw error;
            } finally {
                dispatch.setLoading(false);
            }
        };
    },
    
    duplicateBlock(id: number) {
        return async ({ dispatch }: any) => {
            dispatch.setLoading(true);
            dispatch.setError(null);
            
            try {
                const response = await apiFetch({
                    path: `/cbd/v1/blocks/${id}/duplicate`,
                    method: 'POST',
                });
                
                dispatch.addBlock(response as BlockData);
                return response;
            } catch (error) {
                dispatch.setError((error as Error).message);
                throw error;
            } finally {
                dispatch.setLoading(false);
            }
        };
    },
};

// Reducer
const reducer = (state = DEFAULT_STATE, action: any): State => {
    switch (action.type) {
        case 'SET_BLOCKS':
            const blocks: Record<number, BlockData> = {};
            const blockIds: number[] = [];
            
            action.blocks.forEach((block: BlockData) => {
                blocks[block.id] = block;
                blockIds.push(block.id);
            });
            
            return {
                ...state,
                blocks,
                blockIds,
            };
            
        case 'ADD_BLOCK':
            return {
                ...state,
                blocks: {
                    ...state.blocks,
                    [action.block.id]: action.block,
                },
                blockIds: [action.block.id, ...state.blockIds],
            };
            
        case 'UPDATE_BLOCK':
            return {
                ...state,
                blocks: {
                    ...state.blocks,
                    [action.id]: {
                        ...state.blocks[action.id],
                        ...action.updates,
                    },
                },
            };
            
        case 'REMOVE_BLOCK':
            const { [action.id]: removed, ...remainingBlocks } = state.blocks;
            return {
                ...state,
                blocks: remainingBlocks,
                blockIds: state.blockIds.filter(id => id !== action.id),
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
            
        case 'SET_PAGINATION':
            return {
                ...state,
                currentPage: action.currentPage,
                totalPages: action.totalPages,
            };
            
        default:
            return state;
    }
};

// Selectors
const selectors = {
    getBlocks(state: State, filters?: BlockFilters) {
        let blocks = state.blockIds.map(id => state.blocks[id]);
        
        if (filters?.status) {
            blocks = blocks.filter(block => block.status === filters.status);
        }
        
        if (filters?.search) {
            const search = filters.search.toLowerCase();
            blocks = blocks.filter(block => 
                block.title.toLowerCase().includes(search) ||
                block.description?.toLowerCase().includes(search)
            );
        }
        
        return blocks;
    },
    
    getBlock(state: State, id: number) {
        return state.blocks[id] || null;
    },
    
    isLoading(state: State) {
        return state.isLoading;
    },
    
    getError(state: State) {
        return state.error;
    },
    
    getPagination(state: State) {
        return {
            currentPage: state.currentPage,
            totalPages: state.totalPages,
        };
    },
};

// Create and export store
export const store = createReduxStore('container-block-designer', {
    reducer,
    actions,
    selectors,
});