<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class User implements InputFilterAwareInterface
{
    protected $inputFilter;


    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->inputFilter = new InputFilter();
            $factory           = new InputFactory();

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'email',
                    'required' => false,
                ))
            );

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'month_start_date',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'NotEmpty'
                        ),
                        array(
                            'name' => 'GreaterThan',
                            'options' => array(
                                'min' => 0,
                                'max' => 31,
                            )
                        ),
                    ),
                ))
            );

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'default_currency',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'NotEmpty',
                        ),
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'min' => 3,
                                'max' => 3,
                            )
                        ),
                    ),
                ))
            );

        }

        return $this->inputFilter;
    }

}
