<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\Icon;
use App\Utility\StringHelper;
use Cake\Core\Configure;
use Cake\Datasource\FactoryLocator;
use Cake\Http\ServerRequest;
use Cake\View\Helper;
use Exception;

class LayoutHelper extends Helper
{
    public array $helpers = ['Url', 'Html'];

    protected $Projects;

    public function humanFilesize($size, $precision = 2)
    {
        static $units = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        $step = 1024;
        $i = 0;
        while ($size / $step > 0.9) {
            $size = $size / $step;
            $i++;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }

    public function getInitials(string $fullName): string
    {
        return StringHelper::getInitials($fullName);
    }

    protected function getProjectSubitems(ServerRequest $request): array
    {
        $identity = $request->getAttribute('identity');
        if (!$identity) {
            return [];
        }

        if (!$this->Projects) {
            $this->Projects = FactoryLocator::get('Table')->get('Projects');
        }

        $currentController = $request->getParam('controller');
        $currentAction = $request->getParam('action');
        $currentId = $request->getParam('pass.0');

        // If viewing, adding, or starting a process, get its project_id
        $processProjectId = null;
        if ($currentController === 'Processes' && in_array($currentAction, ['view', 'start', 'totalResult']) && $currentId) {
            $Processes = FactoryLocator::get('Table')->get('Processes');
            $process = $Processes->find()
                ->where(['Processes.id' => $currentId])
                ->select(['Processes.project_id'])
                ->first();
            if ($process) {
                $processProjectId = $process->project_id;
            }
        }
        // If adding a process, project_id is passed as the first parameter
        if ($currentController === 'Processes' && $currentAction === 'add' && $currentId) {
            $processProjectId = $currentId;
        }
        // If working with a use case description, get its project_id through the process
        if ($currentController === 'UsecaseDescriptions' && $currentId) {
            $UsecaseDescriptions = FactoryLocator::get('Table')->get('UsecaseDescriptions');
            $Processes = FactoryLocator::get('Table')->get('Processes');

            if ($currentAction === 'add') {
                // For add, currentId is the process_id
                $process = $Processes->find()
                    ->where(['Processes.id' => $currentId])
                    ->select(['Processes.project_id'])
                    ->first();
                if ($process) {
                    $processProjectId = $process->project_id;
                }
            } elseif (in_array($currentAction, ['edit', 'view', 'review'])) {
                // For edit/view/review, currentId is the usecase_description_id
                $ucd = $UsecaseDescriptions->find()
                    ->where(['UsecaseDescriptions.id' => $currentId])
                    ->select(['process_id'])
                    ->first();
                if ($ucd) {
                    $process = $Processes->find()
                        ->where(['Processes.id' => $ucd->process_id])
                        ->select(['Processes.project_id'])
                        ->first();
                    if ($process) {
                        $processProjectId = $process->project_id;
                    }
                }
            }
        }
        // If working with criteria (protection needs analysis), get its project_id through the process
        if ($currentController === 'Criteria' && $currentId) {
            $Processes = FactoryLocator::get('Table')->get('Processes');
            // For criteria, currentId is always the process_id
            $process = $Processes->find()
                ->where(['Processes.id' => $currentId])
                ->select(['Processes.project_id'])
                ->first();
            if ($process) {
                $processProjectId = $process->project_id;
            }
        }
        // If working with indicators (VCIO classification), get its project_id through the process
        if ($currentController === 'Indicators' && $currentId) {
            $Processes = FactoryLocator::get('Table')->get('Processes');
            // For indicators, currentId is always the process_id
            $process = $Processes->find()
                ->where(['Processes.id' => $currentId])
                ->select(['Processes.project_id'])
                ->first();
            if ($process) {
                $processProjectId = $process->project_id;
            }
        }

        $projects = $this->Projects
            ->find('all')
            ->where(['Projects.user_id' => $identity->id])
            ->select(['id', 'title'])
            ->limit(10)
            ->orderDesc('Projects.modified')
            ->all();

        $subitems = [];
        foreach ($projects as $project) {
            $isActive = false;

            // Active if viewing/editing this project
            if (
                $currentController === 'Projects'
                && in_array($currentAction, ['view', 'edit'])
                && $currentId == $project->id
            ) {
                $isActive = true;
            }

            // Active if viewing, adding, or starting a process belonging to this project
            if (
                $currentController === 'Processes'
                && in_array($currentAction, ['view', 'add', 'start', 'totalResult'])
                && $processProjectId == $project->id
            ) {
                $isActive = true;
            }

            // Active if working with a use case description belonging to this project
            if (
                $currentController === 'UsecaseDescriptions'
                && in_array($currentAction, ['add', 'edit', 'view', 'review'])
                && $processProjectId == $project->id
            ) {
                $isActive = true;
            }

            // Active if working with criteria (protection needs analysis) belonging to this project
            if (
                $currentController === 'Criteria'
                && in_array($currentAction, ['index', 'rateQD', 'editRateQD', 'complete'])
                && $processProjectId == $project->id
            ) {
                $isActive = true;
            }

            // Active if working with indicators (VCIO classification) belonging to this project
            if (
                $currentController === 'Indicators'
                && in_array($currentAction, ['index', 'add', 'complete', 'view', 'decideValidation', 'validationView'])
                && $processProjectId == $project->id
            ) {
                $isActive = true;
            }

            $subitems[] = [
                'text' => $project->title,
                'url' => ['controller' => 'Projects', 'action' => 'view', $project->id],
                'active' => $isActive,
            ];
        }

        $subitems[] = [
            'text' => __('New Project'),
            'url' => ['controller' => 'Projects', 'action' => 'add'],
            'active' => $currentController === 'Projects' && $currentAction === 'add',
        ];

        return $subitems;
    }

    /**
     * Get sidebar navigation sections with authorization checking
     *
     * Generates navigation sections for the application sidebar.
     * Filters navigation items based on user permissions using canAccessRoute().
     *
     * @param \Cake\Http\ServerRequest $request Current request
     * @return array Array of navigation sections
     */
    public function getSidebarSections(ServerRequest $request): array
    {
        $currentController = $request->getParam('controller');
        $currentAction = $request->getParam('action');

        $sections = [];

        $projectSubitems = $this->getProjectSubitems($request);

        // Expand Projects section if viewing Projects, Processes, UsecaseDescriptions, Criteria, or Indicators
        $expandProjects = $currentController === 'Projects' ||
                         ($currentController === 'Processes' && in_array($currentAction, ['view', 'add', 'start', 'totalResult'])) ||
                         ($currentController === 'UsecaseDescriptions' && in_array($currentAction, ['add', 'edit', 'view', 'review'])) ||
                         ($currentController === 'Criteria' && in_array($currentAction, ['index', 'rateQD', 'editRateQD', 'complete', 'view'])) ||
                         ($currentController === 'Indicators' && in_array($currentAction, ['index', 'add', 'complete', 'view', 'decideValidation', 'validationView']));

        $navigationItems = [
            [
                'text' => __('Dashboard'),
                'url' => ['controller' => 'Pages', 'action' => 'display', 'home'],
                'icon' => Icon::BAR_CHART,
                'active' => $currentController === 'Pages' && $currentAction === 'display',
            ],
            [
                'text' => __('Projects'),
                'url' => ['controller' => 'Projects', 'action' => 'index'],
                'icon' => Icon::ACTIVITY,
                'active' => $currentController === 'Projects' && $currentAction === 'index',
                'subitems' => $projectSubitems,
                'expanded' => $expandProjects,
            ],
            [
                'text' => __('Comments'),
                'url' => ['controller' => 'Processes', 'action' => 'comments'],
                'icon' => Icon::ANNOTATION,
                'active' => $currentController === 'Comments',
            ],
            [
                'text' => __('Settings'),
                'url' => ['controller' => 'Users', 'action' => 'view'],
                'icon' => Icon::SETTINGS,
                'active' => $currentController === 'Users' && $currentAction === 'view',
            ],
        ];

        $authorizedItems = [];
        foreach ($navigationItems as $item) {
            if ($this->canAccessRoute($item['url'], $request)) {
                $authorizedItems[] = $item;
            }
        }

        if (!empty($authorizedItems)) {
            $sections[] = ['items' => $authorizedItems];
        }

        $docLinks = Configure::read('documentation_links', []);
        if (!empty($docLinks)) {
            $items = [];
            foreach ($docLinks as $link) {
                $items[] = [
                    'text' => __('{0}', $link['text']),
                    'url' => $link['url'],
                    'icon' => Icon::EXTERNAL_LINK,
                    'external' => true,
                    'active' => false,
                ];
            }

            $sections[] = [
                'heading' => __('Documentation'),
                'items' => $items,
            ];
        }

        // Beta-Version section with bug report link (opens in modal dialog)
        $sections[] = [
            'heading' => __('Beta-Version'),
            'items' => [
                [
                    'text' => __('Fehler melden'),
                    'url' => '#',
                    'icon' => Icon::ALERT_TRIANGLE,
                    'active' => false,
                    'modal_trigger' => 'bug-report-modal',
                ],
            ],
        ];

        return $sections;
    }

    public function getBreadcrumbs(ServerRequest $request): array
    {
        $currentController = $request->getParam('controller');
        $currentAction = $request->getParam('action');

        $breadcrumbs = [
            [
                'text' => __('Home'),
                'url' => '/',
                'icon' => Icon::HOME,
            ],
        ];

        switch ($currentController) {
            case 'Projects':
                $breadcrumbs[] = [
                    'text' => __('Projects'),
                    'url' => ['controller' => 'Projects', 'action' => 'index'],
                ];

                if ($currentAction === 'add') {
                    $breadcrumbs[] = [
                        'text' => __('New Project'),
                        'active' => true,
                    ];
                } elseif ($currentAction === 'edit') {
                    $projectId = $request->getParam('pass.0');
                    $breadcrumbs[] = [
                        'text' => __('Edit Project'),
                        'active' => true,
                    ];
                } elseif ($currentAction === 'view') {
                    $projectId = $request->getParam('pass.0');
                    $projectTitle = __('Project Details');

                    if ($projectId) {
                        if (!$this->Projects) {
                            $this->Projects = FactoryLocator::get('Table')->get('Projects');
                        }
                        $project = $this->Projects->find()->where(['Projects.id' => $projectId])->select(['title'])->first();
                        if ($project) {
                            $projectTitle = $project->title;
                        }
                    }

                    $breadcrumbs[] = [
                        'text' => $projectTitle,
                        'active' => true,
                    ];
                } else {
                    $breadcrumbs[count($breadcrumbs) - 1]['active'] = true;
                }
                break;

            case 'Processes':
                if ($currentAction === 'filterProject') {
                    $breadcrumbs[] = [
                        'text' => __('Dashboard'),
                        'active' => true,
                    ];
                } elseif ($currentAction === 'comments') {
                    $breadcrumbs[] = [
                        'text' => __('Comments'),
                        'active' => true,
                    ];
                } elseif ($currentAction === 'start') {
                    $processId = $request->getParam('pass.0');

                    if ($processId) {
                        $Processes = FactoryLocator::get('Table')->get('Processes');
                        $process = $Processes->find()
                            ->where(['Processes.id' => $processId])
                            ->contain(['Projects' => function ($q) {
                                return $q->select(['Projects.id', 'Projects.title']);
                            }])
                            ->select(['Processes.title', 'Processes.project_id'])
                            ->first();

                        if ($process) {
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $process->project_id],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->title,
                                'active' => true,
                            ];
                        }
                    }
                } elseif ($currentAction === 'add') {
                    $projectId = $request->getParam('pass.0');

                    if ($projectId) {
                        if (!$this->Projects) {
                            $this->Projects = FactoryLocator::get('Table')->get('Projects');
                        }
                        $project = $this->Projects->find()
                            ->where(['Projects.id' => $projectId])
                            ->select(['title'])
                            ->first();

                        if ($project) {
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $projectId],
                            ];
                        }
                    }

