<?php

/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer\Table;

/**
 * Interface \Magento\Framework\Indexer\Table\StrategyInterface
 *
 * @api
 */
interface StrategyInterface
{
    public const IDX_SUFFIX = '_idx';

    public const TMP_SUFFIX = '_tmp';

    /**
     * Get IDX table usage flag
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getUseIdxTable();

    /**
     * Set IDX table usage flag
     *
     * @param bool $value
     *
     * @return $this
     */
    public function setUseIdxTable($value = false);

    /**
     * Prepare index table name
     *
     * @param string $tablePrefix
     *
     * @return string
     */
    public function prepareTableName($tablePrefix);

    /**
     * Returns target table name
     *
     * @param string $tablePrefix
     *
     * @return string
     */
    public function getTableName($tablePrefix);
}
