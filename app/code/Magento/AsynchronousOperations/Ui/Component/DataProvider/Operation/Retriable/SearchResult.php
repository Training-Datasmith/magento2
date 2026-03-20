<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Data_Provider\Operation\Retriable;

use Magento\Asynchronous_Operations\Ui\Component\Data_Provider\Bulk\Identifier_Resolver;
use Magento\Framework\Bulk\Operation_Interface;
use Magento\Framework\Data\Collection\Db\Fetch_Strategy_Interface as FetchStrategy;
use Magento\Framework\Data\Collection\Entity_Factory_Interface as EntityFactory;
use Magento\Framework\Event\Manager_Interface as EventManager;
use Psr\Log\Logger_Interface as Logger;
/**
 * Class SearchResult
 */
class Search_Result extends \Magento\Framework\View\Element\Ui_Component\Data_Provider\Search_Result
{
    /**
     * SearchResult constructor.
     * @param string $mainTable
     * @param string $identifierName
     */
    public function __construct(Entity_Factory $entity_factory, Logger $logger, Fetch_Strategy $fetch_strategy, Event_Manager $event_manager, private readonly Identifier_Resolver $identifier_resolver, $main_table = 'magento_operation', $resource_model = null, $identifier_name = 'id')
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $main_table, $resource_model, $identifier_name);
    }
    /**
     * {@inheritdoc}
     */
    protected function _init_select(): static
    {
        $bulk_uuid = $this->identifier_resolver->execute();
        $this->get_select()->from(['main_table' => $this->get_main_table()], ['id', 'result_message', 'error_code'])->where('bulk_uuid=?', $bulk_uuid)->where('status=?', Operation_Interface::STATUS_TYPE_RETRIABLY_FAILED)->group('error_code')->columns(['records_qty' => new \Zend_Db_Expr('COUNT(id)')]);
        return $this;
    }
}