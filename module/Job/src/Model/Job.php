<?php
declare(strict_types=1);

namespace Job\Model;

use Components\Model\AbstractBaseModel;

class Job extends AbstractBaseModel
{
    const OPEN_STATUS = 3;
    const CANCELED_STATUS = 4;
    const UNABLE_TO_FILL_STATUS = 5;
    const WORK_REASON_STATUS = 6;
    
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
    public $CONTACT;
    
    /**
     * 
     * @var string
     */
    public $COMPANY_UUID;
    
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
    
    /**
     * @var string
     */
    public $EMP_UUID;
    
    public function __construct($adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('job');
    }
    
    public static function retrieveStatus($status)
    {
        $statuses = [
            NULL => 'Inactive',
            self::INACTIVE_STATUS => 'Inactive',
            self::ACTIVE_STATUS => 'Active',
            self::OPEN_STATUS => 'Open',
            self::CANCELED_STATUS => 'Canceled',
            self::UNABLE_TO_FILL_STATUS => 'Unable to Fill',
            self::WORK_REASON_STATUS => 'Work Reason',
        ];
        
        return $statuses[$status];
    }
}