<?php
declare(strict_types=1);

namespace Job\Controller;

use Annotation\Form\AnnotationForm;
use Annotation\Traits\AnnotationAwareTrait;
use Components\Controller\AbstractBaseController;
use Components\Form\Element\DatabaseSelect;
use Contact\Form\ContactForm;
use Dassociates\Html\Div;
use Job\Form\FilterForm;
use Job\Form\JobForm;
use Job\Form\JobTypeForm;
use Job\Model\Job;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Form\Element\Textarea;
use Laminas\View\Model\ViewModel;
use Roster\Model\Roster;
use Employee\Model\EmployeeModel;

class JobController extends AbstractBaseController
{
    use AnnotationAwareTrait;
    
    public $date;
    
    public function dashboardAction()
    {
        $start = $this->params()->fromRoute('start_date', 0);
        $end = $this->params()->fromRoute('end_date',0);
        $session = $this->params()->fromRoute('session', 0);
        if (!$start) {
            $this->date = new \DateTime('now',new \DateTimeZone('UTC'));
            $start = $this->date->format('Y-m-d');
            $end = $this->date->format('Y-m-d');
            $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();
            $this->redirect()->toRoute($route, ['start_date' => $start, 'end_date' => $end]);
        }
       
        $view = new ViewModel();
        $form = $this->form;
        
        $job_div = new Div();
        $job_div->class = 'form-control';
        
        /****************************************
         * Retrieve Jobs
         ****************************************/
        /**
         * Jobs
         * @var $job Job
         */
        $job = $this->model;
        
        $select = new Select();
        $select->from($job->getTableName());
        $select->join('session_job', 'session_job.JOB_UUID = job.UUID', ['SESSION_UUID','JOB_UUID']);
        
        $where = new Where();
        $where->between('REQUESTED_START', "$start 00:00:00", "$end 23:59:59")->and->equalTo('SESSION_UUID', $session);
        
        $job->setSelect($select);
        $jobs = $job->fetchAll($where);
        
        if (! $jobs) {
            $jobs = [];
        }
        
        $modals = [];
        
        $forms = [];
        foreach ($jobs as $job) {
            $x = clone $form;
            $y = new Job($this->adapter);
            if (!$y->read(['UUID' => $job['UUID']])) {
                continue;
            }
            $x->bind($y);
            $x->setAttribute('name', $y->UUID);
            $x->add($this->generateCommentSection($y->UUID));
            $forms[] = clone $x;
            
            /**
             * Annotations
             */
            $add_annotation = new AnnotationForm();
            $add_annotation->tablename = 'job';
            $add_annotation->prikey = $job['UUID'];
            $add_annotation->user = $this->currentUser()->UUID;
            $add_annotation->init();
            
            $modals[] = $add_annotation;
        }
        $view->setVariable('modals', $modals);
        
        /**
         * Process Submission
         */
        $this->form->bind($this->model);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
            
            $this->form->setData($data);
            
            if ($this->form->isValid()) {
                $this->model->update();
                
                $this->flashmessenger()->addSuccessMessage('Update Successful');
                
                $url = $this->getRequest()->getHeader('Referer')->getUri();
                return $this->redirect()->toUrl($url);
            } else {
                foreach ($this->form->getMessages() as $message) {
                    if (is_array($message)) {
                        $message = array_pop($message);
                    }
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
        }
        
        
        $view->setVariable('jobs', $jobs);
        $view->setVariable('forms', $forms);
        
        $add_job_type = new JobTypeForm();
        $add_job_type->init();
        $view->setVariable('add_job_type_form', $add_job_type);
        
        $add_contact = new ContactForm();
        $add_contact->init();
        $view->setVariables([
            'add_contact_form' => $add_contact,
            'add_contact_title' => 'Add Contact',
            'add_contact_attributes' => [
                'action' => $this->url()->fromRoute('contact/default', ['action' => 'create']),
            ],
            'add_contact_id' => 'add_contact',
        ]);
        
        /**
         * Roster
         */
        $roster = new Roster($this->adapter);
        $roster_data = $roster->fetchEntities($session);
        foreach ($roster_data as $x => $y) {
            $roster_data[$x]['STATUS'] = ($y['RESPONSE'] > 0) ? $y['RESPONSE']: 1;
            unset($roster_data[$x]['RESPONSE']);
        }
        $view->setVariable('roster_data', $roster_data);
        unset($roster);
        unset($roster_data);
        
        /**
         * Date Filter
         */
        $date_filter = new FilterForm('DATE-FILTER');
        $date_filter->setDbAdapter($this->adapter);
        $date_filter->init();
        
        $date_filter->get('START_DATE')->setValue($start);
        $date_filter->get('END_DATE')->setValue($end);
        $date_filter->get('SESSION_UUID')->setvalue($session);
        $view->setVariable('date_filter_form', $date_filter);
        
        $view->setVariables([
            'add_annotation_title' => 'Add Annotation',
            'add_annotation_attributes' => [
                'action' => $this->url()->fromRoute('annotation/default', ['action' => 'create']),
            ],
            'add_annotation_id' => 'add_annotation',
        ]);
        $view->setVariables($this->getAnnotations());
        return $view;
    }
    
    public function createAction()
    {
        $view = new ViewModel();
        
        if ($type = $this->params()->fromRoute('uuid')) {
            $this->form->remove('TYPE_UUID');
        }
        
        $view = parent::createAction();
        
        return $view;
    }
    
    public function updateAction()
    {
        $view = new ViewModel();
        $view = parent::updateAction();
        
        /**
         * Create Customized Select Element
         * @var \Laminas\Db\Sql\Select $select
         */
        $select = new Select();
        $select->from('contact');
        $select->columns(['UUID','COMPANY','LNAME','FNAME']);
        $select->order(['COMPANY']);
        
        $where = new Where();
        $where->equalTo('TYPE_UUID', $this->model->TYPE_UUID);
        
        $select->where($where);
        
        $contact = new DatabaseSelect('CONTACT_UUID');
        $contact->setAttributes([
            'class' => 'form-select',
            'id' => 'CONTACT_UUID',
            'placeholder' => '',
        ]);
        $contact->setOptions([
            'label' => 'Contact',
            'database_object' => $select,
            'database_id_column' => 'UUID',
            'database_adapter' => $this->adapter,
        ]);
        $contact->populateElement();
        
        /**
         * Replace default Contact Select Element
         * @var JobForm $form
         */
//         $form = $this->getForm();
//         $form->remove('CONTACT_UUID');
//         $form->add($contact);
        
        return $view;
    }
   
    public function assignAction()
    {
        $emp_uuid = $this->params()->fromPost('dragId', 0);
        $job_uuid = $this->params()->fromPost('dropTarget', 0);
        
        $job = new Job($this->adapter);
        $job->read(['UUID' => $job_uuid]);
        
        $job->EMP_UUID = $emp_uuid;
        $job->STATUS = Job::ASSIGNED_STATUS;
        $job->update();
        
        $emp = new EmployeeModel($this->adapter);
        $emp->read(['UUID' => $emp_uuid]);
        
        $this->flashMessenger()->addInfoMessage(sprintf('Assigned %s to %s', $emp->EMP_NUM, $job_uuid));
        
        $rm = new Roster($this->adapter);
        $rm->read(['EMP_UUID' => $emp_uuid]);
            $LAST_POS = $rm->POSITION;
        $rm->POSITION = 99999;
        $rm->update();
        $rm->organize();
        $rm->read(['EMP_UUID' => $emp_uuid]);
            $CURR_POS = $rm->POSITION;
        
        $params = [
            'EMP_UUID' => $emp_uuid,
            'ACTION' => 'assigned',
            'USER' => $this->currentUser(),
            'LAST_POS' => $LAST_POS,
            'CURR_POS' => $CURR_POS,
        ];
        
        $this->getEventManager()->trigger('roster.update', $this, $params);
                
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function refuseAction()
    {
        $emp_uuid = $this->params()->fromRoute('uuid', 0);
        $this->flashMessenger()->addErrorMessage("Refused $emp_uuid");
        
        //-- Move to bottom of the list --//
        $rm = new Roster($this->adapter);
        $rm->read(['EMP_UUID' => $emp_uuid]);
            $LAST_POS = $rm->POSITION;
        $rm->POSITION = 99999;
        $rm->update();
        $rm->organize();
        $rm->read(['EMP_UUID' => $emp_uuid]);
            $CURR_POS = $rm->POSITION;
            
        $params = [
            'EMP_UUID' => $emp_uuid, 
            'ACTION' => 'refused', 
            'USER' => $this->currentUser(),
            'LAST_POS' => $LAST_POS,
            'CURR_POS' => $CURR_POS,
        ];
        
        $this->getEventManager()->trigger('roster.update', $this, $params);
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function ascendAction()
    {
        $emp_uuid = $this->params()->fromRoute('uuid', 0);
        $this->flashMessenger()->addSuccessMessage("Ascended $emp_uuid");
        
        //-- Move to top of the list --//
        $rm = new Roster($this->adapter);
        $rm->read(['EMP_UUID' => $emp_uuid]);
        $LAST_POS = $rm->POSITION;
        $rm->POSITION = 1;
        $rm->update();
        $rm->organize();
        $rm->read(['EMP_UUID' => $emp_uuid]);
        $CURR_POS = $rm->POSITION;
        
        $params = [
            'EMP_UUID' => $emp_uuid,
            'ACTION' => 'ascended',
            'USER' => $this->currentUser(),
            'LAST_POS' => $LAST_POS,
            'CURR_POS' => $CURR_POS,
        ];
        
        $this->getEventManager()->trigger('roster.update', $this, $params);
        
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function interestedAction()
    {
        $emp_uuid = $this->params()->fromRoute('uuid', 0);
        $this->flashMessenger()->addErrorMessage("Interested $emp_uuid");
        
        //-- Move to bottom of the list --//
        $rm = new Roster($this->adapter);
        $rm->read(['EMP_UUID' => $emp_uuid]);
        $rm->STATUS = Roster::INTERESTED_STATUS;
        $rm->update();
        
        $params = [
            'EMP_UUID' => $emp_uuid,
            'ACTION' => 'interested',
            'USER' => $this->currentUser(),
            'LAST_POS' => $rm->POSITION,
            'CURR_POS' => $rm->POSITION,
        ];
        
        $this->getEventManager()->trigger('roster.update', $this, $params);
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function notinterestedAction()
    {
        $emp_uuid = $this->params()->fromRoute('uuid', 0);
        $this->flashMessenger()->addErrorMessage("Not Interested $emp_uuid");
        
        //-- Move to bottom of the list --//
        $rm = new Roster($this->adapter);
        $rm->read(['EMP_UUID' => $emp_uuid]);
        $rm->STATUS = Roster::NOTINTERESTED_STATUS;
        $rm->update();
        
        $params = [
            'EMP_UUID' => $emp_uuid,
            'ACTION' => 'notinterested',
            'USER' => $this->currentUser(),
            'LAST_POS' => $rm->POSITION,
            'CURR_POS' => $rm->POSITION,
        ];
        
        $this->getEventManager()->trigger('roster.update', $this, $params);
        
        $url = $this->getRequest()->getHeader('Referer')->getUri();
        return $this->redirect()->toUrl($url);
    }
    
    public function filterAction()
    {
        $form = new FilterForm();
        $form->setDbAdapter($this->adapter);
        $form->init();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
                );
        }
        
        return $this->redirect()->toRoute('job/dashboard', ['start_date' => $data['START_DATE'], 'end_date' => $data['END_DATE'], 'session' => $data['SESSION_UUID']]);
    }
    
    private function generateCommentSection(string $UUID) : Textarea
    {
        $textarea = new Textarea('COMMENT', [
            'options' => [
                'form-group' => true,
                'input_group_class' => 'mb-3',
                'add_on_append' => [
                    'element' => [
                        'type' => 'button',
                        'options' => [
                            'label' => 'Add',
                            'variant' => 'outlne-secondary',
                        ],
                    ],
                ],
            ],
        ]);
        $textarea->setAttribute('class', 'form-control');
        $textarea->setAttribute('style', 'height: 160px;');
        $textarea->setLabel('Comments');
        
        $annotations = $this->getAnnotations($this->model->getTableName(), $UUID);
        
        $text = "";
        foreach ($annotations['annotations'] as $note) {
            $text .= sprintf("%s\r\n", $note['ANNOTATION']);
        }
        
        $textarea->setValue($text);
        return $textarea;
    }
}