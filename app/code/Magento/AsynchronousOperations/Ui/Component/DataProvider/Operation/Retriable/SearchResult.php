<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Retriable;

use Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk\IdentifierResolver;
use Magento\Framework\Bulk\OperationInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

/**
 * Class SearchResult
 */
class SearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * SearchResult constructor.
     * @param string $mainTable
     * @param string $identifierName
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        private readonly IdentifierResolver $identifierResolver,
        $mainTable = 'magento_operation',
        $resourceModel = null,
        $identifierName = 'id'
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect(): static
    {
        $bulkUuid = $this->identifierResolver->execute();
        $this->getSelect()->from(['main_table' => $this->getMainTable()], ['id', 'result_message', 'error_code'])
            ->where('bulk_uuid=?', $bulkUuid)
            ->where('status=?', OperationInterface::STATUS_TYPE_RETRIABLY_FAILED)
            ->group('error_code')
            ->columns(['records_qty' => new \Zend_Db_Expr('COUNT(id)')]);
        return $this;
    }
}
