<?php
/**
 * @var \App\View\AppView $this
 * @var array $all_criteria
 * @var \App\Model\Entity\Process $processes
 * @var \App\Model\Entity\Process $process
 * @var \App\Model\Entity\Criterion $criterion
 * @var \App\Controller\Admin\CriteriaController $protectionNeedsAnalysis
 * @var \App\Controller\Admin\CriteriaController $qualityDimensionIds
 * @var \App\Controller\Admin\CriteriaController $relevances
 * @var \App\Controller\AppController $protectionTargetCategories
 * @var \App\Controller\AppController $criterionTypes
 * @var \App\Controller\AppController $questionTypes
 * @var \App\Controller\AppController $statuses
 * @var string $currentLanguage
 *
 *  10 => 'CY' => __('Qualitätsdimension KI-spezifische Cybersicherheit'),
 *  20 => 'TR' => __('Qualitätsdimension Transparenz'),
 *  30 => 'ND' => __('Qualitätsdimension Nicht-Diskriminierung'),
 *  40 => 'VE' => __('Qualitätsdimension Verlässlichkeit'),
 *  50 => 'DA' => __('Qualitätsdimension Datenqualität, -schutz und -Governance'),
 *  60 => 'MA' => __('Qualitätsdimension Menschliche Aufsicht und Kontrolle'),
 *
 * $this->criterionTypes = [
 * 10 => __('Schutzbedarf Kriterium: Datenqualität'),
 * 11 => __('Schutzbedarf Kriterium: Schutz personenbezogener Daten'),
 * 12 => __('Schutzbedarf Kriterium: Schutz proprietärer Daten'),
 * 20 => __('Schutzbedarf Kriterium: Vermeidung von ungerechtfertigter Diskriminierung und Verzerrung'),
 * 30 => __('Schutzbedarf Kriterium: Rückverfolgbarkeit & Dokumentation'),
 * 31 => __('Schutzbedarf Kriterium: Erklärbarkeit & Interpretierbarkeit'),
 * 40 => __('Schutzbedarf Kriterium: Menschliche Handlungsfähigkeit'),
 * 41 => __('Schutzbedarf Kriterium: Menschliche Aufsicht'),
 * 50 => __('Schutzbedarf Kriterium: Leistungsfähigkeit und Robustheit'),
 * 51 => __('Schutzbedarf Kriterium: Rückfallpläne und funktionale Sicherheit'),
 * 60 => __('Schutzbedarf Kriterium: Allgemeine KI-spezifische Cybersicherheit'),
 * 61 => __('Schutzbedarf Kriterium: Widerstandsfähigkeit gegen KI-spezifische Angriffe')
 * ];
 */
