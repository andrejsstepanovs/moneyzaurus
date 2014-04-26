<?php
namespace Application\Controller;

use Application\Helper\Pie\Helper as PieHelper;
use Application\Helper\Pie\Highchart as PieHighchartHelper;
use Application\Helper\Month\Helper as MonthHelper;
use Zend\Db\Sql\Select as Select;
use Zend\Db\Sql\Where;
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
            $this->pieHelper->setPieHighchartHelper(new PieHighchartHelper());
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

    public function ajaxAction()
    {
        $month = $this->getMonthDate();

        $select = $this->getPieHelper()->getSumByGroupSelect();
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        $select->where(array($this->getWhere()->like('date', $month . '%')));

        $select->order('price ' . Select::ORDER_DESCENDING);
        $select->group('name');
        $select->order(new Expression('SUM(t.price) DESC'));

        //\DEBUG::dump(@$select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $data = $this->fetchTransactions($select);

        $colors = $values = array();
        foreach ($data as $row) {
            $values[] = new \pie_value($row['price'] * 100 / 100, $row['group_name'].' ('.sprintf('%0.2f', $row['price']).')');//teksts blakus riņķim
            $colors[] = $this->getRandomHex();
        }

        $pie = new \pie();
        $pie->set_tooltip('#val# Ls of #total# ' . $this->getDefaultUserCurrency() . '<br>#percent# of 100%');

        $pie->add_animation(new \pie_bounce(20));
        $pie->start_angle(0);

        $pie->set_colours($colors);
        $pie->set_values($values);
        $pie->on_click('site.chart.getSubPie');

        $chart = new \open_flash_chart();

        $title = new \title('Groups: ' . $month);
        $title->set_style("{font-size:20px;font-family:Times New Roman;font-weight:bold;color:#A2ACBA;text-align:center;}");

        $chart->set_title($title);
        $chart->add_element($pie);
        $chart->set_bg_colour('#ffffff');

        $chart->x_axis = null;

        return $this->getResponse()->setContent($chart->toPrettyString());
    }

    public function ajaxGroupAction()
    {
        $data = explode('|', $this->getParam('data'));

        $groupId = $data[0];
        $month   = $this->getMonthDate($data[1]);

        $select = $this->getPieHelper()->getSumByGroupSelect();
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());

        $select->where(array($this->getWhere()->like('date', $month . '%')));

        $select->order('price ' . Select::ORDER_DESCENDING);
        $select->group('name');
        $select->order(new Expression('SUM(t.price) DESC'));

        $data = $this->fetchTransactions($select);

        foreach ($data as $i => $row) {
            if ($i == $groupId) {
                break;
            }
        }

        $groupName = $row['group_name'];

        $where = array(
            $this->getWhere()->like('date', $month . '%'),
            $this->getWhere()->equalTo('id_group', $row['group_id']),
        );

        $select = $this->getPieHelper()->getPaymentsByGroupSelect();
        $select = $this->getAbstractHelper()->addTransactionUserFilter($select, $this->getUserId());
        $select->where($where);

        //\DEBUG::dump(@$select->getSqlString(new \Zend\Db\Adapter\Platform\Mysql()));

        $data = $this->fetchTransactions($select);

        $colors = $values = array();
        foreach ($data as $row) {
            $values[] = new \pie_value($row['price'] * 100 / 100, $row['item_name'].' ('.$row['price'].') '.date('d.M', strtotime($row['date'])));//teksts blakus riņķim
            $colors[] = $this->getRandomHex();
        }

        $pie = new \pie();
        $pie->set_tooltip('#val# Ls of #total# ' . $this->getDefaultUserCurrency() . '<br>#percent# of 100%');

        $pie->alpha(0.5);
        $pie->start_angle(0);

        $pie->set_colours($colors);
        $pie->set_values($values);

        $chart = new \open_flash_chart();

        $title = new \title(ucfirst($groupName) . ': ' . $month);

        $title->set_style("{font-size:20px;font-family:Times New Roman;font-weight:bold;color:#A2ACBA;text-align:center;}");

        $chart->set_title($title);
        $chart->add_element($pie);
        $chart->set_bg_colour('#ffffff');

        $chart->x_axis = null;

        return $this->getResponse()->setContent($chart->toPrettyString());
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

    private function getRandomHex()
    {
        return sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

    /**
     * @return \Zend\Db\Sql\Where
     */
    private function getWhere()
    {
        return new Where();
    }

}