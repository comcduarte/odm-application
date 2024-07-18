<?php
declare(strict_types=1);

namespace Roster\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Roster\Controller\ConfigController;

class ConfigControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new ConfigController();
        $adapter = $container->get('roster-model-adapter');
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}