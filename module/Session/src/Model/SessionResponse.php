<?php
declare(strict_types=1);

namespace Session\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;

class SessionResponse extends AbstractBaseModel
{
    public $SESSION_UUID;
    public $EMP_UUID;
    public $RESPONSE;
    
    public function __construct(Adapter $adapter = null)
    {
        parent::__construct($adapter);
        $this->setTableName('session_response');
        return $this;
    }
}