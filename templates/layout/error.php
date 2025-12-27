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
 */
?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?? 'de' ?>">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#3C0483">
    <title><?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css(['tailwind'], ['id' => 'tailwind-css']) ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 overflow-x-hidden">
    <!-- Global Flash Message Container (Fixed at top, overlays content) -->
    <div id="flash-message-container" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4 space-y-2">
        <?= $this->Flash->render() ?>
    </div>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-white rounded-lg shadow-lg p-8 text-center">
            <?= $this->fetch('content') ?>
            <div class="mt-8">
                <a href="javascript:history.back()" class="inline-flex items-center px-4 py-2 bg-purple-700 text-white rounded-md hover:bg-purple-800 transition-colors">
                    <?= __('Back') ?>
                </a>
                <a href="/" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
                    <?= __('Go to Homepage') ?>
                </a>
            </div>
        </div>
    </main>

    <?= $this->element('organisms/footer', ['isUiDemo' => false]) ?>

    <?= $this->Html->script('missionki.bundle') ?>
    <?= $this->fetch('script') ?>

    <!-- Clear deprecated toggle states from localStorage -->
    <script>
        // Remove any stored toggle states to ensure Tailwind is always enabled
        localStorage.removeItem('tailwind-disabled');
        localStorage.removeItem('bootstrap-disabled');
    </script>

    <?= $this->element('organisms/loading_overlay') ?>
</body>
</html>
