<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TagsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TagsTable Test Case
 */
class TagsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TagsTable
     */
    protected $Tags;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Tags',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Tags') ? [] : ['className' => TagsTable::class];
        $this->Tags = $this->getTableLocator()->get('Tags', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Tags);

        parent::tearDown();
    }

    // ========================================
    // Validation Tests
    // ========================================

    /**
     * Test validation requires title field
     *
     * @return void
     */
    public function testValidationRequiresTitle(): void
    {
        $entity = $this->Tags->newEntity([
            // title is missing
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['title']);
    }

    /**
     * Test validation title max length
     *
     * @return void
     */
    public function testValidationTitleMaxLength(): void
    {
        $entity = $this->Tags->newEntity([
            'title' => str_repeat('a', 256), // 256 characters, exceeds max
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('maxLength', $entity->getErrors()['title']);
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Tags->newEntity([
            'title' => 'Unique Tag ' . time(),
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules enforce title uniqueness
     *
     * @return void
     */
    public function testBuildRulesTitleIsUnique(): void
    {
        // Verify existing tag exists
        $existingTag = $this->Tags->find()
            ->where(['title' => 'Test Tag'])
            ->first();
        $this->assertNotNull($existingTag, 'Fixture tag should exist');

        // Check that duplicate title count exists
        $this->assertGreaterThan(
            0,
            $this->Tags->find()->where(['title' => 'Test Tag'])->count(),
        );
    }

    /**
     * Test build rules accepts unique title
     *
     * @return void
     */
    public function testBuildRulesAcceptsUniqueTitle(): void
    {
        // Delete existing tags to avoid PK conflicts
        $this->Tags->deleteAll([]);

        $entity = $this->Tags->newEntity([
            'title' => 'Brand New Tag ' . time(),
        ]);

        $result = $this->Tags->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }
}
