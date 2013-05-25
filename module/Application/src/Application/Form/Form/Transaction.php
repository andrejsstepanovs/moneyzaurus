<?php

namespace Application\Form\Form;

use Zend\Form\Form;


class Transaction extends Form
{
    public function __construct()
    {
        parent::__construct('login');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'item',
            'attributes' => array(
                'type'        => 'text',
                'id'          => 'item',
                'placeholder' => 'Item name',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Item',
            ),
        ));

        $this->add(array(
            'name' => 'group',
            'attributes' => array(
                'id'          => 'group',
                'type'        => 'text',
                'placeholder' => 'Group name',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Group',
            ),
        ));

        $this->add(array(
            'name' => 'price',
            'attributes' => array(
                'id'          => 'price',
                'type'        => 'text',
                'placeholder' => '0.00',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Price',
            ),
        ));

        $this->add(array(
            'name' => 'currency',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'currency',
            ),
            'options' => array(
                'label' => 'Currency',
            ),
        ));

        $this->add(array(
            'name' => 'date',
            'attributes' => array(
                'type' => 'text',
                'id'   => 'date',
                'placeholder' => date('Y-m-d'),
            ),
            'options' => array(
                'label' => 'Date',
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