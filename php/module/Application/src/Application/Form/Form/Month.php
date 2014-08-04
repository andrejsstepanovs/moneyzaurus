<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class Month
 *
 * @package Application\Form\Form
 */
class Month extends Form
{
    public function __construct($name = 'month', $options = array())
    {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'get');

        $this->add(
            array(
                'name'       => 'month',
                'type'       => 'Zend\Form\Element\Month',
                'attributes' => array(
                    'id'  => 'month',
                    //'max' => date('Y-m'),
                ),
                'options'    => array(
                    'label' => 'Month',
                ),
            )
        );

        $this->add(
            array(
            'name'       => 'submit',
            'attributes' => array(
                    'type'         => 'submit',
                    'id'           => 'submit',
                    'value'        => 'Search',
                    'data-icon'    => 'search',
                    'data-iconpos' => 'left',
                    'data-ajax'    => 'false',
                    'class'        => 'disable-ajax'
                ),
            )
        );
    }
}
