<?php
namespace Application\Controller;

use Application\Form\Validator\Transaction as TransactionValidator;
use Application\Form\Form\Transaction as TransactionForm;
use Application\Helper\Transaction\Predict\Price as PredictPrice;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Zend\Json\Json;
use Zend\Http\PhpEnvironment\Request;

/**
 * Class TransactionController
 *
 * @package Application\Controller
 */
class TransactionController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;

    /** @var \Application\Form\Form\Transaction */
    protected $form;

    /** @var \Application\Form\Validator\Transaction */
    protected $validator;

    /** @var PredictPrice */
    protected $predictPrice;

    /** @var array */
    protected $dataList;

    /** @var TransactionHelper */
    protected $transactionHelper;

    /**
     * @return TransactionHelper
     */
    protected function getTransactionHelper()
    {
        if (null === $this->transactionHelper) {
            $this->transactionHelper = new TransactionHelper();
            $this->transactionHelper->setParams($this->params());
            $this->transactionHelper->setAbstractHelper($this->getAbstractHelper());
            $this->transactionHelper->setUserId($this->getUserId());
        }

        return $this->transactionHelper;
    }

    /**
     * @return \Application\Form\Form\Transaction
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = new TransactionForm();
            $this->form->remove('id_user');
            $this->form->remove('currency');
            $this->form->setAttribute('id', 'transactionForm');

            $formElements = $this->form->getElements();

            /** @var \Zend\Form\Element $dateElement */
            $dateElement = $formElements['date'];
            $dateElement->setValue(date('Y-m-d'));
        }

        return $this->form;
    }

    public function unsetFormData()
    {
        $elements = $this->getForm()->getElements();
        /** @var \Zend\Form\Element $element */
        foreach ($elements as $name => $element) {
            if (in_array($name, array('currency', 'date', 'submit'))) {
                continue;
            }
            $element->setValue('');
        }
    }

    /**
     * @return array
     */
    public function getDataList()
    {
        if (null === $this->dataList) {
            $this->dataList = array();
            $dataListElements = array('item', 'group');
            $elements = $this->getForm()->getElements();
            foreach (array_keys($elements) as $name) {
                if (!in_array($name, $dataListElements)) {
                    continue;
                }
                $dataValues = $this
                    ->getTransactionHelper()
                    ->getDistinctTransactionValues($name);

                $this->dataList[$name] = $dataValues;
            }
        }

        return $this->dataList;
    }

    /**
     * @return \Application\Form\Validator\Transaction
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new TransactionValidator();
            $this->validator->getInputFilter()->remove('currency');
        }

        return $this->validator;
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();
        if ($request->isPost()) {
            try {
                $this->saveTransaction($form, $request);
            } catch (\Exception $exc) {
                $this->showMessage($exc->getMessage());
            }
        }

        return array(
            'form'     => $form,
            'datalist' => $this->getDataList(),
        );
    }

    public function predictAction()
    {
        switch ($this->getTransactionHelper()->getPredict()) {
            case 'group':
                $group = $this->predictGroups();
                $price = array();
                break;
            case 'price':
                $group = array();
                $price = $this
                    ->getPredictPrice()
                    ->setTransactions($this->getTransactionHelper()->getPriceTransactions())
                    ->getPredictions();
                break;
            default:
                $group = array();
                $price = array();
                //throw new RuntimeException('Cannot predict. Missing predict parameter.');
                break;
        }

        $data = array(
            'success' => true,
            'data'    => array(
                'group' => $group,
                'price' => $price
            ),
            'error'   => ''
        );

        $response = $this->getResponse();
        $response->setContent(Json::encode($data));

        return $response;
    }

    /**
     * @param TransactionForm                   $form
     * @param \Zend\Http\PhpEnvironment\Request $request
     *
     * @throws \RuntimeException
     */
    protected function saveTransaction(
        TransactionForm $form,
        Request $request
    ) {
        $form->setInputFilter($this->getValidator()->getInputFilter());
        $form->setData($request->getPost());

        if (!$form->isValid()) {
            throw new \RuntimeException('Wrong data');
        }

        $data = $form->getData();
        $data['currency'] = $this->getDefaultUserCurrency();

        $transactionId = $request->getPost()->get('transaction_id');
        $transaction = $this->getTransactionHelper()->saveTransaction(
            $this->getUserId(),
            $transactionId,
            $data['item'],
            $data['group'],
            $data['price'],
            $data['currency'],
            $data['date']
        );

        if ($transaction->getId()) {
            $this->showMessage('Saved');
            $this->unsetFormData();
        } else {
            $this->showMessage('Failed to save');
        }
    }

    /**
     * @return array
     */
    protected function predictGroups()
    {
        $groups = array();
        $transactions = $this->getTransactionHelper()->getGroupTransactions();
        /** @var \Db\ActiveRecord $transaction */
        foreach ($transactions as $transaction) {
            $groups[] = $transaction->getData('group_name');
        }

        return $groups;
    }

    /**
     * @return PredictPrice
     */
    private function getPredictPrice()
    {
        if (null === $this->predictPrice) {
            $this->predictPrice = new PredictPrice();
        }

        return $this->predictPrice;
    }
}
