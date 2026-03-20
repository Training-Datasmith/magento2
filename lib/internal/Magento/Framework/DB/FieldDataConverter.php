<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\DB\Adapter\Adapter_Interface;
use Magento\Framework\DB\Data_Converter\Data_Conversion_Exception;
use Magento\Framework\DB\Data_Converter\Data_Converter_Interface;
use Magento\Framework\DB\Query\Batch_Range_Iterator_Factory;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select\Query_Modifier_Interface;
/**
 * Convert field data from one representation to another
 */
class Field_Data_Converter
{
    /**
     * Batch size env variable name
     */
    public const BATCH_SIZE_VARIABLE_NAME = 'DATA_CONVERTER_BATCH_SIZE';
    /**
     * Default batch size for data converter
     */
    public const DEFAULT_BATCH_SIZE = 50000;
    /**
     * @var Generator
     */
    private $query_generator;
    /**
     * @var DataConverterInterface
     */
    private $data_converter;
    /**
     * @var SelectFactory
     */
    private $select_factory;
    /**
     * @var string|null
     */
    private $env_batch_size;
    /**
     * @var BatchRangeIteratorFactory
     */
    private $batch_iterator_factory;
    /**
     * Constructor
     *
     * @param Generator $queryGenerator
     * @param DataConverterInterface $dataConverter
     * @param SelectFactory $selectFactory
     * @param string|null $envBatchSize
     * @param BatchRangeIteratorFactory|null $batchIteratorFactory
     */
    public function __construct(Generator $query_generator, Data_Converter_Interface $data_converter, Select_Factory $select_factory, $env_batch_size = null, ?Batch_Range_Iterator_Factory $batch_iterator_factory = null)
    {
        $this->query_generator = $query_generator;
        $this->data_converter = $data_converter;
        $this->select_factory = $select_factory;
        $this->env_batch_size = $env_batch_size;
        $this->batch_iterator_factory = $batch_iterator_factory ?? Object_Manager::get_instance()->get(Batch_Range_Iterator_Factory::class);
    }
    /**
     * Convert table field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param string $identifier
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @throws FieldDataConversionException
     * @return void
     */
    public function convert(Adapter_Interface $connection, $table, $identifier, $field, ?Query_Modifier_Interface $query_modifier = null)
    {
        $identifiers = explode(',', (string) $identifier);
        if (count($identifiers) > 1) {
            $this->process_table_with_composite_identifier($connection, $table, $identifiers, $field, $query_modifier);
        } else {
            $this->process_table_with_unique_identifier($connection, $table, $identifier, $field, $query_modifier);
        }
    }
    /**
     * Convert table (with unique identifier) field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param string $identifier
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @return void
     */
    private function process_table_with_unique_identifier(Adapter_Interface $connection, $table, $identifier, $field, ?Query_Modifier_Interface $query_modifier = null): void
    {
        $select = $this->select_factory->create($connection)->from($table, [$identifier, $field])->where($field . ' IS NOT NULL');
        if ($query_modifier) {
            $query_modifier->modify($select);
        }
        $iterator = $this->query_generator->generate($identifier, $select, $this->get_batch_size());
        foreach ($iterator as $select_by_range) {
            $rows = $connection->fetch_pairs($select_by_range);
            $unique_field_data_array = array_unique($rows);
            foreach ($unique_field_data_array as $unique_field_data) {
                $ids = array_keys($rows, $unique_field_data);
                try {
                    $converted_value = $this->data_converter->convert($unique_field_data);
                    if ($unique_field_data === $converted_value) {
                        // Skip for data rows that have been already converted
                        continue;
                    }
                    $bind = [$field => $converted_value];
                    $where = [$identifier . ' IN (?)' => $ids];
                    $connection->update($table, $bind, $where);
                } catch (Data_Conversion_Exception $e) {
                    throw new \Magento\Framework\DB\Field_Data_Conversion_Exception(sprintf(\Magento\Framework\DB\Field_Data_Conversion_Exception::MESSAGE_PATTERN, $field, $table, $identifier, implode(', ', $ids), get_class($this->data_converter), $e->get_message()));
                }
            }
        }
    }
    /**
     * Convert table (with composite identifier) field data from one representation to another
     *
     * @param AdapterInterface $connection
     * @param string $table
     * @param array $identifiers
     * @param string $field
     * @param QueryModifierInterface|null $queryModifier
     * @return void
     */
    private function process_table_with_composite_identifier(Adapter_Interface $connection, $table, $identifiers, $field, ?Query_Modifier_Interface $query_modifier = null): void
    {
        $columns = $identifiers;
        $columns[] = $field;
        $select = $this->select_factory->create($connection)->from($table, $columns)->where($field . ' IS NOT NULL');
        if ($query_modifier) {
            $query_modifier->modify($select);
        }
        $iterator = $this->batch_iterator_factory->create(['batchSize' => $this->get_batch_size(), 'select' => $select, 'correlationName' => $table, 'rangeField' => $identifiers, 'rangeFieldAlias' => '']);
        foreach ($iterator as $select_by_range) {
            $rows = [];
            foreach ($connection->fetch_all($select_by_range) as $row) {
                $value = $row[$field];
                unset($row[$field]);
                $constraints = [];
                foreach ($row as $col => $val) {
                    $constraints[] = $connection->prepare_sql_condition($col, $val);
                }
                $rows[implode(' AND ', $constraints)] = $value;
            }
            $unique_field_data_array = array_unique($rows);
            foreach ($unique_field_data_array as $unique_field_data) {
                $constraints = array_keys($rows, $unique_field_data);
                try {
                    $converted_value = $this->data_converter->convert($unique_field_data);
                    if ($unique_field_data === $converted_value) {
                        // Skip for data rows that have been already converted
                        continue;
                    }
                    $bind = [$field => $converted_value];
                    foreach ($constraints as $where) {
                        $connection->update($table, $bind, $where);
                    }
                } catch (Data_Conversion_Exception $e) {
                    throw new \Magento\Framework\DB\Field_Data_Conversion_Exception(sprintf(\Magento\Framework\DB\Field_Data_Conversion_Exception::MESSAGE_PATTERN, $field, $table, implode(', ', $identifiers), '(' . implode(') OR (', $constraints) . ')', get_class($this->data_converter), $e->get_message()));
                }
            }
        }
    }
    /**
     * Get batch size from environment variable or default
     *
     * @return int
     */
    private function get_batch_size()
    {
        if (null !== $this->env_batch_size) {
            $raw_value = (string) $this->env_batch_size;
            $numeric = preg_replace('#[^0-9]+#', '', $raw_value);
            // Reject empty, non-numeric or out-of-range values before any integer cast (avoid PHP 8.1+ warnings)
            if ($numeric === '' || bccomp($numeric, (string) PHP_INT_MAX, 0) === 1) {
                throw new \InvalidArgumentException('Invalid value for environment variable ' . self::BATCH_SIZE_VARIABLE_NAME . '. ' . 'Should be integer, >= 1 and < value of PHP_INT_MAX');
            }
            $batch_size = (int) $numeric;
            if ($batch_size < 1) {
                throw new \InvalidArgumentException('Invalid value for environment variable ' . self::BATCH_SIZE_VARIABLE_NAME . '. ' . 'Should be integer, >= 1 and < value of PHP_INT_MAX');
            }
            return $batch_size;
        }
        return self::DEFAULT_BATCH_SIZE;
    }
}