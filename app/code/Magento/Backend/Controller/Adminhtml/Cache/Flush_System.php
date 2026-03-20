<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
class Flush_System extends \Magento\Backend\Controller\Adminhtml\Cache implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::flush_magento_cache';
    /**
     * Flush all magento cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->_cache_frontend_pool as $cache_frontend) {
            $cache_frontend->clean();
        }
        $this->_event_manager->dispatch('adminhtml_cache_flush_system');
        $this->message_manager->add_success_message(__('The Magento cache storage has been flushed.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path('adminhtml/*');
    }
}