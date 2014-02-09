<?php

namespace Application\Form\Form;

/**
 * Class User
 *
 * @package Application\Form\Form
 */
class User extends Register
{
    public function __construct($name = 'users', $options = array())
    {
        parent::__construct($name, $options);
    }
}
