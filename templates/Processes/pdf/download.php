<?php
/**
 * PDF Download Template for Process Total Result
 *
 * Multi-page PDF report:
 * - Page 1: Header with metadata, purpose, tasks, and disclaimer
 * - Pages 2-7: One page per quality dimension with assessment card and criteria table
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Process $process
 * @var array $qualityDimensionsConfig
 * @var array $indicatorsSummary
 * @var array $criteria
 * @var array $ucd
 * @var array $qualityDimensionsData
 * @var array $qualityDimensionsSummary
 */

use App\Model\Enum\QualityDimension;

$applicationPurpose = array_key_exists('UC_1-2', $ucd) ? $ucd['UC_1-2'] : '';
$applicationTasks = array_key_exists('UC_1-3', $ucd) ? $ucd['UC_1-3'] : '';

$ratingLabels = ['D', 'C', 'B', 'A'];
$protectionLabels = ['N/A', 'Niedrig', 'Moderat', 'Hoch'];
$qualityDimensionCodes = ['DA', 'ND', 'TR', 'MA', 'VE', 'CY'];
?>
<html>
<head>
    <style>
        @page {
            margin: 0cm 0cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin-top: 1.8cm;
            margin-left: 1cm;
            margin-right: 1cm;
            margin-bottom: 1.8cm;
            font-size: 9px;
            line-height: 1.3;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            background-color: #3C0483;
            color: white;
            text-align: center;
            line-height: 0.8cm;
            font-size: smaller;
        }

        header a {
            color: white;
            text-decoration: none;
        }

        footer {
            font-size: smaller;
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 1.5cm;
            background-color: #3C0483;
            color: white;
            text-align: center;
            line-height: 0.8cm;
        }

        .page-break {
            page-break-before: always;
        }

        .overall-assessment {
            background-color: #3C0483;
            color: white;
            border-radius: 6px;
            padding: 15px;
        }

        .logo {
            margin-bottom: 12px;
        }

        .logo img {
            height: 30px;
        }

        .main-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            color: white;
        }

        .metadata-section {
            border-top: 2px solid #D4BBFF;
            border-bottom: 2px solid #D4BBFF;
            padding: 10px 0;
            margin-bottom: 15px;
        }

        .metadata-table {
            width: 100%;
            border-collapse: collapse;
        }

        .metadata-table td {
            vertical-align: top;
            padding: 3px 8px;
        }

        .metadata-label {
            color: #FDE047;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 2px;
        }

        .metadata-value {
            color: white;
            font-size: 9px;
        }

        .content-section {
            margin-bottom: 15px;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .content-table td {
            vertical-align: top;
            padding: 0 10px 0 0;
            width: 50%;
        }

        .content-table td:last-child {
            padding-right: 0;
        }

        .section-title {
            color: #FDE047;
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .section-text {
            color: white;
            line-height: 1.4;
        }

        .footer-section {
            border-top: 2px solid #D4BBFF;
            padding-top: 10px;
            margin-top: 12px;
        }

        .process-id {
            text-align: center;
            font-size: 9px;
            color: white;
        }

        .disclaimer {
            background-color: #F3F4F6;
            border-radius: 6px;
            padding: 10px;
            margin-top: 12px;
            color: #374151;
            font-size: 8px;
            line-height: 1.4;
        }

        .disclaimer-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #111827;
        }

        .qd-page {
            padding-top: 10px;
        }

        .assessment-card {
            background-color: #3C0483;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            color: white;
        }

        .card-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .card-icon {
            width: 40px;
            vertical-align: middle;
            padding-right: 10px;
        }

        .card-icon svg,
        .card-icon img {
            width: 24px;
            height: 24px;
        }

        .icon-badge {
            display: inline-block;
            width: 28px;
            height: 28px;
            line-height: 28px;
            text-align: center;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 10px;
            font-weight: bold;
        }

        .card-title {
            color: white;
            font-size: 14px;
            font-weight: bold;
            vertical-align: middle;
        }

        .card-status {
            width: 30px;
            text-align: right;
            vertical-align: middle;
        }

        .status-passed {
            color: #4ADE80;
            font-size: 18px;
        }

        .status-failed {
            color: rgba(255, 255, 255, 0.4);
            font-size: 18px;
        }

        .rating-section {
            margin-bottom: 10px;
        }

        .rating-label {
            color: white;
            font-size: 9px;
            text-align: center;
            margin-bottom: 4px;
        }

        .rating-bar-container {
            border: 1px solid #D4BBFF;
            border-radius: 4px;
            height: 14px;
            padding: 2px;
            margin-bottom: 3px;
        }

        .rating-bar-fill {
            background-color: #D4BBFF;
            height: 100%;
            border-radius: 3px;
        }

        .rating-labels-table {
            width: 100%;
            border-collapse: collapse;
        }

        .rating-labels-table td {
            color: rgba(255, 255, 255, 0.6);
            font-size: 8px;
            text-align: center;
            padding: 0;
        }

        .rating-labels-table td.active {
            color: white;
            font-weight: bold;
            text-decoration: underline;
        }

        .not-rated-text {
            color: rgba(255, 255, 255, 0.8);
            font-style: italic;
            font-size: 10px;
            padding: 10px 0;
            text-align: center;
        }

        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
        }

        .criteria-table th {
            background-color: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            padding: 8px 10px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            color: #6B7280;
            text-transform: uppercase;
        }

        .criteria-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E5E7EB;
            font-size: 9px;
            color: #374151;
            vertical-align: middle;
        }

        .criteria-table tr:last-child td {
            border-bottom: none;
        }

        .criterion-index {
            background-color: #E5E7EB;
            color: #374151;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .protection-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .protection-low {
            background-color: #D1FAE5;
            color: #065F46;
        }

        .protection-moderate {
            background-color: #FEF3C7;
            color: #92400E;
        }

        .protection-high {
            background-color: #FEE2E2;
            color: #991B1B;
        }

        .protection-na {
            background-color: #F3F4F6;
            color: #6B7280;
        }

        .classification-badge {
            display: inline-block;
            min-width: 14px;
            height: 16px;
            line-height: 16px;
            text-align: center;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            margin-right: 3px;
            vertical-align: middle;
            padding: 0 3px;
        }

        .classification-outline {
            border: 1px solid #6B7280;
            background-color: transparent;
            color: #6B7280;
            height: 14px;
            line-height: 14px;
        }

        .classification-a {
            background-color: #065F46;
            color: white;
        }

        .classification-b {
            background-color: #0D9488;
            color: white;
        }

        .classification-c {
            background-color: #F59E0B;
            color: white;
        }

        .classification-d {
            background-color: #DC2626;
            color: white;
        }

        .classification-na {
            background-color: #9CA3AF;
            color: white;
        }

        .fulfillment-yes {
            color: #065F46;
            font-weight: bold;
        }

        .fulfillment-no {
            color: #DC2626;
            font-weight: bold;
        }

        .fulfillment-na {
            color: #6B7280;
        }

        .ratings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ratings-table td {
            width: 50%;
            padding: 0 5px;
            vertical-align: top;
        }

        /* Front Page Styles */
        .front-page {
            text-align: center;
            padding-top: 35%;
        }

        .front-logo {
            height: 80px;
            margin-bottom: 40px;
        }

        .front-title {
            font-size: 28px;
            font-weight: bold;
            color: #3C0483;
            margin-bottom: 20px;
        }

        .front-subtitle {
            font-size: 18px;
            color: #374151;
            margin-bottom: 30px;
        }

        .front-date {
            font-size: 12px;
            color: #6B7280;
        }

        /* Page Title */
        .page-title {
            font-size: 18px;
            font-weight: bold;
            color: #3C0483;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3C0483;
        }

        /* Core Data Styles */
        .core-data-section {
            margin-bottom: 15px;
        }

        .core-data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .core-data-table tr {
            border-bottom: 1px solid #E5E7EB;
        }

        .core-data-table tr:last-child {
            border-bottom: none;
        }

        .core-data-label {
            width: 140px;
            padding: 6px 10px 6px 0;
            font-weight: bold;
            color: #374151;
            font-size: 9px;
            vertical-align: top;
        }

        .core-data-value {
            padding: 6px 0;
            color: #111827;
            font-size: 9px;
            vertical-align: top;
        }

        /* Table Legend */
        .table-legend {
            margin-top: 8px;
            padding: 6px 10px;
            background-color: #F9FAFB;
            border-radius: 4px;
            font-size: 8px;
            color: #6B7280;
        }

        .legend-table {
            border-collapse: collapse;
        }

        .legend-table td {
            padding: 2px 5px;
            vertical-align: middle;
        }

        .legend-table td:first-child {
            padding-left: 0;
            width: 20px;
        }
    </style>
