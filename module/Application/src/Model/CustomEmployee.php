<?php
declare(strict_types=1);

namespace Application\Model;

use Employee\Model\EmployeeModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Exception\InvalidArgumentException;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;

class CustomEmployee extends EmployeeModel
{
    public $EMP_UUID;
    public $HOURLY_RATE;
    public $OVERTIME_RATE;
    public $RANK;
    
    public function __construct(Adapter $adapter = NULL)
    {
        parent::__construct($adapter);
        $this->setTableName('employees_custom');
    }
    
    public function update() : bool
    {
        $date = new \DateTime('now',new \DateTimeZone('UTC'));
        $this->DATE_MODIFIED = $date->format('Y-m-d H:i:s');
        
        $sql = new Sql($this->adapter);
        
        $this->EMP_UUID = $this->UUID;
        $values = $this->getArrayCopy();
        $this->record_exists();
        
        $update = new Update();
        $update->table('employees');
        $update->set($values);
        $update->join('employee_custom', 'employees.UUID = employee_custom.EMP_UUID', Join::JOIN_LEFT);
        $update->where([$this->primary_key => $this->UUID]);
        
        $statement = $sql->prepareStatementForSqlObject($update);
        
        try {
            $statement->execute();
        } catch (\Exception $e) {
            return FALSE;
        }
        return TRUE;
    }
    
    private function record_exists()
    {
        $sql = sprintf("INSERT INTO employee_custom (EMP_UUID) VALUES ('%s') ON DUPLICATE KEY UPDATE EMP_UUID = '%s'", $this->EMP_UUID, $this->EMP_UUID);
        try {
            $this->adapter->query($sql, $this->adapter::QUERY_MODE_EXECUTE);
        } catch (InvalidArgumentException $e) {

        }
    }
}