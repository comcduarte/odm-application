<?php
declare(strict_types=1);

namespace Job\Form\Factory;

use Job\Form\JobTypeForm;
use Psr\Container\ContainerInterface;

class JobTypeFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new JobTypeForm();
        return $form;
    }
}