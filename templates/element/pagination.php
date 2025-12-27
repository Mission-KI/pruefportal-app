<?php
/**
 * @var \Cake\View\View $this
 */
?>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center flex-wrap">
        <?= $this->Paginator->first('&laquo; ' . __('first'), [
            'class' => 'page-link',
            'escape' => false,
            'templates' => [
                'firstActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                'firstDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>'
            ]
        ]) ?>
        
        <?= $this->Paginator->prev('&lsaquo; ' . __('previous'), [
            'class' => 'page-link',
            'escape' => false,
            'templates' => [
                'prevActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                'prevDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>'
            ]
        ]) ?>
        
        <?= $this->Paginator->numbers([
            'class' => 'page-link',
            'modulus' => 3,
            'first' => 1,
            'last' => 1,
            'templates' => [
                'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                'current' => '<li class="page-item active" aria-current="page"><a class="page-link" href="">{{text}} <span class="visually-hidden">(current)</span></a></li>',
                'ellipsis' => '<li class="page-item disabled"><span class="page-link">...</span></li>'
            ]
        ]) ?>
        
        <?= $this->Paginator->next(__('next') . ' &rsaquo;', [
            'class' => 'page-link',
            'escape' => false,
            'templates' => [
                'nextActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                'nextDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>'
            ]
        ]) ?>
        
        <?= $this->Paginator->last(__('last') . ' &raquo;', [
            'class' => 'page-link',
            'escape' => false,
            'templates' => [
                'lastActive' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                'lastDisabled' => '<li class="page-item disabled"><a class="page-link" href="" tabindex="-1" aria-disabled="true">{{text}}</a></li>'
            ]
        ]) ?>
    </ul>
    
    <div class="text-center text-muted small mt-2">
        <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
    </div>
</nav>
