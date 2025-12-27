<?php
/**
 * UI Demo Sidebar
 *
 * Table of contents and navigation for UI component documentation.
 * Shows components organized by category with status indicators.
 *
 * @var \App\View\AppView $this
 */

use App\Utility\ComponentRegistry;

// Initialize component registry
$registry = new ComponentRegistry();
$categories = $registry->getCategories();
$stats = $registry->getStatistics();
$showcase = $registry->getShowcaseSections();
?>

<nav class="flex-shrink-0 block bg-gray-50 border-l border-gray-200">
    <div class="sticky top-20 pt-4 pb-8 px-4 max-h-screen overflow-y-auto">

        <!-- Sidebar Header -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                Components
            </h3>

            <!-- Search Input -->
            <div class="mb-4">
                <?= $this->element('atoms/form_input', [
                    'id' => 'component-search',
                    'name' => 'component_search',
                    'type' => 'text',
                    'placeholder' => 'Search components...',
                    'options' => [
                        'class' => 'w-full text-sm',
                        'x-on:input' => 'searchComponents($event.target.value)'
                    ]
                ]) ?>
            </div>

            <!-- Progress Bar -->
            <div class="mb-2">
                <p class="text-sm text-gray-600 mb-1">
                    <?= $stats['documented'] ?>/<?= $stats['total'] ?> documented
                </p>
                <?= $this->element('atoms/progress_bar', [
                    'value' => $stats['documented'],
                    'max' => $stats['total'],
                    'variant' => $stats['documented'] / max($stats['total'], 1) >= 0.8 ? 'success' : ($stats['documented'] / max($stats['total'], 1) >= 0.5 ? 'blue' : 'danger'),
                ]) ?>
            </div>
        </div>




        <!-- Other Demo Pages -->
        <div class="mb-6 pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Other Demos</h4>
            <ul class="space-y-2">
                <li>
                    <?= $this->Html->link(
                        'Form Renderer Demo',
                        ['controller' => 'UiDemo', 'action' => 'formDemo'],
                        ['class' => 'flex items-center text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition-colors']
                    ) ?>
                </li>
                <li>
                    <?= $this->Html->link(
                        'Protection Needs Demo',
                        ['controller' => 'UiDemo', 'action' => 'criteriaDemo'],
                        ['class' => 'flex items-center text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition-colors']
                    ) ?>
                </li>
                <li>
                    <?= $this->Html->link(
                        'Back to App',
                        ['controller' => 'Projects', 'action' => 'index'],
                        ['class' => 'flex items-center text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 px-2 py-1 rounded transition-colors']
                    ) ?>
                </li>
            </ul>
        </div>

        <!-- Component Statistics -->
        <div class="pt-4 border-t border-gray-200">
            <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wider">Statistics</h4>
            <div class="space-y-2 text-xs text-gray-600">
                <?php foreach ($stats['by_category'] as $categoryKey => $categoryStats): ?>
                    <?php if ($categoryStats['total'] > 0): ?>
                        <div class="flex justify-between">
                            <span><?= h($categories[$categoryKey]['label'] ?? ucfirst($categoryKey)) ?>:</span>
                            <span>
                                <span class="text-green-600"><?= $categoryStats['documented'] ?></span>/<span class="text-gray-500"><?= $categoryStats['total'] ?></span>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                <div class="flex justify-between font-semibold pt-2 border-t border-gray-300">
                    <span>Total:</span>
                    <span>
                        <span class="text-green-600"><?= $stats['documented'] ?></span>/<span class="text-gray-500"><?= $stats['total'] ?></span>
                    </span>
                </div>
                <div class="text-center text-gray-500">
                    <?= round(($stats['documented'] / max($stats['total'], 1)) * 100) ?>% complete
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- JavaScript for search functionality and navigation -->
<script>
// Alpine.js search and filter functions
function searchComponents(query) {
    // Update Alpine data
    if (this.searchQuery !== undefined) {
        this.searchQuery = query.toLowerCase();
    }
}

function filterComponent(title, description, query) {
    if (!query) return true;
    return title.toLowerCase().includes(query) || description.toLowerCase().includes(query);
}

function filterComponents(category, query) {
    if (!query) return true;
    // Show category if any component in it matches the search
    return true; // Simplified for now - categories always show when searching
}

// Make functions globally available for Alpine.js
window.searchComponents = searchComponents;
window.filterComponent = filterComponent;
window.filterComponents = filterComponents;

document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll to sections and accordions
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const target = document.getElementById(targetId);

            if (target) {
                // If target is inside an accordion, open it first
                const accordionItem = target.closest('[x-data]');
                if (accordionItem && accordionItem.__x && accordionItem.__x.$data) {
                    accordionItem.__x.$data.open = true;
                    // Wait for accordion animation to complete
                    setTimeout(() => {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 150);
                } else {
                    // Direct scroll to section
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Highlight active section in sidebar based on scroll position
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const targetId = entry.target.id;

                // Remove active classes from all main section links
                document.querySelectorAll('.main-section-link').forEach(link => {
                    link.classList.remove('active', 'bg-blue-100', 'text-blue-700', 'font-bold');
                    link.classList.add('text-gray-700', 'font-semibold');
                });

                // Remove active classes from all sub-section links
                document.querySelectorAll('.sub-section-link').forEach(link => {
                    link.classList.remove('active', 'bg-blue-50', 'text-blue-600', 'font-medium');
                    link.classList.add('text-gray-500');
                });

                // Find and activate the corresponding link
                const activeLink = document.querySelector(`a[href="#${targetId}"]`);
                if (activeLink) {
                    if (activeLink.classList.contains('main-section-link')) {
                        // Main section is active
                        activeLink.classList.add('active', 'bg-blue-100', 'text-blue-700', 'font-bold');
                        activeLink.classList.remove('text-gray-700', 'font-semibold');
                    } else if (activeLink.classList.contains('sub-section-link')) {
                        // Sub-section is active
                        activeLink.classList.add('active', 'bg-blue-50', 'text-blue-600', 'font-medium');
                        activeLink.classList.remove('text-gray-500');

                        // Also highlight the parent main section
                        const parentMainLink = activeLink.closest('li').parentElement.previousElementSibling;
                        if (parentMainLink && parentMainLink.classList.contains('main-section-link')) {
                            parentMainLink.classList.add('active', 'bg-blue-100', 'text-blue-700', 'font-bold');
                            parentMainLink.classList.remove('text-gray-700', 'font-semibold');
                        }
                    }
                }
            }
        });
    }, {
        rootMargin: '-10% 0% -60% 0%',
        threshold: 0.3
    });

    // Observe all sections and accordion content areas
    document.querySelectorAll('section[id], div[id*="-parameters"], div[id*="-example-"]').forEach((element) => {
        observer.observe(element);
    });
});
</script>
