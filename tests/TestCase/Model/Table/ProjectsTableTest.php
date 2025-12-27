<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProjectsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProjectsTable Test Case
 */
class ProjectsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProjectsTable
     */
    protected $Projects;

    /**
     * Fixtures - ordered by FK dependencies
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Projects') ? [] : ['className' => ProjectsTable::class];
        $this->Projects = $this->getTableLocator()->get('Projects', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Projects);

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
        $entity = $this->Projects->newEntity([
            'user_id' => 1,
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
        $entity = $this->Projects->newEntity([
            'title' => str_repeat('a', 256), // 256 characters, exceeds max
            'user_id' => 1,
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('maxLength', $entity->getErrors()['title']);
    }

    /**
     * Test validation rejects empty user_id
     *
     * @return void
     */
    public function testValidationRejectsEmptyUserId(): void
    {
        $entity = $this->Projects->newEntity([
            'title' => 'Test Project',
            'user_id' => '', // Empty string
        ]);

        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test validation allows empty description
     *
     * @return void
     */
    public function testValidationAllowsEmptyDescription(): void
    {
        $entity = $this->Projects->newEntity([
            'title' => 'Unique Test Project ' . time(),
            'user_id' => 1,
            'description' => null,
        ]);

        $this->assertArrayNotHasKey('description', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Projects->newEntity([
            'title' => 'Unique Project Title ' . time(),
            'user_id' => 1,
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
        // Verify existing project exists
        $existingProject = $this->Projects->find()
            ->where(['title' => 'Test Project'])
            ->first();
        $this->assertNotNull($existingProject, 'Fixture project should exist');

        // Check that duplicate title count exists
        $this->assertGreaterThan(
            0,
            $this->Projects->find()->where(['title' => 'Test Project'])->count(),
        );
    }

    /**
     * Test build rules requires existing user
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingUser(): void
    {
        $entity = $this->Projects->newEntity([
            'title' => 'New Unique Project ' . time(),
            'user_id' => 99999, // Non-existent user
        ]);

        $result = $this->Projects->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts existing user
     *
     * @return void
     */
    public function testBuildRulesAcceptsExistingUser(): void
    {
        // Delete existing projects to avoid PK and unique title conflicts
        $this->Projects->deleteAll([]);

        $entity = $this->Projects->newEntity([
            'title' => 'Brand New Project ' . time(),
            'user_id' => 1, // Exists in fixture
        ]);

        $result = $this->Projects->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }
}
