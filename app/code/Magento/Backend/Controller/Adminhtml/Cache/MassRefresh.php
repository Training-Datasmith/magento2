<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
class Mass_Refresh extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::refresh_cache_type';
    /**
     * Mass action for cache refresh
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $types = $this->get_request()->get_param('types');
            $updated_types = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validate_types($types);
            foreach ($types as $type) {
                $this->_cache_type_list->clean_type($type);
                $updated_types++;
            }
            if ($updated_types > 0) {
                $this->message_manager->add_success_message(__('%1 cache type(s) refreshed.', $updated_types));
            }
        } catch (Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('An error occurred while refreshing cache.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_factory->create(Result_Factory::TYPE_REDIRECT);
        return $result_redirect->set_path('adminhtml/*');
    }
}