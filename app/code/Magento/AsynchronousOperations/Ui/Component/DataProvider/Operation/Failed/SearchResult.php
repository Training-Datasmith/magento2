<?php

declare (strict_types=1);
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Asynchronous_Operations\Ui\Component\Data_Provider\Operation\Failed;

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
     * @param string $identifierName identifier field name for collection items
     */
    public function __construct(Entity_Factory $entity_factory, Logger $logger, Fetch_Strategy $fetch_strategy, Event_Manager $event_manager, private readonly Identifier_Resolver $identifier_resolver, private readonly \Magento\Framework\Json\Helper\Data $json_helper, $main_table = 'magento_operation', $resource_model = null, $identifier_name = 'id')
    {
        parent::__construct($entity_factory, $logger, $fetch_strategy, $event_manager, $main_table, $resource_model, $identifier_name);
    }
    /**
     * {@inheritdoc}
     */
    protected function _init_select(): static
    {
        $bulk_uuid = $this->identifier_resolver->execute();
        $this->get_select()->from(['main_table' => $this->get_main_table()], ['id', 'result_message', 'serialized_data'])->where('bulk_uuid=?', $bulk_uuid)->where('status=?', Operation_Interface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function _after_load(): static
    {
        parent::_after_load();
        foreach ($this->_items as $key => $item) {
            try {
                $unserialized_data = $this->json_helper->json_decode($item['serialized_data']);
            } catch (\Exception $e) {
                $this->_logger->error($e->get_message());
                $unserialized_data = [];
            }
            $this->_items[$key]->set_data('meta_information', $this->provide_meta_info($unserialized_data));
            $this->_items[$key]->set_data('link', $this->get_link($unserialized_data));
            $this->_items[$key]->set_data('entity_id', $this->get_entity_id($unserialized_data));
        }
        return $this;
    }
    /**
     * Provide meta info by serialized data
     *
     * @return string
     */
    private function provide_meta_info(array $item)
    {
        return $item['meta_information'] ?? '';
    }
    /**
     * Get link from serialized data
     *
     * @return string
     */
    private function get_link(array $item)
    {
        return $item['entity_link'] ?? '';
    }
    /**
     * Get entity id from serialized data
     *
     * @return string
     */
    private function get_entity_id(array $item)
    {
        return $item['entity_id'] ?? '';
    }
}