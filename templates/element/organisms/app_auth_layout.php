<?php
/**
 * Authentication Layout Organism
 *
 * Shared layout for login, register, and accept-invitation pages
 * Provides split-screen design with purple branding left and form content right
 *
 * @var \App\View\AppView $this
 * @var string $title Page title (required)
 * @var string $subtitle Page subtitle/description (required)
 * @var string $content Form content (required)
 * @var bool $show_footer Whether to show footer links (default: true)
 * @var bool $show_docs_button Whether to show documentation button (default: false)
 * @var bool $allow_scroll Whether content can scroll beyond viewport (default: true)
 */

$title = $title ?? '';
$subtitle = $subtitle ?? '';
$content = $content ?? '';
$show_footer = $show_footer ?? true;
$show_docs_button = $show_docs_button ?? false;
$allow_scroll = $allow_scroll ?? true;

// Right side positioning classes
// If allow_scroll is true, don't use bottom-0 to prevent background clipping on tall content
$right_classes = [
    'lg:absolute',
    'lg:top-0',
    'lg:right-0',
    'lg:left-1/2',
    'flex',
    'items-center',
    'justify-center',
    'p-8',
    'bg-white',
    'pt-16'
];

if (!$allow_scroll) {
    $right_classes[] = 'lg:bottom-0';
}

$right_class_string = implode(' ', $right_classes);
?>

<div class="min-h-screen lg:relative">
    <!-- Left Side: Branding (Purple Background) -->
    <?= $this->element('organisms/app_left_purple_branding') ?>

    <!-- Right Side: Content -->
    <div class="<?= $right_class_string ?>">
        <div class="w-full max-w-105">
            <?php if ($title || $subtitle): ?>
            <div class="mb-10">
                <?php if ($title): ?>
                <h1 class="text-brand-deep font-semibold mb-4 display-md">
                    <?= $title ?>
                </h1>
                <?php endif; ?>

                <?php if ($subtitle): ?>
                <p class="text-gray-600 text-md">
                    <?= $subtitle ?>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Main content (form) -->
            <?= $content ?>

            <?php if ($show_footer): ?>
            <!-- Footer Links -->
            <div class="mt-8 flex justify-between items-center flex-wrap">
                <p class="text-gray-600 text-sm">
                    <a href="mailto:<?= __('help@missionki.de') ?>" class="text-gray-500 inline-flex items-center gap-2 text-sm">
                        <?= $this->element('atoms/icon', ['name' => 'mail', 'size' => 'xs']) ?>
                        <?= __('help@missionki.de') ?>
                    </a>
                </p>
                <p class="text-gray-600 text-sm">
                    <a href="https://www.mission-ki.de/" class="text-gray-500 inline-flex items-center gap-2 text-sm">mission-ki.de</a>
                </p>
            </div>
            <?php endif; ?>

            <?php if ($show_docs_button): ?>
            <!-- Documentation Button -->
            <div class="mt-8 flex justify-between align-items-end flex-wrap">
                <?= $this->element('atoms/button', [
                    'label' => __('Zur Dokumentation') . ' →',
                    'variant' => 'secondary',
                    'size' => 'XS',
                    'url' => 'https://docs.pruefportal.mission-ki.de/'
                ]) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
