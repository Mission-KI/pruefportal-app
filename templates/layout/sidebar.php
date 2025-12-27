<?php
    /**
     * Sidebar
     *
     *
     * @var isLoggedIn
     */
?>
<nav id="sidebar" class="w-full md:w-1/4 lg:w-1/6 block md:block bg-gray-100 sidebar collapse">
    <div class="sticky pt-3">
        <ul class="nav flex flex-col">
            <?php if ($isLoggedIn): ?>
                <?= $this->element('Users/user_sidebar'); ?>
            <?php else:?>
            <li class="nav-item">
                <?= $this->Html->link(
                    '<i class="bi bi-house-door mr-2"></i> ' . __('Home'),
                    ['controller' => 'Pages', 'action' => 'display', 'home'],
                    ['class' => 'nav-link', 'escape' => false]
                ) ?>
            </li>
            <li class="nav-item">
                <?= $this->Html->link(
                    '<i class="bi bi-info-circle mr-2"></i> ' . __('About'),
                    ['controller' => 'Pages', 'action' => 'display', 'about'],
                    ['class' => 'nav-link', 'escape' => false]
                ) ?>
            </li>
            <?php endif;?>
        </ul>
    </div>
</nav>
