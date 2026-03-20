<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Model\Resource_Model\Table;

use Magento\Framework\App\Resource_Connection;
/**
 * Provides full list of tables in the database. This list excludes views, to allow different backup process.
 */
class Get_List_Tables
{
    private const TABLE_TYPE = 'BASE TABLE';
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @param ResourceConnection $resource
     */
    public function __construct(Resource_Connection $resource)
    {
        $this->resource = $resource;
    }
    /**
     * Get list of database tables excluding views.
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->resource->get_connection('backup')->fetch_col('SHOW FULL TABLES WHERE `Table_type` = ?', self::TABLE_TYPE);
    }
}