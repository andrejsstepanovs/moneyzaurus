<?php
namespace Application\Controller;

use Application\Form\Validator\Transaction as TransactionValidator;
use Application\Form\Form\Transaction as TransactionForm;
use Application\Controller\AbstractActionController;
use Application\Exception;


class TransactionController extends AbstractActionController
{
    /** @var array */
    protected $activeRecords = array();

    /** @var integer */
    protected $userId;

    /** @var \Application\Form\Transaction */
    protected $form;

    /** @var \Application\Form\Validator\Transaction */
    protected $validator;

    /** @var array */
    protected $datalist;


    /**
     * @return \Application\Form\Transaction
     */
    public function getForm()
    {
        if (null === $this->form) {
            $this->form = new TransactionForm();

            $formElements = $this->form->getElements();

            $currencyElement = $formElements['currency'];
            $dateElement     = $formElements['date'];

            $currencyElement->setValueOptions($this->getCurrencyValueOptions());
            $dateElement->setValue(date('Y-m-d'));
        }

        return $this->form;
    }

    public function unsetFormData()
    {
        $elements = $this->getForm()->getElements();
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
    public function getDatalist()
    {
        if (null === $this->datalist) {
            $this->datalist = array();
            $datalistElements = array(
                'item'  => 'name',
                'group' => 'name'
            );
            $elements = $this->getForm()->getElements();
            foreach ($elements as $name => $element) {
                if (!array_key_exists($name, $datalistElements)) {
                    continue;
                }
                $column = $datalistElements[$name];

                /** @var $table \Db\Db\AbstractTable */
                /** @var $results \Zend\Db\ResultSet\HydratingResultSet */
                $table = $this->getTable($name)->getTable();
                $results = $table->fetchUniqeColum($column, array(
                    'id_user' => $this->getUserId()
                ));

                $dataValues = array();
                foreach ($results as $model) {
                    $dataValues[] = $model->getData($column);
                }

                $this->datalist[$name] = $dataValues;
            }
        }

        return $this->datalist;
    }

    /**
     * @return \Application\Form\Validator\Transaction
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new TransactionValidator();
        }

        return $this->validator;
    }

    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setInputFilter($this->getValidator()->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $data = $form->getData();
                try {

                    $transaction = $this->saveTransaction(
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

                } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
                    $this->showMessage('Data missing');
                } catch (Exception $exc) {
                    $this->showMessage($exc->getMessage());
                }

            } else {
                $this->flashmessenger()->addMessage('Wrong data');
            }
        }

        return array(
            'form'     => $form,
            'datalist' => $this->getDatalist(),
        );
    }

    /**
     * @param string $item
     * @param string $group
     * @param float $price
     * @param string $currency
     * @param date $date
     * @return \Db\Db\ActiveRecord transaction
     */
    protected function saveTransaction(
            $itemName,
            $groupName,
            $price,
            $currencyId,
            $date
    ) {
        $currency = $this->getTable('currency')
                         ->setId($currencyId)
                         ->load();

        $item = $this->getTable('item');
        try {
            $item->setName($itemName)
                 ->setIdUser($this->getUserId())
                 ->load();
        } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
            $item->save();
        }

        $group = $this->getTable('group');
        try {
            $group->setName($groupName)
                  ->setIdUser($this->getUserId())
                  ->load();
        } catch (\Db\Db\Exception\ModelNotFoundException $exc) {
            $group->save();
        }

        return $this->getTable('transaction')
                    ->setPrice($price)
                    ->setDate($date)
                    ->setIdUser($this->getUserId())
                    ->setIdItem($item->getId())
                    ->setIdGroup($group->getId())
                    ->setIdCurrency($currency->getId())
                    ->save();
    }

}
