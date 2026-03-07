<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\DataProvider;

use Magento\AsynchronousOperations\Model\BulkStatus\CalculatedStatusSql;
use Magento\AsynchronousOperations\Model\StatusMapper;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Bulk\BulkSummaryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Psr\Log\LoggerInterface as Logger;

class SearchResult extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var array|int
     */
    private $operationStatus;

    /**
     * @param string $mainTable
     * @param AbstractResource $resourceModel
     * @param string $identifierName
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        private readonly UserContextInterface $userContext,
        private readonly StatusMapper $statusMapper,
        private readonly CalculatedStatusSql $calculatedStatusSql,
        $mainTable = 'magento_bulk',
        $resourceModel = null,
        $identifierName = 'uuid'
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
     * @inheritdoc
     */
    protected function _initSelect(): static
    {
        $this->getSelect()->from(
            ['main_table' => $this->getMainTable()],
            [
                '*',
                'status' => $this->calculatedStatusSql->get($this->getTable('magento_operation')),
            ]
        )->where(
            'user_id=?',
            $this->userContext->getUserId()
        )->where(
            'user_type=?',
            UserContextInterface::USER_TYPE_ADMIN
        )->orWhere(
            'user_type=?',
            UserContextInterface::USER_TYPE_INTEGRATION
        );
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        /** @var BulkSummaryInterface $item */
        foreach ($this->getItems() as $item) {
            $item->setStatus($this->statusMapper->operationStatusToBulkSummaryStatus($item->getStatus()));
        }
        return parent::_afterLoad();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'status') {
            if (is_array($condition)) {
                foreach ($condition as $value) {
                    $this->operationStatus = $this->statusMapper->bulkSummaryStatusToOperationStatus($value);
                    if (is_array($this->operationStatus)) {
                        foreach ($this->operationStatus as $statusValue) {
                            $this->getSelect()->orHaving('status = ?', $statusValue);
                        }
                        continue;
                    }
                    $this->getSelect()->having('status = ?', $this->operationStatus);
                }
            }
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @inheritdoc
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->columns(['status' => $this->calculatedStatusSql->get($this->getTable('magento_operation'))]);
        //add grouping by status if filtering by status was executed
        if (isset($this->operationStatus)) {
            $select->group('status');
        }
        return $select;
    }
}
