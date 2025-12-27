<div class="paginator">
    <ul class="pagination">
        <?= $this->Paginator->first('<< ' . __d('admin', 'first')) ?>
        <?= $this->Paginator->prev('< ' . __d('admin', 'previous')) ?>
        <?= $this->Paginator->numbers() ?>
        <?= $this->Paginator->next(__d('admin', 'next') . ' >') ?>
        <?= $this->Paginator->last(__d('admin', 'last') . ' >>') ?>
    </ul>
    <p><?= $this->Paginator->counter(__d('admin', 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
</div>
