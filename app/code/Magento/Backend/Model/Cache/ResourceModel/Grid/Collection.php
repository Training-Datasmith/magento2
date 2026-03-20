<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Model\Cache\Resource_Model\Grid;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Data\Collection
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cache_type_list;
    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(\Magento\Framework\Data\Collection\Entity_Factory $entity_factory, \Magento\Framework\App\Cache\Type_List_Interface $cache_type_list)
    {
        $this->_cache_type_list = $cache_type_list;
        parent::__construct($entity_factory);
    }
    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load_data($print_query = false, $log_query = false)
    {
        if (!$this->is_loaded()) {
            foreach ($this->_cache_type_list->get_types() as $type) {
                $this->add_item($type);
            }
            $this->_set_is_loaded(true);
        }
        return $this;
    }
}