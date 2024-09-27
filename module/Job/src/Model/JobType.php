<?php
declare(strict_types=1);

namespace Job\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;

class JobType extends AbstractBaseModel
{
    public $TYPE;
    
    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
        $this->setTableName('job_type');
    }
}