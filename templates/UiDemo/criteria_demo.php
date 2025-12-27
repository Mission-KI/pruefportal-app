<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Criterion $criterion
 * @var object $process
 * @var array $qualityDimensionQuestions
 * @var array $icons
 * @var array $questionTypes
 * @var string $quality_dimension
 * @var int $question_id
 * @var array $currentQuestions
 */
$this->assign('title', __('Protection Needs Analysis Demo - Server-side Rendering'));
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold mb-4"><?= __('Protection Needs Analysis Demo') ?></h1>
        <p class="text-lg text-gray-600">
            This demonstrates server-side rendering using FormFieldHelper and atomic design components.
        </p>
        <p class="text-sm text-blue-700 bg-blue-50 p-4 rounded mt-4">
            <strong>Note:</strong> This uses server-side rendering with data from
            <code>ProtectionNeedsAnalysis_<?= $quality_dimension ?>.json</code>.
            The form fields are rendered using the <code>protection_needs_renderer</code> molecule.
        </p>
    </div>

    <div class="max-w-4xl mx-auto" x-data="criteriaDemo()">
        <?php
        // Build the card body content
        ob_start();
        ?>

        <?= $this->Form->create($criterion, [
            'class' => 'space-y-6',
            'id' => 'criteria-demo-form',
            '@submit.prevent' => 'handleSubmit()'
        ]) ?>

        <!-- Protection Needs Analysis Header Card -->
        <div class="card card-primary rounded-lg p-6 mb-6">
            <div class="space-y-2">
                <h5 class="text-xl font-semibold text-white"><?= __('Protection Needs Analysis') ?></h5>
                <h2 class="text-3xl font-bold text-white mt-2"><?= h($questionTypes[$question_id]) ?></h2>
                <div class="flex items-center gap-2 mt-2">
                    <?= $this->element('atoms/icon', [
                        'name' => $icons[$quality_dimension],
                        'size' => 'md',
                        'options' => ['class' => 'text-white']
                    ]) ?>
                    <span class="text-white text-sm"><?= h($qualityDimensionQuestions[$quality_dimension]) ?> (<?= h($quality_dimension) ?>)</span>
                </div>
            </div>
        </div>

        <?php
            echo $this->Form->control('quality_dimension_id', ['type' => 'hidden', 'value' => 0]);
            echo $this->Form->control('question_id', ['type' => 'hidden', 'value' => $question_id]);
            echo $this->Form->control('process_id', ['type' => 'hidden', 'value' => $process->id]);
        ?>

        <!-- Render Protection Needs Questions using shared molecule -->
        <?= $this->element('molecules/protection_needs_renderer', [
            'currentQuestions' => $currentQuestions
        ]) ?>

        <div class="mt-6">
            <?= $this->element('atoms/button', [
                'label' => __('Next step'),
                'type' => 'submit',
                'variant' => 'primary'
            ]) ?>
        </div>

        <?= $this->Form->end() ?>

        <?php
        $cardBody = ob_get_clean();

        // Render the main card
        echo $this->element('molecules/card', [
            'title' => h($process->title),
            'body' => $cardBody,
            'escape' => false
        ]);
        ?>
    </div>

    <!-- How it works explanation card -->
    <div class="max-w-4xl mx-auto mt-8">
        <?php
        ob_start();
        ?>
        <div>
            <h3 class="font-semibold mb-2">How it works:</h3>
            <ol class="list-decimal list-inside space-y-2 text-sm text-gray-700">
                <li>Controller loads <code>ProtectionNeedsAnalysis_<?= h($quality_dimension) ?>.json</code></li>
                <li>JSON data is parsed and passed to the template</li>
                <li>Template uses <code>molecules/protection_needs_renderer</code> shared component</li>
                <li>Each question is rendered using <code>molecules/form_field</code> wrapper</li>
                <li>Radio buttons use <code>molecules/form_radio_group</code> atom</li>
                <li>No client-side JavaScript needed for rendering</li>
            </ol>
        </div>
        <?php
        $explanationContent = ob_get_clean();
        echo $this->element('molecules/card', [
            'variant' => 'default',
            'body' => $explanationContent,
            'escape' => false
        ]);
        ?>
    </div>
</div>

<!-- Alpine.js Component for Demo Form -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('criteriaDemo', () => ({
        handleSubmit() {
            // Collect form data
            const form = document.getElementById('criteria-demo-form');
            const formData = new FormData(form);
            const data = {};

            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            // Log to console
            console.log('Protection Needs Analysis Demo - Form Data:', data);

            // In the real application, this would:
            // 1. Submit to server (POST to /criteria/rate-q-d/{processId}/{quality_dimension})
            // 2. Server saves answers and increments question_id
            // 3. Server loads next question set from ProtectionNeedsAnalysis_{QD}.json
            // 4. Page reloads with new questions

            // For demo: simulate progression by reloading with next question_id
            const currentQuestionId = parseInt(new URLSearchParams(window.location.search).get('question_id') || '0');
            const maxQuestionId = 2; // MA has 3 question sets (0, 1, 2)

            if (currentQuestionId < maxQuestionId) {
                const nextQuestionId = currentQuestionId + 1;
                alert(`Answers saved!\n\nMoving to next question set (${nextQuestionId + 1}/${maxQuestionId + 1})`);
                window.location.href = '<?= $this->Url->build(['controller' => 'UiDemo', 'action' => 'criteriaDemo']) ?>?question_id=' + nextQuestionId;
            } else {
                alert('All questions completed!\n\nIn the real app, this would redirect to the next quality dimension or completion page.');
            }
        }
    }));
});
</script>

