<?php
declare(strict_types=1);

namespace Job\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Text;

class JobForm extends AbstractBaseForm
{
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'JOB_NUM',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'JOB_NUM',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Job Number',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'REQUESTED_START',
            'type' => Date::class,
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
            'type' => Date::class,
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
        
        $this->add([
            'name' => 'ACTUAL_START',
            'type' => Date::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'ACTUAL_START',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Actual Start Date',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'ACTUAL_END',
            'type' => Date::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'ACTUAL_END',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Actual End Date',
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
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'TYPE_UUID',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Job Type',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'LOCATION',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'LOCATION',
                'required' => 'true',
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
                'class' => 'form-control',
                'id' => 'CRUISER',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Cruiser',
            ],
        ],['priority' => 100]);
    }
}