<?php
declare(strict_types=1);

namespace Session\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;

class SessionLog extends AbstractBaseModel
{
    public $SESSION_UUID;
    public $EMP_UUID;
    public $ACTION;
    public $VALUE;
    
    const ACTION_RESPONSE = 'response';
    const ACTION_NOTIFY = 'notify';
    
    public function __construct(Adapter $adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('session_log');
        return $this;
    }
}