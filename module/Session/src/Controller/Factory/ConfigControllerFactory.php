<?php
declare(strict_types=1);

namespace Session\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Session\Controller\ConfigController;

class ConfigControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new ConfigController();
        $adapter = $container->get('session-model-adapter');
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}