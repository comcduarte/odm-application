<?php
declare(strict_types=1);

namespace Job\Controller\Factory;

use Job\Controller\JobController;
use Job\Form\JobForm;
use Job\Model\Job;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class JobControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $controller = new JobController();
        
        $adapter = $container->get('job-model-adapter');
        
        $model = new Job($adapter);
        $form = $container->get('FormElementManager')->get(JobForm::class);
        
        $controller->setModel($model);
        $controller->setForm($form);
        $controller->setDbAdapter($adapter);
        return $controller;
    }
}