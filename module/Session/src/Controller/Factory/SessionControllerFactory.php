<?php
declare(strict_types=1);

namespace Session\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Session\Controller\SessionController;
use Session\Form\SessionForm;
use Session\Model\Session;

class SessionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new SessionController();
        
        $adapter = $container->get('session-model-adapter');
        
        $model = new Session($adapter);
        $form = $container->get('FormElementManager')->get(SessionForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}