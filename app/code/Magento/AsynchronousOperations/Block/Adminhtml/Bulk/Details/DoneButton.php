<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details;

use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Back button configuration provider
 */
class DoneButton implements ButtonProviderInterface
{
    public function __construct(private readonly \Magento\Framework\Bulk\BulkStatusInterface $bulkStatus, private readonly \Magento\Framework\App\RequestInterface $request)
    {
    }

    /**
     * Retrieve button data
     *
     * @return array button configuration
     */
    public function getButtonData(): array
    {
        $uuid = $this->request->getParam('uuid');
        $operationsCount = $this->bulkStatus->getOperationsCountByBulkIdAndStatus(
            $uuid,
            OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
        );

        if ($this->request->getParam('buttons') && $operationsCount === 0) {
            return [
                'label' => __('Done'),
                'class' => 'primary',
                'sort_order' => 10,
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'notification_area.notification_area.modalContainer.modal',
                                    'actionName' => 'closeModal',
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        return [];
    }
}
