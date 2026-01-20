/**
 * Branding Block Kit - Frontend Scripts
 * Click-to-copy functionality for color swatches and gradients
 */
(function() {
    'use strict';

    /**
     * Initialize click-to-copy on all swatches (colors and gradients)
     */
    function initClickToCopy() {
        // Color swatches
        const colorSwatches = document.querySelectorAll('.bbk-brand-color-swatch[data-color]');
        colorSwatches.forEach(function(swatch) {
            setupClickToCopy(swatch, 'data-color', 'Click to copy color value');
        });

        // Gradient swatches
        const gradientSwatches = document.querySelectorAll('.bbk-brand-gradient-swatch[data-gradient]');
        gradientSwatches.forEach(function(swatch) {
            setupClickToCopy(swatch, 'data-gradient', 'Click to copy gradient CSS');
        });
    }

    /**
     * Setup click-to-copy on an element
     */
    function setupClickToCopy(element, dataAttr, title) {
        element.style.cursor = 'pointer';
        if (!element.getAttribute('title')) {
            element.setAttribute('title', title);
        }
        
        // Avoid duplicate listeners
        if (element.dataset.bbkCopyInit) return;
        element.dataset.bbkCopyInit = 'true';
        
        element.addEventListener('click', function(e) {
            const value = this.getAttribute(dataAttr);
            
            if (!value) return;
            
            copyToClipboard(value).then(function() {
                showCopiedFeedback(element);
            }).catch(function(err) {
                console.error('Failed to copy:', err);
            });
        });
    }

    /**
     * Copy text to clipboard using modern API with fallback
     */
    function copyToClipboard(text) {
        // Modern async clipboard API
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text);
        }
        
        // Fallback for older browsers
        return new Promise(function(resolve, reject) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-9999px';
            textArea.style.top = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                const successful = document.execCommand('copy');
                document.body.removeChild(textArea);
                if (successful) {
                    resolve();
                } else {
                    reject(new Error('Copy command failed'));
                }
            } catch (err) {
                document.body.removeChild(textArea);
                reject(err);
            }
        });
    }

    /**
     * Show "Copied!" feedback on the element
     */
    function showCopiedFeedback(element) {
        // Remove any existing feedback
        const existingFeedback = element.querySelector('.bbk-copy-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Create feedback element
        const feedback = document.createElement('div');
        feedback.className = 'bbk-copy-feedback';
        feedback.textContent = 'Copied!';
        
        element.appendChild(feedback);
        
        // Trigger animation
        requestAnimationFrame(function() {
            feedback.classList.add('bbk-copy-feedback--visible');
        });
        
        // Remove after animation
        setTimeout(function() {
            feedback.classList.remove('bbk-copy-feedback--visible');
            setTimeout(function() {
                feedback.remove();
            }, 300);
        }, 1200);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClickToCopy);
    } else {
        initClickToCopy();
    }

    // Re-initialize on Gutenberg block updates (for editor preview)
    if (window.wp && window.wp.domReady) {
        window.wp.domReady(initClickToCopy);
    }

})();
