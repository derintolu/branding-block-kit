/**
 * Branding Block Kit - Frontend Scripts
 * Click-to-copy functionality for color swatches
 */
(function() {
    'use strict';

    /**
     * Initialize click-to-copy on all color swatches
     */
    function initClickToCopy() {
        const swatches = document.querySelectorAll('.bbk-brand-color-swatch[data-color]');
        
        swatches.forEach(function(swatch) {
            swatch.style.cursor = 'pointer';
            swatch.setAttribute('title', 'Click to copy color value');
            
            swatch.addEventListener('click', function(e) {
                const colorValue = this.getAttribute('data-color');
                
                if (!colorValue) return;
                
                copyToClipboard(colorValue).then(function() {
                    showCopiedFeedback(swatch, colorValue);
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                });
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
     * Show "Copied!" feedback on the swatch
     */
    function showCopiedFeedback(swatch, colorValue) {
        // Remove any existing feedback
        const existingFeedback = swatch.querySelector('.bbk-copy-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        // Create feedback element
        const feedback = document.createElement('div');
        feedback.className = 'bbk-copy-feedback';
        feedback.textContent = 'Copied!';
        
        // Determine text color based on background
        const colorEl = swatch.querySelector('.bbk-brand-color-swatch__color');
        if (colorEl) {
            const bgColor = colorEl.style.backgroundColor || colorValue;
            feedback.style.color = getContrastColor(bgColor);
        }
        
        swatch.appendChild(feedback);
        
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

    /**
     * Get contrasting text color (black or white) based on background
     */
    function getContrastColor(color) {
        // Handle hex colors
        let hex = color;
        
        if (color.startsWith('rgb')) {
            // Extract RGB values from rgb() or rgba()
            const match = color.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
            if (match) {
                const r = parseInt(match[1]);
                const g = parseInt(match[2]);
                const b = parseInt(match[3]);
                const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
                return luminance > 0.5 ? '#000000' : '#ffffff';
            }
        }
        
        // Handle hex colors
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        
        // Calculate relative luminance
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        
        return luminance > 0.5 ? '#000000' : '#ffffff';
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
