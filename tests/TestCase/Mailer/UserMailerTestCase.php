<?php
declare(strict_types=1);

namespace App\Test\TestCase\Mailer;

use App\Mailer\UserMailer;
use App\Model\Entity\User;
use Cake\TestSuite\EmailTrait;
use Cake\TestSuite\TestCase;

class UserMailerTestCase extends TestCase
{
    use EmailTrait;

    public function setUp(): void
    {
        parent::setUp();
        $this->loadRoutes();
    }

    public function testName()
    {
        $user = new User([
            'name' => 'Alice Alittea',
            'email' => 'alice@example.org',
        ]);
        $mailer = new UserMailer();
        $mailer->send('welcome', [$user]);

        $this->assertMailSentTo($user->email);
        $this->assertMailContainsText('Hi ' . $user->name);
        $this->assertMailContainsText('Welcome to CakePHP!');
    }
}