</head>
<body>
<header>
    <a href="<?= $this->Url->build('/', ['fullBase' => true]); ?>" target="_blank">
        MISSION KI - AI MADE IN GERMANY
    </a>
</header>
<footer>
    Copyright &copy; <?php echo date("Y"); ?> Prüf-ID: <?= h($process->id) ?>
</footer>

<!-- Front Page -->
<div class="front-page">
    <?= $this->Html->image('pruefportal_logo2_compact.svg', [
        'alt' => 'MISSION KI Prüfportal',
        'class' => 'front-logo',
        'fullBase' => true
    ]) ?>
    <div class="front-title"><?= __('Prüfbericht') ?></div>
    <div class="front-subtitle"><?= h($process->title) ?></div>
    <div class="front-date"><?= $process->modified->format('d.m.Y') ?></div>
</div>

<!-- Overview Page -->
<div class="page-break">
    <h1 class="page-title"><?= __('Übersicht') ?></h1>

    <!-- Core Data Section -->
    <div class="core-data-section">
        <table class="core-data-table">
            <tr>
                <td class="core-data-label"><?= __('Projekt') ?>:</td>
                <td class="core-data-value"><?= h($process->project->title ?? '-') ?></td>
            </tr>
            <tr>
                <td class="core-data-label"><?= __('Prüfgegenstand') ?>:</td>
                <td class="core-data-value"><?= h($process->title) ?></td>
            </tr>
            <?php if (!empty($process->description)): ?>
            <tr>
                <td class="core-data-label"><?= __('Beschreibung') ?>:</td>
                <td class="core-data-value"><?= h($process->description) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td class="core-data-label"><?= __('Kandidat') ?>:</td>
                <td class="core-data-value"><?= h($process->candidate->full_name ?? '-') ?></td>
            </tr>
            <tr>
                <td class="core-data-label"><?= __('Prüfer') ?>:</td>
                <td class="core-data-value">
                    <?php if (!empty($process->examiners)): ?>
                        <?= h(implode(', ', array_map(fn($e) => $e->full_name, $process->examiners))) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td class="core-data-label"><?= __('Datum der Prüfung') ?>:</td>
                <td class="core-data-value"><?= $process->modified->format('d.m.Y') ?></td>
            </tr>
            <tr>
                <td class="core-data-label"><?= __('Prüf-ID') ?>:</td>
                <td class="core-data-value"><?= h($process->id) ?></td>
            </tr>
            <?php if ($process->has('project') && $process->project->has('user')): ?>
            <tr>
                <td class="core-data-label"><?= __('Kontakt') ?>:</td>
                <td class="core-data-value">
                    <?= h($process->project->user->full_name) ?>
                    <?php if (!empty($process->project->user->username)): ?>
                        (<?= h($process->project->user->username) ?>)
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Disclaimer -->
    <div class="disclaimer">
        <div class="disclaimer-title"><?= __('Haftungsausschluss') ?></div>
        <?= __('Die angebotene Selbstprüfung dient ausschließlich der freiwilligen internen Bewertung durch das teilnehmende Unternehmen. Sie stellt keine behördliche Prüfung, Zertifizierung oder rechtsverbindliche Bewertung dar. acatech übernimmt keine Gewähr für Vollständigkeit, Richtigkeit oder rechtliche Wirkung der Ergebnisse dieser Selbstprüfung. Die Nutzung erfolgt auf eigenes Risiko und in eigener Verantwortung des teilnehmenden Unternehmens. acatech haftet nur bei Vorsatz oder grober Fahrlässigkeit sowie in Fällen gesetzlicher Haftung.') ?>
    </div>

    <!-- Purple Summary Box -->
    <div class="overall-assessment">
        <div class="logo">
            <?= $this->Html->image('pruefportal_logo2_compact.svg', [
                'alt' => 'MISSION KI Prüfportal Beta',
                'style' => 'height: 40px; filter: brightness(0) invert(1);',
                'fullBase' => true
            ]) ?>
        </div>

        <div class="main-title">
            <?= __('Bewertung der KI-Anwendung') ?> "<?= h($process->title) ?>"
        </div>

        <div class="content-section">
            <table class="content-table">
                <tr>
                    <td>
                        <div class="section-title"><?= __('Zweck der Anwendung') ?></div>
                        <div class="section-text"><?= nl2br(h($applicationPurpose)) ?></div>
                    </td>
                    <td>
                        <div class="section-title"><?= __('Aufgaben der KI') ?></div>
                        <div class="section-text"><?= nl2br(h($applicationTasks)) ?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<?php
