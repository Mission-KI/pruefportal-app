<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProcessesExaminersFixture
 *
 * Junction table fixture for the many-to-many relationship between Processes and Users (Examiners)
 */
class ProcessesExaminersFixture extends TestFixture
{
    /**
     * Table name
     *
     * @var string
     */
    public string $table = 'processes_examiners';

    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'process_id' => 1,
                'user_id' => 2,
                'created' => '2025-01-01 10:00:00',
                'created_by' => 1,
            ],
        ];
        parent::init();
    }
}
