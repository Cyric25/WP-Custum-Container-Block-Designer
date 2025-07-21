/**
 * Blocks Registration Entry Point
 */

// Import block types
import './container';

// Register block variations for dynamic blocks
import { registerBlockVariation } from '@wordpress/blocks';
import { select, subscribe } from '@wordpress/data';

// Type for dynamic block data
interface DynamicBlock {
    id: number;
    name: string;
    title: string;
    description: string;
    icon: string;
}

// Wait for blocks data to be available
const unsubscribe = subscribe(() => {
    if (window.cbdBlocks) {
        // Register variations for each dynamic block
        Object.values(window.cbdBlocks).forEach((block: DynamicBlock) => {
            registerBlockVariation('container-block-designer/container', {
                name: `cbd-${block.name}`,
                title: block.title,
                description: block.description,
                icon: block.icon || 'layout',
                attributes: {
                    blockId: String(block.id),
                },
                scope: ['inserter'],
                isActive: (blockAttributes: any) => blockAttributes.blockId === String(block.id),
            });
        });
        
        // Unsubscribe after registration
        unsubscribe();
    }
});