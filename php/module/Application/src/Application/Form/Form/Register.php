<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class Register
 *
 * @package Application\Form\Form
 */
class Register extends Form
{
    public function __construct()
    {
        parent::__construct('register-form');

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name'       => 'email',
                'type'       => 'Zend\Form\Element\Email',
                'attributes' => array(
                    'id'           => 'register-username',
                    'placeholder'  => 'Email',
                    'required'     => 'required',
                    'autocomplete' => 'off'
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
                    'id'           => 'register-password',
                    'type'         => 'password',
                    'placeholder'  => 'Password',
                    'required'     => 'required',
                    'autocomplete' => 'off'
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
                    'id'    => 'register-submit',
                    'type'  => 'submit',
                    'value' => 'Sign up',
                ),
            )
        );
    }
}
