<!-- Sidebar -->
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if ($this->Identity->isLoggedIn()): ?>
                <?= $this->element('Users/user_sidebar'); ?>
            <?php else:?>
            <li class="nav-item">
                <?= $this->Html->link(
                    '<i class="bi bi-house-door me-2"></i> ' . __('Home'),
                    ['controller' => 'Pages', 'action' => 'display', 'home'],
                    ['class' => 'nav-link', 'escape' => false]
                ) ?>
            </li>
            <li class="nav-item">
                <?= $this->Html->link(
                    '<i class="bi bi-info-circle me-2"></i> ' . __('About'),
                    ['controller' => 'Pages', 'action' => 'display', 'about'],
                    ['class' => 'nav-link', 'escape' => false]
                ) ?>
            </li>
            <?php endif;?>
        </ul>
    </div>
</nav>