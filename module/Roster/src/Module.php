<?php
declare(strict_types=1);

namespace Roster;

use Laminas\EventManager\LazyListenerAggregate;
use Laminas\Mvc\MvcEvent;
use Roster\Listener\RosterListener;

class Module
{
    public function getConfig() : array
    {
        $config = include __DIR__ . '/../config/module.config.php';
        return $config;
    }
    
    public function onBootStrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
        $config = $serviceManager->get('config');
        
        $notificationListener = $serviceManager->get(RosterListener::class);
        $notificationListener->attach($eventManager);
        
        /****************************************
         * Lazy Listeners Aggregate
         ****************************************/
        if (array_key_exists('event_manager', $config)
            && is_array($config['event_manager'])
            && array_key_exists('lazy_listeners', $config['event_manager'])
            ) {
                $listeners = $config['event_manager']['lazy_listeners'];
                $container = $serviceManager;
                $aggregate = new LazyListenerAggregate($listeners, $container);
                $aggregate->attach($eventManager);
            }
    }
    
}