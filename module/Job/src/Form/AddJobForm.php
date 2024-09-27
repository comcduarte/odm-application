<?php
declare(strict_types=1);

namespace Job\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Db\Adapter\AdapterAwareTrait;

class AddJobForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
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
        
        $this->remove('STATUS');
    }
}