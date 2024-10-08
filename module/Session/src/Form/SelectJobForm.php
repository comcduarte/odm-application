<?php
declare(strict_types=1);

namespace Session\Form;

use Laminas\Form\Form;
use Laminas\Form\Element\Checkbox;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;

class SelectJobForm extends Form
{
    public $jobs;
    
    /**
     * @return mixed
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @param mixed $jobs
     */
    public function setJobs($jobs)
    {
        $this->jobs = $jobs;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \Laminas\Form\Element::init()
     */
    public function init()
    {
        foreach ($this->jobs as $job) {
            $this->add([
                'name' => $job['UUID'],
                'type' => Checkbox::class,
                'attributes' => [
                    'class' => 'form-check-input',
                    'id' => $job['UUID'],
                    'value' => $job['UUID'],
                ],
                'options' => [
                    'switch' => true,
                    'label' => sprintf('%s-%s:%s', $job['REQUESTED_START'], $job['REQUESTED_END'], $job['CONTACT']),
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
        }
        
        $this->add(new Csrf('SECURITY'),['priority' => 0]);
        
        $this->add([
            'name' => 'SUBMIT',
            'type' => Submit::class,
            'attributes' => [
                'value' => 'Submit',
                'class' => 'btn btn-primary form-control mt-4',
                'id' => 'SUBMIT',
            ],
        ],['priority' => 0]);
    }
    
}