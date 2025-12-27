<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * UI Demo Controller
 *
 * Provides demonstration pages for UI components and atomic design elements.
 * Used for development and testing purposes.
 */
class UiDemoController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index', 'formDemo', 'formOriginal', 'criteriaDemo', 'modalDemo']);
    }

    /**
     * UI Demo Index - Component catalog and demo links
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        // Add flash message examples for demonstration
        if ($this->request->getQuery('flash') === 'all') {
            $this->Flash->success(__('The Criteria has been saved.'));
            $this->Flash->error(__('The User could not be saved. Please, try again.'));
            $this->Flash->warning(__('This action may have unintended consequences.'));
            $this->Flash->info(__('Your session will expire in 10 minutes.'));
        }

        $this->set('title', 'UI Component Demo Catalog');
    }

    /**
     * Form Renderer Demo - Side-by-side comparison of Handlebars vs server-side rendering
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function formDemo()
    {
        $this->set('title', 'Form Renderer Demo - Handlebars vs Server-side');
    }

    /**
     * Original Form Implementation - Preserved for comparison
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function formOriginal()
    {
        $this->set('title', 'Original Form Implementation (Handlebars)');
    }

    /**
     * Criteria Demo - Protection Needs Analysis server-side rendering demo
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function criteriaDemo()
    {
        $criterion = $this->fetchTable('Criteria')->newEmptyEntity();

        $process = (object)[
            'id' => 999,
            'title' => 'Demo Protection Needs Analysis',
            'status_id' => 20,
            'examiners' => [
                (object)['id' => 1, 'full_name' => 'Demo Examiner'],
            ],
        ];

        $quality_dimension = 'MA';
        $question_id = (int)($this->request->getQuery('question_id') ?? 0);

        $qualityDimensionQuestions = [
            'MA' => __('Qualitätsdimension Menschliche Aufsicht und Kontrolle'),
            'VE' => __('Qualitätsdimension Verlässlichkeit'),
            'TR' => __('Qualitätsdimension Transparenz'),
        ];

        $icons = [
            'MA' => 'user-group',
            'VE' => 'shield-check',
            'TR' => 'transparency',
        ];

        $questionTypes = [
            __('Applikationsfragen'),
            __('Grundfragen'),
            __('Erweiterungsfragen'),
        ];

        // Load the JSON configuration
        $configPath = WWW_ROOT . 'js' . DS . 'json' . DS . 'ProtectionNeedsAnalysis_' . $quality_dimension . '.json';
        $jsonContent = file_get_contents($configPath);
        $questionsData = json_decode($jsonContent, true);

        // Get questions for the current step (question_id)
        $currentQuestions = $questionsData[$question_id] ?? [];

        $this->set(compact(
            'criterion',
            'process',
            'quality_dimension',
            'question_id',
            'qualityDimensionQuestions',
            'icons',
            'questionTypes',
            'currentQuestions',
        ));
    }

    /**
     * Modal Demo - Interactive demonstrations of Alpine.js modal component
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function modalDemo()
    {
        $this->set('title', 'Modal Component Demo - Alpine.js + Tailwind 4');
    }
}
