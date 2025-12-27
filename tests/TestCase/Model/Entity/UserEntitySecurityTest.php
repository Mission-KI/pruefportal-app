<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Entity;

use App\Model\Entity\User;
use App\Model\Enum\Role;
use App\Model\Enum\Salutation;
use Cake\TestSuite\TestCase;

/**
 * Security tests for User Entity
 *
 * These tests verify that sensitive fields are properly protected
 * against exposure and mass assignment attacks.
 */
class UserEntitySecurityTest extends TestCase
{
    /**
     * Test that sensitive fields are hidden from JSON serialization
     *
     * @return void
     */
    public function testSensitiveFieldsAreHiddenInJson(): void
    {
        $user = new User([
            'id' => 1,
            'username' => 'test@example.com',
            'password' => 'hashed_password_value',
            'key' => 'secret_invitation_token_12345',
            'full_name' => 'Test User',
            'company' => 'Test Company',
            'role' => Role::User,
            'salutation' => Salutation::Mr,
            'enabled' => true,
        ]);

        $json = json_encode($user);
        $decoded = json_decode($json, true);

        // Password should NOT be in JSON output
        $this->assertArrayNotHasKey(
            'password',
            $decoded,
            'Password field should be hidden from JSON serialization',
        );

        // Key (invitation token) should NOT be in JSON output
        $this->assertArrayNotHasKey(
            'key',
            $decoded,
            'Key field (invitation token) should be hidden from JSON serialization',
        );

        // Safe fields should still be present
        $this->assertArrayHasKey('username', $decoded);
        $this->assertArrayHasKey('full_name', $decoded);
        $this->assertArrayHasKey('company', $decoded);
    }

    /**
     * Test that sensitive fields are hidden from array conversion
     *
     * @return void
     */
    public function testSensitiveFieldsAreHiddenInArray(): void
    {
        $user = new User([
            'id' => 1,
            'username' => 'test@example.com',
            'password' => 'hashed_password_value',
            'key' => 'secret_invitation_token_12345',
            'full_name' => 'Test User',
            'company' => 'Test Company',
        ]);

        $array = $user->toArray();

        // Password should NOT be in array output
        $this->assertArrayNotHasKey(
            'password',
            $array,
            'Password field should be hidden from array conversion',
        );

        // Key (invitation token) should NOT be in array output
        $this->assertArrayNotHasKey(
            'key',
            $array,
            'Key field (invitation token) should be hidden from array conversion',
        );
    }

    /**
     * Test that role field cannot be mass-assigned via patchEntity
     *
     * This prevents privilege escalation attacks where a user could
     * set their own role to 'admin' through form submissions.
     *
     * Note: Direct Entity construction bypasses $_accessible checks.
     * The real protection happens via Table::newEntity() and Table::patchEntity().
     *
     * @return void
     */
    public function testRoleCannotBeMassAssigned(): void
    {
        // Create an existing user with 'user' role
        $user = new User([
            'id' => 1,
            'username' => 'user@example.com',
            'full_name' => 'Normal User',
            'company' => 'Good Company',
            'role' => Role::User,
        ]);

        // Now try to mass-assign a different role using patch() with guard
        // This simulates what patchEntity does when processing form data
        $user->patch(['role' => Role::Admin], ['guard' => true]);

        // The role should still be 'user' because 'role' is not accessible
        $this->assertEquals(
            Role::User,
            $user->role,
            'Role field should not be changeable via guarded mass assignment',
        );
    }

    /**
     * Test that role can still be set explicitly (for admin operations)
     *
     * @return void
     */
    public function testRoleCanBeSetExplicitly(): void
    {
        $user = new User();

        // Admin explicitly sets the role (not via mass assignment)
        $user->role = Role::Admin;

        $this->assertEquals(
            Role::Admin,
            $user->role,
            'Role should still be settable via explicit assignment',
        );
    }

    /**
     * Test that other safe fields can still be mass-assigned
     *
     * @return void
     */
    public function testSafeFieldsCanBeMassAssigned(): void
    {
        $user = new User([
            'username' => 'user@example.com',
            'full_name' => 'Safe User',
            'company' => 'Good Company',
            'salutation' => Salutation::Ms,
        ]);

        $this->assertEquals('user@example.com', $user->username);
        $this->assertEquals('Safe User', $user->full_name);
        $this->assertEquals('Good Company', $user->company);
        $this->assertEquals(Salutation::Ms, $user->salutation);
    }

    /**
     * Test the hidden fields list directly
     *
     * @return void
     */
    public function testHiddenFieldsList(): void
    {
        $user = new User();
        $hidden = $user->getHidden();

        $this->assertContains(
            'password',
            $hidden,
            'Password should be in hidden fields list',
        );

        $this->assertContains(
            'key',
            $hidden,
            'Key (invitation token) should be in hidden fields list',
        );
    }

    /**
     * Test the accessible fields list for role
     *
     * @return void
     */
    public function testRoleIsNotAccessible(): void
    {
        $user = new User();

        $this->assertFalse(
            $user->isAccessible('role'),
            'Role field should not be accessible for mass assignment',
        );
    }
}
