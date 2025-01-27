<?php
declare(strict_types=1);

namespace Session\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Session\Model\Session;
use Session\Model\SessionResponse;
use Exception;
use Session\Form\ResponseForm;

class DashboardController extends AbstractActionController
{
    use AdapterAwareTrait;
    
    public function userAction()
    {
        $view = new ViewModel();
        $adapter = $this->adapter;
        
        /****************************************
         * SESSION INFORMATION
         ****************************************/
        $session = new Session($adapter);
        
        $where = new Where();
        $where->equalTo('STATUS', Session::ACTIVE_STATUS);
        
        $sessions = $session->fetchAll($where);
        
        foreach ($sessions as $index => $session) {
            /****************************************
             * JOB INFORMATION
             ****************************************/
            $sql = new Sql($this->adapter);
            
            $select = new Select();
            $select->from('session_job');
            
            $where = new Where();
            $where->equalTo('SESSION_UUID', $session['UUID']);
            
            $select->where($where);
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet();
            try {
                $results = $statement->execute();
                $resultSet->initialize($results);
                $jobs = $resultSet->toArray();
            } catch (Exception $e) {
                return [];
            }
            $sessions[$index]['JOBS'] = $jobs;
        }
        
        $view->setVariable('sessions', $sessions);
        
        /****************************************
         * USER INFORMATION
         ****************************************/
        $user = $this->currentUser();
        
        /****************************************
         * RESPONSE INFORMATION
         ****************************************/
        $responses = [];
        $response = new SessionResponse($this->adapter);
        foreach ($sessions as $session) {
            $uuid = $session['UUID'];
            
            if ($response->read(['SESSION_UUID' => $uuid, 'EMP_UUID' => $user->UUID])) {
                $responses[$uuid] = $response->RESPONSE;
            }
        }
        $view->setVariable('responses', $responses);
        
        /****************************************
         * RESPONSE FORM
         ****************************************/
        $response_form = new ResponseForm();
        $response_form->init();
        $view->setVariable('response_form', $response_form);
        
        return $view;
    }
}