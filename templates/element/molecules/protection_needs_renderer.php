<?php
/**
 * Protection Needs Analysis Renderer Molecule
 *
 * Reusable component for rendering Protection Needs Analysis questions.
 * Used by both rate_q_d.php and criteria_demo.php to ensure identical rendering.
 *
 * @var \App\View\AppView $this
 * @var array $currentQuestions Array of questions from JSON
 */

if (empty($currentQuestions)) {
    echo '<p class="text-gray-600">No questions available for this section.</p>';
    return;
}

?>

<!-- Render Protection Needs Questions -->
<div class="space-y-6">
    <?php foreach ($currentQuestions as $idx => $question): ?>
        <?php
            // Ensure question is an array with required fields
            if (!is_array($question) || empty($question)) {
                continue;
            }

            // Get question ID from 'question_id' property (renamed from JSON 'id' to avoid conflict with DB record ID)
            $questionId = $question['question_id'] ?? null;

            // Ensure we have required fields
            if (!$questionId || !isset($question['answers']) || !is_array($question['answers'])) {
                continue;
            }

            $relatedClass = null;
            if(array_key_exists('relatedQuestions', $question)) {
                $relatedClass = ' js-related-question ' . implode(' ', $question['relatedQuestions']); // add relations as CSS classes for JS Handling the sync of related questions
            }

            // Convert answers object to options array for radio group
            $options = [];
            foreach ($question['answers'] as $value => $label) {
                $options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        ?>
    <?php if(array_key_exists('id', $question)): // Edit criterion ?>
        <input type="hidden" name="criteria[<?= $questionId ?>][id]" value="<?= $question['id'] ?>">
    <?php endif;?>
        <input type="hidden" name="criteria[<?= $questionId ?>][protection_target_category_id]" value="<?= $question['category'] ?>">
        <input type="hidden" name="criteria[<?= $questionId ?>][criterion_type_id]" value="<?= $question['criteria'] ?>">
        <input type="hidden" name="criteria[<?= $questionId ?>][title]" value="<?= $questionId ?>">

        <?= $this->element('molecules/form_field', [
            'name' => 'criteria['.$questionId.'][value]',
            'label' => $question['question'],
            'tooltip' => !empty($question['tooltip']) ? $question['tooltip'] : null,
            'type' => 'radio',
            'index' => $questionId,
            'required' => true,
            'atom_element' => 'molecules/form_radio_group',
            'atom_data' => [
                'relatedClass' => $relatedClass,
                'required' => true,
                'name' => 'criteria['.$questionId.'][value]',
                'baseId' => 'pna_' . $questionId,
                'options' => $options,
                'selectedValue' => array_key_exists('value', $question) ? $question['value'] : null,
                'layout' => 'vertical'
            ],
            'client_error_messages' => [
                __('Please select an answer for this question.')
            ]
        ]) ?>
    <?php endforeach; ?>
</div>
