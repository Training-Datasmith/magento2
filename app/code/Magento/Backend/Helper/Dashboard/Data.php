<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Helper\Dashboard;

use Magento\Backend\Model\Dashboard\Period;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\App\Helper\Abstract_Helper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Data helper for dashboard
 *
 * @api
 * @since 100.0.2
 */
class Data extends Abstract_Helper
{
    /**
     * @var AbstractDb
     */
    protected $_stores;
    /**
     * @var string
     */
    protected $_install_date;
    /**
     * @var StoreManagerInterface
     */
    private $_store_manager;
    /**
     * @var Period
     */
    private $period;
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DeploymentConfig $deploymentConfig
     * @param Period|null $period
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function __construct(Context $context, Store_Manager_Interface $store_manager, Deployment_Config $deployment_config, ?Period $period = null)
    {
        parent::__construct($context);
        $this->_install_date = $deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_INSTALL_DATE);
        $this->_store_manager = $store_manager;
        $this->period = $period ?? Object_Manager::get_instance()->get(Period::class);
    }
    /**
     * Retrieve stores configured in system.
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function get_stores()
    {
        if (!$this->_stores) {
            $this->_stores = $this->_store_manager->get_store()->get_resource_collection()->load();
        }
        return $this->_stores;
    }
    /**
     * Retrieve number of loaded stores
     *
     * @return int
     */
    public function count_stores()
    {
        return count($this->_stores->get_items());
    }
    /**
     * Prepare array with periods for dashboard graphs
     *
     * @deprecated 102.0.0 periods were moved to it's own class
     * @see Period::getDatePeriods()
     *
     * @return array
     */
    public function get_date_periods()
    {
        return $this->period->get_date_periods();
    }
    /**
     * Create data hash to ensure that we got valid data and it is not changed by some one else.
     *
     * @param string $data
     * @return string
     */
    public function get_chart_data_hash($data)
    {
        $secret = $this->_install_date;
        // phpcs:disable Magento2.Security.InsecureFunction.FoundWithAlternative
        return md5($data . $secret);
    }
}