<?php

namespace Application\Form\Form;

use Zend\Form\Form;


class AjaxMonth extends Month
{
    public function __construct()
    {
        parent::__construct();

        $submit = $this->get('submit');

        $this->add(array(
                        'name' => 'submit',
                        'attributes' => array(
                            'type'         => 'submit',
                            'id'           => 'submit',
                            'value'        => 'Search',
                            'data-icon'    => 'plus',
                            'data-iconpos' => 'left',
                            'data-ajax'    => 'false',
                            'class' => 'disable-ajax'
                        ),
                   ));
    }
}