<?php
declare(strict_types=1);

namespace Roster\Form\Factory;

use Psr\Container\ContainerInterface;
use Roster\Form\RosterForm;

class RosterFormFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $form = new RosterForm();
        $adapter = $container->get('roster-model-adapter');
        $form->setDbAdapter($adapter);
        return $form;
    }
}