<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\Authorization\Model\UserContextInterface;

class AccessValidator
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory
     */
    private $bulkSummaryFactory;

    public function __construct(
        private readonly UserContextInterface $userContext,
        private readonly \Magento\Framework\EntityManager\EntityManager $entityManager,
        \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory $bulkSummaryFactory
    ) {
        $this->bulkSummaryFactory = $bulkSummaryFactory;
    }

    /**
     * Check if content allowed for current user
     *
     * @param int $bulkUuid
     * @return bool
     */
    public function isAllowed($bulkUuid)
    {
        /** @var BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->entityManager->load(
            $this->bulkSummaryFactory->create(),
            $bulkUuid
        );
        if ((int) $bulkSummary->getUserType() === UserContextInterface::USER_TYPE_INTEGRATION) {
            return true;
        }

        return ((int) $bulkSummary->getUserId()) === ((int) $this->userContext->getUserId());
    }
}
