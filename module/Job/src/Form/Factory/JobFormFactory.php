<?php
declare(strict_types=1);

namespace Job\Form\Factory;

use Job\Form\JobForm;
use Psr\Container\ContainerInterface;

class JobFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new JobForm();
        return $form;
    }
}