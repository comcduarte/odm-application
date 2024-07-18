<?php
declare(strict_types=1);

namespace Roster;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'roster' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/roster',
                    'defaults' => [
                        'action' => 'index',
                        'controller' => Controller\RosterController::class,
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
                                'controller' => Controller\ConfigController::class,
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
                                'controller' => Controller\RosterController::class,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'EVERYONE' => [
            'roster/default' => [],
        ],
        'admin' => [
            'roster/default' => [],
            'roster/config' => [],
        ],
    ],
    'controllers' => [
        'aliases' => [
            
        ],
        'factories' => [
            Controller\ConfigController::class => Controller\Factory\ConfigControllerFactory::class,
            Controller\RosterController::class => Controller\Factory\RosterControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\RosterForm::class => Form\Factory\RosterFormFactory::class,
        ],
    ],
    'navigation' => [
        'default' => [
            'roster' => [
                'label' => 'Roster',
                'class' => 'dropdown',
                'route' => 'roster/default',
                'resource' => 'roster/default',
                'privilege' => 'menu',
                'pages' => [
                    [
                        'label' => 'Add Employee to Roster',
                        'route' => 'roster/default',
                        'action' => 'create',
                        'resource' => 'roster/default',
                        'privilege' => 'create',
                    ],
                    [
                        'label' => 'Show Roster',
                        'route' => 'roster/default',
                        'action' => 'index',
                        'resource' => 'roster/default',
                        'privilege' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'roster-model-adapter' => 'model-adapter',
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