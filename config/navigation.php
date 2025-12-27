<?php
/**
 * Navigation Configuration
 *
 * Configure navigation links for the application.
 * URLs can be overridden via environment variables for different deployment environments.
 */

return [
    'documentation_links' => [
        [
            'text' => 'Prüfmethoden',
            //'url' => env('DOCS_URL_PROCESS', 'https://www.mission-ki.de/pruefprozess'),
            'url' => 'https://docs.pruefportal.mission-ki.de/methods',
            'icon' => 'external-link'
        ],
        [
            'text' => 'Prüfkriterien',
            //'url' => env('DOCS_URL_PROCESS', 'https://www.mission-ki.de/pruefprozess'),
            'url' => 'https://docs.pruefportal.mission-ki.de/indicators',
            'icon' => 'external-link'
        ],
        [
            'text' => 'Hilfe-Artikel',
            //'url' => env('DOCS_URL_METHODS', 'https://www.mission-ki.de/pruefmethoden'),
            'url' => 'https://docs.pruefportal.mission-ki.de/entries',
            'icon' => 'external-link'
        ],
    ]
];
