/**
 * Container Block Save Component
 */
import React from 'react';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';
import classnames from 'classnames';

interface SaveProps {
    attributes: {
        blockId: string;
        customClasses: string;
        fullWidth: boolean;
    };
}

const Save: React.FC<SaveProps> = ({ attributes }) => {
    const { blockId, customClasses, fullWidth } = attributes;
    
    const blockProps = useBlockProps.save({
        className: classnames(
            'cbd-container',
            customClasses,
            fullWidth && 'alignfull',
            blockId && `cbd-container-${blockId}`
        ),
        'data-block-id': blockId || undefined,
    });
    
    return (
        <div {...blockProps}>
            <InnerBlocks.Content />
        </div>
    );
};

export default Save;