<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * UsecaseDescription Entity
 *
 * @property int $id
 * @property int $step
 * @property int $version
 * @property string|null $description
 * @property int $process_id
 * @property int $user_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Process $process
 * @property \App\Model\Entity\User $user
 */
class UsecaseDescription extends Entity
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
        'step' => true,
        'version' => true,
        'description' => true,
        'process_id' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'process' => true,
        'user' => true,
    ];

    /**
     * Parse the description JSON field in a backwards-compatible way.
     *
     * Handles two formats:
     * - Old format (comma-separated JSON objects): {"step1":"data"},{"step2":"data"}
     * - New format (single merged JSON object): {"step1":"data","step2":"data"}
     *
     * @return array Associative array of all UCD fields merged together
     */
    public function getParsedDescription(): array
    {
        if (empty($this->description)) {
            return [];
        }

        // Try parsing as new format (single JSON object)
        $decoded = json_decode($this->description, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Empty object is valid new format
            if (empty($decoded)) {
                return [];
            }

            // Check if it's an associative array (new format) vs indexed array
            if (array_keys($decoded) !== range(0, count($decoded) - 1)) {
                return $decoded; // New format: single object
            }
        }

        // Fall back to old format (comma-separated objects)
        $oldFormat = '[' . $this->description . ']';
        $chunks = json_decode($oldFormat, true) ?: [];

        // Merge all chunks into single object
        $merged = [];
        foreach ($chunks as $chunk) {
            if (is_array($chunk)) {
                $merged = array_merge($merged, $chunk);
            }
        }

        return $merged;
    }
}
