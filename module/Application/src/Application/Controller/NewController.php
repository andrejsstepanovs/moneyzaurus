<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;

use Application\Helper\Purchase;

class NewController extends AbstractActionController
{
    protected $transactionTable;

    public function indexAction()
    {

    }

    public function createAction()
    {
        $data = array(
            'item' => 'Maize',
            'group' => 'Pārtika',
            'price' => '0.10',
            'date' => '2012-01-01',
        );

        $purchaseHelper = $this->getServiceLocator()->get('Application\Helper\Purchase');
        $purchaseModel = $purchaseHelper->save(
                $this->zfcUserAuthentication()->getIdentity()->getId(),
                $itemValue,
                $groupValue,
                $priceValue,
                $currencyCode,
                $dateValue
        );

        return array(
            'purchase' => $purchaseModel,
        );
    }
}
