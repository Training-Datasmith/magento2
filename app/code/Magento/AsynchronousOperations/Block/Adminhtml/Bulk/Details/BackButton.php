<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Back button configuration provider
 */
class BackButton implements ButtonProviderInterface
{
    public function __construct(
        /**
         * URL builder
         */
        private readonly UrlInterface $urlBuilder
    ) {
    }

    /**
     * Retrieve button data
     *
     * @return array button configuration
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->urlBuilder->getUrl('*/')),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }
}
