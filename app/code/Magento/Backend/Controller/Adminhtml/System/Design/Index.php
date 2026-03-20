<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Index extends \Magento\Backend\Controller\Adminhtml\System\Design implements Http_Get_Action_Interface
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $result_page = $this->result_page_factory->create();
        $result_page->set_active_menu('Magento_Backend::system_design_schedule');
        $result_page->get_config()->get_title()->prepend(__('Store Design'));
        return $result_page;
    }
}