<?php

declare(strict_types=1);

namespace Application;

use Application\Service\Factory\DatabaseAdapterFactory;
use Dassociates\ActionMenu\View\Helper\ActionMenu;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'import' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/import[/:action]',
                    'defaults' => [
                        'controller' => Controller\ImportController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'acl' => [
        'EVERYONE' => [
            'home' => ['index'],
        ],
        'admin' => [
            'application' => [],
            'import' => [],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ImportController::class => Controller\Factory\ImportControllerFactory::class,
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'log' => [
        'syslogger' => [
            'writers' => [
                'syslog' => [
                    'name' => \Laminas\Log\Writer\Syslog::class,
                    'options' => [
                        'application' => 'PDH',
                        'formatter' => [
                            'name' => \Laminas\Log\Formatter\Json::class,
                            'options' => [
                                'format' => '%priorityName%: %message% %extra%',
                                'dateTimeFormat' => 'c',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            'home' => [
                'label' => 'Home',
                'route' => 'home',
                'order' => 0,
            ],
            'contact' => ['order' => '20'],
            'job' => ['order' => '30'],
        ],
    ],
    'service_manager' => [
        'aliases' => [
            'employee-model-adapter-config' => 'model-adapter-config',
            'model-adapter' => \Laminas\Db\Adapter\AdapterInterface::class,
        ],
        'factories' => [
            'model-adapter' => DatabaseAdapterFactory::class,
        ],
    ],
    'view_helpers' => [
        'aliases' => [
            'actionmenu' => ActionMenu::class,
        ],
        'factories' => [
            ActionMenu::class => \Laminas\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/custom-layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'import/generic'            => __DIR__ . '/../view/application/import/generic-index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
