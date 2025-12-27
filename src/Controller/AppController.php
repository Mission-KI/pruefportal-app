<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use App\Model\Enum\Role;
use App\Service\Storage\LocalAdapter;
use App\Service\Storage\S3Adapter;
use App\Service\UploadService;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\I18n\I18n;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    public string $currentLanguage = 'de';

    public array $languages;

    public array $statuses;

    public array $protectionTargetCategories;

    public array $questionTypes;

    public array $criterionTypes;

    public array $observables;

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        $this->loadComponent('Authentication.Authentication');

        // Prevent non-admins from accessing the admin area
        if ($this->request->getParam('prefix') === 'Admin') {
            $this->viewBuilder()->setLayout('admin');
        }
        if ($this->request->getParam('prefix') === 'Admin' && $this->Authentication->getIdentity() && $this->Authentication->getIdentity()->role === Role::User) {
            throw new ForbiddenException();
        }
        if ($this->request->getParam('prefix') === null && $this->Authentication->getIdentity() && $this->Authentication->getIdentity()->role === Role::Admin) {
            $this->redirect(['controller' => 'Users', 'action' => 'display', 'prefix' => 'Admin']);
        }
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->languages = ['de' => 'DE', 'en' => 'EN'];
        $this->set('availableLanguages', $this->languages);

        $this->statuses = Configure::read('statuses') ?? [];
        $this->set('statuses', $this->statuses);

        // <-- TODO refactor this constants to env
        $this->protectionTargetCategories = [
            __('allgemein'),
            __('Leib und Leben'),
            __('Gesundheit'),
            __('Grundrechte'),
            __('Schutz personenbezogener Daten'),
            __('Eigentum und Sachen'),
            __('Nichtdiskriminierung'),
            __('Menschenwürde'),
            __('Umwelt'),
            __('AF'),
        ];
        $this->set('protectionTargetCategories', $this->protectionTargetCategories);

        $this->questionTypes = [
            0 => __('Applikationsfragen'),
            1 => __('Grundfragen'),
            2 => __('Erweiterungsfragen'),
        ];
        $this->set('questionTypes', $this->questionTypes);

        $this->criterionTypes = [
            10 => __('Schutzbedarf Kriterium: Datenqualität'),
            11 => __('Schutzbedarf Kriterium: Schutz personenbezogener Daten'),
            12 => __('Schutzbedarf Kriterium: Schutz proprietärer Daten'),
            20 => __('Schutzbedarf Kriterium: Vermeidung von ungerechtfertigter Diskriminierung und Verzerrung'),
            30 => __('Schutzbedarf Kriterium: Rückverfolgbarkeit & Dokumentation'),
            31 => __('Schutzbedarf Kriterium: Erklärbarkeit & Interpretierbarkeit'),
            40 => __('Schutzbedarf Kriterium: Menschliche Handlungsfähigkeit'),
            41 => __('Schutzbedarf Kriterium: Menschliche Aufsicht'),
            50 => __('Schutzbedarf Kriterium: Leistungsfähigkeit und Robustheit'),
            51 => __('Schutzbedarf Kriterium: Rückfallpläne und funktionale Sicherheit'),
            60 => __('Schutzbedarf Kriterium: Allgemeine KI-spezifische Cybersicherheit'),
            61 => __('Schutzbedarf Kriterium: Widerstandsfähigkeit gegen KI-spezifische Angriffe'),
        ];
        $this->set('criterionTypes', $this->criterionTypes);

        $this->observables = [
            3 => 'A',
            2 => 'B',
            1 => 'C',
            0 => 'D',
        ];
        $this->set('observables', $this->observables);
        // --->


        $this->setLanguage();
    }

    /**
     * Get the DI container from the request.
     *
     * @return \Cake\Core\ContainerInterface|null
     */
    protected function getContainer(): ?ContainerInterface
    {
        return $this->request->getAttribute('container');
    }

    /**
     * Create an UploadService instance with the appropriate storage adapter.
     *
     * @return \App\Service\UploadService
     */
    protected function createUploadService(): UploadService
    {
        $driver = env('STORAGE_DRIVER', 'local');
        $adapter = match ($driver) {
            'local' => new LocalAdapter(),
            's3' => new S3Adapter(),
            default => new LocalAdapter(),
        };

        return new UploadService($adapter);
    }

    /**
     * @return void
     */
    private function setLanguage()
    {
        $session = $this->request->getSession();
        if ($session->check('Config.language') && array_key_exists($session->read('Config.language'), $this->languages)) {
            $this->currentLanguage = $session->read('Config.language');
        }

        if (isset($this->currentLanguage) && !array_key_exists($this->currentLanguage, $this->languages)) {
            $this->currentLanguage = substr(I18n::getLocale(), 0, 2);
            $session->write('Config.language', $this->currentLanguage);
        }

        $this->set('currentLanguage', $this->currentLanguage);
        setlocale(LC_ALL, $this->currentLanguage);
        setlocale(LC_MESSAGES, $this->currentLanguage);
        I18n::setLocale($this->currentLanguage);
    }
}
