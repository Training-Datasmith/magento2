<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Controller\Adminhtml\Notification;

use Magento\Asynchronous_Operations\Model\Bulk_Notification_Management;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Http_Post_Action_Interface;
use Magento\Framework\Controller\Result_Factory;
/**
 * Class Bulk Notification Dismiss Controller
 */
class Dismiss extends Action implements Http_Post_Action_Interface
{
    /**
     * Class constructor.
     */
    public function __construct(Context $context, private readonly Bulk_Notification_Management $notification_management)
    {
        parent::__construct($context);
    }
    /**
     * @inheritDoc
     */
    protected function _is_allowed()
    {
        return $this->_authorization->is_allowed('Magento_Logging::system_magento_logging_bulk_operations');
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $bulk_uuids = [];
        foreach ((array) $this->get_request()->get_param('uuid', []) as $bulk_uuid) {
            $bulk_uuids[] = (string) $bulk_uuid;
        }
        $is_acknowledged = $this->notification_management->acknowledge_bulks($bulk_uuids);
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->result_factory->create(Result_Factory::TYPE_JSON)->set_data(['']);
        if (!$is_acknowledged) {
            $result->set_http_response_code(400);
        }
        return $result;
    }
}