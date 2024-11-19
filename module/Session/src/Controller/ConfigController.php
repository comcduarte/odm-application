<?php
declare(strict_types=1);

namespace Session\Controller;

use Components\Controller\AbstractConfigController;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Db\Sql\Ddl\Constraint\UniqueKey;

class ConfigController extends AbstractConfigController
{
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('session');
        $ddl[] = new DropTable('session_job');
        $ddl[] = new DropTable('session_response');
        $ddl[] = new DropTable('session_log');
        
        foreach ($ddl as $obj) {
            try {
                $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
            } catch (\PDOException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        
        $this->clearSettings('SESSION');
    }

    public function createDatabase()
    {
        /******************************
         * SESSION
         ******************************/
        $ddl = new CreateTable('session');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('NAME', 255, TRUE));
        $ddl->addColumn(new Varchar('DESC', 25, TRUE));
        $ddl->addColumn(new Datetime('DATE_START', TRUE));
        $ddl->addColumn(new Datetime('DATE_CLOSE', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * SESSION JOB
         ******************************/
        $ddl = new CreateTable('session_job');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('SESSION_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('JOB_UUID', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * SESSION RESPONSE
         ******************************/
        $ddl = new CreateTable('session_response');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('SESSION_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        $ddl->addColumn(new Integer('RESPONSE', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        $ddl->addConstraint(new UniqueKey(['SESSION_UUID','EMP_UUID'], 'SESSION_EMP'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * SESSION LOG
         ******************************/
        $ddl = new CreateTable('session_log');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('SESSION_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('ACTION', 36, TRUE));
        $ddl->addColumn(new Varchar('VALUE', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
    }
}