<style>
/* Force white text on ALL Filament buttons - Ultimate Override */
.fi-btn, .fi-btn * { color: white !important; }
.fi-btn-primary, .fi-btn-primary * { color: white !important; }
.fi-btn-success, .fi-btn-success * { color: white !important; }
.fi-btn-warning, .fi-btn-warning * { color: white !important; }
.fi-btn-info, .fi-btn-info * { color: white !important; }
.fi-btn-danger, .fi-btn-danger * { color: white !important; }
.fi-btn-gray, .fi-btn-gray * { color: white !important; }

/* Action buttons */
.fi-ac-btn, .fi-ac-btn * { color: white !important; }
.fi-ac-btn-primary, .fi-ac-btn-primary * { color: white !important; }
.fi-ac-btn-success, .fi-ac-btn-success * { color: white !important; }
.fi-ac-btn-warning, .fi-ac-btn-warning * { color: white !important; }
.fi-ac-btn-info, .fi-ac-btn-info * { color: white !important; }
.fi-ac-btn-danger, .fi-ac-btn-danger * { color: white !important; }
.fi-ac-btn-gray, .fi-ac-btn-gray * { color: white !important; }

/* Form action buttons */
.fi-fo-actions .fi-btn, .fi-fo-actions .fi-btn * { color: white !important; }
.fi-fo-actions .fi-btn-primary, .fi-fo-actions .fi-btn-primary * { color: white !important; }
.fi-fo-actions .fi-btn-success, .fi-fo-actions .fi-btn-success * { color: white !important; }
.fi-fo-actions .fi-btn-warning, .fi-fo-actions .fi-btn-warning * { color: white !important; }
.fi-fo-actions .fi-btn-info, .fi-fo-actions .fi-btn-info * { color: white !important; }
.fi-fo-actions .fi-btn-danger, .fi-fo-actions .fi-btn-danger * { color: white !important; }
.fi-fo-actions .fi-btn-gray, .fi-fo-actions .fi-btn-gray * { color: white !important; }

/* Button text elements */
.fi-btn-text, .fi-btn-label, .fi-btn-content { color: white !important; }
.fi-btn-text *, .fi-btn-label *, .fi-btn-content * { color: white !important; }

/* Override any inline styles */
.fi-btn[style*="color"], .fi-ac-btn[style*="color"] { color: white !important; }
.fi-btn[style*="color"] *, .fi-ac-btn[style*="color"] * { color: white !important; }

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

