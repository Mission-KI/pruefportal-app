<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

/*
 * This file is loaded in the context of the `Application` class.
 * So you can use `$this` to reference the application class instance
 * if required.
 */
return function (RouteBuilder $routes): void {
    /*
     * The default class to use for all routes
     *
     * The following route classes are supplied with CakePHP and are appropriate
     * to set as the default:
     *
     * - Route
     * - InflectedRoute
     * - DashedRoute
     *
     * If no call is made to `Router::defaultRouteClass()`, the class used is
     * `Route` (`Cake\Routing\Route\Route`)
     *
     * Note that `Route` does not do any inflections on URLs which will result in
     * inconsistently cased URLs when used with `{plugin}`, `{controller}` and
     * `{action}` markers.
     */
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        /*
         * Here, we are connecting '/' (base path) to a controller called 'Pages',
         * its action called 'display', and we pass a param to select the view file
         * to use (in this case, templates/Pages/home.php)...
         */
        $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

        // Health check endpoint (no authentication required)
        $builder->connect('/health', ['controller' => 'Pages', 'action' => 'health']);

        // S3 Test Routes
        $builder->connect('/s3-test', ['controller' => 'S3Test', 'action' => 'index']);
        $builder->connect('/s3-test/upload', ['controller' => 'S3Test', 'action' => 'upload']);
        $builder->connect('/s3-test/download', ['controller' => 'S3Test', 'action' => 'download']);
        $builder->connect('/s3-test/delete', ['controller' => 'S3Test', 'action' => 'delete']);
        $builder->connect('/s3-test/test-connection', ['controller' => 'S3Test', 'action' => 'testConnection']);

        // Log Test Routes (for monitoring verification)
        // TODO(@sascha): wieder entfernen
        $builder->connect('/log-test/info', ['controller' => 'LogTest', 'action' => 'info']);
        $builder->connect('/log-test/warning', ['controller' => 'LogTest', 'action' => 'warning']);
        $builder->connect('/log-test/error', ['controller' => 'LogTest', 'action' => 'error']);
        $builder->connect('/log-test/critical', ['controller' => 'LogTest', 'action' => 'critical']);
        $builder->connect('/log-test/exception', ['controller' => 'LogTest', 'action' => 'exception']);

        // $builder->connect('/', ['controller' => 'Users', 'action' => 'display']);
        $builder->connect('/set-new-password/{token}', ['controller' => 'Users', 'action' => 'setNewPassword'], ['pass' => ['token']]);
        $builder->connect('/activate-account/{token}', ['controller' => 'Users', 'action' => 'activate'], ['pass' => ['token']]);
        $builder->connect('/accept-invitation/{token}', ['controller' => 'Users', 'action' => 'acceptInvitation'], ['pass' => ['token']]);
        $builder->connect('/my-account', ['controller' => 'Users', 'action' => 'view']);
        $builder->connect('/edit-my-account', ['controller' => 'Users', 'action' => 'edit']);

        // Bug Reports
        $builder->connect('/bug-reports', ['controller' => 'BugReports', 'action' => 'form']);
        $builder->connect('/bug-reports/add', ['controller' => 'BugReports', 'action' => 'add']);

        $builder->connect('/processes/start/{id}', ['controller' => 'Processes', 'action' => 'start'])->setPass(['id'])->setPatterns(['id' => '[0-9]+']);
        $builder->connect('/processes/protection-needs-analysis/{process_id}', ['controller' => 'Criteria', 'action' => 'index'])->setPass(['process_id'])->setPatterns(['process_id' => '[0-9]+']);
        $builder->connect('/processes/protection-needs-analysis/{process_id}-{qd_id}', ['controller' => 'Criteria', 'action' => 'rateQD'])->setPass(['process_id', 'qd_id'])->setPatterns(['process_id' => '[0-9]+', 'qd_id' => '[A-Z]+']);
        $builder->connect('/processes/protection-needs-analysis/edit/{process_id}-{qd_id}-{question_id}', ['controller' => 'Criteria', 'action' => 'editRateQD'])->setPass(['process_id', 'qd_id', 'question_id'])->setPatterns(['process_id' => '[0-9]+', 'qd_id' => '[A-Z]+', 'question_id' => '[0-2]+']);
        $builder->connect(
            '/criteria/save-draft/{process_id}',
            ['controller' => 'Criteria', 'action' => 'saveDraft']
        )
        ->setPass(['process_id'])
        ->setMethods(['POST']);

        $builder->connect('/indicators/view/{id}', ['controller' => 'Indicators', 'action' => 'view'])->setPass(['id'])->setPatterns(['id' => '[0-9]+']);

        $builder->connect(
            '/indicators/save-draft/{process_id}',
            ['controller' => 'Indicators', 'action' => 'saveDraft']
        )
        ->setPass(['process_id'])
        ->setMethods(['POST']);

        $builder->connect(
            '/indicators/edit/{process_id}/{qd_id}',
            ['controller' => 'Indicators', 'action' => 'edit']
        )
        ->setPass(['process_id', 'qd_id'])
        ->setMethods(['GET', 'POST']);

        $builder->connect(
            '/usecase-descriptions/save-draft/{id}',
            ['controller' => 'UsecaseDescriptions', 'action' => 'saveDraft']
        )
        ->setPass(['id'])
        ->setMethods(['POST', 'PUT', 'PATCH']);

        $builder->connect('/projects/overall-assessment', ['controller' => 'Projects', 'action' => 'overallAssessment']);

        /*
         * ...and connect the rest of 'Pages' controller's URLs.
         */
        $builder->connect('/pages/*', 'Pages::display');

        /*
         * Connect catchall routes for all controllers.
         *
         * The `fallbacks` method is a shortcut for
         *
         * ```
         * $builder->connect('/{controller}', ['action' => 'index']);
         * $builder->connect('/{controller}/{action}/*', []);
         * ```
         *
         * It is NOT recommended to use fallback routes after your initial prototyping phase!
         * See https://book.cakephp.org/5/en/development/routing.html#fallbacks-method for more information
         */
        $builder->fallbacks();
    });

    $routes->prefix('admin', function (RouteBuilder $builder): void {
        $builder->registerMiddleware(
            'auth',
            new \Authentication\Middleware\AuthenticationMiddleware($this)
        );
        $builder->applyMiddleware('auth');

        // Because you are in the admin scope,
        // you do not need to include the /admin prefix
        // or the Admin route element.
        $builder->connect('/', ['controller' => 'Users', 'action' => 'display']);
        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
