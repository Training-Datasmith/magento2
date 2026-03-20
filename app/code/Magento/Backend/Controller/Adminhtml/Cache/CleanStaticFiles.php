<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\Controller\Result_Factory;
class Clean_Static_Files extends \Magento\Backend\Controller\Adminhtml\Cache implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::flush_static_files';
    /**
     * Clean static files cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->_object_manager->get(\Magento\Framework\App\State\Cleanup_Files::class)->clear_materialized_view_files();
        $this->_event_manager->dispatch('clean_static_files_cache_after');
        $this->message_manager->add_success_message(__('The static files cache has been cleaned.'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        return $result_redirect->set_path('adminhtml/*');
    }
}