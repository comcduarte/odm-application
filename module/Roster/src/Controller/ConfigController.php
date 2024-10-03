<?php
declare(strict_types=1);

namespace Roster\Controller;

use Components\Controller\AbstractConfigController;
use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;

class ConfigController extends AbstractConfigController
{
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable('roster');
        $ddl[] = new DropTable('roster_audit');
        
        foreach ($ddl as $obj) {
            try {
                $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
            } catch (InvalidQueryException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        
        $this->clearSettings('ROSTER');
    }

    public function createDatabase()
    {
        /******************************
         * Roster
         ******************************/
        $ddl = new CreateTable('roster');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        $ddl->addColumn(new Integer('POSITION', TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
        
        /******************************
         * Roster Audit
         ******************************/
        $ddl = new CreateTable('roster_audit');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('EMP_UUID', 36, TRUE));
        $ddl->addColumn(new Integer('LAST_POS', TRUE));
        $ddl->addColumn(new Integer('CURR_POS', TRUE));
        $ddl->addColumn(new Varchar('ACTION', 255, TRUE));
        $ddl->addColumn(new Varchar('USER', 36, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl);
    }
}