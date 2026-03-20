<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\DB\Ddl;

/**
 * Class Sequence represents DDL for manage sequences
 */
class Sequence
{
    /**
     * Return SQL for create sequence
     *
     * @param string $name The name of table in create statement
     * @param int $startNumber The auto increment start number
     * @param string $columnType Type of sequence_value column
     * @param bool|true $unsigned Flag to set sequence_value as UNSIGNED field
     * @return string
     */
    public function get_create_sequence_ddl($name, $start_number = 1, $column_type = Table::TYPE_INTEGER, $unsigned = true)
    {
        $format = 'CREATE TABLE %s (
                     sequence_value %s %s NOT NULL AUTO_INCREMENT,
                     PRIMARY KEY (sequence_value)
            ) AUTO_INCREMENT = %d ENGINE = INNODB';
        return sprintf($format, $name, $column_type, $unsigned ? 'UNSIGNED' : '', $start_number);
    }
    /**
     * Return SQL for drop sequence
     *
     * @param string $name
     * @return string
     */
    public function drop_sequence($name)
    {
        $format = 'DROP TABLE %s';
        return sprintf($format, $name);
    }
}