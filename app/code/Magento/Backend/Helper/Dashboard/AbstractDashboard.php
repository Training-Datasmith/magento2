<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Helper\Dashboard;

/**
 * Adminhtml abstract  dashboard helper.
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class Abstract_Dashboard extends \Magento\Framework\App\Helper\Abstract_Helper
{
    /**
     * Helper collection
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|array
     */
    protected $_collection;
    /**
     * Parameters for helper
     *
     * @var array
     */
    protected $_params = [];
    /**
     * Return collections
     *
     * @return array|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function get_collection()
    {
        if ($this->_collection === null) {
            $this->_init_collection();
        }
        return $this->_collection;
    }
    /**
     * Init collections
     *
     * @return void
     */
    abstract protected function _init_collection();
    /**
     * Returns collection items
     *
     * @return array
     */
    public function get_items()
    {
        return is_array($this->get_collection()) ? $this->get_collection() : $this->get_collection()->get_items();
    }
    /**
     * Return items count
     *
     * @return int
     */
    public function get_count()
    {
        return count($this->get_items());
    }
    /**
     * Return column
     *
     * @param string $index
     * @return array
     */
    public function get_column($index)
    {
        $result = [];
        foreach ($this->get_items() as $item) {
            if (is_array($item)) {
                if (isset($item[$index])) {
                    $result[] = $item[$index];
                } else {
                    $result[] = null;
                }
            } elseif ($item instanceof \Magento\Framework\Data_Object) {
                $result[] = $item->get_data($index);
            } else {
                $result[] = null;
            }
        }
        return $result;
    }
    /**
     * Set params with value
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set_param($name, $value)
    {
        $this->_params[$name] = $value;
    }
    /**
     * Set params
     *
     * @param array $params
     * @return void
     */
    public function set_params(array $params)
    {
        $this->_params = $params;
    }
    /**
     * Get params with name
     *
     * @param string $name
     * @return mixed
     */
    public function get_param($name)
    {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }
        return null;
    }
    /**
     * Get params
     *
     * @return array
     */
    public function get_params()
    {
        return $this->_params;
    }
}