<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Indicator Entity
 *
 * @property int $id
 * @property string $title
 * @property int $level_candidate
 * @property int $level_examiner
 * @property int $version
 * @property string $phase
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property int|null $process_id
 * @property int|null $quality_dimension_id
 * @property string|null $evidence
 *
 * @property \App\Model\Entity\Process $process
 */
class Indicator extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'title' => true,
        'level_candidate' => true,
        'level_examiner' => true,
        'version' => true,
        'phase' => true,
        'created' => true,
        'modified' => true,
        'process_id' => true,
        'quality_dimension_id' => true,
        'evidence' => true,
        'process' => true,
    ];
}
