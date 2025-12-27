<?php
/**
 * User Badge Molecule Component
 *
 * Composes avatar and user info for consistent user display.
 * Refactored to use avatar atom + user_info molecule.
 *
 * @var \App\View\AppView $this
 * @var string $avatar_initials Avatar initials (required)
 * @var string $full_name User full name (required)
 * @var string $role User role/title (optional)
 * @var string $orientation User info orientation ('horizontal' | 'vertical', default: 'vertical')
 * @var array|string $url Profile link URL (optional)
 * @var bool $online_status Online status indicator (optional)
 * @var array $options Additional HTML attributes
 */

use App\Utility\Icon;

$avatar_initials = $avatar_initials ?? '';
$full_name = $full_name ?? '';
$role = $role ?? '';
$orientation = $orientation ?? 'vertical';
$url = $url ?? null;
$online_status = $online_status ?? null;
$options = $options ?? [];

if (empty($avatar_initials) || empty($full_name)) {
    return;
}

$classes = ['inline-flex', 'items-center', 'gap-3'];

if (isset($options['class'])) {
    $classes[] = $options['class'];
    unset($options['class']);
}

$options['class'] = implode(' ', $classes);

$avatarHtml = $this->element('atoms/avatar', [
    'initials' => $avatar_initials,
    'full_name' => $full_name,
    'size' => 'md',
    'online_status' => $online_status
]);

$userInfoHtml = $this->element('molecules/user_info', [
    'full_name' => $full_name,
    'role' => $role,
    'orientation' => $orientation
]);

$content = $avatarHtml . $userInfoHtml;
?>

<?php if ($url): ?>
    <?php
    $linkOptions = array_merge($options, [
        'escape' => false,
        'class' => ($options['class'] ?? '') . ' no-underline'
    ]);
    ?>
    <?= $this->Html->link($content, $url, $linkOptions) ?>
<?php else: ?>
    <div<?= $this->Html->templater()->formatAttributes($options) ?>>
        <?= $content ?>
    </div>
<?php endif; ?>
