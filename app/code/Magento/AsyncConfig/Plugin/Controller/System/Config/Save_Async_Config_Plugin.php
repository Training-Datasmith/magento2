<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Plugin\Controller\System\Config;

use Magento\Async_Config\Api\Async_Config_Publisher_Interface;
use Magento\Async_Config\Setup\Config_Options_List;
use Magento\Config\Controller\Adminhtml\System\Config\Save;
use Magento\Framework\App\Deployment_Config;
use Magento\Framework\Controller\Result\Redirect_Factory;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Message\Manager_Interface;
class Save_Async_Config_Plugin
{
    public function __construct(private readonly Deployment_Config $deployment_config, private readonly Async_Config_Publisher_Interface $async_config_publisher, private readonly Redirect_Factory $result_redirect_factory, private readonly Manager_Interface $message_manager)
    {
    }
    /**
     * Around Config save controller
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws RuntimeException
     */
    public function around_execute(Save $subject, callable $proceed)
    {
        if (!$this->deployment_config->get(Config_Options_List::CONFIG_PATH_ASYNC_CONFIG_SAVE)) {
            return $proceed();
        }
        $config_data = $subject->get_config_data();
        $this->async_config_publisher->save_config_data($config_data);
        $this->message_manager->add_success_message(__('Configuration changes will be applied by consumer soon.'));
        $subject->_save_state($subject->get_request()->get_post('config_state'));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $result_redirect = $this->result_redirect_factory->create();
        return $result_redirect->set_path('adminhtml/system_config/edit', ['_current' => ['section', 'website', 'store'], '_nosid' => true]);
    }
}