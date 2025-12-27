<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Comment Entity
 *
 * @property int $id
 * @property string|null $content
 * @property string $reference_id
 * @property int $user_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property bool|null $seen
 * @property int $process_id
 * @property int|null $parent_id
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Process $process
 * @property \App\Model\Entity\Comment[] $child_comments
 */
class Comment extends Entity
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
        'content' => true,
        'reference_id' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'seen' => true,
        'process_id' => true,
        'parent_id' => true,
        'user' => true,
        'process' => true,
        'child_comments' => true,
    ];
}
