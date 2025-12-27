/**
 * Alpine.js Modal Helpers
 *
 * Utility functions for working with Alpine.js-based modals.
 * Provides simple API for opening, closing, and loading content into modals.
 */

/**
 * Opens a modal by dispatching a custom event
 * @param {string} modalId - The ID of the modal to open
 */
export function openModal(modalId) {
    window.dispatchEvent(new CustomEvent(`open-modal-${modalId}`));
}

/**
 * Closes a modal by dispatching a custom event
 * @param {string} modalId - The ID of the modal to close
 */
export function closeModal(modalId) {
    window.dispatchEvent(new CustomEvent(`close-modal-${modalId}`));
}

/**
 * Loads content into a modal via AJAX
 * @param {string} modalId - The ID of the modal
 * @param {string} url - The URL to load content from
 * @param {string} referenceId - Optional reference ID to pre-select in forms
 */
export function loadModalContent(modalId, url, referenceId = '') {
    window.dispatchEvent(new CustomEvent(`load-modal-${modalId}`, {
        detail: { url, referenceId }
    }));
}

/**
 * Populates a select dropdown with references from the page
 * @param {HTMLElement} modal - The modal element
 * @param {string} referenceId - The reference ID to pre-select
 */
function populateReferences(modal, referenceId) {
    const jsReferencesSelect = modal.querySelector('#jsReferences');
    if (!jsReferencesSelect) return;

    // If already populated, just update the selection
    if (jsReferencesSelect.options.length > 1) {
        // Clear all selections first
        for (let i = 0; i < jsReferencesSelect.options.length; i++) {
            jsReferencesSelect.options[i].selected = false;
        }

        // Select the matching option
        for (let i = 0; i < jsReferencesSelect.options.length; i++) {
            if (jsReferencesSelect.options[i].value === referenceId) {
                jsReferencesSelect.options[i].selected = true;
                break;
            }
        }
        return;
    }

    // First time: populate the select with all references
    const references = [...document.querySelectorAll('[data-reference]')].map(el => el.dataset.reference);

    references.forEach(txt => {
        const opt = document.createElement('option');
        opt.value = txt;
        opt.textContent = document.getElementById(txt).querySelector('span[data-reference]')?.textContent || txt;

        if (txt === referenceId) {
            opt.selected = true;
        }

        jsReferencesSelect.appendChild(opt);
    });
}

/**
 * Initializes modal triggers on page load
 * Finds all elements with data-modal-trigger and sets up click handlers
 */
export function initModalTriggers() {
    document.querySelectorAll('[data-modal-trigger]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.preventDefault();

            // Read attributes dynamically on each click to support runtime updates
            const modalId = trigger.getAttribute('data-modal-trigger');
            const loadUrl = trigger.getAttribute('data-modal-url');
            const referenceId = trigger.getAttribute('data-reference-id');

            if (loadUrl) {
                loadModalContent(modalId, loadUrl, referenceId);
            } else {
                openModal(modalId);

                // For static modals, populate references after a small delay to ensure modal is rendered
                if (referenceId) {
                    setTimeout(() => {
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            populateReferences(modal, referenceId);
                        }
                    }, 100);
                }
            }
        });
    });
}

/**
 * Sets up AJAX form handling within modals
 * Finds forms with .js-ajax-form class and handles submission
 */
