<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Session;

use Magento\Customer\Api\Customer_Repository_Interface;
use Magento\Customer\Api\Group_Management_Interface;
/**
 * Adminhtml quote session
 *
 * @api
 * @method Quote setCustomerId($id)
 * @method int getCustomerId()
 * @method bool hasCustomerId()
 * @method Quote setStoreId($storeId)
 * @method int getStoreId()
 * @method Quote setQuoteId($quoteId)
 * @method int getQuoteId()
 * @method Quote setCurrencyId($currencyId)
 * @method int getCurrencyId()
 * @method Quote setOrderId($orderId)
 * @method int getOrderId()
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Quote extends \Magento\Framework\Session\Session_Manager
{
    /**
     * Quote model object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;
    /**
     * Store model object
     *
     * @var \Magento\Store\Model\Store
     */
    protected $_store;
    /**
     * Order model object
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order_factory;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customer_repository;
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quote_repository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_store_manager;
    /**
     * @var GroupManagementInterface
     */
    protected $group_management;
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote_factory;
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Request\Http $request, \Magento\Framework\Session\Sid_Resolver_Interface $sid_resolver, \Magento\Framework\Session\Config\Config_Interface $session_config, \Magento\Framework\Session\Save_Handler_Interface $save_handler, \Magento\Framework\Session\Validator_Interface $validator, \Magento\Framework\Session\Storage_Interface $storage, \Magento\Framework\Stdlib\Cookie_Manager_Interface $cookie_manager, \Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory $cookie_metadata_factory, \Magento\Framework\App\State $app_state, Customer_Repository_Interface $customer_repository, \Magento\Quote\Api\Cart_Repository_Interface $quote_repository, \Magento\Sales\Model\Order_Factory $order_factory, \Magento\Store\Model\Store_Manager_Interface $store_manager, Group_Management_Interface $group_management, \Magento\Quote\Model\Quote_Factory $quote_factory)
    {
        $this->customer_repository = $customer_repository;
        $this->quote_repository = $quote_repository;
        $this->_order_factory = $order_factory;
        $this->_store_manager = $store_manager;
        $this->group_management = $group_management;
        $this->quote_factory = $quote_factory;
        parent::__construct($request, $sid_resolver, $session_config, $save_handler, $validator, $storage, $cookie_manager, $cookie_metadata_factory, $app_state);
        if ($this->_store_manager->has_single_store()) {
            $this->set_store_id($this->_store_manager->get_store(true)->get_id());
        }
    }
    /**
     * @inheritDoc
     */
    public function _reset_state(): void
    {
        parent::_reset_state();
        $this->_quote = null;
        $this->_store = null;
        $this->_order = null;
    }
    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function get_quote()
    {
        if ($this->_quote === null) {
            $this->_quote = $this->quote_factory->create();
            if ($this->get_store_id()) {
                if (!$this->get_quote_id()) {
                    $customer_group_id = $this->group_management->get_default_group($this->get_store_id())->get_id();
                    $this->_quote->set_customer_group_id($customer_group_id);
                    $this->_quote->set_is_active(false);
                    $this->_quote->set_store_id($this->get_store_id());
                    $this->quote_repository->save($this->_quote);
                    $this->set_quote_id($this->_quote->get_id());
                    $this->_quote = $this->quote_repository->get($this->get_quote_id(), [$this->get_store_id()]);
                } else {
                    $this->_quote = $this->quote_repository->get($this->get_quote_id(), [$this->get_store_id()]);
                    $this->_quote->set_store_id($this->get_store_id());
                }
                if ($this->get_customer_id() && $this->get_customer_id() != $this->_quote->get_customer_id()) {
                    $customer = $this->customer_repository->get_by_id($this->get_customer_id());
                    $this->_quote->assign_customer($customer);
                    $this->quote_repository->save($this->_quote);
                }
            }
            $this->_quote->set_ignore_old_qty(true);
            $this->_quote->set_is_super_mode(true);
        }
        return $this->_quote;
    }
    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function get_store()
    {
        if ($this->_store === null) {
            $this->_store = $this->_store_manager->get_store($this->get_store_id());
            $currency_id = $this->get_currency_id();
            if ($currency_id) {
                $this->_store->set_current_currency_code($currency_id);
            }
        }
        return $this->_store;
    }
    /**
     * Retrieve order model object
     *
     * @return \Magento\Sales\Model\Order
     */
    public function get_order()
    {
        if ($this->_order === null) {
            $this->_order = $this->_order_factory->create();
            if ($this->get_order_id()) {
                $this->_order->load($this->get_order_id());
            }
        }
        return $this->_order;
    }
}