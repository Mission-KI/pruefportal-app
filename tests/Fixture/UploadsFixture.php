<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UploadsFixture
 */
class UploadsFixture extends TestFixture
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
                'key' => 'Uploads/1234567890_test-file.pdf',
                'name' => 'test-file.pdf',
                'size' => 1024,
                'location' => 'https://s3.eu-central-1.amazonaws.com/bucket/Uploads/1234567890_test-file.pdf',
                'etag' => 'abc123def456',
                'process_id' => 1,
                'comment_id' => null,
                'indicator_id' => null,
                'created' => '2025-01-01 10:00:00',
                'modified' => '2025-01-01 10:00:00',
            ],
        ];
        parent::init();
    }
}
