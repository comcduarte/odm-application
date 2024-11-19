<?php
declare(strict_types=1);

namespace Session\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Text;

class ResponseForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'DESC',
            'type' => Text::class,
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