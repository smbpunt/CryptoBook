<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    'app' => [
        'path' => './assets/app.js',
        'entrypoint' => true,
    ],
    'tab' => [
        'path' => './assets/tab.js',
        'entrypoint' => true,
    ],
    'modal' => [
        'path' => './assets/modal.js',
        'entrypoint' => true,
    ],
    'tooltip' => [
        'path' => './assets/tooltip.js',
        'entrypoint' => true,
    ],
    'select2' => [
        'path' => './assets/select2.js',
        'entrypoint' => true,
    ],
    'datepicker' => [
        'path' => './assets/date-picker.js',
        'entrypoint' => true,
    ],
    'home' => [
        'path' => './assets/home.js',
        'entrypoint' => true,
    ],
    'position_form' => [
        'path' => './assets/position/position_form.js',
        'entrypoint' => true,
    ],
    'position_index' => [
        'path' => './assets/position/index.js',
        'entrypoint' => true,
    ],
    'loan_index' => [
        'path' => './assets/loan/index.js',
        'entrypoint' => true,
    ],
    'farming_form' => [
        'path' => './assets/farming/form.js',
        'entrypoint' => true,
    ],
    'loan_form' => [
        'path' => './assets/loan/form.js',
        'entrypoint' => true,
    ],
    'lp_form' => [
        'path' => './assets/lp/form.js',
        'entrypoint' => true,
    ],
    'dca_form' => [
        'path' => './assets/dca/dca_form.js',
        'entrypoint' => true,
    ],
    'dca_index' => [
        'path' => './assets/dca/index.js',
        'entrypoint' => true,
    ],
    'collection' => [
        'path' => './assets/collection.js',
        'entrypoint' => true,
    ],
    '@hotwired/stimulus' => [
        'version' => '3.2.2',
    ],
    '@symfony/stimulus-bundle' => [
        'path' => './vendor/symfony/stimulus-bundle/assets/dist/loader.js',
    ],
    'bootstrap' => [
        'version' => '5.3.2',
    ],
    'bootstrap/dist/css/bootstrap.min.css' => [
        'version' => '5.3.2',
        'type' => 'css',
    ],
    '@popperjs/core' => [
        'version' => '2.11.8',
    ],
    'chart.js/auto' => [
        'version' => '3.9.1',
    ],
    'choices.js' => [
        'version' => '9.1.0',
    ],
    'choices.js/public/assets/styles/choices.min.css' => [
        'version' => '9.1.0',
        'type' => 'css',
    ],
    'flatpickr' => [
        'version' => '4.6.13',
    ],
    'flatpickr/dist/flatpickr.min.css' => [
        'version' => '4.6.13',
        'type' => 'css',
    ],
    'bootstrap-icons/font/bootstrap-icons.min.css' => [
        'path' => './vendor/twbs/bootstrap-icons/font/bootstrap-icons.min.css',
        'type' => 'css',
    ],
];
