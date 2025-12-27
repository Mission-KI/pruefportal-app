<?php
/**
 * @var App\View\Cell\ProcessesCell $candidateProcesses
 */
use Cake\Core\Configure;
if(count($candidateProcesses) > 0):
    $statuses = Configure::read('statuses');
    $steps = [0=>0, 10=>1, 15=>1, 20=>2, 30=>3, 35=>4, 40=>4, 50=>5, 60=>5];

    echo $this->element('process_cards_list', [
        'processes' => $candidateProcesses,
        'statuses' => $statuses,
        'steps' => $steps
    ]);
endif;
