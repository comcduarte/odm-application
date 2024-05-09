<?php
declare(strict_types=1);

namespace Job\Controller;

use Components\Controller\AbstractBaseController;
use Laminas\View\Model\ViewModel;

class JobController extends AbstractBaseController
{
    public function dashboardAction()
    {
        $view = new ViewModel();
        return $view;
    }
}