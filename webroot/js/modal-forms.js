/**
 * Modal Forms Handler
 * Automatically handles AJAX calls for elements with .js-modal-handler class
 */

/**
 * Closes a modal and updates the associated link state
 * @param {HTMLElement} modal - The modal element to close
 */
function toggleModalLink(modal) {
    if (!modal) return;

    const link = modal.previousElementSibling;

    if (link.classList.contains('disabled')) {
        link.classList.remove('disabled');
        link.setAttribute('aria-disabled', 'false');
    } else {
        link.classList.add('disabled');
        link.setAttribute('aria-disabled', 'true');
    }
}

document.addEventListener('DOMContentLoaded', function() {

    // Handle click events on modal form links
    document.querySelector('.js-modal-handler').addEventListener('click', function(e) {
        e.preventDefault();
        const link = this;
        if (!link || link.classList.contains('disabled')) return;

        const url = link.getAttribute('href');
        const modalId = link.getAttribute('data-modal-id');
        if (!modalId) return;

        // Get the modal
        let modal = document.getElementById(modalId);
        const modalBody = modal.querySelector('.modal-body');
        const modalTitle = modal.querySelector('.modal-title');

        toggleModalLink(modal);

        const referenceId = link.getAttribute('data-reference-id') || '';

        // Make AJAX request
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html, application/xhtml+xml',
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(html => {
                // Update modal content
                modalBody.innerHTML = html;

                // Update title if data-modal-title is set
                const title = link.getAttribute('data-modal-title');
                if (title) {
                    modalTitle.textContent = title;
                }

                // Initialize any form validation or other JS for the loaded content
                initModalForms(modal, referenceId);
            })
            .catch(error => {
                console.error('Error loading modal content:', error);
                modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error loading content. Please try again.
                </div>`;
            });
    });

    // Handle form submissions within modals
    function initModalForms(modal, referenceId) {
        const forms = modal.getElementsByTagName('form');

        Array.from(forms).forEach(form => {
            if (form.classList.contains('js-ajax-form')) {

                // Find all references in the document and add it to the select element
                const jsReferencesSelect = form.querySelector('#jsReferences');
                if(jsReferencesSelect) {
                    const references = [...document.querySelectorAll('[data-reference]')].map(el => el.dataset.reference);
                    references.forEach(txt => {
                        const opt = document.createElement('option');
                        opt.value = txt;        // value submitted
                        opt.textContent = document.getElementById(txt).textContent || txt; // visible label
                        if(txt === referenceId) {
                            opt.selected = true;
                        }
                        jsReferencesSelect.appendChild(opt);
                    });
                }
                form.querySelector('button[type="submit"]').addEventListener('click', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: form.method,
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html, application/xhtml+xml',
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const alert = document.createElement('div');
                        alert.textContent = data.message;
                        if(data.success) {
                            alert.className = 'alert alert-success';
                        } else {
                            const ul = document.createElement('ul');
                            ul.className = 'error-list';
                            for (const field in data.errors) {
                                const rules = data.errors[field];
                                for (const rule in rules) {
                                    const li = document.createElement('li');
                                    li.innerHTML = `<strong>${field}</strong>: ${rules[rule]}`;
                                    ul.appendChild(li);
                                }
                            }
                            alert.appendChild(ul);
                            alert.className = 'alert alert-danger';
                        }
                        form.replaceWith(alert);
                    })
                    .catch(error => {
                        console.error('Error submitting form:', error);
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-danger';
                        alert.textContent = 'An error occurred. Please try again.';
                        form.prepend(alert);
                    })
                    .finally(() => {
                        setTimeout(() => {
                            toggleModalLink(modal);
                        }, 1500);
                    });
                });
            }
        });
    }
});
