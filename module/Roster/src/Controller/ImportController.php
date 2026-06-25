<?php
declare(strict_types=1);

namespace Roster\Controller;

use Components\Controller\AbstractImportController;
use Application\Model\Entity\UserEntity;
use Employee\Model\EmployeeModel;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class ImportController extends AbstractImportController
{
    const EmpNumber = "COL1";
    const PostID = "COL2";
    const LastName = "COL3";
    const MiddleName = "COL4";
    const FirstName = "COL5";
    const RadioNumberOld = "COL6";
    const RadioNumber = "COL7";
    const ContactPhoneNumber = "COL8";
    const SSN = "COL9";
    const Department = "COL10";
    const Rank = "COL11";
    const HoursPerWeek = "COL12";
    const OTHourlyRate = "COL13";
    const AnniversaryDate = "COL14";
    const ReviewPeriodStart = "COL15";
    const ReviewPeriodEnd = "COL16";
    const IncludeInReports = "COL17";
    const LastDayOfEmployment = "COL18";
    const ActiveRecord = "COL19";
    const CategoryCode = "COL20";
    const Opportunity = "COL21";
    const Admin = "COL22";
    const ShowInPrivateDuty = "COL23";
    const KARH = "COL24";
    const K9 = "COL25";
    
    public function process(): array
    {
        $object = $this->getObject();
        
        if (!$object) {
            return [
                'count' => $this->getCounts(self::PROCESS_UNPROCESSED),
                'total' => $this->getCounts(self::PROCESS_UNPROCESSED, self::PROCESS_SUCCESS, self::PROCESS_ERROR),
            ];
        }
        
        /**
         * Create Update Object
         */
        $sql = new Sql($this->adapter);
        $update = new Update();
        $update->table($this->import_table);
        
        $update->where(['UUID' => $object['UUID']]);
        
        
        /**
         * EMP_NUM should be 6 characters zero padded.
         */
        $update->set([self::EmpNumber => sprintf('%06d', $object[self::EmpNumber])]);
        
        
        
        
        /**
         * Execute SQL Process Statement
         */
        $statement = $sql->prepareStatementForSqlObject($update);
        try {
            $statement->execute();
        } catch (Exception $e) {
            $this->flashmessenger()->addErrorMessage($e->getMessage());
            $url = $this->getRequest()->getHeader('Referer')->getUri();
            return $this->redirect()->toUrl($url);
        }
        
        $this->setObjectStatus(self::PROCESS_SUCCESS, $object);
        
        /**
         * Return
         */
        return [
            'count' => $this->getCounts(self::PROCESS_UNPROCESSED),
            'total' => $this->getCounts(self::PROCESS_UNPROCESSED, self::PROCESS_SUCCESS, self::PROCESS_ERROR),
        ];
    }

    public function import(): array
    {
        $object = $this->getObject(self::PROCESS_SUCCESS);
        $retval = [];
        
        /**
         * Lookup by Employee Number
         */
        $employee = new EmployeeModel($this->adapter);
        if (!$employee->read(['EMP_NUM' => $object[self::EmpNumber]])) {
            $this->setObjectStatus(self::IMPORT_ERROR, $object);
            throw new \Exception('Unable to find employee by number');
        }
        $retval['employee'] = $employee;
        
        /**
         * Find Employee > User Relationship
         */
        $user_uuid = "";
        $sql = new Sql($this->adapter);
        
        $where = new Where();
        $where->equalTo('EMP_UUID', $employee->UUID);
        
        $select = new Select();
        $select->from('user_employee');
        $select->where($where);
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
            
            $data = $resultSet->toArray();
            if (sizeof($data)) {
                $user_uuid = $data[0]['USER_UUID'];
            } else {
                $user_uuid = 'SYSTEM';
            }
        } catch (Exception $e) {
            $user_uuid = 'SYSTEM';
        }
        $retval['user_uuid'] = $user_uuid;
        
        /**
         * Create User and relationship if not present
         */
        if ($user_uuid == 'SYSTEM') {
            $entity = new UserEntity($this->adapter);
            
            $entity->user->USERNAME = sprintf('%s%s', $employee->LNAME, $employee->FNAME[0]);
            $entity->user->FNAME = $employee->FNAME;
            $entity->user->LNAME = $employee->LNAME;
            $entity->user->PASSWORD = $entity->user->UUID;
            
            /**
             * Create properly formatted phone number
             */
            try {
                //-- @TODO Change Phone Field from 10 chr to 12 --//
                $phoneUtil = PhoneNumberUtil::getInstance();
                $number = $phoneUtil->parse($object[self::ContactPhoneNumber], "US");
                if ($phoneUtil->isValidNumber($number)) {
                    $entity->user->PHONE = $phoneUtil->format($number, PhoneNumberFormat::E164);
                }
            } catch (NumberParseException $e) {
                $entity->user->PHONE = "";
            }
            
            /**
             * Set Employee Status
             */
            if (($object[self::LastDayOfEmployment] == 1) && ($object[self::ActiveRecord] == 1)) {
                $entity->user->STATUS = $entity->user::ACTIVE_STATUS;
            } else {
                $entity->user->STATUS = $entity->user::INACTIVE_STATUS;
            }
            
            try {
                $entity->user->create();
            } catch (Exception $e) {
            }
            
            
            /**
             * Create Relationship User > Employee
             */
            
            $entity->getUser($entity->user->UUID);
        }
        
        $this->setObjectStatus(self::IMPORT_SUCCESS, $object);
        
        return [
            'retval' => $retval,
            'count' => $this->getCounts(self::PROCESS_SUCCESS),
            'total' => $this->getCounts(self::PROCESS_SUCCESS, self::IMPORT_SUCCESS, self::IMPORT_ERROR),
        ];
    }

}