<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Retry button configuration provider
 */
class RetryButton implements ButtonProviderInterface
{
    /**
     * RetryButton constructor.
     */
    public function __construct(private readonly \Magento\AsynchronousOperations\Model\Operation\Details $details, private readonly \Magento\Framework\App\RequestInterface $request)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData(): array
    {
        $uuid = $this->request->getParam('uuid');
        $details = $this->details->getDetails($uuid);
        if ($details['failed_retriable'] === 0) {
            return [];
        }
        return [
            'label' => __('Retry'),
            'class' => 'retry primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
        ];
    }
}
