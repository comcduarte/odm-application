<?php
declare(strict_types=1);

namespace Application\Controller;

use Components\Form\UploadFileForm;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Exception;

class ImportController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    const NO_ADAPTER = 1;
    const NO_DATABASE_TABLE = 2;
    const TABLE_EMPTY = 3;
    const OK = 4;
    
    public $import_table = 'import';
    
    public $import_error_table = 'import_errors';
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        $upload_form = new UploadFileForm('UPLOAD');
        $upload_form->init();
        $view->setVariable('upload_form', $upload_form);
        
        
        return $view;
    }
    
    public function uploadAction()
    {
        $request = $this->getRequest();
        
        $form = new UploadFileForm();
        $form->init();
        $form->addInputFilter();
        
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $form->setData($data);
            
            $record = null;
            
            if ($form->isValid()) {
                $data = $form->getData();
                if (($handle = fopen($data['FILE']['tmp_name'],"r")) !== FALSE) {
                    //-- Retrieve first line --//
                    $record = fgetcsv($handle, NULL, ",");
                    
                    if ($data['HEADINGS_FIRST']) {
                        //-- Use record to create temporary table --//
                        
                    }
                    
                    $table_exists = $this->tableExists();
                    
                    if ($table_exists != self::OK) {
                        $this->createTable($record);
                        $this->flashMessenger()->addSuccessMessage('Table Created');
                    }
                    
                    $sql = new Sql($this->adapter);
                    
                    $insert = new Insert();
                    $insert->into($this->import_table);
                    
                    $columns = [
                        'UUID',
                        'STATUS',
                        'DATE_CREATED',
                        'DATE_MODIFIED',
                    ];
                    
                    for ($i = 1; $i <= count($record); $i++) {
                        $columns[] = 'COL' . $i;
                    }
                    $insert->columns($columns);
                    
                    $j = 0;
                    while (($record = fgetcsv($handle, NULL, ",")) !== FALSE) {
                        $defaults = [
                            $j++,
                            1,
                            null,
                            null,
                        ];
                        
                        $insert->values(array_merge($defaults, $record), Insert::VALUES_SET);
                        $statement = $sql->prepareStatementForSqlObject($insert);
                        
                        try {
                            $statement->execute();
                        } catch (Exception $e) {
                        }
                        
                    }
                }
            } else {
                $this->flashMessenger()->addErrorMessage('Upload Invalid');
            }
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function flushAction()
    {
        $sql = new Sql($this->timecard_adapter);
        $ddl = [];
        
        $ddl[] = new DropTable($this->import_table);
        
        foreach ($ddl as $obj) {
            $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
        }
    }
    
    private function tableExists(): int
    {
        
        try {
            $db = $this->adapter;
            if (! $db instanceof Adapter)
                return self::NO_ADAPTER;
        } catch (Exception $e) {
            return self::NO_ADAPTER;
        }
        
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select
            ->from($this->import_table)
            ->limit(1);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            return self::NO_DATABASE_TABLE;
        }
        
        return self::OK;
    }
    
    private function createTable($record)
    {
        $sql = new Sql($this->adapter);
        
        /******************************
         * TIMECARD
         ******************************/
        $ddl = new CreateTable($this->import_table);
        
        $ddl->addColumn(new Varchar('UUID', 36));
        $ddl->addColumn(new Integer('STATUS', TRUE));
        $ddl->addColumn(new Datetime('DATE_CREATED', TRUE));
        $ddl->addColumn(new Datetime('DATE_MODIFIED', TRUE));
        
        $i = 0;
        foreach ($record as $var => $value) {
            $i++;
            $ddl->addColumn(new Varchar('COL' . $i, 255, TRUE));
        }
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
    }
    
}