<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Store;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\Controller\Result_Factory;
/**
 * Class Index returns Stores page
 */
class Index extends \Magento\Backend\Controller\Adminhtml\System\Store implements Http_Get_Action_Interface
{
    /**
     * Returns Stores page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_factory->create(Result_Factory::TYPE_PAGE);
        $result_page->set_active_menu('Magento_Backend::system_store');
        $result_page->add_breadcrumb(__('Stores'), __('Stores'));
        $result_page->add_breadcrumb(__('All Stores'), __('All Stores'));
        $result_page->get_config()->get_title()->prepend(__('Stores'));
        return $result_page;
    }
}