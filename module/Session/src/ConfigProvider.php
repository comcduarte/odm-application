<?php
declare(strict_types=1);

namespace Session;

use Laminas\Router\Http\{Literal,Segment};
use Laminas\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }
    
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'session-model-adapter' => 'model-adapter',
            ],
            'factories' => [
                Command\Help::class => InvokableFactory::class,
            ],
        ];
    }
    
    public function getRouterConfig()
    {
        return [
            'routes' => [
                'response' => [
                    'type'    => Literal::class,
                    'options' => [
                        'route'    => '/response',
                        'defaults' => [
                            'action'     => 'index',
                            'controller' => Controller\ResponseController::class,
                        ],
                    ],
                    'may_terminate' => TRUE,
                    'child_routes' => [
                        'default' => [
                            'type' => Segment::class,
                            'priority' => -100,
                            'options' => [
                                'route' => '/[:action[/:uuid]]',
                                'defaults' => [
                                    'action' => 'index',
                                    'controller' => Controller\ResponseController::class,
                                ],
                            ],
                        ],
                    ],
                ],
                'session' => [
                    'type'    => Literal::class,
                    'options' => [
                        'route'    => '/session',
                        'defaults' => [
                            'action'     => 'index',
                            'controller' => Controller\SessionController::class,
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
                                    'controller' => Controller\SessionController::class,
                                ],
                            ],
                        ],
                    ],
                ],
                'session_rest' => [
                    'type'    => Literal::class,
                    'options' => [
                        'route'    => '/session/rest',
                        'defaults' => [
                            'controller' => Controller\SessionRestfulController::class,
                        ],
                    ],
                    'may_terminate' => TRUE,
                    'child_routes' => [
                        'default' => [
                            'type' => Segment::class,
                            'priority' => -100,
                            'options' => [
                                'route' => '/[:action[/:uuid]]',
                                'defaults' => [
                                    'action' => 'index',
                                    'controller' => Controller\SessionRestfulController::class,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getNavigationConfig()
    {
        return [
            'default' => [
                'session' => [
                    'label' => 'Sessions',
                    'class' => 'dropdown',
                    'route' => 'session/default',
                    'resource' => 'session/default',
                    'privilege' => 'menu',
                    'pages' => [
                        [
                            'label' => 'Add New Session',
                            'route' => 'session/default',
                            'action' => 'create',
                            'resource' => 'session/default',
                            'privilege' => 'create',
                        ],
                        [
                            'label' => 'List Sessions',
                            'route' => 'session/default',
                            'action' => 'index',
                            'resource' => 'session/default',
                            'privilege' => 'index',
                        ],
                    ],
                ],
                'settings' => [
                    'label' => 'Settings',
                    'pages' => [
                        'contact' => [
                            'label'  => 'Session Settings',
                            'route'  => 'session/config',
                            'action' => 'index',
                            'resource' => 'session/config',
                            'privilege' => 'menu',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function getAclConfig()
    {
        return [
            'admin' => [
                'session/default' => [],
                'session/config' => [],
            ],
        ];
    }
}