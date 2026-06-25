<?php
declare(strict_types=1);

namespace Job\Form\Factory;

use Job\Form\FilterForm;
use Psr\Container\ContainerInterface;

class FilterFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new FilterForm();
        $adapter = $container->get('job-model-adapter');
        $form->setDbAdapter($adapter);
        return $form;
    }
}