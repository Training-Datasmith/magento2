<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\Localized_Exception;
abstract class Cache extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::cache';
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cache_type_list;
    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cache_state;
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cache_frontend_pool;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, \Magento\Framework\App\Cache\Type_List_Interface $cache_type_list, \Magento\Framework\App\Cache\State_Interface $cache_state, \Magento\Framework\App\Cache\Frontend\Pool $cache_frontend_pool, \Magento\Framework\View\Result\Page_Factory $result_page_factory)
    {
        parent::__construct($context);
        $this->_cache_type_list = $cache_type_list;
        $this->_cache_state = $cache_state;
        $this->_cache_frontend_pool = $cache_frontend_pool;
        $this->result_page_factory = $result_page_factory;
    }
    /**
     * Check whether specified cache types exist
     *
     * @param array $types
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _validate_types(array $types)
    {
        if (empty($types)) {
            return;
        }
        $all_types = array_keys($this->_cache_type_list->get_types());
        $invalid_types = array_diff($types, $all_types);
        if (count($invalid_types) > 0) {
            throw new Localized_Exception(__('These cache type(s) don\'t exist: %1', join(', ', $invalid_types)));
        }
    }
}