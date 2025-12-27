<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper\FormHelper;

/**
 * AppForm Helper
 *
 * Extends CakePHP's FormHelper to automatically add mki-form class to all forms
 */
class AppFormHelper extends FormHelper
{
    /**
     * Create form with mki-form class automatically added
     *
     * @param mixed $context The context for the form
     * @param array $options Options for form creation
     * @return string HTML form opening tag
     */
    public function create(mixed $context = null, array $options = []): string
    {
        $existingClass = $options['class'] ?? '';
        $options['class'] = $existingClass ? 'mki-form ' . $existingClass : 'mki-form';

        return parent::create($context, $options);
    }
}
