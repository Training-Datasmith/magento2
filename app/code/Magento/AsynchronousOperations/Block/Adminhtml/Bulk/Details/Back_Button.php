<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\Url_Interface;
use Magento\Framework\View\Element\Ui_Component\Control\Button_Provider_Interface;
/**
 * Back button configuration provider
 */
class Back_Button implements Button_Provider_Interface
{
    public function __construct(
        /**
         * URL builder
         */
        private readonly Url_Interface $url_builder
    )
    {
    }
    /**
     * Retrieve button data
     *
     * @return array button configuration
     */
    public function get_button_data(): array
    {
        return ['label' => __('Back'), 'on_click' => sprintf("location.href = '%s';", $this->url_builder->get_url('*/')), 'class' => 'back', 'sort_order' => 10];
    }
}