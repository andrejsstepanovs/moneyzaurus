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
 * @method Helper setTransactionsDataValue(array $data)
 * @method Helper setSortedGroupsDataValue(array $data)
 * @method Helper setChartDataValue(\HighchartsPHP\Highcharts $data)
 * @method Helper setGroupedDataValue(array $data)
 * @method Helper setPieHighchartHelper(\Application\Helper\Pie\Highchart $data)
 * @method \Application\Helper\Pie\Highchart getPieHighchartHelper()
 * @method array getTransactionsDataValue()
 * @method array getSortedGroupsDataValue()
 * @method array getChartDataValue()
 * @method array getGroupedDataValue()
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
        if (null === $this->getGroupedDataValue()) {
            $groupedData = array();
            $transactionsData = $this->getTransactionsData();
            if ($transactionsData) {
                foreach ($transactionsData as $model) {
                    $groupedData[$model['group_name']][] = $model;
                }
            }
            $this->setGroupedDataValue($groupedData);
        }
        return $this->getGroupedDataValue();
    }

    /**
     * @return Highchart
     */
    public function getChartData()
    {
        if (null === $this->getChartDataValue()) {
            $groupedData = $this->getGroupedData();
            $sortedGroups = $this->getSortedGroups();
            $count = count($sortedGroups);

            if ($this->_groupCount > $count) {
                $this->_groupCount = $count;
            }

            for ($i = 0; $i < $this->_groupCount; $i++) {
                $priceData = $categories = array();
                $rows = $this->_compactItems($groupedData[$sortedGroups[$i]]);
                foreach ($rows AS $row) {
                    $priceData[]  = round((float)$row->getPrice(), 2);
                    $categories[] = $row->getData('item_name');
                }
                $this->getPieHighchartHelper()->setChartData($priceData, $categories);
            }

            $priceDataTmp = $categoriesTmp = array();
            for ($i; $i < $count; $i++) {
                $groupName = $sortedGroups[$i];
                $categoriesTmp[] = $groupName;

                $total = 0;
                foreach ($groupedData[$groupName] AS $row) {
                    $total += round((float)$row->getPrice(), 2);
                }

                $priceDataTmp[] = $total;
            }

            $count = count($priceDataTmp);
            $priceData = $categories = array();
            for ($i = 0; $i < $this->_otherGroupCount; $i++) {
                $priceData[] = $priceDataTmp[$i];
                $categories[] = $categoriesTmp[$i];
            }

            $total = 0;
            for ($i; $i < $count; $i++) {
                $total += $priceDataTmp[$i];
            }
            $priceData[] = $total;
            $categories[] = 'Other';

            $this->getPieHighchartHelper()->setChartData($priceData, $categories);
            $this->setChartDataValue($this->getPieHighchartHelper()->getChartData());
        }

        return $this->getChartDataValue();
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

        $newRows[] = $row->setPrice($price)->setItemName('Other Items');

        return $newRows;
    }

    /**
     * @return array
     */
    public function getSortedGroups($full = true)
    {
        if (null === $this->getSortedGroupsDataValue()) {
            $groups = array();
            /** @var \Db\Db\ActiveRecord $row */
            foreach ($this->getTransactionsData() AS $row) {
                $groupName = $row->getGroupName();
                if (!array_key_exists($groupName, $groups)) {
                    $groups[$groupName] = $row->getPrice();
                } else {
                    $groups[$groupName] += $row->getPrice();
                }
            }

            arsort($groups, SORT_NUMERIC);
            $this->setSortedGroupsDataValue(array_keys($groups));
        }

        $data = $this->getSortedGroupsDataValue();

        if (!$full) {
            $data = array_slice($data, 0, $this->_groupCount);
            $data[] = 'Other Groups';
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getTransactionsData()
    {
        return $this->getTransactionsDataValue();
    }

    /**
     * @return $this
     */
    public function setTransactionsData(array $transactionsData)
    {
        return $this->setTransactionsDataValue($transactionsData);
    }

}