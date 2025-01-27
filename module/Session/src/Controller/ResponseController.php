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
    
    public function responseAction()
    {
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            switch (true) {
                case isset($post['YES']):
                    break;
                case isset($post['NO']):
                    break;
                default:
                    throw new \Exception('Unable to parse post.');
                    break;
            }
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}