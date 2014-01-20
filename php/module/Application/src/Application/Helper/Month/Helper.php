<?php

namespace Application\Helper\Month;

use Application\Helper\AbstractHelper;
use Application\Form\Form\Month as MonthForm;
use Application\Form\Validator\Month as MonthValidator;

/**
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method string getMonthRequestValueValue()
 * @method MonthForm getMonthFormValue()
 * @method MonthValidator getMonthValidatorValue()
 * @method $this setMonthFormValue(MonthForm $monthForm)
 * @method $this setRequest(Request $request)
 * @method $this setMonthRequestValueValue(string $value)
 * @method $this setMonthValidatorValue(MonthValidator $monthValidator)
 */
class Helper extends AbstractHelper
{
    /**
     * @return \Application\Form\Form\Month
     */
    public function getMonthForm()
    {
        if (null === $this->getMonthFormValue()) {
            $this->setMonthFormValue(new MonthForm());
        }

        return $this->getMonthFormValue();
    }

    /**
     * @return \Application\Form\Validator\Month
     */
    public function getMonthValidator()
    {
        if (null === $this->getMonthValidatorValue()) {
            $this->setMonthValidatorValue(new MonthValidator());
        }

        return $this->getMonthValidatorValue();
    }

    /**
     * @return bool|string
     */
    public function getMonthRequestValue()
    {
        if (null === $this->getMonthRequestValueValue()) {
            $value = $this->getRequest()->getQuery()->get('month');

            if (empty($value)) {
                $value = date('Y-m');
            }
            $this->setMonthRequestValueValue($value);
        }

        return $this->getMonthRequestValueValue();
    }
}