$iconBasePath = WWW_ROOT . 'icons' . DS;

foreach ($qualityDimensionCodes as $code):
    $qd = QualityDimension::tryFrom($code);
    if (!$qd) continue;

    $dimension = $qualityDimensionsConfig[$code] ?? null;
    if (!$dimension) continue;

    $summary = $qualityDimensionsSummary[$code] ?? [];
    $protectionNeeds = $summary['protectionNeeds'] ?? 0;
    $rating = $summary['rating'] ?? 0;
    $notRated = $summary['notRated'] ?? true;
    $passed = $summary['passed'] ?? false;
    $ratingPercentage = $summary['ratingPercentage'] ?? 0;
    $protectionPercentage = $summary['protectionPercentage'] ?? 0;

    $qdCriteria = $qualityDimensionsData[$code]['criteria'] ?? [];

    $iconPath = $iconBasePath . $qd->icon() . '.svg';
    $iconSvg = '<span style="color:white;font-weight:bold;">' . h($code) . '</span>';
    if (file_exists($iconPath)) {
        $svgContent = file_get_contents($iconPath);
        $svgContent = preg_replace('/fill="#3[Cc]0483"/', 'fill="#FFFFFF"', $svgContent);
        $svgContent = preg_replace("/fill='#3[Cc]0483'/", "fill='#FFFFFF'", $svgContent);
        $svgContent = preg_replace('/stroke="#3[Cc]0483"/', 'stroke="#FFFFFF"', $svgContent);
        $svgContent = preg_replace("/stroke='#3[Cc]0483'/", "stroke='#FFFFFF'", $svgContent);
        $svgContent = preg_replace('/<svg([^>]*) fill="none"/', '<svg$1', $svgContent);
        $svgContent = preg_replace('/fill-rule="[^"]*"/', '', $svgContent);
        $svgContent = preg_replace('/clip-rule="[^"]*"/', '', $svgContent);
        $svgContent = preg_replace('/width="[^"]*"/', 'width="24"', $svgContent);
        $svgContent = preg_replace('/height="[^"]*"/', 'height="24"', $svgContent);
        $base64 = base64_encode($svgContent);
        $iconSvg = '<img src="data:image/svg+xml;base64,' . $base64 . '" width="24" height="24" alt="' . h($code) . '">';
    }
