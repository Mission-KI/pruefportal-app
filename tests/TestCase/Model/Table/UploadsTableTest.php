<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UploadsTable;
use Cake\ORM\Association\BelongsTo;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use ReflectionClass;

/**
 * App\Model\Table\UploadsTable Test Case
 *
 * Note: Storage operations are now handled by UploadService.
 * This test class focuses on table configuration and validation.
 */
class UploadsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UploadsTable
     */
    protected $Uploads;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Uploads',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Uploads') ? [] : ['className' => UploadsTable::class];
        $this->Uploads = $this->getTableLocator()->get('Uploads', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Uploads);

        parent::tearDown();
    }

    // ========================================
    // Table Configuration Tests
    // ========================================

    public function testTableName(): void
    {
        $this->assertEquals('uploads', $this->Uploads->getTable());
    }

    public function testDisplayField(): void
    {
        $this->assertEquals('name', $this->Uploads->getDisplayField());
    }

    public function testPrimaryKey(): void
    {
        $this->assertEquals('id', $this->Uploads->getPrimaryKey());
    }

    public function testHasTimestampBehavior(): void
    {
        $this->assertTrue($this->Uploads->hasBehavior('Timestamp'));
    }

    // ========================================
    // Association Tests
    // ========================================

    public function testBelongsToProcesses(): void
    {
        $association = $this->Uploads->getAssociation('Processes');

        $this->assertInstanceOf(BelongsTo::class, $association);
        $this->assertEquals('process_id', $association->getForeignKey());
    }

    public function testBelongsToComments(): void
    {
        $association = $this->Uploads->getAssociation('Comments');

        $this->assertInstanceOf(BelongsTo::class, $association);
        $this->assertEquals('comment_id', $association->getForeignKey());
    }

    public function testBelongsToIndicators(): void
    {
        $association = $this->Uploads->getAssociation('Indicators');

        $this->assertInstanceOf(BelongsTo::class, $association);
        $this->assertEquals('indicator_id', $association->getForeignKey());
    }

    // ========================================
    // Validation Tests
    // ========================================

    public function testValidationDefault(): void
    {
        $validator = $this->Uploads->getValidator('default');

        // Check key field validation
        $this->assertTrue($validator->hasField('key'));

        // Check name field validation
        $this->assertTrue($validator->hasField('name'));

        // Check size field validation
        $this->assertTrue($validator->hasField('size'));

        // Check location field validation
        $this->assertTrue($validator->hasField('location'));

        // Check etag field validation
        $this->assertTrue($validator->hasField('etag'));
    }

    public function testValidationKeyMaxLength(): void
    {
        $upload = $this->Uploads->newEntity([
            'key' => str_repeat('a', 256), // Exceeds 255 max
            'process_id' => 1,
        ]);

        $this->assertNotEmpty($upload->getErrors());
        $this->assertArrayHasKey('key', $upload->getErrors());
    }

    public function testValidationNameMaxLength(): void
    {
        $upload = $this->Uploads->newEntity([
            'name' => str_repeat('a', 256), // Exceeds 255 max
            'process_id' => 1,
        ]);

        $this->assertNotEmpty($upload->getErrors());
        $this->assertArrayHasKey('name', $upload->getErrors());
    }

    // ========================================
    // Finder Tests
    // ========================================

    public function testFindByProcessFinderExists(): void
    {
        $query = $this->Uploads->find('byProcess', process: 1);

        $this->assertInstanceOf(SelectQuery::class, $query);
    }

    public function testFindByProcessFiltersCorrectly(): void
    {
        $results = $this->Uploads->find('byProcess', process: 1)->all();

        foreach ($results as $upload) {
            $this->assertEquals(1, $upload->process_id);
        }
    }

    public function testFindByProcessIncludesCorrectCondition(): void
    {
        $query = $this->Uploads->find('byProcess', process: 1);
        $sql = $query->sql();

        $this->assertStringContainsString('process_id', $sql);
    }

    // ========================================
    // Architecture Tests
    // ========================================

    /**
     * Test that UploadsTable does not have a request property
     *
     * Table classes should not have access to the request object.
     */
    public function testTableDoesNotHaveRequestProperty(): void
    {
        $this->assertFalse(
            property_exists($this->Uploads, 'request'),
            'UploadsTable should not have a request property - request handling belongs in controllers',
        );
    }

    /**
     * Test that storage methods have been removed
     *
     * Storage operations are now handled by UploadService.
     */
    public function testStorageMethodsRemoved(): void
    {
        $this->assertFalse(
            method_exists($this->Uploads, 'uploadToS3'),
            'uploadToS3 should be removed - use UploadService instead',
        );

        $this->assertFalse(
            method_exists($this->Uploads, 'downloadS3File'),
            'downloadS3File should be removed - use UploadService instead',
        );

        $this->assertFalse(
            method_exists($this->Uploads, 'deleteS3File'),
            'deleteS3File should be removed - use UploadService instead',
        );

        $this->assertFalse(
            method_exists($this->Uploads, 'getClient'),
            'getClient should be removed - S3 client is now in S3Adapter',
        );

        $this->assertFalse(
            method_exists($this->Uploads, 'getBucketName'),
            'getBucketName should be removed - bucket config is now in S3Adapter',
        );
    }

    /**
     * Test that ORM callbacks for storage have been removed
     */
    public function testNoStorageCallbacks(): void
    {
        // The table should not have beforeSave or afterDelete callbacks
        // that handle storage operations
        $reflection = new ReflectionClass($this->Uploads);

        $this->assertFalse(
            $reflection->hasMethod('beforeSave'),
            'beforeSave callback should be removed - storage is handled by UploadService',
        );

        $this->assertFalse(
            $reflection->hasMethod('afterDelete'),
            'afterDelete callback should be removed - storage is handled by UploadService',
        );
    }
}
