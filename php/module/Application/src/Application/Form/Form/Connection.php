<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class Connection
 *
 * @package Application\Form\Form
 */
class Connection extends Form
{
    public function __construct($name = 'connection', $options = array())
    {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name'       => 'email',
                'type'       => 'Zend\Form\Element\Email',
                'attributes' => array(
                    'id'          => 'login-username',
                    'placeholder' => 'Email',
                    'required'    => 'required',
                ),
                'options'    => array(
                    'label' => 'Email',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'message',
                'attributes' => array(
                    'id'          => 'message',
                    'type'        => 'textarea',
                    'placeholder' => 'Message',
                    'required'    => 'required',
                ),
                'options'    => array(
                    'label' => 'Message',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'submit',
                'attributes' => array(
                    'id'    => 'connection-submit',
                    'type'  => 'submit',
                    'value' => 'Send',
                ),
            )
        );
    }
}
