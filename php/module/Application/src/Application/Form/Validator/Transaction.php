<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class Transaction implements InputFilterAwareInterface
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
                    'name'     => 'item',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'NotEmpty'
                        ),
                    ),
                ))
            );

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'group',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'NotEmpty'
                        ),
                    ),
                ))
            );

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'price',
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
                            )
                        ),
                    ),
                ))
            );

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'currency',
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

            $this->inputFilter->add(
                $factory->createInput(array(
                    'name'     => 'date',
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
                            'name' => 'Date'
                        ),
                    ),
                ))
            );
        }

        return $this->inputFilter;
    }

}
