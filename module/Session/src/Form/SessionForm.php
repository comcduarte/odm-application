<?php
declare(strict_types=1);

namespace Session\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\DateTimeLocal;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Textarea;

class SessionForm extends AbstractBaseForm
{
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'DATE_START',
            'type' => DateTimeLocal::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DATE_START',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Start Date and Time',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DATE_CLOSE',
            'type' => DateTimeLocal::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DATE_CLOSE',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Close Date and Time',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'NAME',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'NAME',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Session Name',
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'DESC',
            'type' => Textarea::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'DESC',
            ],
            'options' => [
                'label' => 'Description',
            ],
        ],['priority' => 100]);
    }
}