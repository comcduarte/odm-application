<?php
declare(strict_types=1);

namespace Roster\Form;

use Components\Form\AbstractBaseForm;
use Components\Form\Element\DatabaseSelect;
use Laminas\Form\Element\Text;
use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Join;

class RosterForm extends AbstractBaseForm
{
    use AdapterAwareTrait;
    
    public function init()
    {
        parent::init();
        
        $select = new Select();
        $select
            ->columns(['UUID', 'EMP_NUM', 'LNAME', 'FNAME'])
            ->from('employees')
            ->join('departments', 'employees.DEPT = departments.UUID', ['DEPT_NAME' => 'NAME'], Join::JOIN_INNER)
            ->where(['departments.CODE' => '18000']);
        
        $this->add([
            'name' => 'EMP_UUID',
            'type' => DatabaseSelect::class,
            'attributes' => [
                'class' => 'form-select',
                'id' => 'EMP_UUID',
                'required' => 'true',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Employee',
                'database_object' => $select,
                'database_table' => 'employees',
                'database_id_column' => 'UUID',
                'database_value_columns' => ['EMP_NUM','LNAME','FNAME'],
                'database_adapter' => $this->adapter,
            ],
        ],['priority' => 100]);
        
        $this->add([
            'name' => 'POSITION',
            'type' => Text::class,
            'attributes' => [
                'class' => 'form-control',
                'id' => 'POSITION',
                'placeholder' => '',
            ],
            'options' => [
                'label' => 'Position',
            ],
        ],['priority' => 100]);
    }
}