<li class="nav-item">
    <?= $this->Html->link(
        '<i class="bi bi-file-bar-graph me-2"></i> ' . __('Dashboard'),
        ['controller' => 'Pages', 'action' => 'display', 'home'],
        ['class' => 'nav-link', 'escape' => false]
    ) ?>
</li>
<li class="nav-item">
    <?= $this->Html->link(
        '<i class="bi bi-activity me-2"></i> ' . __('Projects'),
        ['controller' => 'Projects', 'action' => 'index'],
        ['class' => 'nav-link', 'escape' => false]
    ) ?>
</li>
<li class="nav-item">
    <?= $this->Html->link(
        '<i class="bi bi-chat-square me-2"></i> ' . __('Comments'),
        ['controller' => 'Processes', 'action' => 'comments'],
        ['class' => 'nav-link', 'escape' => false]
    ) ?>
</li>
<li class="nav-item">
    <?= $this->Html->link(
        '<i class="bi bi-person me-2"></i> ' . __('My Account'),
        ['controller' => 'Users', 'action' => 'view'],
        ['class' => 'nav-link', 'escape' => false]
    ) ?>
</li>
