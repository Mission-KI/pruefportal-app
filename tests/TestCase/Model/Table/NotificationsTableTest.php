<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Notification;
use App\Model\Table\NotificationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\NotificationsTable Test Case
 */
class NotificationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\NotificationsTable
     */
    protected $Notifications;

    /**
     * Fixtures - ordered by FK dependencies
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Notifications',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Notifications') ? [] : ['className' => NotificationsTable::class];
        $this->Notifications = $this->getTableLocator()->get('Notifications', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Notifications);

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
        $entity = $this->Notifications->newEntity([
            'description' => 'Test description',
            // title is missing
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['title']);
    }

    /**
     * Test validation requires description field
     *
     * @return void
     */
    public function testValidationRequiresDescription(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            // description is missing
        ]);

        $this->assertArrayHasKey('description', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['description']);
    }

    /**
     * Test validation allows empty user_id
     *
     * @return void
     */
    public function testValidationAllowsEmptyUserId(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'user_id' => null,
        ]);

        $this->assertArrayNotHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test validation allows empty process_id
     *
     * @return void
     */
    public function testValidationAllowsEmptyProcessId(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'process_id' => null,
        ]);

        $this->assertArrayNotHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test validation allows empty seen
     *
     * @return void
     */
    public function testValidationAllowsEmptySeen(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'seen' => null,
        ]);

        $this->assertArrayNotHasKey('seen', $entity->getErrors());
    }

    /**
     * Test validation allows empty mailed
     *
     * @return void
     */
    public function testValidationAllowsEmptyMailed(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'mailed' => null,
        ]);

        $this->assertArrayNotHasKey('mailed', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules requires existing user when user_id is provided
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingUser(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'user_id' => 99999, // Non-existent user
            'process_id' => 1,
        ]);

        $result = $this->Notifications->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('user_id', $entity->getErrors());
    }

    /**
     * Test build rules requires existing process when process_id is provided
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingProcess(): void
    {
        $entity = $this->Notifications->newEntity([
            'title' => 'Test Notification',
            'description' => 'Test description',
            'user_id' => 1,
            'process_id' => 99999, // Non-existent process
        ]);

        $result = $this->Notifications->save($entity);

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
        // Delete existing notifications to avoid PK conflicts
        $this->Notifications->deleteAll([]);

        $entity = $this->Notifications->newEntity([
            'title' => 'New Test Notification',
            'description' => 'New test description',
            'user_id' => 1, // Exists in fixture
            'process_id' => 1, // Exists in fixture
        ]);

        $result = $this->Notifications->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }

    // ========================================
    // Business Logic Tests
    // ========================================

    /**
     * Test createNotification creates and saves notification
     *
     * @return void
     */
    public function testCreateNotificationReturnsEntity(): void
    {
        // Delete existing notifications to avoid PK conflicts
        $this->Notifications->deleteAll([]);

        $result = $this->Notifications->createNotification(
            'Test Title',
            'Test Description',
            1, // user_id from fixture
            1,  // process_id from fixture
        );

        $this->assertNotFalse($result);
        $this->assertInstanceOf(Notification::class, $result);
        $this->assertEquals('Test Title', $result->title);
        $this->assertEquals('Test Description', $result->description);
        $this->assertEquals(1, $result->user_id);
        $this->assertEquals(1, $result->process_id);
    }
}
