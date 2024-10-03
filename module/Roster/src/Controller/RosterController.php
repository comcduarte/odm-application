<?php
declare(strict_types=1);

namespace Roster\Controller;

use Components\Controller\AbstractBaseController;
use Roster\Model\Roster;

class RosterController extends AbstractBaseController
{
    public function updateAction()
    {
        $this->getEventManager()->trigger('roster.update', $this);
        
        return parent::updateAction();
    }
    
    public function clearAction()
    {
        $roster = new Roster($this->adapter);
        $list = $roster->fetchEntities();
        foreach ($list as $entity) {
            $roster->read(['EMP_UUID' => $entity['UUID']]);
            $roster->STATUS = Roster::ACTIVE_STATUS;
            $roster->update();
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
}