$options = ['type' => 'select', 'options' => $processes, 'empty' => true, 'class' => 'w-full', 'label' => __d('admin', 'Process')];
if(isset($process)) {
    $options['value'] = $process->id;
}
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __d('admin', 'Filter') ?></h4>
            <?= $this->Form->create(null, ['type' => 'get', 'id' => 'process-filter-form']) ?>
            <?= $this->Form->control('process_id', $options) ?>
            <?= $this->Form->end() ?>
            <h4 class="heading"><?= __d('admin', 'Actions') ?></h4>
            <?= $this->Html->link(__d('admin', 'List Criteria'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="criteria view content">
            <h2><?= __d('admin', 'Criteria Calculation') ?></h2>
<?php if(isset($process)): ?>
            <p><strong><?= $process->title ?></strong> - <?= __d('admin', 'Status') ?>: <?= $statuses[$this->Number->format($process->status_id)] ?></p>
    <?php
        $questionTypeRelevance = [];
        $criterionTypeSelection = [];

//pr($all_criteria); die;
        // 1) Calc relevance by $quality_dimension_id (e.g. 10 => 'CY')
        // 2) Sort criteria by $question_id (e.g. 0 => 'AP') and $criterion_type_id (e.g. 10 => 'Schutzbedarf Kriterium: Datenqualität')
        foreach($all_criteria as $quality_dimension_id => $criteria) {
            $EF_questionTotals = 0;
            $EF_questionCounts = 0;
            $questionAverages = [];

            foreach ($criteria as $criterion) {
                if (!isset($questionTypeRelevance[$quality_dimension_id][$criterion->question_id])) {
                    $questionTypeRelevance[$quality_dimension_id][$criterion->question_id] = false; // Default set relevance to false for this $quality_dimension_id => question_id
                }

                $criterionTypeSelection[$criterion->criterion_type_id][] = $criterion;

                // AP question_id = 0 the quality_dimension is relevant because one AP is > 1 so the GF have to be answered
                if((int) $criterion->question_id === 0 && (int) $criterion->value > 1) {
                    $questionTypeRelevance[$quality_dimension_id][$criterion->question_id] = true;
                }
                // GF question_id = 1 the quality_dimension is relevant because one GF is > 2
                if((int) $criterion->question_id === 1 && (int) $criterion->value > 2) {
                    $questionTypeRelevance[$quality_dimension_id][$criterion->question_id] = true;
                }
                // EF question_id = 2
                if((int) $criterion->question_id === 2) {
                    $EF_questionTotals += (int) $criterion->value;
                    $EF_questionCounts++;
                }
            }

            if($EF_questionCounts > 0) {
                $questionAverages[$quality_dimension_id] = ($EF_questionTotals / $EF_questionCounts);
                $questionTypeRelevance[$quality_dimension_id][2] = ($EF_questionTotals / $EF_questionCounts) > 2 ? true : false;
            }
        }
//pr($qualityDimensionIds);
//pr($protectionNeedsAnalysis); die;
        // Display results
        foreach($all_criteria as $quality_dimension_id => $criteria):
            echo '<h4 style="margin: 2rem 0 0 0;">' . $protectionNeedsAnalysis[$qualityDimensionIds[$quality_dimension_id]]['title'][$currentLanguage] . ' (' . $qualityDimensionIds[$quality_dimension_id] . ' - '. $quality_dimension_id .  ')</h4>';
            $displayedQuestions = [];
            foreach ($criteria as $criterion):
                if (!in_array($criterion->question_id, $displayedQuestions)) {
                    echo '<strong>' . $questionTypes[$criterion->question_id] .'</strong>' . $this->element('admin_boolean', ['bool' => $questionTypeRelevance[$quality_dimension_id][$criterion->question_id]]) . __d('admin', ' Question Relevance');
                    $displayedQuestions[] = $criterion->question_id;
                    if(array_key_exists($quality_dimension_id, $questionAverages) && $criterion->question_id === 2) {
                        echo ' &#8709; ' . number_format($questionAverages[$quality_dimension_id], 2);
                    }
                    echo '<br>';
                }
                echo $criterionTypes[$criterion->criterion_type_id] . ' (' . $criterion->criterion_type_id .  '): <strong>' . $criterion->value . '</strong> ' . $this->Html->link($criterion->title, ['action' => 'view', $criterion->id]) . '<br>';
            endforeach;
            $displayedQuestions = [];
        endforeach;
?>
        <hr>
        <h3 style="margin: 2rem 0 0 0;"><?= __d('admin', 'Criteria Types') ?></h3>
    <?php foreach($relevances as $quality_dimension_id => $relevance): ?>
        <div class="mb-6 card">
            <h5 style="margin-bottom: 0">
                <?= $protectionNeedsAnalysis[$qualityDimensionIds[$quality_dimension_id]]['title'][$currentLanguage] ?>
                <?= $quality_dimension_id ?>
                (<?= $qualityDimensionIds[$quality_dimension_id] ?>)
            </h5>
            <ul>

                <?php foreach($relevance as $criterion_type_id => $relevant): ?>
                <li>
                    <?= $criterion_type_id ?>
                    <?= $criterionTypes[$criterion_type_id] ?>
                    <?= $relevant ? $this->element('admin_boolean', ['bool' => (bool) $relevant]) . $relevant : $this->element('admin_boolean', ['bool' => false]) ?>
                </li>
                <?php endforeach; ?>
            </ul>


        </div>
    <?php endforeach; ?>

<?php else: ?>
        Select a Process (SBA is ready)
<?php endif; ?>
        </div>
    </div>
</div>
<?php $this->append('script'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const processSelect = document.getElementById('process-id');
        if (processSelect) {
            processSelect.addEventListener('change', function() {
                document.getElementById('process-filter-form').submit();
            });
        }
    });
</script>
<?php $this->end(); ?>
