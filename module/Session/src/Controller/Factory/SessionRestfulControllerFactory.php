<?php
declare(strict_types=1);

namespace Session\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Session\Controller\SessionRestfulController;

class SessionRestfulControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new SessionRestfulController();
        $adapter = $container->get('session-model-adapter');
        $controller->setDbAdapter($adapter);
        
        $twilio_config = $container->get('twilio-config');
        $controller->twilio_sid = $twilio_config['sid'];
        $controller->twilio_token = $twilio_config['token'];
        $controller->twilio_sender = $twilio_config['sender'];
        
        return $controller;
    }
}