<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Controller\Adminhtml\Bulk;

use Magento\Asynchronous_Operations\Model\Access_Validator;
use Magento\Asynchronous_Operations\Model\Bulk_Management;
use Magento\Asynchronous_Operations\Model\Bulk_Notification_Management;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\Result_Factory;
/**
 * Class Bulk Retry Controller
 */
class Retry extends Action
{
    /**
     * Retry constructor.
     */
    public function __construct(Context $context, private readonly Bulk_Management $bulk_management, private readonly Bulk_Notification_Management $notification_management, private readonly Access_Validator $access_validator)
    {
        parent::__construct($context);
    }
    /**
     * @inheritDoc
     */
    protected function _is_allowed(): bool
    {
        return $this->_authorization->is_allowed('Magento_Logging::system_magento_logging_bulk_operations') && $this->access_validator->is_allowed($this->get_request()->get_param('uuid'));
    }
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $bulk_uuid = $this->get_request()->get_param('uuid');
        $is_ajax = $this->get_request()->get_param('isAjax');
        $operations_to_retry = (array) $this->get_request()->get_param('operations_to_retry', []);
        $error_codes = [];
        foreach ($operations_to_retry as $operation_data) {
            if (isset($operation_data['error_code'])) {
                $error_codes[] = (int) $operation_data['error_code'];
            }
        }
        $affected_operations = $this->bulk_management->retry_bulk($bulk_uuid, $error_codes);
        $this->notification_management->ignore_bulks([$bulk_uuid]);
        if (!$is_ajax) {
            $this->message_manager->add_success_message(__('%1 item(s) have been scheduled for update."', $affected_operations));
            /** @var Redirect $result */
            $result = $this->result_redirect_factory->create();
            $result->set_path('bulk/index');
        } else {
            /** @var \Magento\Framework\Controller\Result\Json $result */
            $result = $this->result_factory->create(Result_Factory::TYPE_JSON);
            $result->set_http_response_code(200);
            $response = new \Magento\Framework\Data_Object();
            $response->set_error(0);
            $result->set_data($response);
        }
        return $result;
    }
}