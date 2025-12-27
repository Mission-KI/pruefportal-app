<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ProcessesTable;
use Cake\ORM\ResultSet;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ProcessesTable Test Case
 */
class ProcessesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ProcessesTable
     */
    protected $Processes;

    /**
     * Fixtures - ordered by foreign key dependencies
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
        $config = $this->getTableLocator()->exists('Processes') ? [] : ['className' => ProcessesTable::class];
        $this->Processes = $this->getTableLocator()->get('Processes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Processes);

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
        $entity = $this->Processes->newEntity([
            'project_id' => 1,
            'status_id' => 1,
            // title is missing
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['title']);
    }

    /**
     * Test validation rejects empty project_id (when provided as empty string)
     *
     * Note: project_id uses notEmptyString but not requirePresence,
     * so the validation only fires when the value is present and empty.
     *
     * @return void
     */
    public function testValidationRejectsEmptyProjectId(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'status_id' => 1,
            'project_id' => '', // Empty string should fail
        ]);

        $this->assertArrayHasKey('project_id', $entity->getErrors());
    }

    /**
     * Test validation requires status_id field
     *
     * @return void
     */
    public function testValidationRequiresStatusId(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            // status_id is missing
        ]);

        $this->assertArrayHasKey('status_id', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['status_id']);
    }

    /**
     * Test validation allows empty candidate_user
     *
     * @return void
     */
    public function testValidationAllowsEmptyCandidateUser(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            'status_id' => 1,
            'candidate_user' => null,
        ]);

        $this->assertArrayNotHasKey('candidate_user', $entity->getErrors());
    }

    /**
     * Test validation allows empty examiners (many-to-many relationship)
     *
     * @return void
     */
    public function testValidationAllowsEmptyExaminers(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            'status_id' => 1,
            'examiners' => [],
        ]);

        $this->assertArrayNotHasKey('examiners', $entity->getErrors());
    }

    /**
     * Test validation rejects same user as candidate and examiner
     * Note: Examiners are now a many-to-many relationship
     *
     * @return void
     */
    public function testValidationExaminerCannotBeSameAsCandidate(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            'status_id' => 1,
            'candidate_user' => 1,
            'examiners' => [['id' => 1]], // Same as candidate - should fail
        ]);

        $this->assertArrayHasKey('examiners', $entity->getErrors());
        $this->assertArrayHasKey('different_from_candidate', $entity->getErrors()['examiners']);
    }

    /**
     * Test validation accepts different users for candidate and examiner
     * Note: Examiners are now a many-to-many relationship
     *
     * @return void
     */
    public function testValidationAcceptsDifferentCandidateAndExaminer(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            'status_id' => 1,
            'candidate_user' => 1,
            'examiners' => [['id' => 2]], // Different from candidate - should pass
        ]);

        $this->assertArrayNotHasKey('examiners', $entity->getErrors());
    }

    /**
     * Test title has max length of 255
     *
     * @return void
     */
    public function testValidationTitleMaxLength(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => str_repeat('a', 256), // 256 characters, exceeds max
            'project_id' => 1,
            'status_id' => 1,
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
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 1,
            'status_id' => 1,
            'description' => 'A test description.',
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules requires existing project
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingProject(): void
    {
        $entity = $this->Processes->newEntity([
            'title' => 'Test Process',
            'project_id' => 99999, // Non-existent project
            'status_id' => 1,
        ]);

        $result = $this->Processes->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('project_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts existing project
     *
     * @return void
     */
    public function testBuildRulesAcceptsExistingProject(): void
    {
        // Delete existing processes to avoid conflicts
        $this->Processes->deleteAll(['project_id' => 1]);

        $entity = $this->Processes->newEntity([
            'title' => 'New Test Process',
            'project_id' => 1, // Exists in fixture
            'status_id' => 1,
        ]);

        $result = $this->Processes->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }

    // ========================================
    // Custom Finder Tests
    // ========================================

    /**
     * Test findCandidate returns query with correct conditions
     *
     * @return void
     */
    public function testFindCandidateFiltersByCandidateUser(): void
    {
        $query = $this->Processes->find('candidate', candidate: 1);

        // The query should filter by candidate_user
        $sql = $query->sql();
        $this->assertStringContainsString('candidate_user', $sql);

        // Execute and verify it returns results or empty (depending on fixture data)
        $results = $query->all();
        $this->assertInstanceOf(ResultSet::class, $results);
    }

    /**
     * Test findExaminer returns query with correct conditions
     * Note: Examiners are now a many-to-many relationship through ProcessesExaminers junction table
     *
     * @return void
     */
    public function testFindExaminerFiltersByExaminerUser(): void
    {
        $query = $this->Processes->find('examiner', examiner: 2);

        // The query should join with Examiners through the junction table
        $sql = $query->sql();
        $this->assertStringContainsString('Examiners', $sql);
        $this->assertStringContainsString('ProcessesExaminers', $sql);

        // Execute and verify it returns results
        $results = $query->all();
        $this->assertInstanceOf(ResultSet::class, $results);
    }

    /**
     * Test findParticipants returns correct fields
     * Note: Examiners are now contained via many-to-many relationship
     *
     * @return void
     */
    public function testFindParticipantsReturnsCorrectFields(): void
    {
        $query = $this->Processes->find('participants', process: 1);

        // Verify query selects the expected fields
        $sql = $query->sql();
        $this->assertStringContainsString('Processes', $sql);
        $this->assertStringContainsString('candidate_user', $sql);
        // Examiners are now loaded via contain(), not as a direct field
        $this->assertStringContainsString('Candidates', $sql);

        // Execute and verify results
        $results = $query->all();
        $this->assertInstanceOf(ResultSet::class, $results);
    }
}
