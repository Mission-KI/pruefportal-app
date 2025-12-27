<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\Process;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use RuntimeException;

/**
 * Criteria Model
 *
 * @property \App\Model\Table\IndicatorsTable&\Cake\ORM\Association\HasMany $Indicators
 * @method \App\Model\Entity\Criterion newEmptyEntity()
 * @method \App\Model\Entity\Criterion newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Criterion> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Criterion get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Criterion findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Criterion patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Criterion> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Criterion|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Criterion saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Criterion>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Criterion>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Criterion>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Criterion> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Criterion>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Criterion>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Criterion>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Criterion> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CriteriaTable extends Table
{
    /**
     * Question type: Applikationsfragen (AP)
     *
     * Application questions determine if a quality dimension is relevant for assessment.
     * If any AP question is answered with value > 1 (Ja), the dimension is relevant.
     */
    public const QUESTION_AP = 0;

    /**
     * Question type: Grundfragen (GF)
     *
     * Basic questions that must be answered if the AP indicates relevance.
     * Used to calculate protection needs level.
     */
    public const QUESTION_GF = 1;

    /**
     * Question type: Erweiterungsfragen (EF)
     *
     * Extended questions that may need to be answered depending on GF results.
     * Used in protection needs calculation when GF max < 3.
     */
    public const QUESTION_EF = 2;

    /**
     * Quality dimension: Verlässlichkeit (VE)
     *
     * Special handling: VE has no AP questions, so GF must always be answered.
     */
    public const QUALITY_DIMENSION_VE = 'VE';

    /**
     * Answer value threshold for "Yes" answers
     *
     * Value > 1 indicates a positive/relevant answer (3 = Ja, 1 = Nein)
     */
    public const VALUE_THRESHOLD_RELEVANT = 1;

    /**
     * Answer value threshold for high-level GF answers
     *
     * If any GF >= this value, EF questions are not required.
     */
    public const VALUE_THRESHOLD_GF_HIGH = 2;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('criteria');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Processes', [
            'foreignKey' => 'process_id',
            'joinType' => 'INNER',
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
            ->integer('quality_dimension_id')
            ->requirePresence('quality_dimension_id', 'create')
            ->notEmptyString('quality_dimension_id');

        $validator
            ->integer('process_id')
            ->notEmptyString('process_id');

        $validator
            ->integer('value')
            ->requirePresence('value', 'create')
            ->notEmptyString('value');

        $validator
            ->integer('criterion_type_id')
            ->requirePresence('criterion_type_id', 'create')
            ->notEmptyString('criterion_type_id');

        $validator
            ->integer('question_id')
            ->requirePresence('question_id', 'create')
            ->notEmptyString('question_id');

        $validator
            ->integer('protection_target_category_id')
            ->requirePresence('protection_target_category_id', 'create')
            ->notEmptyString('protection_target_category_id');

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
     * Returns the protection needs analysis JSON configuration.
     *
     * @return array The protection needs analysis configuration.
     */
    public function getProtectionNeedsAnalysisConfig(): array
    {
        // Load the JSON file
        $jsonPath = WWW_ROOT . 'js' . DS . 'json' . DS . 'ProtectionNeedsAnalysis.json';
        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            if ($jsonContent === false) {
                throw new RuntimeException('ProtectionNeedsAnalysis.json not found');
            }

            return json_decode($jsonContent, true);
        }
        throw new RuntimeException('ProtectionNeedsAnalysis.json not found');
    }

    /**
     * Normalize protection needs analysis data for quality_dimensions_table component.
     *
     * @param array $protectionNeedsAnalysis Raw config from JSON
     * @param array $relevances Protection levels by quality_dimension_id and criterion_type_id
     * @param array $criterionTypes Criterion type ID to name mapping
     * @param \App\Model\Entity\Process $process Process entity for building URLs
     * @return array Normalized data for quality_dimensions_table
     */
    public function normalizeForQualityDimensionsTable(
        array $protectionNeedsAnalysis,
        array $relevances,
        array $criterionTypes,
        Process $process,
    ): array {
        $data = [];
        $criterionIndexMap = [
            10 => 'DA1', 11 => 'DA2', 12 => 'DA3',
            20 => 'ND1',
            30 => 'TR1', 31 => 'TR2',
            40 => 'MA1', 41 => 'MA2',
            50 => 'VE1', 51 => 'VE2',
            60 => 'CY1', 61 => 'CY2',
        ];

        foreach ($protectionNeedsAnalysis as $qdKey => $qd) {
            if (!is_array($qd) || !isset($qd['quality_dimension_id'])) {
                continue;
            }
            $qdId = $qd['quality_dimension_id'];
            if (!isset($relevances[$qdId]) || empty($relevances[$qdId])) {
                continue;
            }

            $criteria = [];
            foreach ($relevances[$qdId] as $criterionTypeId => $level) {
                $criteria[] = [
                    'index' => $criterionIndexMap[$criterionTypeId] ?? (string)$criterionTypeId,
                    'name' => $criterionTypes[$criterionTypeId] ?? '',
                    'protectionLevel' => $level,
                    'classification' => null,
                    'fulfillment' => null,
                ];
            }

            $data[$qdKey] = [
                'criteria' => $criteria,
                'editUrl' => [
                    'controller' => 'Criteria',
                    'action' => 'editRateQD',
                    'process_id' => $process->id,
                    'qd_id' => $qdKey,
                    'question_id' => 0,
                ],
                'indicatorEditUrl' => null,
            ];
        }

        return $data;
    }

    /**
     * Returns an array containing the quality dimension ids as keys and
     * the quality dimension ids as values.
     * 10 => 'CY' => __('Qualitätsdimension KI-spezifische Cybersicherheit'),
     * 20 => 'TR' => __('Qualitätsdimension Transparenz'),
     * 30 => 'ND' => __('Qualitätsdimension Nicht-Diskriminierung'),
     * 40 => 'VE' => __('Qualitätsdimension Verlässlichkeit'),
     * 50 => 'DA' => __('Qualitätsdimension Datenqualität, -schutz und -Governance'),
     * 60 => 'MA' => __('Qualitätsdimension Menschliche Aufsicht und Kontrolle'),
     *
     * @param array $protectionNeedsAnalysisConfig The protection needs analysis configuration.
     * @return array The quality dimension ids as array.
     */
    public function getQualityDimensionIds(?array $protectionNeedsAnalysisConfig = null): array
    {
        if (!$protectionNeedsAnalysisConfig) {
            $protectionNeedsAnalysisConfig = $this->getProtectionNeedsAnalysisConfig();
        }
        $qualityDimensionIds = array_map('intval', array_column($protectionNeedsAnalysisConfig, 'quality_dimension_id'));
        $qd_ids = array_keys($protectionNeedsAnalysisConfig);

        return array_combine($qualityDimensionIds, $qd_ids);
    }

    /**
     * Returns an array containing the related questions as keys and
     * the related questions as values.
     *
     * @param array $protectionNeedsAnalysisConfig The protection needs analysis configuration.
     * @return array The related questions as array.
     */
    public function extractRelatedQuestions(?array $protectionNeedsAnalysisConfig = null): array
    {
        $relatedQuestions = [];

        // Loop through each quality dimension (CY, TR, ND, etc.)
        foreach ($protectionNeedsAnalysisConfig as $dimension) {
            if (isset($dimension['questions'])) {
                // Loop through each question group
                foreach ($dimension['questions'] as $questionGroup) {
                    // Loop through each question in the group
                    foreach ($questionGroup as $question) {
                        // Get the first (and only) key in the question array
                        $questionKey = key($question);
                        $questionData = $question[$questionKey];

                        // Check if relatedQuestions exists
                        if (isset($questionData['relatedQuestions'])) {
                            // Add to our result with the question ID as key
                            $relatedQuestions[$questionKey] = $questionData['relatedQuestions'];
                        }
                    }
                }
            }
        }

        return $relatedQuestions;
    }

    /**
     * Find objects with criterion key: 'receivesValueFromCriterion'
     *
     * @param array $protectionNeedsAnalysisConfig The protection needs analysis JSON configuration.
     * @param array $results The results for recursively search
     * @return array The objects with criterion key as array.
     */
    public function findObjectsWithCriterionKey(array $protectionNeedsAnalysisConfig, array &$results = []): array
    {
        foreach ($protectionNeedsAnalysisConfig as $key => $value) {
            if (is_array($value)) {
                if (isset($value['receivesValueFromCriterion'])) {
                    $results[] = $value;
                }
                // Recursively search in nested arrays
                $results = array_merge($results, $this->findObjectsWithCriterionKey($value, $results));
            }
        }

        return array_unique($results, SORT_REGULAR);
    }

    /**
     * Check relevances based on Applikationsfragen (AP) answers.
     *
     * @param array $quality_dimension_ids Mapping of quality dimension ID to key (e.g., 10 => 'CY')
     * @param int $process_id The process ID to check.
     * @return array<int, bool|null> Relevance status per quality dimension (true=relevant, false=not relevant, null=not answered)
     */
    public function checkRelevancesForAP(array $quality_dimension_ids, int $process_id): array
    {
        $relevance = [];
        foreach ($quality_dimension_ids as $quality_dimension_id => $key) {
            if ($key === self::QUALITY_DIMENSION_VE) {
                $relevance[$quality_dimension_id] = true;
                continue;
            }

            $count_quality_dimension_AP = $this->find()
                ->select(['id', 'title', 'value'])
                ->where([
                    'quality_dimension_id' => $quality_dimension_id,
                    'process_id' => $process_id,
                    'question_id' => self::QUESTION_AP,
                ])->count();

            $qualityDimensionNotYetAnswered = null;
            $relevance[$quality_dimension_id] = $qualityDimensionNotYetAnswered;

            if ($count_quality_dimension_AP > 0) {
                $count_quality_dimension_relevant_AP = $this->find()
                    ->select(['id', 'title', 'value'])
                    ->where([
                        'quality_dimension_id' => $quality_dimension_id,
                        'process_id' => $process_id,
                        'question_id' => self::QUESTION_AP,
                        'value >' => self::VALUE_THRESHOLD_RELEVANT,
                    ])->count();
                $isRelevant = $count_quality_dimension_relevant_AP > 0;
                $relevance[$quality_dimension_id] = $isRelevant;
            }
        }

        return $relevance;
    }

    /**
     * Check relevances based on Grundfragen (GF) answers.
     *
     * If any GF is answered with value > VALUE_THRESHOLD_GF_HIGH, the protection need
     * is established and Erweiterungsfragen (EF) are not required.
     *
     * @param array $quality_dimension_ids Mapping of quality dimension ID to key
     * @param int|null $process_id The process ID to check.
     * @return array<int, bool|null> Whether EF are required (true=required, false=not required, null=not answered)
     */
    public function checkRelevancesForGF(array $quality_dimension_ids, ?int $process_id = null): array
    {
        $relevance = [];
        foreach ($quality_dimension_ids as $quality_dimension_id => $key) {
            $count_quality_dimension_GF = $this->find()
                ->select(['id', 'title', 'value'])
                ->where([
                    'quality_dimension_id' => $quality_dimension_id,
                    'process_id' => $process_id,
                    'question_id' => self::QUESTION_GF,
                ])->count();

            $gfNotYetAnswered = null;
            $relevance[$quality_dimension_id] = $gfNotYetAnswered;

            if ($count_quality_dimension_GF > 0) {
                $count_quality_dimension_relevant_GF = $this->find()
                    ->select(['id', 'title', 'value'])
                    ->where([
                        'quality_dimension_id' => $quality_dimension_id,
                        'process_id' => $process_id,
                        'question_id' => self::QUESTION_GF,
                        'value >' => self::VALUE_THRESHOLD_GF_HIGH,
                    ])->count();
                $efQuestionsRequired = $count_quality_dimension_relevant_GF === 0;
                $relevance[$quality_dimension_id] = $efQuestionsRequired;
            }
        }

        return $relevance;
    }

    /**
     * Calculate relevance for a single criterion type.
     *
     * @var int $process_id The process id.
     * @var int $criterion_type_id The criterion type id.
     * @return array The relevances as array.
     */
    public function calculateRelevanceByCriterionTypeId($process_id, $criterion_type_id): array
    {
        $all_criteria = $this->find('all')
            ->where(['process_id' => $process_id, 'criterion_type_id' => $criterion_type_id])
            ->orderByAsc('quality_dimension_id')
            ->toArray();

        $all_relevances = $this->calculateRelevance([$criterion_type_id => $all_criteria]);

        return $all_relevances;
    }

    /**
     * Calculate relevances for all criteria.
     *
     * @var int $process_id The process id.
     * @var array $criterion_types The criterion types.
     * @return array The relevances as array.
     */
    public function calculateRelevances($process_id, $criterion_types): array
    {
        $all_criteria = [];
        foreach ($criterion_types as $criterion_type_id => $criterion_type) {
            $all_criteria[$criterion_type_id] = $this->find('all')
                ->where(['process_id' => $process_id, 'criterion_type_id' => $criterion_type_id])
                ->orderByAsc('quality_dimension_id') /*10 CY, 20 TR, 30 ND, 40 VE, 50 DA, 60 MA*/
                ->toArray();
        }

        $all_relevances = $this->calculateRelevance($all_criteria);

        $qualityDimensionsCriterionTypes = $this->find('all')
            ->where(['process_id' => $process_id])
            ->select(['quality_dimension_id', 'criterion_type_id'])
            ->distinct()
            ->toArray();

        $relevances = [];
        foreach ($qualityDimensionsCriterionTypes as $criterion) {
            $qd_id = $criterion->quality_dimension_id;
            $ct_id = $criterion->criterion_type_id;
            if (!array_key_exists($qd_id, $relevances)) {
                $relevances[$qd_id] = [];
            }
            $relevances[$qd_id][$ct_id] = $all_relevances[$ct_id] ?? false;
        }

        return $relevances;
    }

    /**
     * Calculate relevances/protection needs for all criteria.
     *
     * Algorithm:
     * - If any AP question value > VALUE_THRESHOLD_RELEVANT, the criterion type is relevant
     * - Protection need = max(GF) if max(GF) >= round(avg(EF))
     * - Otherwise protection need = round(avg(EF,GF)) or just round(avg(EF)) if no GF
     *
     * @param array $all_criteria The criteria grouped by criterion_type_id.
     * @return array The relevances/protection needs as array keyed by criterion_type_id.
     */
    private function calculateRelevance(array $all_criteria): array
    {
        $all_relevances = [];
        foreach ($all_criteria as $criterion_type_id => $criteria) :
            $all_relevances[$criterion_type_id] = false;
            $maxGF = 0;
            $countGF = 0;
            $sumGF = 0;
            $countEF = 0;
            $sumEF = 0;

            if (count($criteria) > 0) :
                foreach ($criteria as $criterion) :
                    $isRelevantAPAnswer = $criterion->question_id === self::QUESTION_AP
                        && $criterion->value > self::VALUE_THRESHOLD_RELEVANT;

                    if ($isRelevantAPAnswer) {
                        $all_relevances[$criterion_type_id] = true;
                    } else {
                        if ($criterion->question_id === self::QUESTION_GF) {
                            if ($maxGF < $criterion->value) {
                                $maxGF = $criterion->value;
                            }
                            $countGF++;
                            $sumGF += (int)$criterion->value;
                        }
                        if ($criterion->question_id === self::QUESTION_EF) {
                            $countEF++;
                            $sumEF += (int)$criterion->value;
                        }
                    }
                endforeach;

                $avgEF = $countEF > 0 ? round($sumEF / $countEF) : 0;
                $avgGF = $countGF > 0 ? round($sumGF / $countGF) : 0;

                $protectionNeedFromMaxGF = $maxGF >= $avgEF;

                if ($protectionNeedFromMaxGF) {
                    $all_relevances[$criterion_type_id] = $maxGF;
                } elseif ($sumGF > 0) {
                    $combinedAverage = round(($avgEF + $avgGF) / 2);
                    $all_relevances[$criterion_type_id] = $combinedAverage;
                } else {
                    $all_relevances[$criterion_type_id] = $avgEF;
                }
            endif;
        endforeach;

        return $all_relevances;
    }

    /**
     * Calculate the overall risk level for a process based on PNA results.
     *
     * @param int $process_id The process id.
     * @return string|null Returns 'high', 'moderate', 'low', or null if PNA incomplete
     */
    public function calculateOverallRiskLevel(int $process_id): ?string
    {
        $criterionTypes = [
            10 => __('Schutzbedarf Kriterium: Datenqualität'),
            11 => __('Schutzbedarf Kriterium: Schutz personenbezogener Daten'),
            12 => __('Schutzbedarf Kriterium: Schutz proprietärer Daten'),
            20 => __('Schutzbedarf Kriterium: Vermeidung von ungerechtfertigter Diskriminierung und Verzerrung'),
            30 => __('Schutzbedarf Kriterium: Rückverfolgbarkeit & Dokumentation'),
            31 => __('Schutzbedarf Kriterium: Erklärbarkeit & Interpretierbarkeit'),
            40 => __('Schutzbedarf Kriterium: Menschliche Handlungsfähigkeit'),
            41 => __('Schutzbedarf Kriterium: Menschliche Aufsicht'),
            50 => __('Schutzbedarf Kriterium: Leistungsfähigkeit und Robustheit'),
            51 => __('Schutzbedarf Kriterium: Rückfallpläne und funktionale Sicherheit'),
            60 => __('Schutzbedarf Kriterium: Allgemeine KI-spezifische Cybersicherheit'),
            61 => __('Schutzbedarf Kriterium: Widerstandsfähigkeit gegen KI-spezifische Angriffe'),
        ];

        $relevances = $this->calculateRelevances($process_id, $criterionTypes);

        $protectionLevels = [];
        foreach ($relevances as $qd_id => $criterionTypesData) {
            foreach ($criterionTypesData as $ct_id => $level) {
                if (is_numeric($level)) {
                    $protectionLevels[] = (int)$level;
                }
            }
        }

        if (empty($protectionLevels)) {
            return null;
        }

        // Aggregation method - change here for different strategies (max, avg, etc.)
        $overallLevel = max($protectionLevels);

        if ($overallLevel >= 3) {
            return 'high';
        }
        if ($overallLevel >= 2) {
            return 'moderate';
        }

        return 'low';
    }
}
