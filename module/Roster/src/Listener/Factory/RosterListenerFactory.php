<?php
declare(strict_types=1);

namespace Roster\Listener\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Roster\Listener\RosterListener;

class RosterListenerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $listener = new RosterListener();
        $adapter = $container->get('roster-model-adapter');
        $listener->setDbAdapter($adapter);
        return $listener;
    }
}