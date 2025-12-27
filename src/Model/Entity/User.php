<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\Enum\Salutation;
use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property bool|null $enabled
 * @property string $username
 * @property string $password
 * @property string|null $role
 * @property string|null $key
 * @property string|null $salutation
 * @property string $full_name
 * @property string $company
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 * @property bool|null $process_updates
 * @property bool|null $comment_notifications
 *
 * @property \App\Model\Entity\Comment[] $comments
 * @property \App\Model\Entity\Notification[] $notifications
 * @property \App\Model\Entity\Project[] $projects
 * @property \App\Model\Entity\Tag[] $tags
 *
 * Security Note - `key` field storage:
 * The `key` field stores invitation/password-reset tokens as plain text (not hashed).
 * This is acceptable because:
 * 1. Tokens are cryptographically secure (generated via Security::hash(Security::randomBytes(25)))
 * 2. Tokens are single-use and invalidated after user activation
 * 3. Tokens are excluded from JSON serialization via $_hidden
 * 4. Hashing would require sending unhashed token via email, negating security benefit
 * 5. Database compromise risk is mitigated by token's short validity period
 */
class User extends Entity
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
        'enabled' => true,
        'username' => true,
        'password' => true,
        'role' => false, // Prevent privilege escalation via mass assignment
        'key' => true,
        'salutation' => true,
        'full_name' => true,
        'company' => true,
        'created' => true,
        'modified' => true,
        'process_updates' => true,
        'comment_notifications' => true,
        'comments' => true,
        'notifications' => true,
        'projects' => true,
        'tags' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var list<string>
     */
    protected array $_hidden = [
        'password',
        'key',
    ];

    /**
     * Automatically hash passwords when they are changed.
     *
     * @param string $password
     * @return string|null
     */
    protected function _setPassword(string $password): ?string
    {
        if (strlen($password) > 0) {
            return (new DefaultPasswordHasher())->hash($password);
        }

        return null;
    }

    /**
     * Returns the full name with salutation for this user.
     *
     * @return string
     */
    protected function _getSalutationName(): string
    {
        $salutation = '';
        if ($this->salutation !== null && $this->salutation !== Salutation::Diverse) {
            $salutation = $this->salutation->label() . ' ';
        }

        return $salutation . $this->full_name;
    }
}
