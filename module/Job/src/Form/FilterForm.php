<?php
declare(strict_types=1);

namespace Job\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Submit;

class FilterForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'START_DATE',
            'type' => Date::class,
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'local' => 'Start Date',
            ]]);
        
        $this->add([
            'name' => 'END_DATE',
            'type' => Date::class,
            'attributes' => [
                'class' => 'form-control',
            ],
            'options' => [
                'local' => 'End Date',
            ]]);
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
    }
}