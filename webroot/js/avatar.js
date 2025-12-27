/**
 * Generates an avatar with the user's initials on a colored background
 * @param {string} selector - CSS selector for the element containing the user's name
 */
export const generateAvatar = (selector) => {
    const colours = [
        "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e",
        "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
        "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6",
        "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d"
    ];

    const name = document.querySelector(selector)?.textContent || '';
    const [firstName = '', lastName = ''] = name.trim().split(' ');
    const initials = `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase();
    const colourIndex = (initials.charCodeAt(0) - 65) % colours.length;

    const canvas = document.getElementById('user-icon');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;

    // Set canvas size with device pixel ratio
    const updateCanvasSize = () => {
        const { width, height } = canvas.getBoundingClientRect();
        canvas.width = Math.floor(width * dpr);
        canvas.height = Math.floor(height * dpr);
        ctx.scale(dpr, dpr);
        return { width, height };
    };

    const { width, height } = updateCanvasSize();

    // Draw avatar
    ctx.fillStyle = colours[colourIndex];
    ctx.fillRect(0, 0, width, height);

    // Set text properties
    ctx.font = `${Math.min(width, height) * 0.6}px Arial`;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillStyle = '#FFFFFF';

    // Draw text
    ctx.fillText(initials, width / 2, height / 2);
};
