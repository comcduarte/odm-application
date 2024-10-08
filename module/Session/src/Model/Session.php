<?php
declare(strict_types=1);

namespace Session\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Sql;

class Session extends AbstractBaseModel
{
    const PENDING_STATUS = 3;
    
    public $NAME;
    public $DESC;
    public $DATE_START;
    public $DATE_CLOSE;
    
    public function __construct(Adapter $adapter) 
    {
        parent::__construct($adapter);
        $this->STATUS = $this::PENDING_STATUS;
        $this->setTableName('session');
        return $this;
    }
    
    public function addJob(string $job_uuid)
    {
        $sql = new Sql($this->adapter);
        
        $insert = new Insert();
        $insert->into('session_job');
        $insert->columns([
            'UUID',
            'STATUS',
            'SESSION_UUID',
            'JOB_UUID',
        ]);
        $insert->values([
            $this->generate_uuid(),
            $this::ACTIVE_STATUS,
            $this->UUID,
            $job_uuid,
        ]);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        
        try {
            $statement->execute();
        } catch (\PDOException $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    public function removeJob(string $uuid)
    {
        $sql = new Sql($this->adapter);
        
        $delete = new Delete();
        $delete->from('session_job');
        $delete->where->equalTo('UUID', $uuid);
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (\PDOException $e) {
            return FALSE;
        }
        return TRUE;
    }
}