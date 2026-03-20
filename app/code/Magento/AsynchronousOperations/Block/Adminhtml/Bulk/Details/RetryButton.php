<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\View\Element\Ui_Component\Control\Button_Provider_Interface;
/**
 * Retry button configuration provider
 */
class Retry_Button implements Button_Provider_Interface
{
    /**
     * RetryButton constructor.
     */
    public function __construct(private readonly \Magento\Asynchronous_Operations\Model\Operation\Details $details, private readonly \Magento\Framework\App\Request_Interface $request)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function get_button_data(): array
    {
        $uuid = $this->request->get_param('uuid');
        $details = $this->details->get_details($uuid);
        if ($details['failed_retriable'] === 0) {
            return [];
        }
        return ['label' => __('Retry'), 'class' => 'retry primary', 'data_attribute' => ['mage-init' => ['button' => ['event' => 'save']], 'form-role' => 'save']];
    }
}