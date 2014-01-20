<?php

namespace Application\Form\Validator;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class Login
 *
 * @package Application\Form\Validator
 */
class Login implements InputFilterAwareInterface
{
    /** @var InputFilterInterface */
    protected $inputFilter;

    /**
     * @param InputFilterInterface $inputFilter
     *
     * @return $this
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        return $this;
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
                        'name'       => 'email',
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
                                'name' => 'EmailAddress',
                            ),
                        ),
                    )
                )
            );

            $this->inputFilter->add(
                $factory->createInput(
                    array(
                        'name'       => 'password',
                        'required'   => true,
                        'filters'    => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'NotEmpty'
                            ),
                        ),
                    )
                )
            );
        }

        return $this->inputFilter;
    }
}
