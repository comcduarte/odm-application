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
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;

class JobConfigController extends ConfigController
{
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
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
         * CONTACT
         ******************************/
        $ddl = new CreateTable('job');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('JOB_NUM', 10, TRUE));
        $ddl->addColumn(new Datetime('REQUESTED_START', TRUE));
        $ddl->addColumn(new Datetime('REQUESTED_END', TRUE));
        $ddl->addColumn(new Datetime('ACTUAL_START', TRUE));
        $ddl->addColumn(new Datetime('ACTUAL_END', TRUE));
        $ddl->addColumn(new Varchar('CONTACT_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('PO', 25, TRUE));
        $ddl->addColumn(new Varchar('TYPE_UUID', 36, TRUE));
        $ddl->addColumn(new Varchar('LOCATION', 255, TRUE));
        $ddl->addColumn(new Boolean('CRUISER', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
    }
}