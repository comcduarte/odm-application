<?php
declare(strict_types=1);

namespace Session\Controller;

use Annotation\Traits\AnnotationAwareTrait;
use Components\Controller\AbstractBaseController;
use Job\Model\Job;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\View\Model\ViewModel;
use Session\Form\SelectJobForm;
use Session\Model\Session;

class SessionController extends AbstractBaseController
{
    use AnnotationAwareTrait;
    
    public function updateAction() 
    {
        $view = new ViewModel();
        $view = parent::updateAction();
        $view->setTemplate('session/update');
        
        $user = $this->currentUser();
        
        /****************************************
         * ANNOTATIONS
         ****************************************/
        $this->annotations_tablename = $this->model->getTableName();
        $this->annotations_prikey = $this->model->UUID;
        $this->annotations_user = $user->UUID;
        $view->setVariables($this->getAnnotations());
        
        /****************************************
         * SESSION JOBS
         ****************************************/
        $job = new Job($this->adapter);
        $where = new Where();
        $where->equalTo('job.STATUS', Job::ACTIVE_STATUS);
        
        $select = new Select();
        $select->columns([
            'REQUESTED_START', 'REQUESTED_END'
        ]);
        $select->join('job_company', 'job_company.UUID = job.COMPANY_UUID', ['NAME']);
        $select->join('job_type', 'job_type.UUID = job.TYPE_UUID', ['TYPE']);
        $select->join('session_job' , 'job.UUID = session_job.JOB_UUID', ['UUID']);
        $job->setSelect($select);
        
        $jobs = $job->fetchAll($where);
        $view->setVariable('jobs', $jobs);
        
        /****************************************
         * SESSION JOB MODAL
         ****************************************/
        $session_job_form = new SelectJobForm('select-job');
        
        $job = new Job($this->adapter);
        $where = new Where();
        $where->equalTo('job.STATUS', Job::ACTIVE_STATUS);
        $jobs = $job->fetchAll($where);
        
        $session_job_form->setJobs($jobs);
        $session_job_form->init();
        
        $session_job_params = [
            'title' => 'Select Jobs',
            'id' => 'modal-session_job_form',
            'form' => $session_job_form,
            'attributes' => [
                'action' => $this->url()->fromRoute('session/default', ['action' => 'select_job']),
            ],
        ];
        $view->setVariable('session_job_params', $session_job_params);
        
        return $view;
    }

    public function selectJobAction() 
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            foreach ($data as $job_uuid => $selected)  {
                if ($selected == '1') {
                    /**
                     * @var Session $session
                     */
                    $session = $this->model;
                    $session->read(['UUID' => 'uuid']);
                    $session->addJob($job_uuid);
                }
            }
        }
        
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function removeJobAction()
    {
        $primary_key = $this->params()->fromRoute(strtolower($this->model->getPrimaryKey()),0);
        
        $view = new ViewModel();
        $view->setTemplate('base/delete');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            
            if ($del == 'Yes') {
                $this->model->removeJob($primary_key);
            }
            
            return $this->redirect()->toUrl($request->getPost('referring_url'));
        }
        
        $view->setVariables([
            'form' => $this->form,
            'primary_key' => $this->model->getPrimaryKey(),
            'referring_url' => $this->getRequest()->getHeader('Referer')->getUri(),
        ]);
        
        
        return $view;
    }
}