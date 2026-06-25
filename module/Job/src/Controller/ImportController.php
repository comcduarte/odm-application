<?php
declare(strict_types=1);

namespace Job\Controller;

use Annotation\Model\AnnotationModel;
use Components\Controller\AbstractImportController;
use Contact\Model\Company;
use Employee\Model\EmployeeModel;
use Job\Model\Job;
use Job\Model\JobType;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Session\Model\Session;
use Exception;

class ImportController extends AbstractImportController
{
	const JobNumber = "COL1";
	const BillingCompanyName = "COL2";
	const NameOfCaller = "COL3";
	const ContactName = "COL4";
	const ContactPhone = "COL5";
	const CompanyWorkingWith = "COL6";
	const DateOfJob = "COL7";
	const LocationOfJob = "COL8";
	const TimeStart = "COL9";
	const TimeEnd = "COL10";
	const HoursNeeded = "COL11";
	const Comments = "COL12";
    const Cruiser = "COL13";
	const JobType = "COL14";
	const Exception = "COL15";
	const OfficerTakingCall = "COL16";
	const JobRecAdded = "COL17";
	const StatusOfJobCode = "COL18";
	const WhoAdded = "COL19";
	const Selected = "COL20";
	const LastMinuteJob = "COL21";
	const UTF = "COL22";
    
    public function process(): array
    {
        $object = $this->getObject();
        
        /**
         * Create Update Object
         */
        $sql = new Sql($this->adapter);
        $update = new Update();
        $update->table($this->import_table);
        
        $update->where(['UUID' => $object['UUID']]);
        
        /**
         * @TODO Run Checks
         */
        
        /**
         * Execute SQL Process Statement
         */
        $update->set(['STATUS' => self::PROCESS_SUCCESS]);
        
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
         * Create Session
         */
        $session = new Session($this->adapter);
        $session->NAME = $object[self::JobNumber];
        $session->STATUS = $session::ACTIVE_STATUS;
        
        /**
         * Create Job
         */
        $job = new Job($this->adapter);
        $job->PO = $object[self::JobNumber];
        
        /**
         * Request Start and End Dates
         */
        $requested_start_date = new \DateTime($object[self::DateOfJob]);
        $requested_end_date = clone $requested_start_date;
        $job->REQUESTED_START = $requested_start_date->format('Y-m-d H:i:s');
        $retval['requested_start_date'] = $requested_start_date;

        
        $requested_start_time = new \DateTime($object[self::TimeStart]);
        $hour   = (int) $requested_start_time->format('H');
        $minute = (int) $requested_start_time->format('i');
        $second = (int) $requested_start_time->format('s');
        $requested_start_date->setTime($hour, $minute, $second);
        $retval['requested_start_time'] = $requested_start_time;

        
        $requested_end_time = new \DateTime($object[self::TimeEnd]);
        $hour   = (int) $requested_end_time->format('H');
        $minute = (int) $requested_end_time->format('i');
        $second = (int) $requested_end_time->format('s');
        $requested_end_date->setTime($hour, $minute, $second);
        $job->REQUESTED_END = $requested_end_date->format('Y-m-d H:i:s');
        $retval['requested_end_time'] = $requested_end_time;

        
        $retval['requested_end_date'] = $requested_end_date;

        
        /**
         * 11
         * NameOfCaller --> CONTACT 
         * (CALLED_IN not configured in model)
         * (ContactName not used in import)
         */
        $job->CONTACT = $object[self::NameOfCaller];
        
        
        /**
         * 12
         * BillingCompany --> COMPANY_UUID
         */
        $company = new Company($this->adapter);
        if (!$company->read(['NAME' => $object[self::BillingCompanyName]])) {
            $company->NAME = $object[self::BillingCompanyName];
            
            $type = new JobType($this->adapter);
            if (!$type->read(['TYPE' => $object[self::JobType]])) {
                $this->setObjectStatus(self::IMPORT_ERROR, $object);
                $retval['error'] = 'Unknown Job Type';
            }
            $company->TYPE_UUID = $type->UUID;
            $company->STATUS = $company::ACTIVE_STATUS;
            $company->create();
        }
        $job->COMPANY_UUID = $company->UUID;
        $job->TYPE_UUID = $company->TYPE_UUID;
        $retval['COMPANY'] = $company;

        
        /**
         * 13
         * Cruiser --> CRUSIER
         */
        $job->CRUISER = $object[self::Cruiser];
        
        /**
         * Find Employee who added record
         */
        $employee = new EmployeeModel($this->adapter);
        if (!$employee->read(['EMP_NUM' => sprintf("%06d", $object[self::WhoAdded])])) {
            $this->setObjectStatus(self::IMPORT_ERROR, $object);
            $retval['error'] = 'Unable to find Employee Number for WhoAdded.';
        }
        
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
        $retval['Employee UUID'] = $employee->UUID;

        $retval['User UUID'] = $user_uuid;

        
        /**
         * Annotations
         */
        $annotations = [];
        $annotation = new AnnotationModel($this->adapter);
        $annotation->TABLENAME = $job->getTableName();
        $annotation->PRIKEY = $job->UUID;
        $annotation->USER = $user_uuid;
        
        $columns = [self::CompanyWorkingWith, self::LocationOfJob, self::Comments];
        
        foreach ($columns as $column) {
            $annotation->ANNOTATION = $object[$column];
            $annotation->UUID = $annotation->generate_uuid();
            $annotation->create();
            
            $annotations[] = $annotation->ANNOTATION;
        }
        $retval['annotations'] = $annotations;

        
        /**
         * Finalize Job
         */
        $job->create();
        $this->setObjectStatus(self::IMPORT_SUCCESS, $object);
        
        /**
         * Finalize Session
         */
        $session->DATE_START = $job->REQUESTED_START;
        $session->DATE_CLOSE = $job->REQUESTED_END;
        $session->STATUS = $session::INACTIVE_STATUS;
        $session->addJob($job->UUID);
        $session->create();
        $retval['session'] = $session;

        $retval['job'] = $job;

        $retval['object'] = $object;

        
        return [
            'retval' => $retval,
            'count' => $this->getCounts(self::PROCESS_SUCCESS),
            'total' => $this->getCounts(self::PROCESS_SUCCESS, self::IMPORT_SUCCESS, self::IMPORT_ERROR),
        ];
    }

}