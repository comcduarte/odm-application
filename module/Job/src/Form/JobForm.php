<?php
declare(strict_types=1);

namespace Job\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Job\Model\Job;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\Form\Element\Text;
use Roster\Model\Roster;

class JobForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $status = $this->get('STATUS');
        $status->setOptions([
            'value_options' => [
                Job::INACTIVE_STATUS => 'Inactive',
                Job::ACTIVE_STATUS => 'Active',
                Job::CANCELED_STATUS => 'Canceled',
                Job::OPEN_STATUS => 'Open',
                Job::UNABLE_TO_FILL_STATUS => 'Unable to Fill',
                Job::WORK_REASON_STATUS => 'Work Reason',
            ],
        ]);
        $status->setAttribute('class', 'form-select form-select-sm');
        
        $this->add([
            'name' => 'REQUESTED_START',
            'type' => DateTimeLocal::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'REQUESTED_START',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Requested Start Date',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'REQUESTED_END',
            'type' => DateTimeLocal::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'REQUESTED_END',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Requested End Date',
            ],
        ],['priority' => 100]);
        
//         $this->add([
//             'name' => 'ACTUAL_START',
//             'type' => DateTimeLocal::class,
//             'attributes' => [
//                 'class' => 'form-control',
//                 'id' => 'ACTUAL_START',
//                 'placeholder' => '',
//             ],
//             'options' => [
//                 'label' => 'Actual Start Date',
//             ],
//         ],['priority' => 100]);
        
//         $this->add([
//             'name' => 'ACTUAL_END',
//             'type' => DateTimeLocal::class,
//             'attributes' => [
//                 'class' => 'form-control',
//                 'id' => 'ACTUAL_END',
//                 'placeholder' => '',
//             ],
//             'options' => [
//                 'label' => 'Actual End Date',
//             ],
//         ],['priority' => 100]);
        
        $this->add([
            'name' => 'CONTACT',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'CONTACT',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Contact',
            ],
        ],['priority' => 100]);
        
//         $this->add([
//             'name' => 'COMPANY',
//             'type' => Text::class,
//             'attributes' => [
//                 'class' => 'form-control',
//                 'id' => 'COMPANY',
//                 'placeholder' => '',
//             ],
//             'options' => [
//                 'label' => 'Company',
//             ],
//         ],['priority' => 100]);
        
        $this->add([
            'name' => 'COMPANY_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'COMPANY_UUID',
            ],
            'options' => [
                'label' => 'Company',
                'database_adapter' => $this->adapter,
                'database_table' => 'job_company',
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'NAME',
                ],
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'PO',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'PO',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Purchase Order',
            ],
        ],['priority' => 100]);
        
        
        $this->add([
            'name' => 'TYPE_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-select',
                'id' => 'TYPE_UUID',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Job Type',
                'database_table' => 'job_type',
                'database_id_column' => 'UUID',
                'database_value_columns' => ['TYPE'],
                'database_adapter' => $this->adapter,
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'LOCATION',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'LOCATION',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Location',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'CRUISER',
            'type' => Checkbox::class,
            'attributes' => [
                'class' => 'form-check-input',
                'id' => 'CRUISER',
            ],
            'options' => [
                'switch' => true,
                'label' => 'Cruiser',
                'label_attributes' => [
                    'class' => 'form-check-label ms-2',
                ],
                'label_options' => [
                    'label_position' => 'append',
                ],
                'use_hidden_element' => true,
                'use_input_group' => true,
            ],
        ],['priority' => 100]);
        
        $roster = new Roster($this->adapter);
        $roster->fetchEntities();
        
        $this->add([
            'name' => 'EMP_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-select',
                'id' => 'EMP_UUID',
                'placeholder' => '',
//                 'disabled' => true,
            ],
            'options' => [
                'label' => 'Assigned Officer',
                'database_object' => $roster->getSelect(),
//                 'database_table' => 'employees',
                'database_id_column' => 'UUID',
                'database_value_columns' => ['EMP_NUM', 'LNAME', 'FNAME'],
                'database_adapter' => $this->adapter,
            ],
        ],['priority' => 100]);
    }
}