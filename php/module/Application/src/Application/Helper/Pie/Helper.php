<?php

namespace Application\Helper\Pie;

use Application\Helper\AbstractHelper;
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
    /** get all */
    const GET_ALL     = 1;

    /** get limited */
    const GET_LIMITED = 2;

    /** get limit */
    const GET_LIMIT   = 3;

    /** @var int */
    protected $groupCount;

    /** @var int */
    protected $itemCount;

    /** @var int */
    protected $otherGroupCount = 4;

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
                $this->groupCount = $count;
            }

            // initial limited data
            for ($i = 0; $i < $this->getOptimalGroupCount(); $i++) {
                $priceData = $categories = array();
                /** @var \Application\Db\Transaction $row */
                $rows = $this->compactItems($groupedData[$sortedGroups[$i]]);
                foreach ($rows as $row) {
                    $priceData[]  = round((float) $row->getPrice(), 2);

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
     * @param int   $iterator
     * @param array $sortedGroups
     * @param array $groupedData
     *
     * @return $this
     */
    protected function setOtherItemsAsGroups($iterator, array $sortedGroups, array $groupedData)
    {
        $count = count($sortedGroups);

        // preparing other items data total price
        $priceDataTmp = array();
        $groupsTmp =  array();
        $groupsTmpData = array();

        for ($iterator; $iterator < $count; $iterator++) {
            $groupName = $sortedGroups[$iterator];

            $groupId = null;
            /** @var \Application\Db\Transaction $group */
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
            /** @var \Application\Db\Transaction $row */
            foreach ($groupedData[$groupName] as $row) {
                $total += round((float) $row->getPrice(), 2);
            }

            $priceDataTmp[] = $total;
        }

        // sort by price
        arsort($priceDataTmp);
        foreach (array_keys($priceDataTmp) as $j) {
            $groupsTmpData[] = $groupsTmp[$j];
        }

        $priceDataTmp = array_values($priceDataTmp);
        $groupsTmp = $groupsTmpData;

        // set rest items as groups
        $priceData = $categories = array();

        if (!empty($priceDataTmp)) {
            for ($iterator = 0; $iterator < $this->otherGroupCount; $iterator++) {
                //if (array_key_exists($i, ))
                $priceData[]  = $priceDataTmp[$iterator];
                $categories[] = $groupsTmp[$iterator];
            }

            // limit rest items as groups
            $total = 0;
            $count = count($priceDataTmp);
            for ($iterator; $iterator < $count; $iterator++) {
                $total += $priceDataTmp[$iterator];
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
        if (null === $this->groupCount) {
            $this->groupCount = 9;
        }

        return $this->groupCount;
    }

    /**
     * @return int
     */
    protected function getOptimalItemCountInGroup()
    {
        if (null === $this->itemCount) {
            $this->itemCount = 30;
        }

        return $this->itemCount;
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
            /** @var \Application\Db\Transaction $item */
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
    private function compactItems(array $rows)
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
            /** @var \Application\Db\Transaction $row */
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
        /** @var \Application\Db\Transaction $row */
        foreach ($this->getTransactionsData() as $row) {
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

        uasort(
            $groups,
            function ($arrayA, $arrayB) {
                return intval($arrayB['price'] - $arrayA['price']);
            }
        );

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
