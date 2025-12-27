<?php
/**
 * Pagination Molecule Component
 *
 * Enhanced pagination component with configurable display options.
 * Wraps CakePHP pagination helpers with consistent Bootstrap styling.
 *
 * @var \App\View\AppView $this
 * @var bool $show_first_last Whether to show first/last page links (default: true)
 * @var bool $show_prev_next Whether to show prev/next page links (default: true)
 * @var bool $show_numbers Whether to show page number links (default: true)
 * @var bool $show_counter Whether to show pagination counter text (default: true)
 * @var int $modulus Number of page links to show around current page (default: 3)
 * @var string $size Pagination size (sm|lg, default: normal)
 * @var string $alignment Pagination alignment (start|center|end, default: center)
 * @var array $options Additional HTML attributes for the nav container
 * @var string $counter_format Custom counter text format (optional)
 */

// Set defaults
$show_first_last = $show_first_last ?? true;
$show_prev_next = $show_prev_next ?? true;
$show_numbers = $show_numbers ?? true;
$show_counter = $show_counter ?? true;
$modulus = $modulus ?? 3;
$size = $size ?? '';
$alignment = $alignment ?? 'center';
$options = $options ?? [];
$counter_format = $counter_format ?? __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total');

// Build pagination classes
$paginationClasses = ['pagination', 'flex-wrap'];

// Add size class
if ($size === 'sm') {
    $paginationClasses[] = 'pagination-sm';
} elseif ($size === 'lg') {
    $paginationClasses[] = 'pagination-lg';
}

// Add alignment class
switch ($alignment) {
    case 'start':
        $paginationClasses[] = 'justify-content-start';
        break;
    case 'end':
        $paginationClasses[] = 'justify-content-end';
        break;
    case 'center':
    default:
        $paginationClasses[] = 'justify-content-center';
        break;
}

// Common pagination templates for consistent styling
$templates = [
    'firstActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'firstDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>',
    'prevActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'prevDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>',
    'nextActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'nextDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>',
    'lastActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'lastDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>',
    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
    'current' => '<li class="page-item active" aria-current="page"><a class="page-link" href="">{{text}} <span class="visually-hidden">(current)</span></a></li>',
    'ellipsis' => '<li class="page-item disabled"><span class="page-link">...</span></li>'
];

// Check if pagination is needed
if (!$this->Paginator->hasNext() && !$this->Paginator->hasPrev() && $this->Paginator->counter('{{pages}}') <= 1) {
    return; // Don't show pagination for single page
}
?>

<nav aria-label="Page navigation"<?= isset($options['id']) ? ' id="' . h($options['id']) . '"' : '' ?>>
    <ul class="<?= implode(' ', $paginationClasses) ?>">
        <?php if ($show_first_last): ?>
            <?= $this->Paginator->first('&laquo; ' . __('first'), [
                'class' => 'page-link',
                'escape' => false,
                'templates' => $templates
            ]) ?>
        <?php endif; ?>

        <?php if ($show_prev_next): ?>
            <?= $this->Paginator->prev('&lsaquo; ' . __('previous'), [
                'class' => 'page-link',
                'escape' => false,
                'templates' => $templates
            ]) ?>
        <?php endif; ?>

        <?php if ($show_numbers): ?>
            <?= $this->Paginator->numbers([
                'class' => 'page-link',
                'modulus' => $modulus,
                'first' => 1,
                'last' => 1,
                'templates' => $templates
            ]) ?>
        <?php endif; ?>

        <?php if ($show_prev_next): ?>
            <?= $this->Paginator->next(__('next') . ' &rsaquo;', [
                'class' => 'page-link',
                'escape' => false,
                'templates' => $templates
            ]) ?>
        <?php endif; ?>

        <?php if ($show_first_last): ?>
            <?= $this->Paginator->last(__('last') . ' &raquo;', [
                'class' => 'page-link',
                'escape' => false,
                'templates' => $templates
            ]) ?>
        <?php endif; ?>
    </ul>

    <?php if ($show_counter): ?>
        <div class="text-<?= $alignment ?> text-muted small mt-2">
            <?= $this->Paginator->counter($counter_format) ?>
        </div>
    <?php endif; ?>
</nav>