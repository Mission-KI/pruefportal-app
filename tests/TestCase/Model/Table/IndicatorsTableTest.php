<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\IndicatorsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\IndicatorsTable Test Case
 */
class IndicatorsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\IndicatorsTable
     */
    protected $Indicators;

    /**
     * Fixtures - ordered by FK dependencies
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Projects',
        'app.Processes',
        'app.Indicators',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Indicators') ? [] : ['className' => IndicatorsTable::class];
        $this->Indicators = $this->getTableLocator()->get('Indicators', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Indicators);

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
        $entity = $this->Indicators->newEntity([
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
            // title is missing
        ]);

        $this->assertArrayHasKey('title', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['title']);
    }

    /**
     * Test validation requires level_candidate field
     *
     * @return void
     */
    public function testValidationRequiresLevelCandidate(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'quality_dimension_id' => 10,
            // level_candidate is missing
        ]);

        $this->assertArrayHasKey('level_candidate', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['level_candidate']);
    }

    /**
     * Test validation requires quality_dimension_id field
     *
     * @return void
     */
    public function testValidationRequiresQualityDimensionId(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            // quality_dimension_id is missing
        ]);

        $this->assertArrayHasKey('quality_dimension_id', $entity->getErrors());
        $this->assertArrayHasKey('_required', $entity->getErrors()['quality_dimension_id']);
    }

    /**
     * Test validation allows empty process_id
     *
     * @return void
     */
    public function testValidationAllowsEmptyProcessId(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
            'process_id' => null,
        ]);

        $this->assertArrayNotHasKey('process_id', $entity->getErrors());
    }

    /**
     * Test validation allows empty level_examiner
     *
     * @return void
     */
    public function testValidationAllowsEmptyLevelExaminer(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
            'level_examiner' => null,
        ]);

        $this->assertArrayNotHasKey('level_examiner', $entity->getErrors());
    }

    /**
     * Test validation allows empty evidence
     *
     * @return void
     */
    public function testValidationAllowsEmptyEvidence(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
            'evidence' => null,
        ]);

        $this->assertArrayNotHasKey('evidence', $entity->getErrors());
    }

    /**
     * Test validation accepts valid entity
     *
     * @return void
     */
    public function testValidationAcceptsValidEntity(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
        ]);

        $this->assertEmpty($entity->getErrors());
    }

    // ========================================
    // Build Rules Tests
    // ========================================

    /**
     * Test build rules requires existing process when process_id is provided
     *
     * @return void
     */
    public function testBuildRulesRequiresExistingProcess(): void
    {
        $entity = $this->Indicators->newEntity([
            'title' => 'Test Indicator',
            'level_candidate' => 2,
            'quality_dimension_id' => 10,
            'process_id' => 99999, // Non-existent process
        ]);

        $result = $this->Indicators->save($entity);

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
        // Delete existing indicators to avoid PK conflicts
        $this->Indicators->deleteAll([]);

        $entity = $this->Indicators->newEntity([
            'title' => 'New Test Indicator',
            'level_candidate' => 3,
            'quality_dimension_id' => 10,
            'process_id' => 1, // Exists in fixture
        ]);

        $result = $this->Indicators->save($entity);

        $this->assertNotFalse($result);
        $this->assertNotNull($result->id);
    }

    // ========================================
    // Business Logic Tests - Config Methods
    // ========================================

    /**
     * Test getQualityDimensionIds returns correct mapping
     *
     * @return void
     */
    public function testGetQualityDimensionIdsReturnsCorrectMapping(): void
    {
        $mockConfig = [
            'CY' => ['quality_dimension_id' => 10],
            'TR' => ['quality_dimension_id' => 20],
        ];

        $result = $this->Indicators->getQualityDimensionIds($mockConfig);

        $this->assertIsArray($result);
        $this->assertArrayHasKey(10, $result);
        $this->assertArrayHasKey(20, $result);
        $this->assertEquals('CY', $result[10]);
        $this->assertEquals('TR', $result[20]);
    }

    /**
     * Test getCriterionTypeIds returns unique criterion type IDs for dimension
     *
     * @return void
     */
    public function testGetCriterionTypeIdsReturnsUniqueValues(): void
    {
        $mockConfig = [
            'CY' => [
                'quality_dimension_id' => 10,
                'criteria' => [
                    ['criterion_type_id' => 1],
                    ['criterion_type_id' => 2],
                    ['criterion_type_id' => 1], // Duplicate
                ],
            ],
        ];

        $result = $this->Indicators->getCriterionTypeIds($mockConfig, 'CY');

        $this->assertIsArray($result);
        $this->assertCount(2, $result); // Should be unique
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
    }
}
