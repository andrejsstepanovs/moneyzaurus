<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class Month
 *
 * @package Application\Form\Validator
 */
class Month implements InputFilterAwareInterface
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
                        'name'     => 'month',
                        'required' => true,
                        'filters'  => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                    )
                )
            );

            $this->inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'date',
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
                                'name' => 'Date'
                            ),
                        ),
                    )
                )
            );
        }

        return $this->inputFilter;
    }

}