export function initModalForms() {
    document.addEventListener('submit', async function(e) {
        const form = e.target;

        if (!form.classList.contains('js-ajax-form')) {
            return;
        }

        e.preventDefault();

        const submitButton = form.querySelector('button[type="submit"]');
        const originalText = submitButton ? submitButton.textContent : '';

        try {
            if (submitButton) {
                submitButton.disabled = true;
            }

            const formData = new FormData(form);

            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            // Check if we were redirected to login page (session expired)
            if (response.url.includes('/login')) {
                // Redirect to login with current page as redirect target (not the AJAX route)
                const loginUrl = new URL(response.url);
                loginUrl.searchParams.set('redirect', window.location.pathname);
                window.location.href = loginUrl.toString();
                return;
            }

            if (!response.ok) {
                throw new Error('Network response was not ok');
            }

            const data = await response.json();

            // Create success/error message
            const messageDiv = document.createElement('div');
            messageDiv.className = data.success
                ? 'rounded-lg bg-success-50 border border-success-200 p-4 mb-4'
                : 'rounded-lg bg-error-50 border border-error-200 p-4 mb-4';

            if (data.success) {
                messageDiv.innerHTML = `
                    <div class="flex">
                        <div class="ml-3">
                            <p class="text-sm text-success-800">${data.message || 'Success!'}</p>
                        </div>
                    </div>
                `;

                // Update the triggering button state after comment is added
                const referenceId = formData.get('reference_id');
                const processId = formData.get('process_id');

                if (referenceId && processId) {
                    // Find the button that triggered this modal
                    const triggerButton = document.querySelector(`button[data-reference-id="${referenceId}"]`);

                    if (triggerButton) {
                        // Update the modal URL from ajax_add to ajax_view
                        const newUrl = `/comments/ajax_view/${processId}/${referenceId}`;
                        triggerButton.setAttribute('data-modal-url', newUrl);

                        // Update the icon from message-plus-square to annotation
                        const iconWrapper = triggerButton.querySelector('.icon');
                        if (iconWrapper) {
                            // Fetch and replace with annotation icon
                            fetch('/icons/annotation.svg')
                                .then(response => response.text())
                                .then(svgContent => {
                                    // Replace stroke colors with currentColor for CSS styling
                                    svgContent = svgContent.replace(/stroke="black"/g, 'stroke="currentColor"');
                                    svgContent = svgContent.replace(/stroke="#000000"/g, 'stroke="currentColor"');
                                    svgContent = svgContent.replace(/stroke="#000"/g, 'stroke="currentColor"');
                                    iconWrapper.innerHTML = svgContent;
                                })
                                .catch(err => console.error('Error loading annotation icon:', err));
                        }
                    }
                }

                // Auto-close modal after success
                const modal = form.closest('[x-data]');
                if (modal && modal.id) {
                    setTimeout(() => {
                        closeModal(modal.id);
                    }, 1500);
                }
            } else {
                let errorHtml = `
                    <div class="flex">
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-error-800">${data.message || 'Error'}</h3>
                `;

                if (data.errors) {
                    errorHtml += '<ul class="mt-2 text-sm text-error-700 list-disc list-inside">';
                    for (const field in data.errors) {
                        const rules = data.errors[field];
                        for (const rule in rules) {
                            errorHtml += `<li><strong>${field}</strong>: ${rules[rule]}</li>`;
                        }
                    }
                    errorHtml += '</ul>';
                }

                errorHtml += '</div></div>';
                messageDiv.innerHTML = errorHtml;
            }

            // Replace form with message or insert message above form
            if (data.success) {
                form.replaceWith(messageDiv);
            } else {
                // Remove any existing error messages
                const existingError = form.querySelector('.bg-error-50');
                if (existingError) {
                    existingError.remove();
                }
                form.prepend(messageDiv);
            }

        } catch (error) {
            console.error('Error submitting form:', error);

            const errorDiv = document.createElement('div');
            errorDiv.className = 'rounded-lg bg-error-50 border border-error-200 p-4 mb-4';
            errorDiv.innerHTML = `
                <div class="flex">
                    <div class="ml-3">
                        <p class="text-sm text-error-800">An error occurred. Please try again.</p>
                    </div>
                </div>
            `;

            form.prepend(errorDiv);
        } finally {
            // Hide loading overlay
            window.dispatchEvent(new CustomEvent('hide-loading'));

            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    });
}

/**
 * Initialize all modal functionality on page load
 */
export function initModals() {
    initModalTriggers();
    initModalForms();

    // Listen for modal content loaded events to populate references
    window.addEventListener('modal-content-loaded', (e) => {
        const { modalBody, referenceId } = e.detail;
        if (modalBody && referenceId) {
            populateReferences(modalBody, referenceId);
        }
    });
}
