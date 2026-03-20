<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;
abstract class Design extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::schedule';
    /**
     * Core registry instance
     *
     * @var \Magento\Framework\Registry
     */
    protected $_core_registry = null;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $date_filter;
    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $result_forward_factory;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $result_page_factory;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $result_layout_factory;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $core_registry, \Magento\Framework\Stdlib\DateTime\Filter\Date $date_filter, \Magento\Backend\Model\View\Result\Forward_Factory $result_forward_factory, \Magento\Framework\View\Result\Page_Factory $result_page_factory, \Magento\Framework\View\Result\Layout_Factory $result_layout_factory)
    {
        $this->_core_registry = $core_registry;
        $this->date_filter = $date_filter;
        parent::__construct($context);
        $this->result_forward_factory = $result_forward_factory;
        $this->result_page_factory = $result_page_factory;
        $this->result_layout_factory = $result_layout_factory;
    }
}