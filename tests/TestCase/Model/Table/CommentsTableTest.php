<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CommentsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CommentsTable Test Case
 */
class CommentsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CommentsTable
     */
    protected $Comments;

    /**
     * Fixtures - ordered by FK dependencies
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Comments',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Comments') ? [] : ['className' => CommentsTable::class];
        $this->Comments = $this->getTableLocator()->get('Comments', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Comments);

        parent::tearDown();
    }

    // ========================================
    // Validation Tests
    // ========================================

    /**
     * Test validation allows empty content
     *
     * @return void
     */
    public function testValidationAllowsEmptyContent(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref',
            'user_id' => 1,
            'process_id' => 1,
            'content' => null,
        ]);

        $this->assertArrayNotHasKey('content', $entity->getErrors());
    }

    /**
     * Test validation requires reference_id field
     *
     * @return void
     */
    public function testValidationRequiresReferenceId(): void
    {
        $entity = $this->Comments->newEntity([
            'user_id' => 1,
            'process_id' => 1,
            // reference_id is missing
        ]);

        $this->assertArrayHasKey('reference_id', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['reference_id']);
    }

    /**
     * Test validation rejects empty user_id
     *
     * @return void
     */
    public function testValidationRejectsEmptyUserId(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref',
            'user_id' => '', // Empty string
            'process_id' => 1,
        ]);

        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test validation rejects empty process_id
     *
     * @return void
     */
    public function testValidationRejectsEmptyProcessId(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref',
            'user_id' => 1,
            'process_id' => '', // Empty string
        ]);

        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test validation allows empty parent_id
     *
     * @return void
     */
    public function testValidationAllowsEmptyParentId(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref',
            'user_id' => 1,
            'process_id' => 1,
            'parent_id' => null,
        ]);

        $this->assertArrayNotHasKey('parent_id', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref_' . time(),
            'user_id' => 1,
            'process_id' => 1,
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules requires existing user
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingUser(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref_' . time(),
            'user_id' => 99999, // Non-existent user
            'process_id' => 1,
        ]);

        $result = $this->Comments->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test build rules requires existing process
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingProcess(): void
    {
        $entity = $this->Comments->newEntity([
            'reference_id' => 'test_ref_' . time(),
            'user_id' => 1,
            'process_id' => 99999, // Non-existent process
        ]);

        $result = $this->Comments->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts existing user and process
     *
     * @return void
     */
    public function testBuildRulesAcceptsExistingUserAndProcess(): void
    {
        // Delete existing comments to avoid PK conflicts
        $this->Comments->deleteAll([]);

        $entity = $this->Comments->newEntity([
            'reference_id' => 'new_test_ref_' . time(),
            'user_id' => 1, // Exists in fixture
            'process_id' => 1, // Exists in fixture
        ]);

        $result = $this->Comments->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }
}
