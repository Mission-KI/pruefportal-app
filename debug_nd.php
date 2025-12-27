#!/usr/bin/env php
<?php
/**
 * Debug script to check ND criteria data
 * Run from command line: php debug_nd.php
 */

require dirname(__FILE__) . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Database\Connection;
use Cake\Datasource\ConnectionManager;

// Bootstrap CakePHP
require dirname(__FILE__) . '/config/bootstrap.php';

// Get the most recent process
$connection = ConnectionManager::get('default');

echo "\n=== Checking ND Dimension Data ===\n\n";

// Get latest process
$process = $connection->execute(
    'SELECT id, title, status_id FROM processes ORDER BY modified DESC LIMIT 1'
)->fetch('assoc');

if (!$process) {
    echo "No process found!\n";
    exit(1);
}

echo "Process: #{$process['id']} - {$process['title']}\n";
echo "Status: {$process['status_id']}\n\n";

// Get ND criteria (quality_dimension_id = 30)
$criteria = $connection->execute(
    'SELECT id, quality_dimension_id, criterion_type_id, question_id, title, value
     FROM criteria
     WHERE process_id = ? AND quality_dimension_id = 30
     ORDER BY question_id, title',
    [$process['id']]
)->fetchAll('assoc');

if (empty($criteria)) {
    echo "❌ NO ND CRITERIA FOUND IN DATABASE!\n";
    echo "This means the data wasn't saved. Try submitting the form again.\n\n";
    exit(1);
}

echo "Found " . count($criteria) . " ND criteria records:\n\n";

$byQuestionType = ['AP' => [], 'GF' => [], 'EF' => []];
$questionTypes = [0 => 'AP', 1 => 'GF', 2 => 'EF'];

foreach ($criteria as $c) {
    $type = $questionTypes[$c['question_id']] ?? 'Unknown';
    $byQuestionType[$type][] = $c;
    echo sprintf(
        "%s | criterion_type=%d | question_id=%d | title=%s | value=%d\n",
        $type,
        $c['criterion_type_id'],
        $c['question_id'],
        $c['title'],
        $c['value']
    );
}

echo "\n=== Calculation ===\n\n";

// Group by criterion_type_id (should be 20 for all ND questions)
$byCriterionType = [];
foreach ($criteria as $c) {
    $ct = $c['criterion_type_id'];
    if (!isset($byCriterionType[$ct])) {
        $byCriterionType[$ct] = ['AP' => [], 'GF' => [], 'EF' => []];
    }
    $type = $questionTypes[$c['question_id']];
    $byCriterionType[$ct][$type][] = $c['value'];
}

foreach ($byCriterionType as $ct => $data) {
    echo "Criterion Type $ct:\n";

    // Check AP relevance
    $hasRelevantAP = false;
    foreach ($data['AP'] as $val) {
        if ($val > 1) {
            $hasRelevantAP = true;
            break;
        }
    }

    if (!$hasRelevantAP && !empty($data['AP'])) {
        echo "  ❌ All AP = 1 (Nein) → Criterion NOT RELEVANT → N/A\n\n";
        continue;
    }

    if (!$hasRelevantAP && empty($data['AP'])) {
        echo "  ⚠️  No AP questions found\n";
    } else {
        echo "  ✓ AP relevance = true (at least one Ja)\n";
    }

    // Calculate GF
    $maxGF = 0;
    $sumGF = 0;
    $countGF = count($data['GF']);
    foreach ($data['GF'] as $val) {
        $maxGF = max($maxGF, $val);
        $sumGF += $val;
    }
    $avgGF = $countGF > 0 ? round($sumGF / $countGF) : 0;

    echo "  GF: count=$countGF, sum=$sumGF, max=$maxGF, avg=$avgGF\n";

    // Calculate EF
    $sumEF = 0;
    $countEF = count($data['EF']);
    foreach ($data['EF'] as $val) {
        $sumEF += $val;
    }
    $avgEF = $countEF > 0 ? round($sumEF / $countEF) : 0;

    echo "  EF: count=$countEF, sum=$sumEF, avg=$avgEF\n";

    // Calculate final score
    if ($maxGF >= $avgEF) {
        $score = $maxGF;
        echo "  → maxGF ($maxGF) >= avgEF ($avgEF) → Score = $score\n";
    } else {
        if ($sumGF > 0) {
            $score = round(($avgEF + $avgGF) / 2);
            echo "  → maxGF ($maxGF) < avgEF ($avgEF) AND sumGF > 0 → Score = round(($avgEF + $avgGF) / 2) = $score\n";
        } else {
            $score = $avgEF;
            echo "  → No GF data → Score = avgEF = $score\n";
        }
    }

    // Map to protection level
    $levels = [0 => 'N/A (score=0)', 1 => 'gering', 2 => 'moderat', 3 => 'hoch'];
    $level = $levels[$score] ?? 'N/A (invalid score)';
    echo "  → Protection Level: $level\n\n";
}

echo "Done!\n\n";
