/**
 * Admin Entry Point
 */
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { registerStore } from '@wordpress/data';

// Components
import App from './App';

// Store
import { store } from './store';

// Styles
import './styles/admin.scss';

// Register the store
registerStore('container-block-designer', store);

// Wait for DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Main admin app
    const adminRoot = document.getElementById('cbd-admin-app');
    if (adminRoot) {
        const root = createRoot(adminRoot);
        root.render(<App />);
    }
    
    // Block designer
    const designerRoot = document.getElementById('cbd-block-designer');
    if (designerRoot) {
        import('./components/BlockDesigner').then(({ default: BlockDesigner }) => {
            const root = createRoot(designerRoot);
            const blockId = new URLSearchParams(window.location.search).get('block_id');
            root.render(<BlockDesigner blockId={blockId ? parseInt(blockId) : undefined} />);
        });
    }
    
    // Remove loading indicators
    document.querySelectorAll('.cbd-loading').forEach(el => {
        el.remove();
    });
});