<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backup\Model\Resource_Model\View;

use Magento\Framework\App\Resource_Connection;
/**
 * Get list of database views.
 */
class Get_List_Views
{
    private const TABLE_TYPE = 'VIEW';
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
     * Get list of database views.
     *
     * @return array
     */
    public function execute(): array
    {
        return $this->resource->get_connection('backup')->fetch_col('SHOW FULL TABLES WHERE `Table_type` = ?', self::TABLE_TYPE);
    }
}