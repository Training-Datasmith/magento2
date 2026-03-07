<?php

declare(strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Operation\Failed;

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
     * @param string $identifierName identifier field name for collection items
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        private readonly IdentifierResolver $identifierResolver,
        private readonly \Magento\Framework\Json\Helper\Data $jsonHelper,
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
        $this->getSelect()->from(['main_table' => $this->getMainTable()], ['id', 'result_message', 'serialized_data'])
            ->where('bulk_uuid=?', $bulkUuid)
            ->where('status=?', OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad(): static
    {
        parent::_afterLoad();
        foreach ($this->_items as $key => $item) {
            try {
                $unserializedData = $this->jsonHelper->jsonDecode($item['serialized_data']);
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                $unserializedData = [];
            }
            $this->_items[$key]->setData('meta_information', $this->provideMetaInfo($unserializedData));
            $this->_items[$key]->setData('link', $this->getLink($unserializedData));
            $this->_items[$key]->setData('entity_id', $this->getEntityId($unserializedData));
        }
        return $this;
    }

    /**
     * Provide meta info by serialized data
     *
     * @return string
     */
    private function provideMetaInfo(array $item)
    {
        return $item['meta_information'] ?? '';
    }

    /**
     * Get link from serialized data
     *
     * @return string
     */
    private function getLink(array $item)
    {
        return $item['entity_link'] ?? '';
    }

    /**
     * Get entity id from serialized data
     *
     * @return string
     */
    private function getEntityId(array $item)
    {
        return $item['entity_id'] ?? '';
    }
}
