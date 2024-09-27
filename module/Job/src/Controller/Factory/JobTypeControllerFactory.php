<?php
declare(strict_types=1);

namespace Job\Controller\Factory;

use Job\Controller\JobTypeController;
use Job\Form\JobTypeForm;
use Job\Model\JobType;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JobTypeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new JobTypeController();
        
        $adapter = $container->get('job-model-adapter');
        
        $model = new JobType($adapter);
        $form = $container->get('FormElementManager')->get(JobTypeForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}