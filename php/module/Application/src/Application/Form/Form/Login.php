<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class Login
 *
 * @package Application\Form\Form
 */
class Login extends Form
{
    public function __construct($name = 'login-form', $options = array())
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
                'name'       => 'password',
                'attributes' => array(
                    'id'          => 'login-password',
                    'type'        => 'password',
                    'placeholder' => 'Password',
                    'required'    => 'required',
                ),
                'options'    => array(
                    'label' => 'Password',
                ),
            )
        );

        $this->add(
            array(
                'name'       => 'submit',
                'attributes' => array(
                    'id'    => 'login-submit',
                    'type'  => 'submit',
                    'value' => 'Sign in',
                ),
            )
        );
    }
}
