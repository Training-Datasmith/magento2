<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Action\Http_Get_Action_Interface as HttpGetActionInterface;
use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
class Clean_Media extends \Magento\Backend\Controller\Adminhtml\Cache implements Http_Get_Action_Interface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::flush_js_css';
    /**
     * Clean JS/css files cache
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $this->_object_manager->get(\Magento\Framework\View\Asset\Merge_Service::class)->clean_merged_js_css();
            $this->_event_manager->dispatch('clean_media_cache_after');
            $this->message_manager->add_success_message(__('The JavaScript/CSS cache has been cleaned.'));
        } catch (Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('An error occurred while clearing the JavaScript/CSS cache.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        return $result_redirect->set_path('adminhtml/*');
    }
}