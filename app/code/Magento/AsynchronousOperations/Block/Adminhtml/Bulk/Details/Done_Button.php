<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\Bulk\Operation_Interface;
use Magento\Framework\View\Element\Ui_Component\Control\Button_Provider_Interface;
/**
 * Back button configuration provider
 */
class Done_Button implements Button_Provider_Interface
{
    public function __construct(private readonly \Magento\Framework\Bulk\Bulk_Status_Interface $bulk_status, private readonly \Magento\Framework\App\Request_Interface $request)
    {
    }
    /**
     * Retrieve button data
     *
     * @return array button configuration
     */
    public function get_button_data(): array
    {
        $uuid = $this->request->get_param('uuid');
        $operations_count = $this->bulk_status->get_operations_count_by_bulk_id_and_status($uuid, Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED);
        if ($this->request->get_param('buttons') && $operations_count === 0) {
            return ['label' => __('Done'), 'class' => 'primary', 'sort_order' => 10, 'on_click' => '', 'data_attribute' => ['mage-init' => ['Magento_Ui/js/form/button-adapter' => ['actions' => [['targetName' => 'notification_area.notification_area.modalContainer.modal', 'actionName' => 'closeModal']]]]]];
        }
        return [];
    }
}