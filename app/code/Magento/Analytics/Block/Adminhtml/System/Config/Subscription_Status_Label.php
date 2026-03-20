<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Analytics\Model\Subscription_Status_Provider;
use Magento\Backend\Block\Template\Context;
/**
 * Provides labels for subscription status
 * Status can be reviewed in System Configuration
 */
class Subscription_Status_Label extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(Context $context, private readonly Subscription_Status_Provider $subscription_status_provider, array $data = [])
    {
        parent::__construct($context, $data);
    }
    /**
     * Add Subscription status to comment
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\Abstract_Element $element)
    {
        $element->set_data('comment', $this->prepare_label_value());
        return parent::render($element);
    }
    /**
     * Prepare label for subscription status
     */
    private function prepare_label_value(): string
    {
        return __('Subscription status') . ': ' . $this->subscription_status_provider->get_status();
    }
}