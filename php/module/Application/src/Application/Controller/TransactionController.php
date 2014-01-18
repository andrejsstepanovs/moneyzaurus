<?php
namespace Application\Controller;

use Application\Form\Validator\Transaction as TransactionValidator;
use Application\Form\Form\Transaction as TransactionForm;
use Application\Controller\AbstractActionController;
use Application\Exception;
use Application\Helper\Transaction\Helper as TransactionHelper;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\Exception\RuntimeException;

/**
 * @method \Application\Helper\Transaction\Helper getHelper()
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

    /** @var array */
    protected $dataList;

    /** @var array */
    protected $_whereFilter;

    /**
     * @return void
     */
    protected function init()
    {
        $helper = new TransactionHelper();
        $helper->setParams($this->params());
        $this->setHelper($helper);
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

            if (array_key_exists('currency', $formElements)) {
                $currencyElement = $formElements['currency'];
                $currencyElement->setValueOptions($this->getCurrencyValueOptions());
            }

            $dateElement = $formElements['date'];
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
        if (null === $this->dataList) {
            $this->dataList = array();
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

    public function indexAction()
    {
        $form = $this->getForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $form->setInputFilter($this->getValidator()->getInputFilter());
            $form->setData($request->getPost());

            $transactionId = $request->getPost()->get('transaction_id');

            if ($form->isValid()) {

                $data = $form->getData();
                $data['currency'] = $this->getDefaultUserCurrency();
                try {
                    $transaction = $this->saveTransaction(
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
     * @param int    $transactionId
     * @param string $item
     * @param string $group
     * @param float  $price
     * @param string $currency
     * @param date   $date
     * @return \Db\Db\ActiveRecord transaction
     */
    protected function saveTransaction(
            $transactionId,
            $itemName,
            $groupName,
            $price,
            $currencyId,
            $date
    ) {
        if ($transactionId == 0) {
            $transactionId = null;
        }

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

        return $this
            ->getTable('transaction')
            ->setTransactionId($transactionId)
            ->setPrice($price)
            ->setDate($date)
            ->setIdUser($this->getUserId())
            ->setIdItem($item->getId())
            ->setIdGroup($group->getId())
            ->setIdCurrency($currency->getId())
            ->save();
    }

    public function predictAction()
    {
        switch ($this->getHelper()->getPredict()) {
            case 'group':
                $group = $this->_predictGroups();
                $price = array();
                break;
            case 'price':
                $group = array();
                $price = $this->_predictPrice();
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
        $response->setContent(\Zend\Json\Json::encode($data));

        return $response;
    }

    /**
     * @return array
     */
    protected function _predictGroups()
    {
        $groups = array();
        $transactions = $this->getGroupTransactions();
        /** \Db\Db\ActiveRecord */
        foreach ($transactions as $transaction) {
            $groups[] = $transaction->getData('group_name');
        }

        return $groups;
    }

    /**
     * @return array
     */
    protected function _predictPrice()
    {
        $data = array('by_count' => array(), 'by_day' => array());

        $transactions = $this->getPriceTransactions();
        /** \Db\Db\ActiveRecord */
        foreach ($transactions as $transaction) {
            $day = $transaction->getData('day_of_the_week');
            $price = $transaction->getData('price');

            if (empty($data['by_count'][$price])) {
                $data['by_count'][$price] = 1;
            } else {
                $data['by_count'][$price] = $data['by_count'][$price] + 1;
            }

            if (empty($data[$day][$price])) {
                $data['by_day'][$day][$price] = 1;
            } else {
                $data['by_day'][$day][$price] = $data[$day][$price] + 1;
            }
        }

        $allPrices = array_keys($data['by_count']);

        asort($data['by_count']);

        $prices = array();
        $prices[] = end(array_keys($data['by_count'])); // most popular

        $currentDay = date('w') + 1;
        if (array_key_exists($currentDay, $data['by_day'])) {
            $pricesInThisDay = array_keys($data['by_day'][$currentDay]);
            $prices[] = reset($pricesInThisDay); // last used in this day
            $prices[] = next($pricesInThisDay);  // next last used

            sort($pricesInThisDay);
            $prices[] = end($pricesInThisDay); // most popular in this day
        }

        $prices[] = next(array_keys($data['by_count'])); // next most popular
        $prices[] = next(array_keys($data['by_count'])); // next most popular
        $prices[] = max($allPrices);

        $prices = array_filter($prices, function($val){ return empty($val) ? false : true; });

        sort($prices);
        $prices = array_unique($prices);
        return $prices;
    }

    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getGroupTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('times_used' => new Expression("COUNT(*)")))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array())
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
               ->group('g.name')
               ->order(new Expression("COUNT(*) DESC"))
               ->limit(5);

        $where = $this->_getWhereFilter();
        if (count($where)) {
            $select->where($where);
        }

        //\DEBUG::dump($select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }


    /**
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    protected function getPriceTransactions()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('price', 'day_of_the_week' => new Expression('DAYOFWEEK(t.date)')))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array())
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array())
               ->order($this->getHelper()->getOrderBy() . ' ' . $this->getHelper()->getOrder())
               //->limit(100)
               ;

        $where = $this->_getWhereFilter();
        if (count($where)) {
            $select->where($where);
        }

        //\DEBUG::dump($select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable($transactionTable);

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select)->buffer();

        return $transactionsResults;
    }

    /**
     * @return array
     */
    protected function _getWhereFilter()
    {
        if (null === $this->_whereFilter) {
            $item   = $this->getHelper()->getItem();
            $group  = $this->getHelper()->getGroup();
            $idUser = $this->getUserId();

            $where = array();

            if (!empty($item)) {
                $where[] = $this->getWhere()->equalTo('i.name', $item);
            }

            if (!empty($group)) {
                $where[] = $this->getWhere()->equalTo('g.name', $group);
            }

            //$where[] = $this->getWhere()->greaterThan('t.date', date('Y-m-d H:i:s', strtotime('-1 year')));

            $where[] = $this->getWhere()->equalTo('t.id_user', $idUser);

            $this->_whereFilter = $where;
        }

        return $this->_whereFilter;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

}