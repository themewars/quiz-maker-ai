<style>
/* Force white text on ALL buttons - Ultimate Override */
*[class*="fi-btn"] { color: white !important; }
*[class*="fi-btn"] * { color: white !important; }
*[class*="fi-ac-btn"] { color: white !important; }
*[class*="fi-ac-btn"] * { color: white !important; }
button { color: white !important; }
button * { color: white !important; }

/* Specific targeting for fi-btn-label */
.fi-btn-label { color: white !important; }
.fi-btn-label * { color: white !important; }
*[class*="fi-btn-label"] { color: white !important; }
*[class*="fi-btn-label"] * { color: white !important; }

/* JavaScript to force white text on buttons - Ultimate Solution */
<script>
document.addEventListener('DOMContentLoaded', function() {
    function forceWhiteText() {
        // Target ALL possible elements that could contain button text
        const allElements = document.querySelectorAll('*');
        
        allElements.forEach(element => {
            const classes = element.className || '';
            const tagName = element.tagName.toLowerCase();
            
            // Check if this element is a button or has button-related classes
            if (
                tagName === 'button' ||
                tagName === 'a' ||
                classes.includes('fi-btn') ||
                classes.includes('fi-ac-btn') ||
                classes.includes('fi-btn-label') ||
                classes.includes('btn') ||
                element.getAttribute('role') === 'button' ||
                element.getAttribute('type') === 'submit' ||
                element.getAttribute('type') === 'button'
            ) {
                // Force white text with maximum priority
                element.style.cssText += 'color: white !important;';
                element.style.setProperty('color', 'white', 'important');
                element.style.setProperty('--tw-text-opacity', '1', 'important');
                
                // Also force on all children
                const children = element.querySelectorAll('*');
                children.forEach(child => {
                    child.style.cssText += 'color: white !important;';
                    child.style.setProperty('color', 'white', 'important');
                    child.style.setProperty('--tw-text-opacity', '1', 'important');
                });
            }
        });
        
        // Also target specific text elements
        const textElements = document.querySelectorAll('span, div, p, label, strong, em');
        textElements.forEach(element => {
            const parent = element.closest('button, [class*="fi-btn"], [class*="fi-ac-btn"]');
            if (parent) {
                element.style.cssText += 'color: white !important;';
                element.style.setProperty('color', 'white', 'important');
            }
        });
    }
    
    // Run immediately
    forceWhiteText();
    
    // Run multiple times with different intervals
    setTimeout(forceWhiteText, 50);
    setTimeout(forceWhiteText, 200);
    setTimeout(forceWhiteText, 500);
    setTimeout(forceWhiteText, 1000);
    setTimeout(forceWhiteText, 2000);
    setTimeout(forceWhiteText, 5000);
    
    // Run when DOM changes
    const observer = new MutationObserver(function(mutations) {
        let shouldRun = false;
        mutations.forEach(mutation => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                shouldRun = true;
            }
        });
        if (shouldRun) {
            setTimeout(forceWhiteText, 50);
        }
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    // Run on window load
    window.addEventListener('load', forceWhiteText);
    
    // Run on any click (in case buttons are dynamically created)
    document.addEventListener('click', function() {
        setTimeout(forceWhiteText, 100);
    });
});
</script>

/* Ensure Filament select dropdown panels are visible above content */
.fi-dropdown-panel, .choices__list--dropdown, .fi-fo-select .fi-input-wrp > div[role="listbox"], .fi-fo-select .fi-select-panel {
    z-index: 9999 !important;
}

/* Prevent container overflow from clipping dropdowns in SPA */
.fi-body, .fi-layout, .fi-main, .fi-page, .fi-section, .fi-form, .fi-fo-field-wrp {
    overflow: visible !important;
}

/* Make sure the portal root allows overflow */
div[x-portal-root], .fi-portal, .fi-dropdown {
    overflow: visible !important;
}

/* Fix for nested positioning contexts */
.fi-fo-select .fi-select-panel {
    position: absolute !important;
}

/* Reduce chance of being hidden in transformed parents */
[x-data] {
    transform: none !important;
}

/* Filament Button Text Color Override */
.fi-btn {
    color: white !important;
}

.fi-btn-primary {
    color: white !important;
}

.fi-btn-success {
    color: white !important;
}

.fi-btn-warning {
    color: white !important;
}

.fi-btn-info {
    color: white !important;
}

.fi-btn-gray {
    color: white !important;
}

/* Action buttons specific styling */
.fi-ac-btn {
    color: white !important;
}

.fi-ac-btn-primary {
    color: white !important;
}

.fi-ac-btn-success {
    color: white !important;
}

.fi-ac-btn-warning {
    color: white !important;
}

.fi-ac-btn-info {
    color: white !important;
}

.fi-ac-btn-gray {
    color: white !important;
}

/* Form action buttons */
.fi-fo-actions .fi-btn {
    color: white !important;
}

.fi-fo-actions .fi-btn-primary {
    color: white !important;
}

.fi-fo-actions .fi-btn-success {
    color: white !important;
}

.fi-fo-actions .fi-btn-warning {
    color: white !important;
}

.fi-fo-actions .fi-btn-info {
    color: white !important;
}

.fi-fo-actions .fi-btn-gray {
    color: white !important;
}

/* Additional comprehensive button text overrides */
.fi-btn *, .fi-btn-primary *, .fi-btn-success *, .fi-btn-warning *, .fi-btn-info *, .fi-btn-gray * {
    color: white !important;
}

.fi-ac-btn *, .fi-ac-btn-primary *, .fi-ac-btn-success *, .fi-ac-btn-warning *, .fi-ac-btn-info *, .fi-ac-btn-gray * {
    color: white !important;
}

.fi-fo-actions .fi-btn *, .fi-fo-actions .fi-btn-primary *, .fi-fo-actions .fi-btn-success *, .fi-fo-actions .fi-btn-warning *, .fi-fo-actions .fi-btn-info *, .fi-fo-actions .fi-btn-gray * {
    color: white !important;
}

/* Button text spans and labels */
.fi-btn span, .fi-btn label, .fi-btn .fi-btn-text {
    color: white !important;
}

.fi-ac-btn span, .fi-ac-btn label, .fi-ac-btn .fi-btn-text {
    color: white !important;
}

/* Force white text on all button elements */
button[class*="fi-btn"], button[class*="fi-ac-btn"] {
    color: white !important;
}

button[class*="fi-btn"] *, button[class*="fi-ac-btn"] * {
    color: white !important;
}

/* Override any inline styles */
.fi-btn[style*="color"], .fi-ac-btn[style*="color"] {
    color: white !important;
}

/* Specific button text elements */
.fi-btn-text, .fi-btn-label, .fi-btn-content {
    color: white !important;
}

/* Additional selectors for all possible button variants */
[class*="fi-btn"][class*="primary"], [class*="fi-btn"][class*="success"], [class*="fi-btn"][class*="warning"], [class*="fi-btn"][class*="info"], [class*="fi-btn"][class*="gray"] {
    color: white !important;
}

[class*="fi-btn"][class*="primary"] *, [class*="fi-btn"][class*="success"] *, [class*="fi-btn"][class*="warning"] *, [class*="fi-btn"][class*="info"] *, [class*="fi-btn"][class*="gray"] * {
    color: white !important;
}
</style>

