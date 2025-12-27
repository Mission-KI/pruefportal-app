<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Criterion Entity
 *
 * @property int $id
 * @property string $title
 * @property int $quality_dimension_id
 * @property int $process_id
 * @property int $value
 * @property int $criterion_type_id
 * @property int $question_id
 * @property int $protection_target_category_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Indicator[] $indicators
 */
class Criterion extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * *  title: TR-Z12 (Wirkt sich das Verhalten oder Ergebnis des KI-Systems substanziell auf das Handeln von natürlichen Personen oder auf persönliche Rechte aus?)
     * *  quality_dimension_id: DA
     * *  question_id: Applikationsfragen|Grundfragen|Erweiterungsfragen
     * *  value: Ja|Nein
     * *  protection_target_category_id: allgemein
     * *  criterion_type_id: Erklärbarkeit & Interpretierbarkeit (Schutzbedarf Kriterium)
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'title' => true,
        'quality_dimension_id' => true,
        'question_id' => true,
        'value' => true,
        'protection_target_category_id' => true,
        'criterion_type_id' => true,
        'process_id' => true,
        'version' => true,
        'phase' => true,
        'created' => true,
        'modified' => true,
        'indicators' => true,
    ];
}
