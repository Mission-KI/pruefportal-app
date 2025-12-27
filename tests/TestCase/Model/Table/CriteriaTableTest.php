<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CriteriaTable;
use App\Service\ProtectionNeedsAnalysisService;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CriteriaTable Test Case
 */
class CriteriaTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CriteriaTable
     */
    protected $Criteria;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Criteria',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Criteria') ? [] : ['className' => CriteriaTable::class];
        $this->Criteria = $this->getTableLocator()->get('Criteria', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Criteria);

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
        $entity = $this->Criteria->newEntity([
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
            // title is missing
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['title']);
    }

    /**
     * Test validation requires quality_dimension_id field
     *
     * @return void
     */
    public function testValidationRequiresQualityDimensionId(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
            // quality_dimension_id is missing
        ]);

        $this->assertArrayHasKey('quality_dimension_id', $entity->getErrors());
    }

    /**
     * Test validation requires value field
     *
     * @return void
     */
    public function testValidationRequiresValue(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
            // value is missing
        ]);

        $this->assertArrayHasKey('value', $entity->getErrors());
    }

    /**
     * Test validation requires criterion_type_id field
     *
     * @return void
     */
    public function testValidationRequiresCriterionTypeId(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
            // criterion_type_id is missing
        ]);

        $this->assertArrayHasKey('criterion_type_id', $entity->getErrors());
    }

    /**
     * Test validation requires question_id field
     *
     * @return void
     */
    public function testValidationRequiresQuestionId(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'protection_target_category_id' => 1,
            // question_id is missing
        ]);

        $this->assertArrayHasKey('question_id', $entity->getErrors());
    }

    /**
     * Test validation requires protection_target_category_id field
     *
     * @return void
     */
    public function testValidationRequiresProtectionTargetCategoryId(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            // protection_target_category_id is missing
        ]);

        $this->assertArrayHasKey('protection_target_category_id', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    /**
     * Test title has max length of 255
     *
     * @return void
     */
    public function testValidationTitleMaxLength(): void
    {
        $entity = $this->Criteria->newEntity([
            'title' => str_repeat('a', 256), // 256 characters, exceeds max
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('maxLength', $entity->getErrors()['title']);
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
        $entity = $this->Criteria->newEntity([
            'title' => 'Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 99999, // Non-existent process
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
        ]);

        $result = $this->Criteria->save($entity);

        $this->assertFalse($result);
        $this->assertArrayHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test build rules accepts existing process
     *
     * @return void
     */
    public function testBuildRulesAcceptsExistingProcess(): void
    {
        // Delete existing fixture data to avoid PK conflicts
        $this->Criteria->deleteAll(['process_id' => 1]);

        $entity = $this->Criteria->newEntity([
            'title' => 'New Test Criterion',
            'quality_dimension_id' => 10,
            'process_id' => 1, // Exists in fixture
            'value' => 1,
            'criterion_type_id' => 1,
            'question_id' => 0,
            'protection_target_category_id' => 1,
        ]);

        $result = $this->Criteria->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }

    // ========================================
    // Business Logic Tests - Config Loading
    // ========================================

    /**
     * Test getQualityDimensionIds returns array with correct mapping
     *
     * @return void
     */
    public function testGetQualityDimensionIdsReturnsArray(): void
    {
        // Create a mock config structure matching production format
        $mockConfig = [
            'CY' => ['quality_dimension_id' => 10],
            'TR' => ['quality_dimension_id' => 20],
        ];

        $result = $this->Criteria->getQualityDimensionIds($mockConfig);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(10, $result);
        $this->assertArrayHasKey(20, $result);
        $this->assertEquals('CY', $result[10]);
        $this->assertEquals('TR', $result[20]);
    }

    /**
     * Test getQualityDimensionIds with null loads default config
     *
     * @return void
     */
    public function testGetQualityDimensionIdsLoadsDefaultConfig(): void
    {
        // When null is passed, method loads config from file
        $result = $this->Criteria->getQualityDimensionIds(null);

        $this->assertIsArray($result);
        // Default config should have at least one quality dimension
        $this->assertNotEmpty($result);
    }

    // ========================================
    // Business Logic Tests - Relevance Checking
    // ========================================

    /**
     * Test checkRelevancesForAP returns array with null for unanswered dimensions
     *
     * The method queries the database for criteria with question_id=0 (AP questions)
     * and returns null for dimensions with no answers.
     *
     * @return void
     */
    public function testCheckRelevancesForAPReturnsNullWhenNotAnswered(): void
    {
        // Quality dimension IDs mapping (id => key)
        // Use a dimension ID that has no criteria in fixtures
        $qualityDimensionIds = [99 => 'TEST'];
        $processId = 1;

        $result = $this->Criteria->checkRelevancesForAP($qualityDimensionIds, $processId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(99, $result);
        $this->assertNull($result[99]);
    }

    /**
     * Test checkRelevancesForAP returns true when AP question has value > 1
     *
     * @return void
     */
    public function testCheckRelevancesForAPReturnsTrueWhenRelevant(): void
    {
        // First, create a criterion with question_id=0 (AP) and value > 1
        $this->Criteria->deleteAll(['process_id' => 1]);
        $entity = $this->Criteria->newEntity([
            'title' => 'AP Question Relevant',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 3, // > 1 means relevant
            'criterion_type_id' => 1,
            'question_id' => 0, // AP question
            'protection_target_category_id' => 1,
        ]);
        $this->Criteria->save($entity);

        $qualityDimensionIds = [10 => 'CY'];
        $processId = 1;

        $result = $this->Criteria->checkRelevancesForAP($qualityDimensionIds, $processId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(10, $result);
        $this->assertTrue($result[10]);
    }

    /**
     * Test checkRelevancesForAP returns false when AP question has value <= 1
     *
     * @return void
     */
    public function testCheckRelevancesForAPReturnsFalseWhenNotRelevant(): void
    {
        // First, create a criterion with question_id=0 (AP) and value <= 1
        $this->Criteria->deleteAll(['process_id' => 1]);
        $entity = $this->Criteria->newEntity([
            'title' => 'AP Question Not Relevant',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 1, // <= 1 means not relevant
            'criterion_type_id' => 1,
            'question_id' => 0, // AP question
            'protection_target_category_id' => 1,
        ]);
        $this->Criteria->save($entity);

        $qualityDimensionIds = [10 => 'CY'];
        $processId = 1;

        $result = $this->Criteria->checkRelevancesForAP($qualityDimensionIds, $processId);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(10, $result);
        $this->assertFalse($result[10]);
    }

    // ========================================
    // Constants Tests - Magic Values Elimination
    // ========================================

    /**
     * Test that question type constants are defined
     *
     * These constants replace magic values for question types:
     * - QUESTION_AP (0) = Applikationsfragen
     * - QUESTION_GF (1) = Grundfragen
     * - QUESTION_EF (2) = Erweiterungsfragen
     *
     * @return void
     */
    public function testQuestionTypeConstantsAreDefined(): void
    {
        // Verify constants exist
        $this->assertTrue(
            defined(CriteriaTable::class . '::QUESTION_AP'),
            'QUESTION_AP constant should be defined',
        );
        $this->assertTrue(
            defined(CriteriaTable::class . '::QUESTION_GF'),
            'QUESTION_GF constant should be defined',
        );
        $this->assertTrue(
            defined(CriteriaTable::class . '::QUESTION_EF'),
            'QUESTION_EF constant should be defined',
        );

        // Verify values
        $this->assertEquals(0, CriteriaTable::QUESTION_AP);
        $this->assertEquals(1, CriteriaTable::QUESTION_GF);
        $this->assertEquals(2, CriteriaTable::QUESTION_EF);
    }

    /**
     * Test that quality dimension constant for VE is defined
     *
     * VE (Verlässlichkeit) has special handling - it has no AP questions.
     *
     * @return void
     */
    public function testQualityDimensionVeConstantIsDefined(): void
    {
        $this->assertTrue(
            defined(CriteriaTable::class . '::QUALITY_DIMENSION_VE'),
            'QUALITY_DIMENSION_VE constant should be defined',
        );

        // VE should have a string identifier
        $this->assertEquals('VE', CriteriaTable::QUALITY_DIMENSION_VE);
    }

    /**
     * Test checkRelevancesForAP uses constants instead of magic values
     *
     * This test verifies the method uses the defined constants.
     * We check by using the constants in our test criteria.
     *
     * @return void
     */
    public function testCheckRelevancesUsesConstants(): void
    {
        $this->Criteria->deleteAll(['process_id' => 1]);

        // Create criterion using the constant
        $entity = $this->Criteria->newEntity([
            'title' => 'Test using constants',
            'quality_dimension_id' => 10,
            'process_id' => 1,
            'value' => 3,
            'criterion_type_id' => 1,
            'question_id' => CriteriaTable::QUESTION_AP,
            'protection_target_category_id' => 1,
        ]);
        $this->Criteria->save($entity);

        $qualityDimensionIds = [10 => 'CY'];
        $result = $this->Criteria->checkRelevancesForAP($qualityDimensionIds, 1);

        $this->assertTrue($result[10]);
    }

    // ========================================
    // Service Extraction Tests
    // ========================================

    /**
     * Test that ProtectionNeedsAnalysisService exists
     *
     * Business logic should be extracted to a service class.
     *
     * @return void
     */
    public function testProtectionNeedsAnalysisServiceExists(): void
    {
        $this->assertTrue(
            class_exists(ProtectionNeedsAnalysisService::class),
            'ProtectionNeedsAnalysisService should exist',
        );
    }

    /**
     * Test ProtectionNeedsAnalysisService has calculateRelevance method
     *
     * @return void
     */
    public function testProtectionNeedsAnalysisServiceHasCalculateRelevance(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        $this->assertTrue(
            method_exists($service, 'calculateRelevance'),
            'Service should have calculateRelevance method',
        );
    }

    /**
     * Test ProtectionNeedsAnalysisService calculateRelevance returns expected structure
     *
     * @return void
     */
    public function testProtectionNeedsAnalysisServiceCalculateRelevanceReturnsArray(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        // Test with empty criteria
        $result = $service->calculateRelevance([]);

        $this->assertIsArray($result);
    }

    /**
     * Test calculateRelevance with AP question value > 1 returns true
     *
     * @return void
     */
    public function testServiceCalculateRelevanceWithRelevantAP(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        $criteria = [
            1 => [
                (object)[
                    'question_id' => CriteriaTable::QUESTION_AP,
                    'value' => 3,
                    'quality_dimension_id' => 10,
                ],
            ],
        ];

        $result = $service->calculateRelevance($criteria);

        $this->assertArrayHasKey(1, $result);
        $this->assertTrue($result[1]);
    }

    /**
     * Test calculateRelevance with GF questions applies max formula
     *
     * @return void
     */
    public function testServiceCalculateRelevanceWithGFQuestions(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        $criteria = [
            1 => [
                (object)[
                    'question_id' => CriteriaTable::QUESTION_GF,
                    'value' => 2,
                ],
                (object)[
                    'question_id' => CriteriaTable::QUESTION_GF,
                    'value' => 3,
                ],
            ],
        ];

        $result = $service->calculateRelevance($criteria);

        $this->assertArrayHasKey(1, $result);
        // max(GF) = 3, no EF so avgEF = 0, therefore result = max(GF) = 3
        $this->assertEquals(3, $result[1]);
    }

    /**
     * Test service checkAPRelevance returns correct values
     *
     * @return void
     */
    public function testServiceCheckAPRelevance(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        // Empty criteria = null (not answered yet)
        $this->assertNull($service->checkAPRelevance([]));

        // All AP <= 1 = false (not relevant)
        $this->assertFalse($service->checkAPRelevance([
            (object)['value' => 1],
        ]));

        // Any AP > 1 = true (relevant)
        $this->assertTrue($service->checkAPRelevance([
            (object)['value' => 3],
        ]));
    }

    /**
     * Test service checkEFRequired returns correct values
     *
     * @return void
     */
    public function testServiceCheckEFRequired(): void
    {
        $service = new ProtectionNeedsAnalysisService();

        // Empty criteria = null (not answered yet)
        $this->assertNull($service->checkEFRequired([]));

        // All GF <= 2 = true (EF required)
        $this->assertTrue($service->checkEFRequired([
            (object)['value' => 2],
        ]));

        // Any GF > 2 = false (EF not required)
        $this->assertFalse($service->checkEFRequired([
            (object)['value' => 3],
        ]));
    }
}
