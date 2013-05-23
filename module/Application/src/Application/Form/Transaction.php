<?php

namespace Application\Form;

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
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Item',
            ),
        ));

        $this->add(array(
            'name' => 'group',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Group',
            ),
        ));

        $this->add(array(
            'name' => 'price',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Price',
            ),
        ));

        $currency = new \Zend\Form\Element\Select('currency');
        $currency->setLabel('Currency');
        $this->add($currency);

        $this->add(array(
            'name' => 'date',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Date',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }

}