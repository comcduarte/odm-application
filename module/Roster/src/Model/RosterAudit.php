<?php
declare(strict_types=1);

namespace Roster\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;

class RosterAudit extends AbstractBaseModel
{
    public $EMP_UUID;
    public $LAST_POS;
    public $CURR_POS;
    public $ACTION;
    public $USER;
    
    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
        
        $this->setTableName('roster_audit');
    }
    
}