?>

<div class="page-break qd-page">
    <div class="assessment-card">
        <table class="card-header-table">
            <tr>
                <td class="card-icon">
                    <?= $iconSvg ?>
                </td>
                <td class="card-title">
                    <?= h($dimension['title']) ?> (<?= h($code) ?>)
                </td>
                <td class="card-status">
                    <?php if ($notRated): ?>
                        <span class="status-failed">&#10007;</span>
                    <?php elseif ($passed): ?>
                        <span class="status-passed">&#10003;</span>
                    <?php else: ?>
                        <span class="status-failed">&#10007;</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php if ($notRated): ?>
            <div class="not-rated-text"><?= __('Nicht bewertet') ?></div>
        <?php else: ?>
            <table class="ratings-table">
                <tr>
                    <td>
                        <div class="rating-section">
                            <div class="rating-label"><?= __('Bewertung') ?></div>
                            <div class="rating-bar-container">
                                <div class="rating-bar-fill" style="width: <?= $ratingPercentage ?>%;"></div>
                            </div>
                            <table class="rating-labels-table">
                                <tr>
                                    <?php foreach ($ratingLabels as $index => $label): ?>
                                        <td class="<?= ($rating == $index) ? 'active' : '' ?>"><?= $label ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td>
                        <div class="rating-section">
                            <div class="rating-label"><?= __('Schutzbedarf') ?></div>
                            <div class="rating-bar-container">
                                <div class="rating-bar-fill" style="width: <?= $protectionPercentage ?>%;"></div>
                            </div>
                            <table class="rating-labels-table">
                                <tr>
                                    <?php foreach ($protectionLabels as $index => $label): ?>
                                        <td class="<?= ($protectionNeeds == $index) ? 'active' : '' ?>"><?= $label ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <?php if (!empty($qdCriteria)): ?>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th style="width: 60px;"><?= __('Kriterium') ?></th>
                    <th><?= __('Name') ?></th>
                    <th style="width: 80px;"><?= __('Schutzbedarf') ?></th>
                    <th style="width: 100px;"><?= __('Einstufung') ?></th>
                    <th style="width: 60px;"><?= __('Erfüllt?') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($qdCriteria as $criterion): ?>
                    <?php
                    $protLevel = $criterion['protectionLevel'] ?? null;
                    $protClass = 'protection-na';
                    $protLabel = 'N/A';
                    if ($protLevel === 1) {
                        $protClass = 'protection-low';
                        $protLabel = 'Niedrig';
                    } elseif ($protLevel === 2) {
                        $protClass = 'protection-moderate';
                        $protLabel = 'Moderat';
                    } elseif ($protLevel >= 3) {
                        $protClass = 'protection-high';
                        $protLabel = 'Hoch';
                    }

                    $classValue = $criterion['classification'] ?? 'N/A';
                    $classClass = 'classification-na';
                    if ($classValue === 'A') $classClass = 'classification-a';
                    elseif ($classValue === 'B') $classClass = 'classification-b';
                    elseif ($classValue === 'C') $classClass = 'classification-c';
                    elseif ($classValue === 'D') $classClass = 'classification-d';

                    $classCandidateValue = $criterion['classificationCandidate'] ?? null;
                    $classCandidateClass = 'classification-outline';

                    $fulfillment = $criterion['fulfillment'] ?? 'N/A';
                    $fulfillClass = 'fulfillment-na';
                    $fulfillLabel = 'N/A';
                    if ($fulfillment === 'ja' || $fulfillment === true || $fulfillment === 1) {
                        $fulfillClass = 'fulfillment-yes';
                        $fulfillLabel = 'Ja';
                    } elseif ($fulfillment === 'nein' || $fulfillment === false || $fulfillment === 0) {
                        $fulfillClass = 'fulfillment-no';
                        $fulfillLabel = 'Nein';
                    }
                    ?>
                    <tr>
                        <td><span class="criterion-index"><?= h($criterion['index']) ?></span></td>
                        <td><?= h($criterion['name']) ?></td>
                        <td><span class="protection-badge <?= $protClass ?>"><?= $protLabel ?></span></td>
                        <td>
                            <?php if ($classCandidateValue): ?>
                                <span class="classification-badge <?= $classCandidateClass ?>"><?= h($classCandidateValue) ?></span>
                            <?php endif; ?>
                            <span class="classification-badge <?= $classClass ?>"><?= h($classValue) ?></span>
                        </td>
                        <td><span class="<?= $fulfillClass ?>"><?= $fulfillLabel ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="table-legend">
            <table class="legend-table">
                <tr>
                    <td><span class="classification-badge classification-outline">A</span></td>
                    <td>= <?= __('Selbsteinschätzung') ?></td>
                    <td style="width: 30px;"></td>
                    <td><span class="classification-badge classification-a">A</span></td>
                    <td>= <?= __('Validierung durch Prüfer') ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php endforeach; ?>

</body>
</html>
