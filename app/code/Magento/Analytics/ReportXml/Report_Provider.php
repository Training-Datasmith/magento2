<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml;

use PDO;
/**
 * Providers for reports data
 */
class Report_Provider implements Batch_Report_Provider_Interface
{
    private int $current_position = 0;
    /**
     * @var int
     */
    private $count_total = 0;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * @var Query
     */
    private $data_select;
    /**
     * @var int|null Last cursor value for cursor-based pagination
     */
    private ?int $last_cursor = null;
    /**
     * @var string|null Cursor column name for cursor-based pagination
     */
    private ?string $cursor_column = null;
    /**
     * ReportProvider constructor.
     */
    public function __construct(private readonly Query_Factory $query_factory, private readonly Connection_Factory $connection_factory, private readonly Iterator_Factory $iterator_factory)
    {
    }
    /**
     * Returns custom iterator name for report. Null for default
     *
     * @return string|null
     */
    private function get_iterator_name(Query $query)
    {
        $config = $query->get_config();
        return $config['iterator'] ?? null;
    }
    /**
     * Returns report data by name and criteria
     *
     * @param string $name
     * @return \IteratorIterator
     */
    public function get_report($name)
    {
        $query = $this->query_factory->create($name);
        $connection = $this->connection_factory->get_connection($query->get_connection_name());
        $statement = $connection->query($query->get_select());
        return $this->iterator_factory->create($statement, $this->get_iterator_name($query));
    }
    /**
     * @inheritdoc
     */
    public function get_batch_report(string $name): \Iterator_Iterator
    {
        if (!$this->data_select || $this->data_select->get_config()['name'] !== $name) {
            $this->data_select = $this->query_factory->create($name);
            $this->last_cursor = null;
            $this->current_position = 0;
            $this->count_total = 0;
            $this->connection = $this->connection_factory->get_connection($this->data_select->get_connection_name());
            $this->cursor_column = $this->get_cursor_column();
            if (!$this->cursor_column) {
                $this->count_total = $this->connection->fetch_one($this->data_select->get_select_count_sql());
            }
        }
        if (!$this->cursor_column) {
            return $this->get_batch_report_with_offset();
        }
        $select = clone $this->data_select->get_select();
        $cursor_value = $this->last_cursor ?? 0;
        $select->where(sprintf('%s > ?', $this->cursor_column), $cursor_value);
        $select->order(sprintf('%s ASC', $this->cursor_column));
        $select->limit(self::BATCH_SIZE);
        $statement = $this->connection->query($select);
        $rows = $statement->fetch_all(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return $this->iterator_factory->create(new \ArrayIterator([]), $this->get_iterator_name($this->data_select));
        }
        $last_row = $rows[count($rows) - 1];
        $this->last_cursor = $last_row[$this->cursor_column] ?? null;
        return $this->iterator_factory->create(new \ArrayIterator($rows), $this->get_iterator_name($this->data_select));
    }
    /**
     * Detect cursor column based on the source table's primary key
     *
     * @return string|null Returns the primary key column name, or null if not found
     */
    private function get_cursor_column(): ?string
    {
        $config = $this->data_select->get_config();
        $table_name = $config['source']['name'] ?? null;
        $analytic_tables = ['customer_entity', 'sales_order', 'sales_order_address', 'quote', 'catalog_product_entity'];
        if (!$table_name) {
            return null;
        }
        if (in_array($table_name, $analytic_tables)) {
            return 'entity_id';
        }
        if ($table_name == 'sales_order_item') {
            return 'item_id';
        }
        return null;
    }
    /**
     * Fallback to offset-based pagination when cursor column cannot be detected
     */
    private function get_batch_report_with_offset(): \Iterator_Iterator
    {
        if ($this->current_position >= $this->count_total) {
            return $this->iterator_factory->create(new \ArrayIterator([]), $this->get_iterator_name($this->data_select));
        }
        $statement = $this->connection->query($this->data_select->get_select()->limit(self::BATCH_SIZE, $this->current_position));
        $this->current_position += self::BATCH_SIZE;
        return $this->iterator_factory->create($statement, $this->get_iterator_name($this->data_select));
    }
}