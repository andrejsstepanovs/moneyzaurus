<?php
namespace Application\Controller;

use Application\Helper\Chart\Helper as PieHelper;
use Application\Helper\Month\Helper as MonthHelper;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Where;
use Zend\Json\Json;
use Zend\Db\Sql\Expression;

require_once  __DIR__ . '/../../../../../library/php-ofc-library/open-flash-chart.php';

/**
 * Class ChartController
 *
 * @package Application\Controller
 */
class ChartController extends AbstractActionController
{
    /** @var array */
    protected $transactionsData;

    /** @var MonthHelper */
    protected $monthHelper;

    /** @var array */
    protected $pieChartElements;

    /** @var PieHelper */
    protected $pieHelper;

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
     * @return \Application\Form\Form\Month
     */
    private function getForm()
    {
        $form = $this->getMonthHelper()->getMonthForm();
        $month = $form->get('month');
        $month->setValue($this->getMonthHelper()->getMonthRequestValue());

        $form->remove('submit');

        return $form;
    }

    /**
     * @return PieHelper
     */
    protected function getPieHelper()
    {
        if (null === $this->pieHelper) {
            $this->pieHelper = new PieHelper();
            $this->pieHelper->setDefaultUserCurrency($this->getDefaultUserCurrency());
        }

        return $this->pieHelper;
    }

    /**
     * @param string $month
     *
     * @return string
     */
    private function getMonthDate($month = null)
    {
        $month = empty($month) ? $this->getParam('month') : $month;
        $monthValue = empty($month) ? date('Y-m') : $month;
        return $monthValue;
    }

    /**
     * @return array
     */
    public function indexAction()
    {
        return array(
            'form' => $this->getForm()
        );
    }

    /**
     * @param string $month
     *
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    private function getSumByGroupData($month)
    {
        $select = $this->getPieHelper()->getSumByGroupSelect();
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        $select->where(array($this->getWhere()->like('date', $month . '%')));

        $select->order('price ' . Select::ORDER_DESCENDING);
        $select->group('group_name');
        $select->order(new Expression('SUM(t.price) DESC'));

        return $this->fetchTransactions($select);
    }

    public function ajaxAction()
    {
        $month   = $this->getMonthDate();
        $data    = $this->getSumByGroupData($month);
        $content = $this->getPieHelper()->getChartString($month, $data);

        return $this->getResponse()->setContent($content);
    }

    public function ajaxGroupAction()
    {
        $params = explode('|', $this->getParam('data'));
        $groupId = $params[0];
        $month   = $this->getMonthDate($params[1]);

        $data = $this->getSumByGroupData($month);
        $groupName = null;
        foreach ($data as $i => $row) {
            if ($i == $groupId) {
                $groupName = $row['group_name'];
                break;
            }
        }

        $select = $this->getPieHelper()->getPaymentsByGroupSelect();
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());
        $select->where(
            array(
                $this->getWhere()->like('date', $month . '%'),
                $this->getWhere()->equalTo('g.name', $groupName),
            )
        );

        //\DEBUG::dump(@$select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $data = $this->fetchTransactions($select);

        $content = $this->getPieHelper()->getSecondaryChartString($data, $groupName, $month);

        return $this->getResponse()->setContent($content);
    }

    public function ajaxGroupHistoryAction()
    {
        $params = explode('|', $this->getParam('data'));
        $groupId = $params[0];
        $month   = $this->getMonthDate($params[1]);

        $data = $this->getSumByGroupData($month);
        $groupName = null;
        foreach ($data as $i => $row) {
            if ($i == $groupId) {
                $groupName = $row['group_name'];
                break;
            }
        }

        $select = $this->getPieHelper()->getSumByGroupSelect();
        $select->columns(
            array(
                'price' => new Expression('SUM(t.price / 100)'),
                'month' => new Expression('CONCAT(YEAR(date), "-",  MONTH(date))')
            )
        );
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());
        $select->where(array($this->getWhere()->equalTo('g.name', $groupName)));
        $select->group(new Expression('CONCAT(YEAR(date), "-",  MONTH(date))'));
        $select->order('date DESC');
        $select->limit(5);

        $data = $this->fetchTransactions($select);
        //\DEBUG::dump(@$select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $array = array();
        foreach ($data as $row) {
            $array[] = $row->getData();
        }

        $responseData = array(
            'success' => true,
            'data'    => $array
        );

        $response = $this->getResponse();
        $response->setContent(Json::encode($responseData));

        return $response;
    }

    /**
     * @param  \Zend\Db\Sql\Select                   $select
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    private function fetchTransactions(Select $select)
    {
        /** @var $transactionsResults \Zend\Db\ResultSet\HydratingResultSet */
        $transactionsResults = $this
            ->getAbstractHelper()
            ->getModel('transaction')
            ->getTable()
            ->setTable(array('t' => 'transaction'))
            ->fetch($select);

        return $transactionsResults;
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

}