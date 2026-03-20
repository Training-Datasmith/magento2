<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

/**
 * Class QueryBuilder
 */
class Query_Builder
{
    /**
     * Select object
     *
     * @var \Magento\Framework\DB\Select
     */
    protected $select;
    /**
     * @var \Magento\Framework\Api\CriteriaInterface
     */
    protected $criteria;
    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $resource;
    /**
     * @var \Magento\Framework\DB\MapperFactory
     */
    protected $mapper_factory;
    /**
     * @var \Magento\Framework\DB\QueryFactory
     */
    protected $query_factory;
    /**
     * @param \Magento\Framework\DB\MapperFactory $mapperFactory
     * @param \Magento\Framework\DB\QueryFactory $queryFactory
     */
    public function __construct(\Magento\Framework\DB\Mapper_Factory $mapper_factory, \Magento\Framework\DB\Query_Factory $query_factory)
    {
        $this->mapper_factory = $mapper_factory;
        $this->query_factory = $query_factory;
    }
    /**
     * Set source Criteria
     *
     * @param \Magento\Framework\Api\CriteriaInterface $criteria
     * @return void
     */
    public function set_criteria(\Magento\Framework\Api\Criteria_Interface $criteria)
    {
        $this->criteria = $criteria;
    }
    /**
     * Set Resource
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @return void
     */
    public function set_resource(\Magento\Framework\Model\Resource_Model\Db\Abstract_Db $resource)
    {
        $this->resource = $resource;
    }
    /**
     * @return \Magento\Framework\DB\QueryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create()
    {
        $mapper = $this->criteria->get_mapper_interface_name();
        $mapper_instance = $this->mapper_factory->create($mapper);
        $select = $mapper_instance->map($this->criteria);
        $query = $this->query_factory->create(\Magento\Framework\DB\Query::class, ['select' => $select, 'criteria' => $this->criteria, 'resource' => $this->resource]);
        return $query;
    }
}