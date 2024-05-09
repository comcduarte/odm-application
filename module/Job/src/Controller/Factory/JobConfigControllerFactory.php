<?php
declare(strict_types=1);

namespace Job\Controller\Factory;

use Job\Controller\JobConfigController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JobConfigControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new JobConfigController();
        $adapter = $container->get('job-model-adapter');
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}