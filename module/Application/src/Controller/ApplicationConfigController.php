<?php
declare(strict_types=1);

namespace Application\Controller;

use Components\Controller\AbstractConfigController;
use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\SqlInterface;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;

class ApplicationConfigController extends AbstractConfigController
{
    public function clearDatabase()
    {
        $sql = new Sql($this->adapter);
        $ddl = [];
        
//         $ddl[] = new DropTable('positions');
        
        foreach ($ddl as $obj) {
            try {
                $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
            } catch (InvalidQueryException $e) {
                $this->flashMessenger()->addErrorMessage($e->getMessage());
            }
        }
        
        $this->clearSettings('APP');
    }

    public function createDatabase()
    {
        /******************************
         * POSITIONS
         ******************************
        $ddl = new CreateTable('positions');
        $ddl = $this->addStandardFields($ddl);
        
        $ddl->addColumn(new Varchar('TITLE', 255, TRUE));
        $ddl->addColumn(new Text('DESC', NULL, TRUE));
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->processDdl($ddl);
        unset($ddl); */
    }

    public function addStandardFields(SqlInterface $ddl)
    {
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        return $ddl;
    }
    
    public function processDdl($ddl)
    {
        $sql = new Sql($this->adapter);
        try {
            $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        } catch (InvalidQueryException $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());
        }
        return;
    }
}