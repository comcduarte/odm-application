<?php
declare(strict_types=1);

namespace Roster\Model;

use Components\Model\AbstractBaseModel;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Predicate\Expression;
use Laminas\EventManager\EventManagerAwareTrait;
use Exception;

class Roster extends AbstractBaseModel
{
    use EventManagerAwareTrait;
    
    const INTERESTED_STATUS = 3;
    const NOTINTERESTED_STATUS = 4;
    
    public $EMP_UUID;
    public $POSITION;
    
    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
        
        $this->setTableName('roster');
        
        array_push($this->private_attributes, 'events');
        $this->setPublicAttributes();
    }
    
    public function fetchEntities($sessionId = null)
    {
        $sql = new Sql($this->adapter);
        
        $select = $this->getSelect();
        $select
            ->columns(['UUID' => 'EMP_UUID', '#' => 'POSITION', 'STATUS' => 'STATUS'])
            ->from('roster')
            ->join('employees', 'employees.UUID = roster.EMP_UUID', ['EMP_NUM', 'FNAME', 'LNAME'], Join::JOIN_INNER)
            ->join('user_employee', 'user_employee.EMP_UUID = roster.EMP_UUID',[], Join::JOIN_LEFT);
            
            $select->join(
                'session_response',
                new Expression(
                    'session_response.EMP_UUID = user_employee.USER_UUID AND session_response.SESSION_UUID = ?',
                    [$sessionId]
                    ),
                ['RESPONSE'],
                Join::JOIN_LEFT
                );
            
            
//         $select->where(['roster.STATUS' => Roster::ACTIVE_STATUS]);
        $select->order('#');
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return [];
        }
        
        return $resultSet->toArray();
    }
    
    public function organize()
    {
        $current_position = 0;
        
        $roster = $this->fetchAll(null, ['POSITION']);
        foreach ($roster as $record) {
            if ($record['POSITION'] != ($current_position+10)) {
                $change = new Roster($this->adapter);
                $change->read(['UUID' => $record['UUID']]);
                $change->POSITION = $current_position + 10;
                $change->update();
                
                $current_position = $current_position + 10;
                continue;
            }
            $current_position = $record['POSITION'];
        }
        
        return;
    }
}