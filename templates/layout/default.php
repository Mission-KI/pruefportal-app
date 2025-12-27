<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 * @var \App\Controller\AppController $currentLanguage
 */
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#3C0483">
    <title><?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->meta('csrfToken', $this->request->getAttribute('csrfToken')) ?>
    <?= $this->Html->css(['tailwind'], ['id' => 'tailwind-css']) ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 overflow-x-hidden">
    <!-- Global Flash Message Container (Fixed at top, overlays content) -->
    <div id="flash-message-container" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4 space-y-2">
        <?= $this->Flash->render() ?>
    </div>

    <?php
    // Detect if we're in UI Demo context
    $controller = $this->request->getParam('controller');
    $isUiDemo = ($controller === 'UiDemo');
    ?>

    <?php if ($this->Identity->isLoggedIn() && !$isUiDemo): ?>
    <div x-data="{ mobileMenuOpen: false }"
         x-on:resize.window="if (window.innerWidth >= 992) mobileMenuOpen = false">

        <?= $this->element('organisms/app_header', [
            'logo_url' => '/',
            'user' => [
                'full_name' => $this->Identity->get('full_name'),
                'role' => 'User', // $this->Identity->get('role') ??
                'initials' => $this->Layout->getInitials($this->Identity->get('full_name')),
                'profile_url' => ['controller' => 'Users', 'action' => 'view']
            ],
            'current_language' => 'DE',
            'available_languages' => [
                ['code' => 'DE', 'label' => 'DE'],
                ['code' => 'EN', 'label' => 'EN']
            ],
            'logout_url' => ['controller' => 'Users', 'action' => 'logout']
        ]) ?>

        <?= $this->element('organisms/app_sidebar', [
            'sections' => $this->Layout->getSidebarSections($this->request)
        ]) ?>

        <!-- Bug Report Modal -->
        <?php $this->start('bug_report_form'); ?>
            <?= $this->element('molecules/bug_report_form') ?>
        <?php $this->end(); ?>
        <?= $this->element('molecules/modal', [
            'id' => 'bug-report-modal',
            'title' => __('Fehler melden'),
            'size' => 'lg',
            'closeable' => true,
            'content' => $this->fetch('bug_report_form'),
            'escape' => false,
        ]) ?>

        <!-- Mobile backdrop overlay -->
        <div x-show="mobileMenuOpen"
             x-cloak
             @click="mobileMenuOpen = false"
             class="fixed inset-0 bg-black/50 z-40 md:hidden"
             x-transition:enter="transition-opacity duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;">
        </div>
    </div>
    <?php endif; ?>

    <?php if ($isUiDemo): ?>
        <!-- UI Demo Layout (3-column with component showcase) -->
        <div class="w-full flex-1">
            <!-- UI Demo Left Sidebar (app_sidebar organism) -->
            <?= $this->element('UiDemo/left_sidebar') ?>

            <div class="flex h-full ml-[var(--layout-sidebar-width)]">
                <!-- UI Demo Main Content -->
                <main class="flex-1 min-w-0 p-6">
                    <?= $this->fetch('content') ?>
                </main>

                <!-- UI Demo Right Sidebar (search, stats, etc) -->
                <?= $this->element('UiDemo/sidebar') ?>
            </div>
        </div>
    <?php elseif ($this->Identity->isLoggedIn()): ?>
        <!-- Logged-in Layout (with app_layout_wrapper, sidebar, and header) -->
        <?php $this->start('page_content'); ?>
            <?= $this->fetch('content') ?>
        <?php $this->end(); ?>

        <?= $this->element('organisms/app_layout_wrapper', [
            'breadcrumbs' => $this->Layout->getBreadcrumbs($this->request),
            'content' => $this->fetch('page_content'),
            'show_sidebar' => true,
            'show_breadcrumbs' => true,
            'show_content_card' => $this->fetch('show_content_card') !== 'false',
            'show_right_sidebar' => in_array($this->fetch('show_right_sidebar'), [true, 'true', 1, '1'], true),
            'right_sidebar_content' => $this->fetch('right_sidebar'),
            'reserve_sidebar_space' => in_array($this->fetch('reserve_sidebar_space'), ['true', true, 1, '1'], true),
            'container_width' => $this->fetch('container_width') ?: 'lg'
        ]); ?>
    <?php else: ?>
        <!-- Public pages (login, register) - No wrapper -->
        <?= $this->fetch('content') ?>
    <?php endif; ?>

    <?= $this->element('organisms/footer', ['isUiDemo' => $isUiDemo]) ?>

    <?= $this->Html->script('missionki.bundle') ?>
    <?= $this->fetch('script') ?>

    <!-- Clear deprecated toggle states from localStorage -->
    <script>
        // Remove any stored toggle states to ensure Tailwind is always enabled
        localStorage.removeItem('tailwind-disabled');
        localStorage.removeItem('bootstrap-disabled');
    </script>

    <?= $this->element('organisms/loading_overlay') ?>

    <script>
        // Show loading overlay on form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Handle form submissions (skip forms with data-no-loading attribute)
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('form');
                if (form && form.hasAttribute('data-no-loading')) {
                    return;
                }
                window.dispatchEvent(new CustomEvent('show-loading'));
            });

            // Handle navigation links (exclude external links and anchors)
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link &&
                    !link.hasAttribute('target') &&
                    !link.hasAttribute('download') &&
                    !link.hasAttribute('data-no-loading') &&
                    link.href &&
                    !link.getAttribute('href')?.startsWith('#') &&
                    link.href.startsWith(window.location.origin)) {
                    window.dispatchEvent(new CustomEvent('show-loading'));
                }
            });

            // Hide loading overlay when page becomes visible (after navigation)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    window.dispatchEvent(new CustomEvent('hide-loading'));
                }
            });

            // Hide loading if page is already loaded
            if (document.readyState === 'complete') {
                window.dispatchEvent(new CustomEvent('hide-loading'));
            }
        });
    </script>

</body>
</html>
