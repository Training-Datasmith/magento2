<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Index extends \Magento\Backend\Controller\Adminhtml\Cache implements Http_Get_Action_Interface
{
    /**
     * Display cache management grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_active_menu('Magento_Backend::system_cache');
        $result_page->get_config()->get_title()->prepend(__('Cache Management'));
        return $result_page;
    }
}