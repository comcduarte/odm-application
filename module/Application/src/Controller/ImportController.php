<?php
declare(strict_types=1);

namespace Application\Controller;

use Components\Form\UploadFileForm;
use Contact\Model\Company;
use Contact\Model\Contact;
use Contact\Model\ContactAddress;
use Contact\Model\ContactPhone;
use Job\Model\JobType;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Laminas\Db\Sql\Ddl\CreateTable;
use Laminas\Db\Sql\Ddl\DropTable;
use Laminas\Db\Sql\Ddl\Column\Datetime;
use Laminas\Db\Sql\Ddl\Column\Integer;
use Laminas\Db\Sql\Ddl\Column\Varchar;
use Laminas\Db\Sql\Ddl\Constraint\PrimaryKey;
use Laminas\Form\Form;
use Laminas\Form\Element\Submit;
use Laminas\I18n\PhoneNumber\Validator\PhoneNumber;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\NotEmpty;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Exception;
use libphonenumber\PhoneNumberUtil;

class ImportController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    const NO_ADAPTER = 1;
    const NO_DATABASE_TABLE = 2;
    const TABLE_EMPTY = 3;
    const OK = 4;
    
    const IMPORT_SUCCESS = 16;
    const IMPORT_ERROR = 8;
    const PROCESS_SUCCESS = 4;
    const PROCESS_ERROR = 2;
    const PROCESS_UNPROCESSED = 1;
    
    public $import_table = 'import';
    
    public $import_error_table = 'import_errors';
    
    public function indexAction()
    {
        $view = new ViewModel();
        
        $upload_form = new UploadFileForm('UPLOAD');
        $upload_form->init();
        $view->setVariable('upload_form', $upload_form);
        
        /**
         * Flush Form
         * @var \Laminas\Form\Form $flush
         */
        $flush = new Form();
        $flush->add([
            'name' => 'FLUSH',
            'type' => Submit::class,
            'attributes' => [
                'class' => 'btn btn-primary mt-2',
                'id' => 'FLUSH',
                'value' => 'Flush',
            ],
        ]);
        $view->setVariable('flushForm', $flush);
        
        $view->setVariable('total', $this->getCounts(self::PROCESS_UNPROCESSED,self::PROCESS_SUCCESS));
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
                    
//                     if ($data['HEADINGS_FIRST']) {
                        //-- Use record to create temporary table --//
                        
//                     }
                    
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
                            $this->flashMessenger()->addErrorMessage(sprintf('%s %s',$e->getLine(), $e->getMessage()));
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
        $sql = new Sql($this->adapter);
        $ddl = [];
        
        $ddl[] = new DropTable($this->import_table);
        
        foreach ($ddl as $obj) {
            $this->adapter->query($sql->buildSqlString($obj), $this->adapter::QUERY_MODE_EXECUTE);
        }
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function processAction()
    {
        $view = new JsonModel();
        
        $notEmpty = new NotEmpty();
        
        
        /**
         * Create Select Object
         * @var \Laminas\Db\Sql\Sql $sql
         */
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->import_table);
        $select->where(['STATUS' => self::PROCESS_UNPROCESSED]);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
            $object = $resultSet->current();
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        /**
         * Create Update Object
         */
        $update = new Update();
        $update->table($this->import_table);
        
        $update->where(['UUID' => $object['UUID']]);
        
        /**
         * Run Checks
         */
        if (!$notEmpty->isValid($object['COL2'])) {
            $update->set(['STATUS' => self::PROCESS_ERROR]);
            
        } else  {
            $update->set(['STATUS' => self::PROCESS_SUCCESS]);
        }
        
        $statement = $sql->prepareStatementForSqlObject($update);
        try {
            $statement->execute();
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $view->setVariables([
            'count' => $this->getCounts(self::PROCESS_UNPROCESSED),
            'total' => $this->getCounts(self::PROCESS_UNPROCESSED, self::PROCESS_SUCCESS, self::PROCESS_ERROR),
        ]);
        return $view;
    }
    
    public function importAction()
    {
        $view = new JsonModel();
        
        /****************************************
         * Column Descriptions
         ****************************************/
        $BillingCompanyName     = "COL1";
        $CustomerType           = "COL2";
        $CustomerAddress1       = "COL3";
//         $CustomerAddress2       = "COL4";
        $CustomerCity           = "COL5";
        $CustomerState          = "COL6";
        $CustomerZip            = "COL7";
//         $PO                     = "COL8";
        $AccountsPayableName    = "COL9";
        $ShopNumber             = "COL10";
        $AccountsPayableNumber  = "COL11";
        $Active                 = "COL12";
        
        /**
         * Create Select Object
         * @var \Laminas\Db\Sql\Sql $sql
         */
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->import_table);
        $select->where(['STATUS' => self::PROCESS_SUCCESS]);
        
        
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
            $object = $resultSet->current();
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        /**
         * Set Update Object
         * 
         */
        $update = new Update();
        $update->table($this->import_table);
        $update->set(['STATUS' => self::PROCESS_ERROR]);
        $update->where(['UUID' => $object['UUID']]);
        $update_statement = $sql->prepareStatementForSqlObject($update);
        
        /**
         * Create Validators
         */
        $notEmpty = new NotEmpty();
        $phoneValidator = new PhoneNumber(['country' => 'US']);
        $phoneUtil = PhoneNumberUtil::getInstance();
        
        /**
         * 
         * @var \Contact\Model\Company $company
         */
        $company = new Company($this->adapter);
        $company->NAME = $object[$BillingCompanyName];
        $type = new JobType($this->adapter);
        if (!$type->read(['TYPE' => $object[$CustomerType]])) {
            //-- ERROR --//
            $update_statement->execute();
        }
        $company->TYPE_UUID = $type->UUID;
        
        if ($object[$Active] == 1) {
            $company->STATUS = $company::INACTIVE_STATUS;
        }
        
        $company->create();
        
        /**
         * 
         * @var \Contact\Model\Contact $contact
         */
        $contact = new Contact($this->adapter);
        $contact->COMPANY_UUID = $company->UUID;
        $contact->TITLE = "Accounts Payable";
        
        if ($object[$Active] == 1) {
            $contact->STATUS = $contact::INACTIVE_STATUS;
        }
        
        $matches = [];
        if (preg_match('/^(\w*)[[:blank:]](.*)/', $object[$AccountsPayableName], $matches)) {
            $contact->FNAME = $matches[1];
            $contact->LNAME = $matches[2];
        } else {
            //-- Entire string stored in FNAME --//
            $contact->FNAME = $object[$AccountsPayableName];
        }
        $contact->create();
        
        /**
         * Address
         * @var \Contact\Model\ContactAddress $address
         */
        $address = new ContactAddress($this->adapter);
        $matches = [];
        
        switch (true) {
            case str_starts_with($object[$CustomerAddress1], "P"):
                //-- PO BOX --//
                preg_match('/^[PpOo\.\s]*\s\w*\s([0-9]*)/', $object[$CustomerAddress1], $matches);
                $address->STREET = sprintf("PO BOX %d", $matches[0]);
                break;
            case preg_match('/^(\d*)\s(.*)/', $object[$CustomerAddress1], $matches):
                //-- Full Street Address --//
                $address->NUM = $matches[1];
                $address->STREET = $matches[2];
                break;
            case !$notEmpty->isValid($object[$CustomerAddress1]):
            default:
                //-- ERROR --//
                $update_statement->execute();
                break;
        }
        
        $address->NAME = "Main";
        $address->CITY = $object[$CustomerCity];
        $address->STATE = $object[$CustomerState];
        $address->ZIP = $object[$CustomerZip];
        $address->CONTACT_UUID = $contact->UUID;
        
        $address->create();
        
        /**
         * 
         * @var \Contact\Model\ContactPhone $phone
         */
        $phone = new ContactPhone($this->adapter);
        
        if ($phoneValidator->isValid($object[$AccountsPayableNumber])) {
            $x = $phoneUtil->parse($object[$AccountsPayableNumber], 'US');
            $phone->PHONE = $x->getNationalNumber();
            $phone->TYPE = "Accounts Payable";
            $phone->CONTACT_UUID = $contact->UUID;
            $phone->create();
        }
        
        $phone = new ContactPhone($this->adapter);
        
        if ($phoneValidator->isValid($object[$ShopNumber])) {
            $x = $phoneUtil->parse($object[$ShopNumber], 'US');
            $phone->PHONE = $x->getNationalNumber();
            $phone->TYPE = "Shop";
            $phone->CONTACT_UUID = $contact->UUID;
            $phone->create();
        }
        

        $sql = new Sql($this->adapter);
        
        /**
         * Remove Imported Record
         * @var \Laminas\Db\Sql\Delete $delete
         */
        $delete = new Delete();
        $delete->from($this->import_table);
        $delete->where(['UUID' => $object['UUID']]);
        $statement = $sql->prepareStatementForSqlObject($delete);
        
        try {
            $statement->execute();
        } catch (Exception $e) {
            $update_statement->execute();
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $view->setVariables([
            'count' => $this->getCounts(self::PROCESS_SUCCESS),
            'total' => $this->getCounts(self::PROCESS_SUCCESS, self::IMPORT_SUCCESS, self::IMPORT_ERROR),
        ]);
        return $view;
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
            $ddl->addColumn(new Varchar('COL' . $i, 1024, TRUE));
        }
        
        $ddl->addConstraint(new PrimaryKey('UUID'));
        
        $this->adapter->query($sql->buildSqlString($ddl), $this->adapter::QUERY_MODE_EXECUTE);
        unset($ddl);
    }
    
    protected function getObject(int $status = self::PROCESS_UNPROCESSED)
    {
        /**
         * Create Select Object
         * @var \Laminas\Db\Sql\Sql $sql
         */
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->from($this->import_table);
        $select->where(['STATUS' => $status]);
        $select->limit(1);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
            $object = $resultSet->current();
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
//             return $this->redirect()->toUrl($url);
            throw new \Exception($e->getMessage());
        }
        
        return $object;
    }
    
    protected function getCounts(int ...$status): int
    {
        /**
         * Get total count of records in temporary table.
         */
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select->columns(['STATUS', new Expression('COUNT(`UUID`)')]);
        $select->from($this->import_table);
        $select->group('STATUS');
        $where = new Where();
        $where->in('STATUS', $status);
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $count = 0;
        
        foreach ($resultSet->toArray() as $status) {
            $count += $status['Expression1'];
        }
        
        return $count;
    }
}