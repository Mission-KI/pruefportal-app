<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * IndicatorsFixture
 */
class IndicatorsFixture extends TestFixture
{
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
                'title' => 'Test Indicator 1',
                'level_candidate' => 2,
                'level_examiner' => 3,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'process_id' => 1,
                'quality_dimension_id' => 10,
                'evidence' => 'Evidence for indicator 1.',
            ],
            [
                'id' => 2,
                'title' => 'Test Indicator 2',
                'level_candidate' => 1,
                'level_examiner' => null,
                'created' => '2025-01-01 11:00:00',
                'modified' => '2025-01-01 11:00:00',
                'process_id' => 1,
                'quality_dimension_id' => 20,
                'evidence' => null,
            ],
        ];
        parent::init();
    }
}
