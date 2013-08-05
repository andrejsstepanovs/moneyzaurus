<?php

namespace Application\Form\Form;

use Zend\Form\Form;


class Month extends Form
{
    public function __construct()
    {
        parent::__construct('month');

        $this->setAttribute('method', 'get');

        $this->add(array(
            'name' => 'month',
            'type' => 'Zend\Form\Element\Month',
            'attributes' => array(
                'id'  => 'month',
                'max' => date('Y-m'),
            ),
            'options' => array(
                'label' => 'Month',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'         => 'submit',
                'id'           => 'submit',
                'value'        => 'Search',
                'data-icon'    => 'plus',
                'data-iconpos' => 'left',
                'data-ajax'    => 'false',
                'class' => 'disable-ajax'
            ),
        ));
    }

}