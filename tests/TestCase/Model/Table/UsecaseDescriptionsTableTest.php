<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsecaseDescriptionsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsecaseDescriptionsTable Test Case
 */
class UsecaseDescriptionsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsecaseDescriptionsTable
     */
    protected $UsecaseDescriptions;

    /**
     * Fixtures - ordered by FK dependencies
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.UsecaseDescriptions',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('UsecaseDescriptions') ? [] : ['className' => UsecaseDescriptionsTable::class];
        $this->UsecaseDescriptions = $this->getTableLocator()->get('UsecaseDescriptions', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->UsecaseDescriptions);

        parent::tearDown();
    }

    // ========================================
    // Validation Tests
    // ========================================

    /**
     * Test validation requires step field
     *
     * @return void
     */
    public function testValidationRequiresStep(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'version' => 1,
            'process_id' => 1,
            'user_id' => 1,
            // step is missing
        ]);

        $this->assertArrayHasKey('step', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['step']);
    }

    /**
     * Test validation requires version field
     *
     * @return void
     */
    public function testValidationRequiresVersion(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'process_id' => 1,
            'user_id' => 1,
            // version is missing
        ]);

        $this->assertArrayHasKey('version', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['version']);
    }

    /**
     * Test validation rejects empty process_id
     *
     * @return void
     */
    public function testValidationRejectsEmptyProcessId(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'user_id' => 1,
            'process_id' => '', // Empty string
        ]);

        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test validation rejects empty user_id
     *
     * @return void
     */
    public function testValidationRejectsEmptyUserId(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'process_id' => 1,
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
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'process_id' => 1,
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
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'process_id' => 1,
            'user_id' => 1,
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules requires existing process
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingProcess(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'process_id' => 99999, // Non-existent process
            'user_id' => 1,
        ]);

        $result = $this->UsecaseDescriptions->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test build rules requires existing user
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingUser(): void
    {
        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 1,
            'version' => 1,
            'process_id' => 1,
            'user_id' => 99999, // Non-existent user
        ]);

        $result = $this->UsecaseDescriptions->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts existing process and user
     *
     * @return void
     */
    public function testBuildRulesAcceptsExistingProcessAndUser(): void
    {
        // Delete existing usecase descriptions to avoid PK conflicts
        $this->UsecaseDescriptions->deleteAll([]);

        $entity = $this->UsecaseDescriptions->newEntity([
            'step' => 99,
            'version' => 99,
            'process_id' => 1, // Exists in fixture
            'user_id' => 1, // Exists in fixture
        ]);

        $result = $this->UsecaseDescriptions->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }
}
