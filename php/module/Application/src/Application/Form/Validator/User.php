<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class User
 *
 * @package Application\Form\Validator
 */
class User implements InputFilterAwareInterface
{
    /** @var InputFilterInterface */
    protected $inputFilter;

    /**
     * @param InputFilterInterface $inputFilter
     *
     * @return void|InputFilterAwareInterface
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
    }

    /**
     * @return InputFilter|InputFilterInterface
     */
    public function getInputFilter()
    {
        if (null === $this->inputFilter) {
            $this->inputFilter = new InputFilter();
            $factory = new InputFactory();

            $this->inputFilter->add(
                $factory->createInput(
                    array(
                        'name'     => 'email',
                        'required' => false,
                    )
                )
            );

            $this->inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'month_start_date',
                        'required'   => true,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty'
                            ),
                            array(
                                'name'    => 'GreaterThan',
                                'options' => array(
                                    'min' => 0,
                                    'max' => 31,
                                )
                            )
                        )
                    )
                )
            );

            $this->inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'default_currency',
                        'required'   => true,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty',
                            ),
                            array(
                                'name'    => 'StringLength',
                                'options' => array(
                                    'min' => 3,
                                    'max' => 3,
                                )
                            )
                        )
                    )
                )
            );

        }

        return $this->inputFilter;
    }
}
