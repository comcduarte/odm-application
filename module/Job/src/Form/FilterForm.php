<?php
declare(strict_types=1);

namespace Job\Form;

use Components\Form\Element\DatabaseSelect;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Laminas\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Date;
use Laminas\Form\Element\Submit;
use Session\Model\Session;

class FilterForm extends Form
{
    use AdapterAwareTrait;
    
    public function init()
    {
        $session_select = new Select();
        $session_select->columns(['UUID', 'NAME']);
        $session_select->from('session');
        $where = new Where();
        $where->equalTo('STATUS', session::ACTIVE_STATUS);
        $session_select->where($where);
        
        
        $this->add([
            'name' => 'SESSION_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'SESSION_UUID',
            ],
            'options' => [
                'label' => 'Session',
                'database_adapter' => $this->adapter,
                'database_object' => $session_select,
                'database_id_column' => 'UUID',
                'database_value_columns' => [
                    'NAME',
                ],
            ],
        ],['priority' => 100]);
        
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