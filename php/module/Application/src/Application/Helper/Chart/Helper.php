<?php

namespace Application\Helper\Chart;

use Application\Helper\AbstractHelper;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Expression;

/**
 * Class Helper
 *
 * @package Application\Helper\Chart
 *
 * @method string getDefaultUserCurrency
 * @method Helper setDefaultUserCurrency(string $currency)
 */
class Helper extends AbstractHelper
{
    /**
     * @return Select
     */
    public function getSumByGroupSelect()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('price' => new Expression('SUM(t.price)')))
               ->join(array('g' => 'group'), 't.id_group = g.group_id', array('group_name' => 'name', 'group_id'))
               ->join(array('u' => 'user'), 't.id_user = u.user_id', array());

        return $select;
    }

    /**
     * @return Select
     */
    public function getPaymentsByGroupSelect()
    {
        $transactionTable = array('t' => 'transaction');

        $select = new Select();
        $select->from($transactionTable)
               ->columns(array('price', 'date'))
               ->join(array('i' => 'item'), 't.id_item = i.item_id', array('item_name' => 'name'))
               ->order('price DESC');

        return $select;
    }

    private function getRandomHex()
    {
        return sprintf("%02X%02X%02X", mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
    }

    /**
     * @param string             $month
     * @param HydratingResultSet $data
     *
     * @return string
     */
    public function getChartString($month, HydratingResultSet $data)
    {
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

        return $chart->toPrettyString();
    }

    /**
     * @param HydratingResultSet $data
     * @param string             $groupName
     * @param string             $month
     *
     * @return string
     */
    public function getSecondaryChartString(HydratingResultSet $data, $groupName, $month)
    {
        $colors = $values = array();
        foreach ($data as $itemRow) {
            $values[] = new \pie_value(
                $itemRow['price'] * 1,
                $itemRow['item_name'] . ' (' . $itemRow['price'] . ') ' . date('d.M', strtotime($itemRow['date']))
            );
            $colors[] = $this->getRandomHex();
        }

        $pie = new \pie();
        $pie->set_tooltip(
            '#val# ' . $this->getDefaultUserCurrency() . ' of #total# '
            . $this->getDefaultUserCurrency() . '<br>#percent# of 100%'
        );

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

        return $chart->toPrettyString();
    }
}
