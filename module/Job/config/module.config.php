<?php
declare(strict_types=1);

namespace Job;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;


return [
    'router' => [
        'routes' => [
            'job' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/job',
                    'defaults' => [
                        'action'     => 'index',
                        'controller' => Controller\JobController::class,
                    ],
                ],
                'may_terminate' => TRUE,
                'child_routes' => [
                    'config' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/config[/:action]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => Controller\JobConfigController::class,
                            ],
                        ],
                    ],
                    'default' => [
                        'type' => Segment::class,
                        'priority' => -100,
                        'options' => [
                            'route' => '/[:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => Controller\JobController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'admin' => [
            'job/default' => [],
            'job/config' => [],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\JobController::class => Controller\Factory\JobControllerFactory::class,
            Controller\JobConfigController::class => Controller\Factory\JobConfigControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\JobForm::class => Form\Factory\JobFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            'job' => [
                'label' => 'Jobs',
                'class' => 'dropdown',
                'route' => 'job/default',
                'resource' => 'job/default',
                'privilege' => 'menu',
                'pages' => [
                    [
                        'label' => 'Dashboard',
                        'route' => 'job/default',
                        'action' => 'dashboard',
                        'resource' => 'job/default',
                        'privilege' => 'dashboard',
                    ],
                    [
                        'label' => 'Add New Job',
                        'route' => 'job/default',
                        'action' => 'create',
                        'resource' => 'job/default',
                        'privilege' => 'create',
                    ],
                    [
                        'label' => 'List Jobs',
                        'route' => 'job/default',
                        'action' => 'index',
                        'resource' => 'job/default',
                        'privilege' => 'index',
                    ],
                ],
            ],
            'settings' => [
                'label' => 'Settings',
                'pages' => [
                    'contact' => [
                        'label'  => 'Job Settings',
                        'route'  => 'job/config',
                        'action' => 'index',
                        'resource' => 'job/config',
                        'privilege' => 'menu',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'job-model-adapter' => 'model-adapter',
        ],
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'aliases' => [
        ],
        'factories' => [
        ],
    ],
    'view_manager' => [
        'template_map' => [
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];