<?php
declare(strict_types=1);

namespace Session;

use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'controllers' => [
        'factories' => [
            Controller\ConfigController::class => Controller\Factory\ConfigControllerFactory::class,
            Controller\SessionController::class => Controller\Factory\SessionControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            Form\SessionForm::class => InvokableFactory::class,
        ],
    ],
    'laminas-cli' => [
        'commands' => [
            'session:help' => Command\Help::class,
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