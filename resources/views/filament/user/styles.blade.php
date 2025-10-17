<style>
/* Targeted button text color fixes - Only for colored buttons */
.fi-btn-primary, .fi-btn-primary * { color: white !important; }
.fi-btn-success, .fi-btn-success * { color: white !important; }
.fi-btn-warning, .fi-btn-warning * { color: white !important; }
.fi-btn-info, .fi-btn-info * { color: white !important; }
.fi-btn-danger, .fi-btn-danger * { color: white !important; }

/* Action buttons with colors */
.fi-ac-btn-primary, .fi-ac-btn-primary * { color: white !important; }
.fi-ac-btn-success, .fi-ac-btn-success * { color: white !important; }
.fi-ac-btn-warning, .fi-ac-btn-warning * { color: white !important; }
.fi-ac-btn-info, .fi-ac-btn-info * { color: white !important; }
.fi-ac-btn-danger, .fi-ac-btn-danger * { color: white !important; }

/* Form action buttons with colors */
.fi-fo-actions .fi-btn-primary, .fi-fo-actions .fi-btn-primary * { color: white !important; }
.fi-fo-actions .fi-btn-success, .fi-fo-actions .fi-btn-success * { color: white !important; }
.fi-fo-actions .fi-btn-warning, .fi-fo-actions .fi-btn-warning * { color: white !important; }
.fi-fo-actions .fi-btn-info, .fi-fo-actions .fi-btn-info * { color: white !important; }
.fi-fo-actions .fi-btn-danger, .fi-fo-actions .fi-btn-danger * { color: white !important; }

/* Specific targeting for fi-btn-label in colored buttons */
.fi-btn-primary .fi-btn-label, .fi-btn-primary .fi-btn-label * { color: white !important; }
.fi-btn-success .fi-btn-label, .fi-btn-success .fi-btn-label * { color: white !important; }
.fi-btn-warning .fi-btn-label, .fi-btn-warning .fi-btn-label * { color: white !important; }
.fi-btn-info .fi-btn-label, .fi-btn-info .fi-btn-label * { color: white !important; }
.fi-btn-danger .fi-btn-label, .fi-btn-danger .fi-btn-label * { color: white !important; }

/* JavaScript to force white text on colored buttons only */
<script>
document.addEventListener('DOMContentLoaded', function() {
    function forceWhiteTextOnColoredButtons() {
        // Target only colored buttons
        const coloredButtonSelectors = [
            '.fi-btn-primary',
            '.fi-btn-success', 
            '.fi-btn-warning',
            '.fi-btn-info',
            '.fi-btn-danger',
            '.fi-ac-btn-primary',
            '.fi-ac-btn-success',
            '.fi-ac-btn-warning', 
            '.fi-ac-btn-info',
            '.fi-ac-btn-danger',
            '.fi-fo-actions .fi-btn-primary',
            '.fi-fo-actions .fi-btn-success',
            '.fi-fo-actions .fi-btn-warning',
            '.fi-fo-actions .fi-btn-info',
            '.fi-fo-actions .fi-btn-danger'
        ];
        
        coloredButtonSelectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                // Force white text on element
                element.style.setProperty('color', 'white', 'important');
                element.style.setProperty('--tw-text-opacity', '1', 'important');
                
                // Force white text on all children
                const children = element.querySelectorAll('*');
                children.forEach(child => {
                    child.style.setProperty('color', 'white', 'important');
                    child.style.setProperty('--tw-text-opacity', '1', 'important');
                });
            });
        });
    }
    
    // Run immediately
    forceWhiteTextOnColoredButtons();
    
    // Run after delays
    setTimeout(forceWhiteTextOnColoredButtons, 500);
    setTimeout(forceWhiteTextOnColoredButtons, 1000);
    setTimeout(forceWhiteTextOnColoredButtons, 2000);
    
    // Run when DOM changes
    const observer = new MutationObserver(function(mutations) {
        let shouldRun = false;
        mutations.forEach(mutation => {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                shouldRun = true;
            }
        });
        if (shouldRun) {
            setTimeout(forceWhiteTextOnColoredButtons, 100);
        }
    });
    
    observer.observe(document.body, { 
        childList: true, 
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style']
    });
    
    // Run on window load
    window.addEventListener('load', forceWhiteTextOnColoredButtons);
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

