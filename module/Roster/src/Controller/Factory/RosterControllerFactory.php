<?php
declare(strict_types=1);

namespace Roster\Controller\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Roster\Controller\RosterController;
use Roster\Form\RosterForm;
use Roster\Model\Roster;

class RosterControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new RosterController();
        
        $adapter = $container->get('roster-model-adapter');
        
        $model = new Roster($adapter);
        $form = $container->get('FormElementManager')->get(RosterForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}