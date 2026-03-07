<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\DB\Select;

/**
 * Query object, contains SQL statement, information about connection, query arguments
 */
class Query implements \JsonSerializable
{
    private ?object $selectCount = null;

    /**
     * Query constructor.
     *
     * @param string $connectionName
     * @param array $config
     */
    public function __construct(private readonly Select $select, private readonly SelectHydrator $selectHydrator, private $connectionName, private $config)
    {
    }

    /**
     * Returns query select
     *
     * @return Select
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Returns Connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Returns configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'connectionName' => $this->getConnectionName(),
            'select_parts' => $this->selectHydrator->extract($this->getSelect()),
            'config' => $this->getConfig(),
        ];
    }

    /**
     * Get SQL for get record count
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function getSelectCountSql(): Select
    {
        if (!$this->selectCount) {
            $this->selectCount = clone $this->getSelect();
            $this->selectCount->reset(\Magento\Framework\DB\Select::ORDER);
            $this->selectCount->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
            $this->selectCount->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
            $this->selectCount->reset(\Magento\Framework\DB\Select::COLUMNS);

            $part = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
            if (!is_array($part) || !count($part)) {
                $this->selectCount->columns(new \Zend_Db_Expr('COUNT(*)'));
                return $this->selectCount;
            }

            $this->selectCount->reset(\Magento\Framework\DB\Select::GROUP);
            $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
            $this->selectCount->columns(new \Zend_Db_Expr(('COUNT(DISTINCT '.implode(', ', $group).')')));
        }
        return $this->selectCount;
    }
}
