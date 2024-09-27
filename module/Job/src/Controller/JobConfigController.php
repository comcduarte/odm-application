<?php
declare(strict_types=1);

namespace Job\Controller;

use Contact\Controller\ConfigController;
use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Boolean;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\ForeignKey;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;

class JobConfigController extends ConfigController
{
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('job_assignment');
        $ddl[] = new DropTable('job_type');
        $ddl[] = new DropTable('job');
        
        foreach ($ddl as $obj) {
            try {
                $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
            } catch (InvalidQueryException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        
        $this->clearSettings('JOB');
    }
    
    public function createDatabase()
    {
        /******************************
         * Job
         ******************************/
        $ddl = new CreateTable('job');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Datetime('REQUESTED_START', TRUE));
        $ddl->addColumn(new Datetime('REQUESTED_END', TRUE));
        $ddl->addColumn(new Datetime('ACTUAL_START', TRUE));
        $ddl->addColumn(new Datetime('ACTUAL_END', TRUE));
        $ddl->addColumn(new Varchar('CONTACT', 255, TRUE));
        $ddl->addColumn(new Varchar('PO', 25, TRUE));
        $ddl->addColumn(new Varchar('COMPANY_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('TYPE_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('LOCATION', 255, TRUE));
        $ddl->addColumn(new Boolean('CRUISER'));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * Job Type
         ******************************/
        $ddl = new CreateTable('job_type');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('TYPE', 255, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * Job Assignment
         ******************************/
        $ddl = new CreateTable('job_assignment');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('JOB_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('EMPLOYEE_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        $ddl->addConstraint(new ForeignKey('JOB_ASSIGNMENT', 'JOB_UUID', 'job', 'UUID', 'NO ACTION', 'NO ACTION'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * Job Roster
         ******************************/
//         $ddl = new CreateTable('job_roster');
//         $ddl = $this->addStandardFields($ddl);
        
//         $ddl->addColumn(new Varchar('EMPLOYEE_UUID', 36, TRUE));
//         $ddl->addColumn(new Integer('ORDER', TRUE));
        
//         $ddl->addConstraint(new PrimaryKey('UUID'));
        
//         $this->processDdl($ddl);
//         unset($ddl);
    }
}