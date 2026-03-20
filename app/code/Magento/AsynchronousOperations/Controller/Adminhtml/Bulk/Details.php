<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Controller\Adminhtml\Bulk;

/**
 * Class View Operation Details Controller
 */
class Details extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\Http_Get_Action_Interface
{
    /**
     * Details constructor.
     * @param string $menuId
     */
    public function __construct(\Magento\Backend\App\Action\Context $context, private readonly \Magento\Framework\View\Result\Page_Factory $result_page_factory, private readonly \Magento\Asynchronous_Operations\Model\Access_Validator $access_validator, private $menu_id = 'Magento_AsynchronousOperations::system_magento_logging_bulk_operations')
    {
        parent::__construct($context);
    }
    /**
     * @inheritDoc
     */
    protected function _is_allowed(): bool
    {
        return $this->_authorization->is_allowed('Magento_Logging::system_magento_logging_bulk_operations') && $this->access_validator->is_allowed($this->get_request()->get_param('uuid'));
    }
    /**
     * Bulk details action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $bulk_id = $this->get_request()->get_param('uuid');
        $result_page = $this->result_page_factory->create();
        $result_page->init_layout();
        $this->_set_active_menu($this->menu_id);
        $result_page->get_config()->get_title()->prepend(__('Action Details - #' . $bulk_id));
        return $result_page;
    }
}