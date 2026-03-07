<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\ReportXml;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;

/**
 * Hydrator for report select parts
 */
class SelectHydrator
{
    /**
     * Array of supported Select parts
     */
    private array $predefinedSelectParts =
        [
            Select::DISTINCT,
            Select::COLUMNS,
            Select::UNION,
            Select::FROM,
            Select::WHERE,
            Select::GROUP,
            Select::HAVING,
            Select::ORDER,
            Select::LIMIT_COUNT,
            Select::LIMIT_OFFSET,
            Select::FOR_UPDATE,
        ];

    /**
     * @param array $selectParts
     */
    public function __construct(private readonly ResourceConnection $resourceConnection, private readonly ObjectManagerInterface $objectManager, private $selectParts = [])
    {
    }

    /**
     * Perform merge of parts
     */
    private function getSelectParts(): array
    {
        return array_merge($this->predefinedSelectParts, $this->selectParts);
    }

    /**
     * Extracts Select metadata parts
     *
     * @throws \Zend_Db_Select_Exception
     */
    public function extract(Select $select): array
    {
        $parts = [];
        foreach ($this->getSelectParts() as $partName) {
            $parts[$partName] = $select->getPart($partName);
        }
        return $parts;
    }

    /**
     * Set parts to the select object
     *
     * @return Select
     */
    public function recreate(array $selectParts)
    {
        $select = $this->resourceConnection->getConnection()->select();

        $select = $this->processColumns($select, $selectParts);

        foreach ($selectParts as $partName => $partValue) {
            $select->setPart($partName, $partValue);
        }

        return $select;
    }

    /**
     * Process COLUMNS part values and add this part into select.
     *
     * If each column contains information about select expression
     * an object with the type of this expression going to be created and assigned to this column.
     */
    private function processColumns(Select $select, array &$selectParts): Select
    {
        if (!empty($selectParts[Select::COLUMNS]) && is_array($selectParts[Select::COLUMNS])) {
            $part = [];

            foreach ($selectParts[Select::COLUMNS] as $columnEntry) {
                [$correlationName, $column, $alias] = $columnEntry;
                if (!empty($column['class'])) {
                    $expression = $this->objectManager->create(
                        $column['class'],
                        $column['arguments'] ?? []
                    );
                    $part[] = [$correlationName, $expression, $alias];
                } else {
                    $part[] = $columnEntry;
                }
            }

            $select->setPart(Select::COLUMNS, $part);
            unset($selectParts[Select::COLUMNS]);
        }

        return $select;
    }
}
