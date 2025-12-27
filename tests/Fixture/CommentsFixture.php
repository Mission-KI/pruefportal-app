<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CommentsFixture
 */
class CommentsFixture extends TestFixture
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
                'content' => 'This is a test comment.',
                'reference_id' => 'criterion_1',
                'user_id' => 1,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
                'seen' => 1,
                'process_id' => 1,
                'parent_id' => null,
            ],
            [
                'id' => 2,
                'content' => 'This is a reply comment.',
                'reference_id' => 'criterion_1',
                'user_id' => 2,
                'created' => '2025-01-01 11:00:00',
                'modified' => '2025-01-01 11:00:00',
                'seen' => 0,
                'process_id' => 1,
                'parent_id' => 1,
            ],
        ];
        parent::init();
    }
}