                    $breadcrumbs[] = [
                        'text' => __('New Process'),
                        'active' => true,
                    ];
                } elseif ($currentAction === 'view' || $currentAction === 'totalResult') {
                    $processId = $request->getParam('pass.0');
                    $processTitle = __('Process Details');

                    if ($processId) {
                        $Processes = FactoryLocator::get('Table')->get('Processes');
                        $process = $Processes->find()
                            ->where(['Processes.id' => $processId])
                            ->contain(['Projects' => function ($q) {
                                return $q->select(['Projects.id', 'Projects.title']);
                            }])
                            ->select(['Processes.title', 'Processes.project_id'])
                            ->first();

                        if ($process) {
                            $processTitle = $process->title;

                            // Add project breadcrumb
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $process->project_id],
                            ];
                        }
                    }

                    if ($currentAction === 'totalResult') {
                        $breadcrumbs[] = [
                            'text' => $processTitle,
                            'url' => ['controller' => 'Processes', 'action' => 'view', $processId],
                        ];
                        $breadcrumbs[] = [
                            'text' => __('Total Result'),
                            'active' => true,
                        ];
                    } else {
                        $breadcrumbs[] = [
                            'text' => $processTitle,
                            'active' => true,
                        ];
                    }
                }
                break;

            case 'Comments':
                $breadcrumbs[] = [
                    'text' => __('Comments'),
                    'active' => true,
                ];
                break;

            case 'Users':
                if ($currentAction === 'view') {
                    $breadcrumbs[] = [
                        'text' => __('Settings'),
                        'active' => true,
                    ];
                }
                break;

            case 'UsecaseDescriptions':
                if ($currentAction === 'add' || $currentAction === 'edit' || $currentAction === 'view' || $currentAction === 'review') {
                    // Get process from the request to find the project
                    $processId = null;

                    if ($currentAction === 'add') {
                        $processId = $request->getParam('pass.0');
                    } elseif ($currentAction === 'edit' || $currentAction === 'view' || $currentAction === 'review') {
                        $usecaseDescriptionId = $request->getParam('pass.0');
                        if ($usecaseDescriptionId) {
                            $UsecaseDescriptions = FactoryLocator::get('Table')->get('UsecaseDescriptions');
                            $ucd = $UsecaseDescriptions->find()
                                ->where(['UsecaseDescriptions.id' => $usecaseDescriptionId])
                                ->select(['process_id'])
                                ->first();
                            if ($ucd) {
                                $processId = $ucd->process_id;
                            }
                        }
                    }

                    if ($processId) {
                        $Processes = FactoryLocator::get('Table')->get('Processes');
                        $process = $Processes->find()
                            ->where(['Processes.id' => $processId])
                            ->contain(['Projects' => function ($q) {
                                return $q->select(['id', 'title']);
                            }])
                            ->select(['Processes.id', 'Processes.project_id'])
                            ->first();

                        if ($process && $process->project) {
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $process->project_id],
                            ];
                            $breadcrumbs[] = [
                                'text' => __('Assessment'),
                                'active' => true,
                            ];
                        }
                    }
                }
                break;

            case 'Criteria':
                if ($currentAction === 'index' || $currentAction === 'rateQD' || $currentAction === 'editRateQD' || $currentAction === 'complete' || $currentAction === 'view') {
                    $processId = $request->getParam('pass.0');

                    if ($processId) {
                        $Processes = FactoryLocator::get('Table')->get('Processes');
                        $process = $Processes->find()
                            ->where(['Processes.id' => $processId])
                            ->contain(['Projects' => function ($q) {
                                return $q->select(['id', 'title']);
                            }])
                            ->select(['Processes.id', 'Processes.title', 'Processes.project_id'])
                            ->first();

                        if ($process && $process->project) {
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $process->project_id],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->title,
                                'url' => ['controller' => 'Processes', 'action' => 'view', $processId],
                            ];
                            $breadcrumbs[] = [
                                'text' => __('Protection Needs Analysis'),
                                'active' => true,
                            ];
                        }
                    }
                }
                break;

            case 'Indicators':
                if (in_array($currentAction, ['index', 'add', 'complete', 'view', 'validation', 'validate', 'completeValidation', 'acceptValidation', 'decideValidation', 'validationView'])) {
                    $processId = $request->getParam('pass.0');

                    if ($processId) {
                        $Processes = FactoryLocator::get('Table')->get('Processes');
                        $process = $Processes->find()
                            ->where(['Processes.id' => $processId])
                            ->contain(['Projects' => function ($q) {
                                return $q->select(['id', 'title']);
                            }])
                            ->select(['Processes.id', 'Processes.title', 'Processes.project_id'])
                            ->first();

                        if ($process && $process->project) {
                            $breadcrumbs[] = [
                                'text' => __('Projects'),
                                'url' => ['controller' => 'Projects', 'action' => 'index'],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->project->title,
                                'url' => ['controller' => 'Projects', 'action' => 'view', $process->project_id],
                            ];
                            $breadcrumbs[] = [
                                'text' => $process->title,
                                'url' => ['controller' => 'Processes', 'action' => 'view', $processId],
                            ];

                            // Determine breadcrumb text based on action
                            $breadcrumbText = __('VCIO Classification');
                            if ($currentAction === 'view') {
                                $breadcrumbText = __('VCIO Self-assessment Results');
                            } elseif ($currentAction === 'decideValidation') {
                                $breadcrumbText = __('Validierung');
                            } elseif (in_array($currentAction, ['validation', 'validate', 'completeValidation'])) {
                                $breadcrumbText = __('VCIO Validierung');
                            } elseif ($currentAction === 'acceptValidation') {
                                $breadcrumbText = __('Accept Validation');
                            }

                            $breadcrumbs[] = [
                                'text' => $breadcrumbText,
                                'active' => true,
                            ];
                        }
                    }
                }
                break;

            case 'Dashboard':
            case 'Pages':
                if ($currentAction === 'display') {
                    $breadcrumbs[] = [
                        'text' => __('Dashboard'),
                        'active' => true,
                    ];
                } else {
                    $breadcrumbs[0]['active'] = true;
                }
                break;
        }

        return $breadcrumbs;
    }

    public function isActiveRoute(array $url, ServerRequest $request): bool
    {
        $currentController = $request->getParam('controller');
        $currentAction = $request->getParam('action');

        $urlController = $url['controller'] ?? null;
        $urlAction = $url['action'] ?? null;

        if ($urlController !== $currentController) {
            return false;
        }

        if ($urlAction && $urlAction !== $currentAction) {
            return false;
        }

        return true;
    }

    /**
     * Check if the current user can access a given route
     *
     * This method provides basic authorization checking for navigation items.
     * It integrates with CakePHP's Authorization plugin if available.
     *
     * Authorization logic:
     * 1. String URLs (external links) are always allowed
     * 2. Routes without controller specified are allowed
     * 3. Unauthenticated users cannot access any routes
     * 4. If Authorization service is available, delegate to it
     * 5. Otherwise, authenticated users can access all routes (default allow)
     *
     * To implement custom authorization:
     * - Install cakephp/authorization plugin
     * - Define policies for controller actions
     * - Or override this method in a custom helper extending LayoutHelper
     *
     * @param array|string $url URL array or string
     * @param \Cake\Http\ServerRequest $request Current request
     * @return bool True if user can access the route
     */
    protected function canAccessRoute(array|string $url, ServerRequest $request): bool
    {
        if (is_string($url)) {
            return true;
        }

        $controller = $url['controller'] ?? null;
        $action = $url['action'] ?? 'index';

        if (!$controller) {
            return true;
        }

        $identity = $request->getAttribute('identity');
        if (!$identity) {
            return false;
        }

        $authorization = $request->getAttribute('authorization');
        if ($authorization && method_exists($authorization, 'can')) {
            try {
                $resource = ['controller' => $controller, 'action' => $action];

                return $authorization->can($identity, 'access', $resource);
            } catch (Exception $e) {
                return true;
            }
        }

        return true;
    }
}
