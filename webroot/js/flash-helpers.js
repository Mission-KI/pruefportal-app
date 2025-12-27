/**
 * Flash Message Helpers
 *
 * Utilities for displaying client-side flash messages
 */

/**
 * Display a flash message in the global flash container
 * @param {string} message - The message to display
 * @param {string} type - Message type: success|error|warning|info (default: info)
 */
export function showFlash(message, type = 'info') {
    const container = document.getElementById('flash-message-container');
    if (!container) return;

    const typeConfig = {
        success: { bg: 'bg-success-50', border: 'border-success-200', text: 'text-success-800', iconColor: 'text-success-600', hover: 'hover:text-success-800' },
        error: { bg: 'bg-error-50', border: 'border-error-200', text: 'text-error-800', iconColor: 'text-error-600', hover: 'hover:text-error-800' },
        warning: { bg: 'bg-warning-50', border: 'border-warning-200', text: 'text-warning-800', iconColor: 'text-warning-600', hover: 'hover:text-warning-800' },
        info: { bg: 'bg-blue-50', border: 'border-blue-200', text: 'text-blue-800', iconColor: 'text-blue-600', hover: 'hover:text-blue-800' }
    };

    const config = typeConfig[type] || typeConfig.info;

    const flashElement = document.createElement('div');
    flashElement.className = `flex items-center gap-3 p-4 rounded-lg border shadow-lg ${config.bg} ${config.border} ${config.text}`;
    flashElement.setAttribute('role', 'alert');
    flashElement.innerHTML = `
        <div class="flex-1">
            <p class="text-sm font-medium">${message}</p>
        </div>
        <button type="button" class="flex-shrink-0 ${config.iconColor} ${config.hover}" onclick="this.parentElement.remove();" aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    `;

    container.appendChild(flashElement);

    // Auto-dismiss for success and info only (not error/warning)
    if (type === 'success' || type === 'info') {
        setTimeout(() => {
            flashElement.remove();
        }, 3000);
    }
}
