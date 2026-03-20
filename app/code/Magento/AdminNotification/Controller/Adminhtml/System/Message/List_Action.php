<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Controller\Adminhtml\System\Message;

use Magento\Framework\Controller\Result_Factory;
/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class List_Action extends \Magento\Backend\App\Abstract_Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_AdminNotification::show_list';
    /**
     * Initialize ListAction
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        /**
         * @deprecated 100.3.0
         * @see \Magento\Framework\Serialize\Serializer\Json
         */
        protected \Magento\Framework\Json\Helper\Data $json_helper,
        protected \Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection $message_collection
    )
    {
        parent::__construct($context);
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $severity = $this->get_request()->get_param('severity');
        if ($severity) {
            $this->message_collection->set_severity($severity);
        }
        $result = [];
        foreach ($this->message_collection->get_items() as $item) {
            $result[] = ['severity' => $item->get_severity(), 'text' => $item->get_text()];
        }
        if (empty($result)) {
            $result[] = ['severity' => (string) \Magento\Framework\Notification\Message_Interface::SEVERITY_NOTICE, 'text' => __('You have viewed and resolved all recent system notices. ' . 'Please refresh the web page to clear the notice alert.')];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $result_json = $this->result_factory->create(Result_Factory::TYPE_JSON);
        $result_json->set_data($result);
        return $result_json;
    }
}