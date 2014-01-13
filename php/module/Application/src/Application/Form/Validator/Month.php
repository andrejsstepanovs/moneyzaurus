<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class Month implements InputFilterAwareInterface
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
                    'name'     => 'month',
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
//                    'validators' => array(
//                        array(
//                            'name' => 'NotEmpty',
//                        ),
//                        array(
//                            'name' => 'StringLength',
//                            'options' => array(
//                                'min' => 6,
//                                'max' => 6,
//                            )
//                        ),
//                    ),
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
