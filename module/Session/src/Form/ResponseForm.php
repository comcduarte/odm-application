<?php
declare(strict_types=1);

namespace Session\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Text;
use Laminas\Form\Element\Submit;

class ResponseForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'YES',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'YES',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'YES',
            ],
        ],['priority' => 0]);
        
        $this->add([
            'name' => 'NO',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'No',
                'class' => 'btn btn-secondary form-control mt-4',
                'id' => 'NO',
            ],
        ],['priority' => 0]);
    }
}