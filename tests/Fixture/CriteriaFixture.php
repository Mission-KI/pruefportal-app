<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CriteriaFixture
 */
class CriteriaFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'criteria';
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'title' => 'Test Criterion AP',
                'quality_dimension_id' => 10,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_id' => 1,
                'value' => 2,
                'criterion_type_id' => 1,
                'question_id' => 0, // AP question
                'protection_target_category_id' => 1,
            ],
            [
                'id' => 2,
                'title' => 'Test Criterion GF',
                'quality_dimension_id' => 10,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_id' => 1,
                'value' => 3,
                'criterion_type_id' => 2,
                'question_id' => 1, // GF question
                'protection_target_category_id' => 1,
            ],
            [
                'id' => 3,
                'title' => 'Test Criterion EF',
                'quality_dimension_id' => 20,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_id' => 1,
                'value' => 1,
                'criterion_type_id' => 3,
                'question_id' => 2, // EF question
                'protection_target_category_id' => 2,
            ],
        ];
        parent::init();
    }
}
