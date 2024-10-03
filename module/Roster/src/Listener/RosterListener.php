<?php
declare(strict_types=1);

namespace Roster\Listener;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Roster\Model\RosterAudit;
use User\Model\UserModel;

class RosterListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    use AdapterAwareTrait;
    
    /**
     *
     * {@inheritDoc}
     * @see \Laminas\EventManager\ListenerAggregateInterface::attach()
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $shared_manager = $events->getSharedManager();
        
        $this->listeners[] = $shared_manager->attach('*', 'roster.update',  [$this, 'onUpdateAction'], -100);
    }
    
    public function onUpdateAction(Event $e)
    {
        /**
         * 
         * @var UserModel $user
         */
        $params = $e->getParams();
        $user = $params['USER'];
        
        $audit = new RosterAudit($this->adapter);
        $audit->EMP_UUID = $params['EMP_UUID'];
        $audit->ACTION = $params['ACTION'];
        $audit->USER = $user->UUID;
        $audit->LAST_POS = $params['LAST_POS'];
        $audit->CURR_POS = $params['CURR_POS'];
        $audit->create();
        return;
    }
}