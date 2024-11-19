<?php
declare(strict_types=1);

namespace Session\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Session\Form\ResponseForm;

class ResponseController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        
        $session_uuid = $this->params()->fromRoute('uuid',0);
        
        $form = new ResponseForm();
        $form->init();
        
        $view->setVariable('form', $form);
        
        
        
        
        
        return $view;
    }
}