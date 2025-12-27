// Import Alpine.js
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Import avatar
import { generateAvatar } from './avatar';

// Import modal helpers
import { initModals } from './modal-helpers';

// Import flash helpers
import { showFlash } from './flash-helpers';

/**
 * Fetches a JSON file from the given URL and returns the parsed object.
 * @param {string} url - The URL of the JSON file to fetch.
 * @returns {Promise<Object>} A promise that resolves with the parsed JSON object.
 * @throws {Error} If the response status is not OK.
 */
async function loadJSON(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error(`❌ ${url} → ${res.status}`);
    return res.json();
}

const initAjaxActions = () => {
    document.querySelectorAll('.js-load-upload').forEach(link => {
        fetch(link.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                link.classList.remove('disabled');
                return response.json().then(err => { throw new Error(err.error || 'Failed to fetch file details'); });
            }
            return response.json();
        })
        .then(data => {
            let createdSpan = '';
            if(link.classList.contains('js-show-created') && data.created !== null) {
                const formattedDate = new Date(data.created).toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                createdSpan = document.createElement("span");
                createdSpan.classList.add('text-gray-500');
                createdSpan.classList.add('text-sm');
                createdSpan.textContent = formattedDate;
            }
            if(link.classList.contains('js-show-filename')) {
                link.innerHTML = `<span class="font-medium" title="${data.size}">${data.filename}</span>`;
            }
            link.href = data.link;
            link.after(createdSpan);
        })
        .catch(error => {
            link.href = '#';
            link.textContent = 'Error loading file: ' + error.message;
            console.error('Error:', error.message);
        });
        link.classList.remove('js-load-upload');
    });
}


// When the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    const { version } = require('../../package.json');
    const versionLink = document.getElementById('packageVersion');
    versionLink.textContent = version;
    const url = new URL(versionLink.href, document.baseURI);
    url.searchParams.append('version', version);
    versionLink.href = url.toString();
    console.info('package version: ' + version);

    // Generate user avatar if element exists
    if (document.querySelector('.js-user-icon')) {
        generateAvatar('.js-user-icon');
    }

    // Initialize Alpine.js
    window.Alpine = Alpine;
    Alpine.plugin(collapse);
    Alpine.start();

    // Make showFlash globally available
    window.showFlash = showFlash;

    initAjaxActions();
    initModals();
});
