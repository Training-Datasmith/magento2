<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Block\Adminhtml\System\Config;

use Magento\Analytics\Model\SubscriptionStatusProvider;
use Magento\Backend\Block\Template\Context;

/**
 * Provides labels for subscription status
 * Status can be reviewed in System Configuration
 */
class SubscriptionStatusLabel extends \Magento\Config\Block\System\Config\Form\Field
{
    public function __construct(
        Context $context,
        private readonly SubscriptionStatusProvider $subscriptionStatusProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Add Subscription status to comment
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setData(
            'comment',
            $this->prepareLabelValue()
        );
        return parent::render($element);
    }

    /**
     * Prepare label for subscription status
     */
    private function prepareLabelValue(): string
    {
        return __('Subscription status') . ': ' . $this->subscriptionStatusProvider->getStatus();
    }
}
