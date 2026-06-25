<?php
declare(strict_types=1);

namespace Job;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Job\Model\JobType;

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
                    'import' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/import[/:action]',
                            'defaults' => [
                                'controller' => Controller\ImportController::class,
                            ],
                        ],
                    ],
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
                    'create' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/create[/:uuid]',
                            'defaults' => [
                                'action' => 'create',
                                'controller' => Controller\JobController::class,
                            ],
                        ],
                    ],
                    'dashboard' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/dashboard[/:start_date[/:end_date[/[:session]]]]',
                            'defaults' => [
                                'action' => 'dashboard',
                                'controller' => Controller\JobController::class,
                            ],
                        ],
                    ],
                    'type' => [
                        'type' => Segment::class,
                        'priority' => 100,
                        'options' => [
                            'route' => '/type[/:action[/:uuid]]',
                            'defaults' => [
                                'action' => 'index',
                                'controller' => Controller\JobTypeController::class,
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
            'job/type' => [],
            'job/dashboard' => [],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\JobController::class => Controller\Factory\JobControllerFactory::class,
            Controller\JobTypeController::class => Controller\Factory\JobTypeControllerFactory::class,
            Controller\JobConfigController::class => Controller\Factory\JobConfigControllerFactory::class,
            Controller\ImportController::class => ReflectionBasedAbstractFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\JobForm::class => Form\Factory\JobFormFactory::class,
            Form\JobTypeForm::class => Form\Factory\JobTypeFormFactory::class,
            Form\FilterForm::class => Form\Factory\FilterFormFactory::class,
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
                        'label' => 'Types',
                        'class' => 'dropdown-submenu',
                        'route' => 'job/type',
                        'action' => 'menu',
                        'resource' => 'job/default',
                        'privilege' => 'menu',
                        'pages' => [
                            [
                                'label' => 'Add New Job Type',
                                'class' => 'dropdown',
                                'route' => 'job/type',
                                'action' => 'create',
                                'resource' => 'job/type',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'List Job Types',
                                'route' => 'job/type',
                                'action' => 'index',
                                'resource' => 'job/type',
                                'privilege' => 'index',
                            ],
                        ],
                    ],
                    [
                        'label' => 'Job',
                        'class' => 'dropdown-submenu',
                        'route' => 'job',
                        'resource' => 'job/default',
                        'privilege' => 'menu',
                        'order' => 90,
                        'pages' => [
                            [
                                'label' => 'Add New Job',
                                'route' => 'job/default', 
                                'action' => 'create',
                                'resource' => 'job/default',
                                'privilege' => 'create',
                            ],
                            [
                                'label' => 'Add New City Job',
                                'route' => 'job/create',
                                'action' => 'create',
                                'resource' => 'job/create',
                                'privilege' => 'create',
                                'params' => [
                                    'uuid' => '0cbd0990-a379-b544-d943-6ce0f82cd2fd',
                                ],
                            ],
                            [
                                'label' => 'Add New Private Duty Job',
                                'route' => 'job/create',
                                'action' => 'create',
                                'resource' => 'job/create',
                                'privilege' => 'create',
                                'params' => [
                                    'uuid' => 'f7f8414c-a003-74d4-f947-7d4bf8ff7b27',
                                ],
                            ],
                            [
                                'label' => 'Add New Non Profit Private Duty Job',
                                'route' => 'job/create',
                                'action' => 'create',
                                'resource' => 'job/create',
                                'privilege' => 'create',
                                'params' => [
                                    'uuid' => 'a5de7fcd-fa39-6ff4-39d6-b55d94298e0c',
                                ],
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