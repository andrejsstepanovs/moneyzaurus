<?php

namespace Application\Form\Form;

use Zend\Form\Form;


class Login extends Form
{
    public function __construct()
    {
        parent::__construct('login');

        $this->setAttribute('method', 'post');

        $this->add(array(
            'name' => 'email',
            'type' => 'Zend\Form\Element\Email',
            'attributes' => array(
                'id'          => 'email',
                'placeholder' => 'Email',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Email',
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'attributes' => array(
                'id'          => 'password',
                'type'        => 'password',
                'placeholder' => 'Password',
                'required'    => 'required',
            ),
            'options' => array(
                'label' => 'Password',
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'id'    => 'submit',
                'type'  => 'submit',
                'value' => 'Login',
            ),
        ));
    }

}