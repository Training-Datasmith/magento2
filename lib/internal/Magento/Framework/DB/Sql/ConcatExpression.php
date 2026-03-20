<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Sql;

use Magento\Framework\App\Resource_Connection;
use Magento\Framework\DB\Adapter\Adapter_Interface;
/**
 * Class Concat
 */
class Concat_Expression extends Expression
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;
    /**
     * @var string[]
     */
    protected $columns;
    /**
     * @var string
     */
    protected $separator;
    /**
     * @param ResourceConnection $resource
     * @param array $columns
     * @param string $separator
     */
    public function __construct(Resource_Connection $resource, array $columns, $separator = ' ')
    {
        $this->adapter = $resource->get_connection();
        $this->columns = $columns;
        $this->separator = $separator;
    }
    /**
     * Returns SQL expression
     *   TRIM(CONCAT_WS(separator, IF(str1 <> '', str1, NULL), IF(str2 <> '', str2, NULL) ...))
     *
     * @return string
     */
    public function __toString()
    {
        $columns = [];
        foreach ($this->columns as $key => $part) {
            if (isset($part['columnName']) && $part['columnName'] instanceof \Zend_Db_Expr) {
                $column = $part['columnName'];
            } else {
                $column = $this->adapter->quote_identifier((isset($part['tableAlias']) ? $part['tableAlias'] . '.' : '') . (isset($part['columnName']) ? $part['columnName'] : $key));
            }
            $columns[] = $this->adapter->get_check_sql($column . " <> ''", $column, 'NULL');
        }
        return sprintf('TRIM(%s)', $this->adapter->get_concat_sql($columns, $this->separator));
    }
}