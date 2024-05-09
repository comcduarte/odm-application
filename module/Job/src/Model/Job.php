<?php
declare(strict_types=1);

namespace Job\Model;

use Components\Model\AbstractBaseModel;

class Job extends AbstractBaseModel
{
    /**
     * Job Number.  Auto incrementing
     * @var string
     */
    public $JOB_NUM;
    
    /**
     * 
     * @var \DateTime
     */
    public $REQUESTED_START;
    
    /**
     * 
     * @var \DateTime
     */
    public $REQUESTED_END;
    
    /**
     *
     * @var \DateTime
     */
    public $ACTUAL_START;
    
    /**
     *
     * @var \DateTime
     */
    public $ACTUAL_END;
    
    /**
     * 
     * @var string
     */
    public $CONTACT_UUID;
    
    /**
     * 
     * @var string
     */
    public $PO;
    
    /**
     * 
     * @var string
     */
    public $TYPE_UUID;
    
    /**
     * 
     * @var string
     */
    public $LOCATION;
    
    /**
     * 
     * @var boolean
     */
    public $CRUISER;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('job');
    }
}