<?php
declare(strict_types=1);

namespace Roster\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;

class Roster extends AbstractBaseModel
{
    public $EMP_UUID;
    public $POSITION;
    
    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
        
        $this->setTableName('roster');
    }
}