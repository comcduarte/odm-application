<?php
declare(strict_types=1);

namespace Job\Form;

use Components\Form\AbstractBaseForm;
use Laminas\Form\Element\Text;

class JobTypeForm extends AbstractBaseForm
{
    public function init()
    {
        parent::init();
        
        $this->add([
            'name' => 'TYPE',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'LOCATION',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Type',
            ],
        ],['priority' => 100]);
    }
}