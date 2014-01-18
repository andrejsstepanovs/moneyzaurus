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
    const GET_ALL     = 1;
    const GET_LIMITED = 2;
    const GET_LIMIT   = 3;

    /** @var int */
    protected $_groupCount;

    /** @var int */
    protected $_itemCount;

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
     * @param string     $title
     * @param string     $elementId  html element id
     * @param null|array $parameters
     *
     * @return string
     */
    public function renderChart($title, $elementId, array $parameters = null)
    {
        $groupName = $this->getSortedGroups(self::GET_LIMITED, 'name');
        $groupIds  = $this->getSortedGroups(self::GET_LIMITED, 'id');

        $jsChartClass = uniqid('a') . '_pieChart';

        $html = array();
        $html[] = 'var ' . $jsChartClass . ' = new PieChartData();';
        $html[] = $jsChartClass;
        $html[] = '.setData(' . $this->getChartData($jsChartClass)->renderOptions() . ')';
        $html[] = '.setGroups(' . json_encode($groupName) . ')';
        $html[] = '.setGroupIds(' . json_encode($groupIds) . ');';

        $highchartOptions = $this
            ->getPieHighchartHelper()
            ->getMainChart($title, $elementId, $jsChartClass, $parameters)
            ->renderOptions();

        $html[] = 'var ' . $jsChartClass . 'Render = new PieChartRender(' . $highchartOptions . ');';
        $html[] = $jsChartClass . 'Render.renderChart();';

        $script = implode(PHP_EOL, $html);
        return $script;
    }

    /**
     * @return \HighchartsPHP\Highcharts
     */
    public function getChartData()
    {
        if (null === $this->getChartDataCache()) {
            $groupedData = $this->getGroupedData();
            $sortedGroups = $this->getSortedGroups(self::GET_ALL, 'name');
            $count = count($sortedGroups);

            if ($this->getOptimalGroupCount() > $count) {
                $this->_groupCount = $count;
            }

            // initial limited data
            for ($i = 0; $i < $this->getOptimalGroupCount(); $i++) {
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

            $this->setOtherItemsAsGroups($i, $sortedGroups, $groupedData);

            $this->setChartDataCache($this->getPieHighchartHelper()->getChartData());
        }

        return $this->getChartDataCache();
    }

    /**
     * Set other group items as groups
     *
     * @param int   $i
     * @param array $sortedGroups
     * @param array $groupedData
     * @return $this
     */
    protected function setOtherItemsAsGroups($i, array $sortedGroups, array $groupedData)
    {
        $count = count($sortedGroups);

        // preparing other items data total price
        $priceDataTmp = $groupsTmp = $categoriesIds = $groupsTmpData = array();
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
            /** @var \Db\Db\ActiveRecord $row */
            foreach ($groupedData[$groupName] AS $row) {
                $total += round((float)$row->getPrice(), 2);
            }

            $priceDataTmp[] = $total;
        }

        // sort by price
        arsort($priceDataTmp);
        foreach ($priceDataTmp as $j => $price) {
            $groupsTmpData[] = $groupsTmp[$j];
        }
        $priceDataTmp = array_values($priceDataTmp);
        $groupsTmp = $groupsTmpData;

        // set rest items as groups
        $priceData = $categories = array();

        if (!empty($priceDataTmp)) {
            for ($i = 0; $i < $this->_otherGroupCount; $i++) {
                //if (array_key_exists($i, ))
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
        }

        return $this;
    }

    /**
     * @return int
     */
    protected function getOptimalGroupCount()
    {
        if (null === $this->_groupCount) {
            $this->_groupCount = 9;
        }

        return $this->_groupCount;
    }

    /**
     * @return int
     */
    protected function getOptimalItemCountInGroup()
    {
        if (null === $this->_itemCount) {
            $this->_itemCount = 30;
        }

        return $this->_itemCount;
    }

    /**
     * @return int
     */
    protected function getDistinctGroupCount()
    {
        $data = $this->getGroupedData();
        $groupCount = count(array_keys($data));
        return $groupCount;
    }

    /**
     * @return int
     */
    protected function getDistinctItemCount()
    {
        $allItems = array();
        $data = $this->getGroupedData();
        foreach ($data as $groupName => $items) {
            /** @var \Db\Db\ActiveRecord $item */
            foreach ($items as $item) {
                $allItems[$groupName . $item->getIdItem()] = null;
            }
        }
        $itemCount = count(array_keys($allItems));
        return $itemCount;
    }

    /**
     * @param array $rows
     *
     * @return array
     */
    private function _compactItems(array $rows)
    {
        $count = count($rows);
        $optimalItemCount = $this->getOptimalItemCountInGroup();

        if ($count <= $optimalItemCount) {
            return $rows;
        }

        $newRows = array();
        for ($i = 0; $i < $optimalItemCount; $i++) {
            $newRows[] = $rows[$i];
        }

        $price = 0;
        for ($i; $i < $count; $i++) {
            $row = $rows[$i];
            $price += $row->getPrice();
        }

        if (isset($row)) {
            $newRows[] = $row->setPrice($price)->setIdItem(0)->setItemName('Other Items');
        }

        return $newRows;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->setChartDataCache(null);
        $this->setGroupedDataCache(null);
        $this->setSortedGroupsDataCache(null);
        //$this->setTransactionsDataCache(null);

        return $this;
    }

    /**
     * @param bool        $full
     * @param null|string $selectKey
     * @param null|int    $level     will return other groups in specific level
     *
     * @return array
     */
    public function getSortedGroups($full = self::GET_ALL, $selectKey = null, $level = null)
    {
        if (null === $this->getSortedGroupsDataCache()) {
            $groups = $this->fetchSortedGroupsData();
            $this->setSortedGroupsDataCache(array_values($groups));
        }

        $data = $this->getSortedGroupsDataCache();

        if ($full == self::GET_LIMITED && count($data) > $this->getOptimalGroupCount()) {
            $data = array_slice($data, 0, $this->getOptimalGroupCount());
            $data[] = array(
                'name'  => 'Other Groups',
                'id'    => 0,
                'price' => 0
            );
        }

        if ($full == self::GET_LIMIT) {
            $data = array_slice($data, $this->getOptimalGroupCount() * $level);
        }

        if (null !== $selectKey) {
            $data = $this->filterByKey($data, $selectKey);
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function fetchSortedGroupsData()
    {
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

        return $groups;
    }

    /**
     * @param array  $data
     * @param string $selectKey
     *
     * @return array
     */
    protected function filterByKey(array $data, $selectKey)
    {
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