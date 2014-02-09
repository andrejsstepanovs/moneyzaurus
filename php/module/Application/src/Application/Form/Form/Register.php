<?php

namespace Application\Form\Form;

/**
 * Class Register
 *
 * @package Application\Form\Form
 */
class Register extends Login
{
    public function __construct($name = 'register-form', $options = array())
    {
        parent::__construct($name, $options);
    }
}
