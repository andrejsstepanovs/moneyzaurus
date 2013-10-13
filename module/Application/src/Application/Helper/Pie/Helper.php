<?php

namespace Application\Helper\Pie;

use Application\Helper\AbstractHelper;
use Zend\Db\Sql\Select;
use Zend\Http\PhpEnvironment\Request;


/**
 * Class Helper
 *
 * @package Application\Helper\Pie
 *
 * @method \Zend\Http\PhpEnvironment\Request getRequest()
 * @method Helper setRequest(Request $request)
 * @method Helper setTransactionsDataCache(array $data)
 * @method Helper setSortedGroupsDataCache(array $data)
 * @method Helper setChartDataCache(\HighchartsPHP\Highcharts $data)
 * @method Helper setGroupedDataCache(array $data)
 * @method Helper setPieHighchartHelper(\Application\Helper\Pie\Highchart $data)
 * @method \Application\Helper\Pie\Highchart getPieHighchartHelper()
 * @method array getTransactionsDataCache()
 * @method array getSortedGroupsDataCache()
 * @method array getChartDataCache()
 * @method array getGroupedDataCache()
 */
class Helper extends AbstractHelper
{
    /** @var int */
    protected $_groupCount = 9;

    /** @var int */
    protected $_itemCount = 9;

    /** @var int */
    protected $_otherGroupCount = 4;

    /**
     * @return array
     */
    private function getGroupedData()
    {
        if (null === $this->getGroupedDataCache()) {
            $groupedData = array();
            $transactionsData = $this->getTransactionsData();
            if ($transactionsData) {
                foreach ($transactionsData as $model) {
                    $groupedData[$model['group_name']][] = $model;
                }
            }
            $this->setGroupedDataCache($groupedData);
        }
        return $this->getGroupedDataCache();
    }

    /**
     * @return string
     */
    public function renderChart()
    {
        $groupName = $this->getSortedGroups(false, 'name');
        $groupIds  = $this->getSortedGroups(false, 'id');

        $html = array();
        $html[] = 'var primaryData = [];';
        $html[] = 'var secondaryData = [];';
        $html[] = 'var colors = Highcharts.getOptions().colors;';
        $html[] = 'var groups = ' . json_encode($groupName) . ';';
        $html[] = 'var groupIds = ' . json_encode($groupIds) . ';';
        $html[] = 'var data = ' . $this->getChartData()->renderOptions() . ';';
        $html[] = 'renderChart(data, groups, groupIds);';

        $script = $this->getPieHighchartHelper()->getMainChart()->renderChart(implode('', $html));
        return $script;
    }

    /**
     * @return Highchart
     */
    public function getChartData()
    {
        if (null === $this->getChartDataCache()) {
            $groupedData = $this->getGroupedData();
            $sortedGroups = $this->getSortedGroups(true, 'name');
            $count = count($sortedGroups);

            if ($this->_groupCount > $count) {
                $this->_groupCount = $count;
            }

            // initial limited data
            for ($i = 0; $i < $this->_groupCount; $i++) {
                $priceData = $categories = array();
                $rows = $this->_compactItems($groupedData[$sortedGroups[$i]]);
                foreach ($rows AS $row) {
                    $priceData[]  = round((float)$row->getPrice(), 2);

                    $categories[] = array(
                        'name'     => $row->getItemName(),
                        'id_group' => $row->getIdGroup(),
                        'id_item'  => $row->getIdItem(),
                        'type'     => 'item'
                    );
                }
                $this->getPieHighchartHelper()->setChartData($priceData, $categories);
            }

            // preparing other items data total price
            $priceDataTmp = $groupsTmp = $categoriesIds = array();
            for ($i; $i < $count; $i++) {
                $groupName = $sortedGroups[$i];

                $groupId = null;
                foreach ($groupedData[$groupName] as $group) {
                    $groupId = $group->getIdGroup();
                    break;
                }

                $groupsTmp[] = array(
                    'name'     => $groupName,
                    'id_group' => $groupId,
                    'id_item'  => 0,
                    'type'     => 'group'
                );

                $total = 0;
                foreach ($groupedData[$groupName] AS $row) {
                    $total += round((float)$row->getPrice(), 2);
                }

                $priceDataTmp[] = $total;
            }

            // set rest items as groups
            $priceData = $categories = array();
            for ($i = 0; $i < $this->_otherGroupCount; $i++) {
                $priceData[]  = $priceDataTmp[$i];
                $categories[] = $groupsTmp[$i];
            }

            // limit rest items as groups
            $total = 0;
            $count = count($priceDataTmp);
            for ($i; $i < $count; $i++) {
                $total += $priceDataTmp[$i];
            }

            $priceData[] = $total;
            $categories[] = array(
                'name'     => 'Other',
                'id_group' => 0,
                'id_item'  => 0,
                'type'     => 'group'
            );

            $this->getPieHighchartHelper()->setChartData($priceData, $categories);

            $this->setChartDataCache($this->getPieHighchartHelper()->getChartData());
        }

        return $this->getChartDataCache();
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private function _compactItems(array $rows)
    {
        $count = count($rows);
        if ($count <= $this->_itemCount) {
            return $rows;
        }

        $newRows = array();
        for ($i = 0; $i < $this->_itemCount; $i++) {
            $newRows[] = $rows[$i];
        }

        $price = 0;
        for ($i; $i < $count; $i++) {
            $row = $rows[$i];
            $price += $row->getPrice();
        }

        if (isset($row)) {
            $newRows[] = $row->setPrice($price)->setItemName('Other Items');
        }

        return $newRows;
    }

    /**
     * @param null|bool   $full
     * @param null|string $selectKey
     *
     * @return array
     */
    public function getSortedGroups($full = true, $selectKey = null)
    {
        if (null === $this->getSortedGroupsDataCache()) {
            $groups = array();
            /** @var \Db\Db\ActiveRecord $row */
            foreach ($this->getTransactionsData() AS $row) {
                $groupName = $row->getGroupName();
                if (!array_key_exists($groupName, $groups)) {
                    $groups[$groupName] = array(
                        'name'  => $groupName,
                        'id'    => $row->getIdGroup(),
                        'price' => $row->getPrice()
                    );
                } else {
                    $groups[$groupName]['price'] += $row->getPrice();
                }
            }

            uasort($groups, function($a, $b){return intval($b['price'] - $a['price']);});

            $this->setSortedGroupsDataCache(array_values($groups));
        }

        $data = $this->getSortedGroupsDataCache();

        if (!$full) {
            $data = array_slice($data, 0, $this->_groupCount);
            $data[] = array(
                'name'  => 'Other Groups',
                'id'    => 0,
                'price' => 0
            );
        }

        if (null !== $selectKey) {
            $return = array();
            foreach ($data as $groupData) {
                foreach ($groupData as $key => $val) {
                    if ($selectKey == $key) {
                        $return[] = $val;
                    }
                }
            }
            return $return;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTransactionsData()
    {
        return $this->getTransactionsDataCache();
    }

    /**
     * @return $this
     */
    public function setTransactionsData(array $transactionsData)
    {
        return $this->setTransactionsDataCache($transactionsData);
    }

}