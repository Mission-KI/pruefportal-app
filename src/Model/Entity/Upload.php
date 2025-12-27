<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Upload Entity
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $name
 * @property int|null $size
 * @property string|null $location
 * @property string|null $etag
 * @property int|null $process_id
 * @property int|null $comment_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property int|null $indicator_id
 *
 * @property \App\Model\Entity\Process $process
 * @property \App\Model\Entity\Comment $comment
 * @property \App\Model\Entity\Indicator $indicator
 */
class Upload extends Entity
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
        'key' => true,
        'name' => true,
        'size' => true,
        'location' => true,
        'etag' => true,
        'process_id' => true,
        'comment_id' => true,
        'indicator_id' => true,
        'created' => true,
        'modified' => true,
        'process' => true,
        'comment' => true,
        'file_url' => true,
    ];
}
