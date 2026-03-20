<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Admin_Analytics\Controller\Adminhtml\Config;

use Magento\Admin_Analytics\Model\Resource_Model\Viewer\Logger as NotificationLogger;
use Magento\Backend\App\Action;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\App\Product_Metadata_Interface;
use Magento\Framework\Controller\Result_Factory;
use Magento\Framework\Controller\Result_Interface;
/**
 * Controller to record that the current admin user has responded to Admin Analytics notice
 */
class Enable_Admin_Usage extends Action implements Http_Post_Action_Interface
{
    public function __construct(Action\Context $context, private readonly Product_Metadata_Interface $product_metadata, private readonly Notification_Logger $notification_logger, private readonly Factory $config_factory)
    {
        parent::__construct($context);
    }
    /**
     * Change the value of config/admin/usage/enabled
     */
    private function enable_admin_usage(): void
    {
        $config_model = $this->config_factory->create();
        $config_model->set_data_by_path('admin/usage/enabled', 1);
        $config_model->save();
    }
    /**
     * Log information about the last user response
     */
    private function mark_user_notified(): Result_Interface
    {
        $response_content = ['success' => $this->notification_logger->log($this->product_metadata->get_version()), 'error_message' => ''];
        $result_json = $this->result_factory->create(Result_Factory::TYPE_JSON);
        return $result_json->set_data($response_content);
    }
    /**
     * Log information about the last shown advertisement
     */
    public function execute(): \Magento\Framework\Controller\Result_Interface
    {
        $this->enable_admin_usage();
        return $this->mark_user_notified();
    }
    /**
     * @inheritDoc
     */
    protected function _is_allowed()
    {
        return $this->_authorization->is_allowed(static::ADMIN_RESOURCE);
    }
}