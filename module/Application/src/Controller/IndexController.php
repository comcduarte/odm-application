<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Michelf\Markdown;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }
    
    public function termsAction()
    {
        $view = new ViewModel();
        
        $parser = new Markdown();
        $contents = file_get_contents(__DIR__ . '/../../../../TERMS.md');
        $html = $parser->defaultTransform($contents);
        
        $view->setVariable('html', $html);
        
        return($view);
    }
    
    public function privacyAction()
    {
        $view = new ViewModel();
        
        $parser = new Markdown();
        $contents = file_get_contents(__DIR__ . '/../../../../PRIVACY.md');
        $html = $parser->defaultTransform($contents);
        
        $view->setVariable('html', $html);
        
        return($view);
    }
}
