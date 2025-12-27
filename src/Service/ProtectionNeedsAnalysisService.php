<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\CriteriaTable;

/**
 * Protection Needs Analysis Service
 *
 * This service encapsulates the business logic for calculating protection needs
 * (Schutzbedarf) based on criteria answers. It implements the MISSION KI
 * protection needs analysis algorithm.
 *
 * The calculation follows this logic:
 * 1. AP (Applikationsfragen): If any AP value > 1, the criterion type is relevant
 * 2. GF (Grundfragen): Track max and average values
 * 3. EF (Erweiterungsfragen): Track count and sum for average calculation
 * 4. Protection need = max(GF) if max(GF) >= round(avg(EF))
 * 5. Otherwise protection need = round(avg(EF,GF)) or just round(avg(EF)) if no GF
 */
class ProtectionNeedsAnalysisService
{
    /**
     * Calculate relevances/protection needs for criteria.
     *
     * @param array $criteriaByType Criteria grouped by criterion_type_id.
     *        Each criterion should have: question_id (int), value (int)
     * @return array The relevances/protection needs keyed by criterion_type_id.
     *         Values can be: false (not relevant), true (relevant), or int (protection level)
     */
    public function calculateRelevance(array $criteriaByType): array
    {
        $relevances = [];

        foreach ($criteriaByType as $criterionTypeId => $criteria) {
            $relevances[$criterionTypeId] = $this->calculateForCriterionType($criteria);
        }

        return $relevances;
    }

    /**
     * Calculate relevance/protection need for a single criterion type.
     *
     * @param array $criteria Array of criteria objects with question_id and value properties.
     * @return int|bool False if not relevant, true if relevant (from AP), or int protection level.
     */
    private function calculateForCriterionType(array $criteria): bool|int
    {
        if (empty($criteria)) {
            return false;
        }

        $maxGF = 0;
        $countGF = 0;
        $sumGF = 0;
        $countEF = 0;
        $sumEF = 0;
        $hasRelevantAP = false;

        foreach ($criteria as $criterion) {
            $questionId = $criterion->question_id ?? $criterion['question_id'] ?? null;
            $value = $criterion->value ?? $criterion['value'] ?? 0;

            $isRelevantAPAnswer = $questionId === CriteriaTable::QUESTION_AP
                && $value > CriteriaTable::VALUE_THRESHOLD_RELEVANT;

            if ($isRelevantAPAnswer) {
                $hasRelevantAP = true;
            }

            if ($questionId === CriteriaTable::QUESTION_GF) {
                if ($maxGF < $value) {
                    $maxGF = $value;
                }
                $countGF++;
                $sumGF += (int)$value;
            }

            if ($questionId === CriteriaTable::QUESTION_EF) {
                $countEF++;
                $sumEF += (int)$value;
            }
        }

        $onlyAPAnswersWithRelevantOne = $hasRelevantAP && $countGF === 0 && $countEF === 0;
        if ($onlyAPAnswersWithRelevantOne) {
            return true;
        }

        $avgEF = $countEF > 0 ? round($sumEF / $countEF) : 0;
        $avgGF = $countGF > 0 ? round($sumGF / $countGF) : 0;

        $protectionNeedFromMaxGF = $maxGF >= $avgEF;
        if ($protectionNeedFromMaxGF) {
            return $maxGF;
        }

        $hasGFAnswers = $sumGF > 0;
        if ($hasGFAnswers) {
            $combinedAverage = (int)round(($avgEF + $avgGF) / 2);

            return $combinedAverage;
        }

        return (int)$avgEF;
    }

    /**
     * Check if a quality dimension is relevant based on AP answers.
     *
     * @param array $apCriteria AP criteria for the quality dimension.
     * @return bool|null True if relevant, false if not relevant, null if not yet answered.
     */
    public function checkAPRelevance(array $apCriteria): ?bool
    {
        if (empty($apCriteria)) {
            return null;
        }

        foreach ($apCriteria as $criterion) {
            $value = $criterion->value ?? $criterion['value'] ?? 0;
            if ($value > CriteriaTable::VALUE_THRESHOLD_RELEVANT) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if Erweiterungsfragen (EF) are required based on GF answers.
     *
     * @param array $gfCriteria GF criteria for the quality dimension.
     * @return bool|null True if EF required, false if not required, null if GF not yet answered.
     */
    public function checkEFRequired(array $gfCriteria): ?bool
    {
        if (empty($gfCriteria)) {
            return null;
        }

        foreach ($gfCriteria as $criterion) {
            $value = $criterion->value ?? $criterion['value'] ?? 0;
            $gfEstablishesProtectionNeed = $value > CriteriaTable::VALUE_THRESHOLD_GF_HIGH;
            if ($gfEstablishesProtectionNeed) {
                $efNotRequired = false;

                return $efNotRequired;
            }
        }

        $efRequired = true;

        return $efRequired;
    }
}
