<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Varient\Database\ActiveRecord\ActiveRecord;

class IndexController extends AbstractActionController
{
    protected $transactionTable;

    public function indexAction()
    {
        $activeRecord = $this->getServiceLocator()->get('ActiveRecord');
        $activeRecord->setTableName('item');
        $activeRecord->load(15);

        $activeRecord->save();
        $d = $activeRecord->delete();


        \DEBUG::dump($activeRecord->getData(), $d);



        \DEBUG::dump($a);

    }
}
