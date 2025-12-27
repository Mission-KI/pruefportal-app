<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Process;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException;

/**
 * Indicators Model
 *
 * @property \App\Model\Table\ProcessesTable&\Cake\ORM\Association\BelongsTo $Processes
 * @method \App\Model\Entity\Indicator newEmptyEntity()
 * @method \App\Model\Entity\Indicator newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Indicator> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Indicator get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Indicator findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Indicator patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Indicator> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Indicator|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Indicator saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Indicator>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Indicator>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Indicator>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Indicator> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Indicator>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Indicator>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Indicator>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Indicator> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class IndicatorsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('indicators');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Processes', [
            'foreignKey' => 'process_id',
        ]);
        $this->hasMany('Uploads', [
            'foreignKey' => 'indicator_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->integer('level_candidate')
            ->requirePresence('level_candidate', 'create')
            ->notEmptyString('level_candidate');

        $validator
            ->integer('level_examiner')
            ->allowEmptyString('level_examiner');

        $validator
            ->integer('process_id')
            ->allowEmptyString('process_id');

        $validator
            ->integer('quality_dimension_id')
            ->requirePresence('quality_dimension_id', 'create')
            ->notEmptyString('quality_dimension_id');

        $validator
            ->scalar('evidence')
            ->allowEmptyString('evidence');

        $validator
            ->integer('version')
            ->range('version', [0, 2])
            ->allowEmptyString('version');

        $validator
            ->scalar('phase')
            ->maxLength('phase', 50)
            ->inList('phase', ['vcio', 'vcio_complete', 'validation_complete'])
            ->allowEmptyString('phase');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['process_id'], 'Processes'), ['errorField' => 'process_id']);

        return $rules;
    }

    /**
     * Returns the VCIO JSON config.
     *
     * @return array The VCIO config.
     */
    public function getVcioConfig(): array
    {
        // Load the JSON file
        $jsonPath = WWW_ROOT . 'js' . DS . 'json' . DS . 'VCIO.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            if ($jsonContent === false) {
                throw new RuntimeException('VCIO.json not found');
            }

            return json_decode($jsonContent, true);
        }
        throw new RuntimeException('VCIO.json not found');
    }

    /**
     * Returns the quality dimension ids.
     *
     * @param array $vcioConfig The VCIO config.
     * @return array The quality dimension ids.
     */
    public function getQualityDimensionIds(array $vcioConfig): array
    {
        $qualityDimensionIds = array_map('intval', array_column($vcioConfig, 'quality_dimension_id'));
        $qd_ids = array_keys($vcioConfig);

        return array_combine($qualityDimensionIds, $qd_ids);
    }

    /**
     * Returns the criterion type ids for a given dimension.
     * Function to get all unique criterion_type_id values for a given dimension
     *
     * @param array $vcioConfig The VCIO config.
     * @param string $dimension The dimension.
     * @return array The criterion type ids.
     */
    public function getCriterionTypeIds(array $vcioConfig, string $dimension): array
    {
        $criterionTypeIds = [];

        if (isset($vcioConfig[$dimension]['criteria'])) {
            foreach ($vcioConfig[$dimension]['criteria'] as $criterion) {
                if (isset($criterion['criterion_type_id'])) {
                    $criterionTypeIds[] = $criterion['criterion_type_id'];
                }
            }
        }

        // Return unique values
        return array_unique($criterionTypeIds);
    }

    /**
     * Calculate VCIO classification with proper weighting support
     *
     * Aggregates indicator levels by criterion type according to their weighting strategy:
     * - "Maximalwert": Uses maximum level (strictest/worst-case)
     * - "Normal": Uses average level
     *
     * @param iterable $indicators Collection of indicator entities
     * @param array $vcioConfig The VCIO configuration from VCIO.json
     * @param array $protectionLevelsByCriterionType Protection levels indexed by criterion_type_id
     * @param string $levelField The indicator field to use for level values (default: 'level_candidate')
     * @return array ['classification' => [...], 'fulfillment' => [...], 'protectionLevels' => [...]]
     */
    public function calculateVcioClassification(
        iterable $indicators,
        array $vcioConfig,
        array $protectionLevelsByCriterionType,
        string $levelField = 'level_candidate',
    ): array {
        // Build mapping: indicator title -> [criterion_type_id, weighting]
        $indicatorMetadata = [];
        foreach ($vcioConfig as $qd_key => $qualityDimension) {
            foreach ($qualityDimension['criteria'] ?? [] as $criterion) {
                $criterionTypeId = $criterion['criterion_type_id'];

                foreach ($criterion['indicators'] ?? [] as $indicatorKey => $indicatorData) {
                    $indicatorMetadata[$indicatorKey] = [
                        'criterion_type_id' => $criterionTypeId,
                        'weighting' => $indicatorData['weighting'] ?? 'Normal',
                    ];
                }
            }
        }

        // Group indicators by criterion_type_id and collect levels with weighting info
        $criterionData = [];
        foreach ($indicators as $indicator) {
            $indicatorTitle = $indicator->title ?? null;
            $metadata = $indicatorMetadata[$indicatorTitle] ?? null;

            if (!$metadata) {
                continue;
            }

            $criterionTypeId = $metadata['criterion_type_id'];
            $weighting = $metadata['weighting'];
            $levelValue = $indicator->{$levelField} ?? null;

            if ($levelValue === null) {
                continue;
            }

            if (!isset($criterionData[$criterionTypeId])) {
                $criterionData[$criterionTypeId] = [
                    'levels' => [],
                    'weightings' => [],
                ];
            }

            $criterionData[$criterionTypeId]['levels'][] = $levelValue;
            $criterionData[$criterionTypeId]['weightings'][] = $weighting;
        }

        // Calculate aggregated classification based on weighting strategy
        $aggregatedClassification = [];
        foreach ($criterionData as $criterionTypeId => $data) {
            $levels = $data['levels'];
            $weightings = $data['weightings'];

            if (empty($levels)) {
                $aggregatedClassification[$criterionTypeId] = ['value' => 'D', 'numeric' => 0];
                continue;
            }

            // Check if ANY indicator has "Maximalwert" weighting
            $hasMaximalwert = in_array('Maximalwert', $weightings, true);

            if ($hasMaximalwert) {
                // Use maximum level (strictest assessment wins)
                $aggregatedLevel = max($levels);
            } else {
                // Use average for normal weighting
                $aggregatedLevel = array_sum($levels) / count($levels);
                $aggregatedLevel = round($aggregatedLevel);
            }

            // Map numeric level to classification letter
            if ($aggregatedLevel >= 3) {
                $aggregatedClassification[$criterionTypeId] = ['value' => 'A', 'numeric' => 3];
            } elseif ($aggregatedLevel == 2) {
                $aggregatedClassification[$criterionTypeId] = ['value' => 'B', 'numeric' => 2];
            } elseif ($aggregatedLevel == 1) {
                $aggregatedClassification[$criterionTypeId] = ['value' => 'C', 'numeric' => 1];
            } else {
                $aggregatedClassification[$criterionTypeId] = ['value' => 'D', 'numeric' => 0];
            }
        }

        // Map to classification letters and calculate fulfillment
        $classification = [];
        $fulfillment = [];
        $protectionLevels = [];

        foreach ($protectionLevelsByCriterionType as $criterionTypeId => $protectionLevelValue) {
            $classificationData = $aggregatedClassification[$criterionTypeId] ?? ['value' => 'D', 'numeric' => 0];

            $classification[$criterionTypeId] = $classificationData['value'];
            $protectionLevels[$criterionTypeId] = $protectionLevelValue;

            // Calculate fulfillment: classification >= protection level
            if (is_numeric($protectionLevelValue)) {
                $fulfillment[$criterionTypeId] = $classificationData['numeric'] >= $protectionLevelValue ? 'ja' : 'nein';
            } else {
                $fulfillment[$criterionTypeId] = 'N/A';
            }
        }

        return [
            'classification' => $classification,
            'fulfillment' => $fulfillment,
            'protectionLevels' => $protectionLevels,
        ];
    }

    /**
     * Normalize VCIO data for quality_dimensions_table component.
     *
     * @param array $vcioConfig VCIO configuration from JSON
     * @param array $indicators Indicator entities
     * @param array $classification Classification values by criterion type
     * @param array $fulfillment Fulfillment values by criterion type
     * @param array $protectionLevels Protection levels by criterion type
     * @param array $criterionTypes Criterion type ID to name mapping
     * @param \App\Model\Entity\Process $process Process entity for building URLs
     * @param array|null $classificationCandidate Optional candidate classification for dual display
     * @param string $indicatorAction Action for indicator edit URL ('edit' or 'validation')
     * @return array Normalized data for quality_dimensions_table
     */
    public function normalizeForQualityDimensionsTable(
        array $vcioConfig,
        array $indicators,
        array $classification,
        array $fulfillment,
        array $protectionLevels,
        array $criterionTypes,
        Process $process,
        ?array $classificationCandidate = null,
        string $indicatorAction = 'edit',
    ): array {
        $data = [];

        $groupedCriteria = [];
        foreach ($indicators as $indicator) {
            $title = $indicator->title ?? null;
            $criterionIndex = preg_replace('/\.\d+$/', '', $title);
            $qdId = $indicator->quality_dimension_id;
            if (!isset($groupedCriteria[$qdId])) {
                $groupedCriteria[$qdId] = [];
            }
            $groupedCriteria[$qdId][$criterionIndex] = true;
        }

        foreach ($vcioConfig as $qdKey => $qd) {
            if (!is_array($qd) || !isset($qd['quality_dimension_id'])) {
                continue;
            }
            $qdId = $qd['quality_dimension_id'];
            if (!isset($groupedCriteria[$qdId])) {
                continue;
            }

            $criteria = [];
            foreach ($qd['criteria'] ?? [] as $criterion) {
                $index = $criterion['index'];
                $typeId = $criterion['criterion_type_id'];
                if (!isset($groupedCriteria[$qdId][$index])) {
                    continue;
                }

                $criterionData = [
                    'index' => $index,
                    'name' => $criterionTypes[$typeId] ?? $index,
                    'protectionLevel' => $protectionLevels[$typeId] ?? null,
                    'classification' => $classification[$typeId] ?? 'N/A',
                    'fulfillment' => $fulfillment[$typeId] ?? 'N/A',
                ];
                if ($classificationCandidate !== null && isset($classificationCandidate[$typeId])) {
                    $criterionData['classificationCandidate'] = $classificationCandidate[$typeId];
                }
                $criteria[] = $criterionData;
            }

            $data[$qdKey] = [
                'criteria' => $criteria,
                'editUrl' => [
                    'controller' => 'Processes',
                    'action' => 'protection-needs-analysis',
                    $process->id . '-' . $qdKey,
                ],
                'indicatorEditUrl' => [
                    'controller' => 'Indicators',
                    'action' => $indicatorAction,
                    $process->id,
                    $qdKey,
                ],
            ];
        }

        return $data;
    }
}
