<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class Transaction
 *
 * @package Application\Form\Form
 */
class Transaction extends Form
{
    public function __construct($name = 'transaction', $options = array())
    {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name'       => 'item',
                'attributes' => array(
                    'type'         => 'text',
                    'id'           => 'item',
                    'placeholder'  => 'Item name',
                    'required'     => 'required',
                    'autocomplete' => 'off',
                ),
                'options'    => array(
                    'label' => 'Item',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'group',
                'attributes' => array(
                    'type'        => 'text',
                    'id'          => 'group',
                    'placeholder' => 'Group name',
                    'required'    => 'required',
                    'autocomplete' => 'off',
                ),
                'options'    => array(
                    'label' => 'Group',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'price',
                'attributes' => array(
                    'id'          => 'price',
                    'type'        => 'text',
                    'placeholder' => '0.00 €',
                    'required'    => 'required',
                    'alt'         => 'decimal',
                    'step'        => '0.01',
                ),
                'options'    => array(
                    'label' => 'Price',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'currency',
                'type'       => 'Zend\Form\Element\Select',
                'attributes' => array(
                    'id' => 'currency',
                ),
                'options'    => array(
                    'label' => 'Currency',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'date',
                'attributes' => array(
                    'type'        => 'Zend\Form\Element\Date',
                    'id'          => 'date',
                    'placeholder' => 'YYYY-MM-DD',
                ),
                'options'    => array(
                    'label' => 'Date',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'id_user',
                'attributes' => array(
                    'type'        => 'Zend\Form\Element\Hidden',
                    'id'          => 'id_user',
                    'placeholder' => 'User',
                ),
                'options'    => array(
                    'label' => 'User Id',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'submit',
                'attributes' => array(
                    'type'         => 'submit',
                    'id'           => 'submit',
                    'value'        => 'Save',
                    'data-icon'    => 'check',
                    'data-iconpos' => 'left'
                ),
            )
        );
    }

}
