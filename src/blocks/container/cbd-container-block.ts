/**
 * Container Block Implementation
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// Components
import Edit from './edit';
import save from './save';

// Styles
import './style.scss';
import './editor.scss';

// Block configuration
import blockConfig from './block.json';

// Types
interface ContainerBlockAttributes {
    blockId: string;
    customClasses: string;
    fullWidth: boolean;
}

// Register block type
registerBlockType<ContainerBlockAttributes>(blockConfig.name as any, {
    ...blockConfig,
    title: __('Container Block', 'container-block-designer'),
    description: __('Ein anpassbarer Container-Block für Ihre Inhalte.', 'container-block-designer'),
    icon: 'layout',
    edit: Edit,
    save,
});