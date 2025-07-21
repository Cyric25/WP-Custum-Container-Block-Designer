/**
 * Frontend Entry Point
 * 
 * This file handles any frontend JavaScript needed for the Container Blocks
 */

// Check if we have any CBD containers on the page
document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.cbd-container');
    
    if (containers.length === 0) {
        return;
    }
    
    // Initialize animations if enabled
    initializeAnimations(containers);
    
    // Handle responsive behavior
    handleResponsive();
});

/**
 * Initialize animations for containers
 */
function initializeAnimations(containers: NodeListOf<Element>) {
    // Set up Intersection Observer for animation triggers
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const container = entry.target as HTMLElement;
                const animationClass = container.dataset.animation;
                
                if (animationClass) {
                    container.classList.add(`cbd-animate-${animationClass}`);
                    observer.unobserve(container);
                }
            }
        });
    }, observerOptions);
    
    // Observe containers with animation data
    containers.forEach(container => {
        const element = container as HTMLElement;
        if (element.dataset.animation) {
            observer.observe(element);
        }
    });
}

/**
 * Handle responsive behavior
 */
function handleResponsive() {
    let resizeTimeout: NodeJS.Timeout;
    
    const handleResize = () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const width = window.innerWidth;
            
            // Update CSS custom properties for responsive behavior
            document.documentElement.style.setProperty(
                '--cbd-viewport-width',
                `${width}px`
            );
            
            // Add viewport class to body
            document.body.classList.remove('cbd-viewport-mobile', 'cbd-viewport-tablet', 'cbd-viewport-desktop');
            
            if (width <= 600) {
                document.body.classList.add('cbd-viewport-mobile');
            } else if (width <= 1024) {
                document.body.classList.add('cbd-viewport-tablet');
            } else {
                document.body.classList.add('cbd-viewport-desktop');
            }
        }, 150);
    };
    
    // Initial call
    handleResize();
    
    // Listen for resize
    window.addEventListener('resize', handleResize);
}

// Export for potential use in other scripts
export { initializeAnimations, handleResponsive };