/**
 * Container Block Edit Component
 */
import React, { useState, useEffect } from 'react';
import { useBlockProps, InnerBlocks, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    SelectControl, 
    ToggleControl, 
    TextControl,
    Spinner,
    Notice 
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import classnames from 'classnames';
import apiFetch from '@wordpress/api-fetch';

// Types
import { BlockData, BlockConfig } from '../../types';

interface EditProps {
    attributes: {
        blockId: string;
        customClasses: string;
        fullWidth: boolean;
    };
    setAttributes: (attrs: Partial<EditProps['attributes']>) => void;
    clientId: string;
}

const Edit: React.FC<EditProps> = ({ attributes, setAttributes }) => {
    const { blockId, customClasses, fullWidth } = attributes;
    const [isLoading, setIsLoading] = useState(false);
    const [blockConfig, setBlockConfig] = useState<BlockConfig | null>(null);
    const [availableBlocks, setAvailableBlocks] = useState<BlockData[]>([]);
    const [error, setError] = useState<string | null>(null);
    
    // Fetch available blocks on mount
    useEffect(() => {
        fetchAvailableBlocks();
    }, []);
    
    // Fetch block config when blockId changes
    useEffect(() => {
        if (blockId) {
            fetchBlockConfig(blockId);
        }
    }, [blockId]);
    
    const fetchAvailableBlocks = async () => {
        try {
            const blocks = await apiFetch({
                path: '/cbd/v1/blocks?status=active&per_page=100',
            }) as BlockData[];
            setAvailableBlocks(blocks);
        } catch (err) {
            console.error('Failed to fetch blocks:', err);
            setError(__('Fehler beim Laden der verfügbaren Blöcke.', 'container-block-designer'));
        }
    };
    
    const fetchBlockConfig = async (id: string) => {
        setIsLoading(true);
        setError(null);
        
        try {
            const block = await apiFetch({
                path: `/cbd/v1/blocks/${id}`,
            }) as BlockData;
            setBlockConfig(block.config);
        } catch (err) {
            console.error('Failed to fetch block config:', err);
            setError(__('Fehler beim Laden der Block-Konfiguration.', 'container-block-designer'));
        } finally {
            setIsLoading(false);
        }
    };
    
    // Generate inline styles based on config
    const generateInlineStyles = () => {
        if (!blockConfig?.styles?.desktop) {
            return {};
        }
        
        const styles = blockConfig.styles.desktop;
        return {
            paddingTop: `${styles.padding.top}px`,
            paddingRight: `${styles.padding.right}px`,
            paddingBottom: `${styles.padding.bottom}px`,
            paddingLeft: `${styles.padding.left}px`,
            marginTop: `${styles.margin.top}px`,
            marginRight: `${styles.margin.right}px`,
            marginBottom: `${styles.margin.bottom}px`,
            marginLeft: `${styles.margin.left}px`,
            backgroundColor: styles.backgroundColor,
            borderWidth: `${styles.borderWidth}px`,
            borderStyle: styles.borderStyle || 'solid',
            borderColor: styles.borderColor,
            borderRadius: `${styles.borderRadius}px`,
            minHeight: styles.minHeight,
            maxWidth: styles.maxWidth,
            ...(styles.display === 'flex' && {
                display: 'flex',
                flexDirection: styles.flexDirection,
                justifyContent: styles.justifyContent,
                alignItems: styles.alignItems,
                gap: styles.gap ? `${styles.gap}px` : undefined,
            }),
        };
    };
    
    // Block props with classes and styles
    const blockProps = useBlockProps({
        className: classnames(
            'cbd-container',
            customClasses,
            fullWidth && 'alignfull',
            blockId && `cbd-container-${blockId}`
        ),
        style: generateInlineStyles(),
    });
    
    // Options for block selection
    const blockOptions = [
        { 
            label: __('Wählen Sie einen Container-Block', 'container-block-designer'), 
            value: '' 
        },
        ...availableBlocks.map(block => ({
            label: block.title,
            value: String(block.id),
        })),
    ];
    
    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Container Einstellungen', 'container-block-designer')}>
                    <SelectControl
                        label={__('Container-Block Typ', 'container-block-designer')}
                        value={blockId}
                        options={blockOptions}
                        onChange={(value) => setAttributes({ blockId: value })}
                        help={
                            blockConfig && availableBlocks.find(b => String(b.id) === blockId)?.description
                        }
                    />
                    
                    <ToggleControl
                        label={__('Volle Breite', 'container-block-designer')}
                        checked={fullWidth}
                        onChange={(value) => setAttributes({ fullWidth: value })}
                        help={__('Container über die gesamte Breite ausdehnen.', 'container-block-designer')}
                    />
                    
                    <TextControl
                        label={__('Zusätzliche CSS-Klassen', 'container-block-designer')}
                        value={customClasses}
                        onChange={(value) => setAttributes({ customClasses: value })}
                        help={__('Fügen Sie eigene CSS-Klassen hinzu (getrennt durch Leerzeichen).', 'container-block-designer')}
                    />
                </PanelBody>
                
                {blockConfig && (
                    <PanelBody 
                        title={__('Block Information', 'container-block-designer')}
                        initialOpen={false}
                    >
                        <p><strong>{__('Erlaubte Blöcke:', 'container-block-designer')}</strong></p>
                        {blockConfig.allowedBlocks && blockConfig.allowedBlocks.length > 0 ? (
                            <ul style={{ marginLeft: '20px', marginTop: '10px' }}>
                                {blockConfig.allowedBlocks.map(block => (
                                    <li key={block}>{block}</li>
                                ))}
                            </ul>
                        ) : (
                            <p>{__('Alle Blöcke erlaubt', 'container-block-designer')}</p>
                        )}
                    </PanelBody>
                )}
            </InspectorControls>
            
            <div {...blockProps}>
                {error && (
                    <Notice status="error" isDismissible={false}>
                        {error}
                    </Notice>
                )}
                
                {!blockId ? (
                    <div className="cbd-placeholder">
                        <div className="cbd-placeholder-icon">
                            <span className="dashicons dashicons-layout"></span>
                        </div>
                        <p className="cbd-placeholder-text">
                            {__('Bitte wählen Sie einen Container-Block Typ in den Block-Einstellungen.', 'container-block-designer')}
                        </p>
                    </div>
                ) : isLoading ? (
                    <div className="cbd-loading">
                        <Spinner />
                        <p>{__('Lade Block-Konfiguration...', 'container-block-designer')}</p>
                    </div>
                ) : (
                    <InnerBlocks 
                        allowedBlocks={blockConfig?.allowedBlocks}
                        template={blockConfig?.template}
                        templateLock={false}
                        renderAppender={InnerBlocks.ButtonBlockAppender}
                    />
                )}
            </div>
        </>
    );
};

export default Edit;