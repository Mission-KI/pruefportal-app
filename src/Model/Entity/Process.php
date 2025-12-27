<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Traversable;

/**
 * Process Entity
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property int $project_id
 * @property int $status_id
 * @property int|null $candidate_user
 * @property \App\Model\Entity\User[] $examiners
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Project $project
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Criterion[] $criteria
 * @property \App\Model\Entity\Notification[] $notifications
 * @property \App\Model\Entity\Indicator[] $indicators
 * @property \App\Model\Entity\UsecaseDescription[] $usecase_descriptions
 */
class Process extends Entity
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
        'description' => true,
        'project_id' => true,
        'status_id' => true,
        'candidate_user' => true,
        'examiners' => true,
        'created' => true,
        'modified' => true,
        'project' => true,
        'comments' => true,
        'criteria' => true,
        'notifications' => true,
        'usecase_descriptions' => true,
    ];

    /**
     * Check if a given user ID is an examiner on this process
     *
     * Note: This method requires the 'examiners' association to be loaded.
     * If not loaded, it will return false.
     *
     * @param int $userId User ID to check
     * @return bool True if user is an examiner, false otherwise
     */
    public function isUserExaminer(int $userId): bool
    {
        if (!is_array($this->examiners) && !($this->examiners instanceof Traversable)) {
            return false;
        }

        if (empty($this->examiners)) {
            return false;
        }

        foreach ($this->examiners as $examiner) {
            if ($examiner->id === $userId) {
                return true;
            }
        }

        return false;
    }
}
