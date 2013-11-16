<?php
namespace Application\Controller;

use Application\Controller\AbstractActionController;
use Application\Form\Form\AjaxMonth;
use Application\Helper\Pie\Helper as PieHelper;
use Application\Helper\Pie\Highchart as PieHighchartHelper;
use Application\Helper\Month\Helper as MonthHelper;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Where;

/**
 * @method \Application\Helper\Pie\Helper getHelper()
 */
class PieController extends AbstractActionController
{
    /** @var array */
    protected $transactionsData;

    /** @var MonthHelper */
    protected $monthHelper;

    /** @var array */
    protected $pieChartElements;


    /**
     * @return MonthHelper
     */
    private function getMonthHelper()
    {
        if (null === $this->monthHelper) {
            $this->monthHelper = new MonthHelper();
            $this->monthHelper->setRequest($this->getRequest());
        }

        return $this->monthHelper;
    }

    /**
     * @return void
     */
    protected function init()
    {
        $pieHelper = new PieHelper();
        $pieHelper->setPieHighchartHelper(new PieHighchartHelper());

        $this->setHelper($pieHelper);
        $pieHelper->setTransactionsData($this->getTransactionsData());
    }

    /**
     * @param int $key
     *
     * @return array
     */
    protected function _getPieChartElements($key = null)
    {
        if (null === $this->pieChartElements) {
            $this->pieChartElements = array(
                0 => 'primaryPieChart',
                1 => 'secondaryPieChart',
                2 => 'tertiaryPieChart',
            );
        }

        if ($key !== null) {
            return $this->pieChartElements[$key];
        }

        return $this->pieChartElements;
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        return array(
            'form'             => $this->getForm(),
            'pieChartElements' => $this->_getPieChartElements()
        );
    }

    /**
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function ajaxAction()
    {
        $parameters = array(
            'month'         => $this->getMonthHelper()->getMonthRequestValue(),
            'targetElement' => $this->_getPieChartElements(2)
        );

        $script = $this->getHelper()->renderChart(
            $this->getPieChartTitle(),
            $this->getParam('targetElement'),
            $parameters
        );

        $data = array(
            'success' => 1,
            'script'  => $script
        );

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($data));

        return $response;
    }

    /**
     * @return \Application\Form\Form\Month
     */
    private function getForm()
    {
        $form = $this->getMonthHelper()->getMonthForm();
        $month = $form->get('month');
        $month->setValue($this->getMonthHelper()->getMonthRequestValue());

        return $form;
    }

    /**
     * @return array
     */
    private function getTransactionsData()
    {
        if (null === $this->transactionsData) {
            /** @var Select $select */
            $select = $this->getTransactionsSelect();
            $select = $this->applyTransactionSelectFilters($select);
            $select->order('price ' . Select::ORDER_DESCENDING);

            $this->transactionsData = array();

            /** @var $resultSet \Zend\Db\ResultSet\HydratingResultSet */
            $resultSet = $this->fetchTransactions($select);
            if ($resultSet->count()) {
                foreach ($resultSet AS $row) {
                    $this->transactionsData[] =  $row;
                }
            }
        }
        return $this->transactionsData;
    }


    /**
     * @return \Zend\Db\Sql\Select
     */
    private function getTransactionsSelect()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
        ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
        ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name'))
        ->join(array('c' => 'currency'), 't.id_currency = c.currency_id', array('currency_html' => 'html'))
        ->join(array('u' => 'user'), 't.id_user = u.user_id', array('email'));

        return $select;
    }


    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\Sql\Select
     */
    private function applyTransactionSelectFilters(Select $select)
    {
        $month = $this->getMonthHelper()->getMonthRequestValue();

        $timestamp = strtotime($month . '-01');
        $monthDateTimeFrom = date('Y-m-d H:i:s', $timestamp);
        $monthDateTimeTill = date('Y-m-d', strtotime($month . '-' . date('t', $timestamp))) . ' 23:59:59';

        $where = array(
            $this->getWhere()->between('date', $monthDateTimeFrom, $monthDateTimeTill),
            $this->getWhere()->expression('t.price > ?', 0)
        );

        switch ($this->getParam('type')) {
            case 'group':
                $idGroup = $this->getParam('id');
                if (!empty($idGroup)) {
                    $where[] = $this->getWhere()->expression('t.id_group = ?', $idGroup);
                } else {
                    // set type = null to get all transaction data. Use this data to get other group ids.
                    $transactionData = $this->setParam('type', null)->getTransactionsData();
                    $this->getHelper()->setTransactionsData($transactionData);

                    $allGroups = $this->getHelper()->getSortedGroups(true, 'id');
                    $visibleGroups = $this->getHelper()->getSortedGroups(false, 'id');

                    $otherGroupIds = array_diff($allGroups, $visibleGroups);

                    $where[] = $this->getWhere()->in('t.id_group', $otherGroupIds);

                    $this->getHelper()->reset();
                }
                break;
            case 'item':
                $idItem  = $this->getParam('id_item');
                $idGroup = $this->getParam('id_group');
                if (!empty($idItem)) {
                    $where[] = $this->getWhere()->expression('t.id_item = ?', $idItem);
                }
                if (!empty($idGroup)) {
                    $where[] = $this->getWhere()->expression('t.id_group = ?', $idGroup);
                }
                break;
            default:

                break;
        }

        $select->where($where);

        return $select;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

    /**
     * @param \Zend\Db\Sql\Select $select
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    private function fetchTransactions(Select $select)
    {
        $transactions = $this->getTable('transactions');
        $table = $transactions->getTable();
        $table->setTable(array('t' => 'transaction'));

        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $table->fetch($select);
        return $transactionsResults;
    }

    /**
     * @return string
     */
    protected function getPieChartTitle()
    {
        $name = $this->getParam('name', 'Pie Chart');
        $name = $this->getEscaper()->escapeHtml($name);

        return $name;
    }
}
