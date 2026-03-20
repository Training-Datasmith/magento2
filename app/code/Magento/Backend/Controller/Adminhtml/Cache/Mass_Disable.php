<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\State;
use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Controller disables some types of cache
 */
class Mass_Disable extends \Magento\Backend\Controller\Adminhtml\Cache
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::toggling_cache_type';
    /**
     * @var State
     */
    private $state;
    /**
     * Mass action for cache disabling
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->get_state()->get_mode() === State::MODE_PRODUCTION) {
            $this->message_manager->add_error_message(__('You can\'t change status of cache type(s) in production mode'));
        } else {
            $this->disable_cache();
        }
        return $this->result_factory->create(Result_Factory::TYPE_REDIRECT)->set_path('adminhtml/*');
    }
    /**
     * Disable cache
     *
     * @return void
     */
    private function disable_cache()
    {
        try {
            $types = $this->get_request()->get_param('types');
            $updated_types = 0;
            if (!is_array($types)) {
                $types = [];
            }
            $this->_validate_types($types);
            foreach ($types as $code) {
                $this->_cache_type_list->clean_type($code);
                if ($this->_cache_state->is_enabled($code)) {
                    $this->_cache_state->set_enabled($code, false);
                    $updated_types++;
                }
            }
            if ($updated_types > 0) {
                $this->_cache_state->persist();
                $this->message_manager->add_success_message(__('%1 cache type(s) disabled.', $updated_types));
            }
        } catch (Localized_Exception $e) {
            $this->message_manager->add_error_message($e->get_message());
        } catch (\Exception $e) {
            $this->message_manager->add_exception_message($e, __('An error occurred while disabling cache.'));
        }
    }
    /**
     * Get State Instance
     *
     * @return State
     * @deprecated 100.2.0
     */
    private function get_state()
    {
        if ($this->state === null) {
            $this->state = Object_Manager::get_instance()->get(State::class);
        }
        return $this->state;
    }
}