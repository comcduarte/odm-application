<?php
declare(strict_types=1);

namespace Roster\Controller\Factory;

use Roster\Controller\ImportController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class ImportControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new ImportController();
        $adapter = $container->get('model-adapter');
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}