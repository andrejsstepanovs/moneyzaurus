<?php

namespace Application\Form\Form;

use Zend\Form\Form;

/**
 * Class ResendPassword
 *
 * @package Application\Form\Form
 */
class ResendPassword extends Form
{
    public function __construct($name = 'resend-password-form', $options = array())
    {
        parent::__construct($name, $options);

        $this->setAttribute('method', 'post');

        $this->add(
            array(
                'name'       => 'email',
                'type'       => 'Zend\Form\Element\Email',
                'attributes' => array(
                    'id'          => 'resend-password-email',
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
                'name'       => 'submit',
                'attributes' => array(
                    'id'    => 'resend-password-submit',
                    'type'  => 'submit',
                    'value' => 'Submit',
                )
            )
        );
    }
}
