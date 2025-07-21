/**
 * Main Admin App Component
 */
import React, { useState, useEffect } from 'react';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { Notice, Spinner } from '@wordpress/components';

// Components
import BlockList from './components/BlockList';
import BlockFilters from './components/BlockFilters';
import { BlockData } from '../types';

const App: React.FC = () => {
    const [filters, setFilters] = useState({
        search: '',
        status: '',
        author: '',
    });
    
    const { blocks, isLoading, error } = useSelect((select) => {
        const store = select('container-block-designer');
        return {
            blocks: store.getBlocks(filters),
            isLoading: store.isLoading(),
            error: store.getError(),
        };
    }, [filters]);
    
    const { fetchBlocks } = useDispatch('container-block-designer');
    
    useEffect(() => {
        fetchBlocks(filters);
    }, [filters]);
    
    const handleFilterChange = (newFilters: typeof filters) => {
        setFilters(newFilters);
    };
    
    const handleBlockDeleted = (blockId: number) => {
        fetchBlocks(filters);
    };
    
    if (isLoading && !blocks.length) {
        return (
            <div className="cbd-admin-loading">
                <Spinner />
                <p>{__('Lade Blöcke...', 'container-block-designer')}</p>
            </div>
        );
    }
    
    return (
        <div className="cbd-admin-app">
            {error && (
                <Notice status="error" onRemove={() => {}}>
                    {error}
                </Notice>
            )}
            
            <div className="cbd-admin-content">
                <BlockFilters
                    filters={filters}
                    onChange={handleFilterChange}
                />
                
                <BlockList
                    blocks={blocks}
                    onBlockDeleted={handleBlockDeleted}
                />
            </div>
        </div>
    );
};

export default App;