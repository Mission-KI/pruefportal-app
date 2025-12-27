<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProcessesExaminersTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProcessesExaminersTable Test Case
 */
class ProcessesExaminersTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProcessesExaminersTable
     */
    protected $ProcessesExaminers;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.ProcessesExaminers',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ProcessesExaminers') ? [] : ['className' => ProcessesExaminersTable::class];
        $this->ProcessesExaminers = $this->getTableLocator()->get('ProcessesExaminers', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ProcessesExaminers);

        parent::tearDown();
    }

    // ========================================
    // Validation Tests
    // ========================================

    /**
     * Test validation requires process_id field
     *
     * @return void
     */
    public function testValidationRequiresProcessId(): void
    {
        $entity = $this->ProcessesExaminers->newEntity([
            'user_id' => 1,
            // process_id is missing
        ]);

        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test validation requires user_id field
     *
     * @return void
     */
    public function testValidationRequiresUserId(): void
    {
        $entity = $this->ProcessesExaminers->newEntity([
            'process_id' => 1,
            // user_id is missing
        ]);

        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->ProcessesExaminers->newEntity([
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
        // Delete existing to avoid duplicate key
        $this->ProcessesExaminers->deleteAll([]);

        $entity = $this->ProcessesExaminers->newEntity([
            'process_id' => 99999, // Non-existent process
            'user_id' => 1,
        ]);

        $result = $this->ProcessesExaminers->save($entity);

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
        // Delete existing to avoid duplicate key
        $this->ProcessesExaminers->deleteAll([]);

        $entity = $this->ProcessesExaminers->newEntity([
            'process_id' => 1,
            'user_id' => 99999, // Non-existent user
        ]);

        $result = $this->ProcessesExaminers->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts valid foreign keys
     *
     * @return void
     */
    public function testBuildRulesAcceptsValidForeignKeys(): void
    {
        // Delete existing to avoid duplicate key
        $this->ProcessesExaminers->deleteAll([]);

        $entity = $this->ProcessesExaminers->newEntity([
            'process_id' => 1, // Exists in fixture
            'user_id' => 1,    // Exists in fixture
        ]);

        $result = $this->ProcessesExaminers->save($entity);

        $this->assertNotFalse($result);
    }
}
