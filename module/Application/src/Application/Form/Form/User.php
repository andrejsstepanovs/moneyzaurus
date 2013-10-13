<?php

namespace Application\Form\Form;

use Zend\Form\Form;


class User extends Form
{
    public function __construct()
    {
        parent::__construct('users');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'attributes' => array(
                'type'        => 'text',
                'id'          => 'email',
                'placeholder' => 'email@email.com',
                'disabled'    => 'disabled',
            ),
            'options' => array(
                'label' => 'Email',
            ),
        ));

        $this->add(array(
            'name' => 'month_start_date',
            'attributes' => array(
                'type'        => 'number',
                'id'          => 'month_start_date',
                'placeholder' => 'Month start date',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Month start date',
            ),
        ));

        $this->add(array(
            'name' => 'default_currency',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'default_currency',
            ),
            'options' => array(
                'label' => 'Default Currency',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'         => 'submit',
                'id'           => 'submit',
                'value'        => 'Save',
                'data-icon'    => 'plus',
                'data-iconpos' => 'left'
            ),
        ));
    }

}