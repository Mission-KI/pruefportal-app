<?php
declare(strict_types=1);

namespace App\Form;

use Cake\Form\Form;
use Cake\Form\Schema;
use Cake\Mailer\MailerAwareTrait;
use Cake\Validation\Validator;

class ResetPasswordForm extends Form
{
    use MailerAwareTrait;

    /**
     * @param \Cake\Form\Schema $schema
     * @return \Cake\Form\Schema
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema->addField('reset_email', 'string');
    }

    /**
     * @param \Cake\Validation\Validator $validator
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->notEmptyString('reset_email')
            ->requirePresence('reset_email')
            ->email('reset_email');

        return $validator;
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function _execute(array $data): bool
    {
        $this->getMailer('User')->send('resetPassword', [$data]);

        return true;
    }
}
