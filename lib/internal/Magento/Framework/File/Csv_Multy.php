<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Csv parse
 */
namespace Magento\Framework\File;

class Csv_Multy extends \Magento\Framework\File\Csv
{
    /**
     * Retrieve CSV file data as pairs with duplicates
     *
     * @param   string $file
     * @param   int $keyIndex
     * @param   int $valueIndex
     * @return  array
     */
    public function get_data_pairs($file, $key_index = 0, $value_index = 1)
    {
        $data = [];
        $csv_data = $this->get_data($file);
        $line_number = 0;
        foreach ($csv_data as $row_data) {
            $line_number++;
            if (isset($row_data[$key_index])) {
                if (isset($data[$row_data[$key_index]])) {
                    if (isset($data[$row_data[$key_index]]['duplicate'])) {
                        $data[$row_data[$key_index]]['duplicate']['line'] .= ', ' . $line_number;
                    } else {
                        $tmp_value = $data[$row_data[$key_index]]['value'];
                        $tmp_line = $data[$row_data[$key_index]]['line'];
                        $data[$row_data[$key_index]]['duplicate'] = [];
                        $data[$row_data[$key_index]]['duplicate']['line'] = $tmp_line . ' ,' . $line_number;
                        $data[$row_data[$key_index]]['duplicate']['value'] = $tmp_value;
                    }
                } else {
                    $data[$row_data[$key_index]] = [];
                    $data[$row_data[$key_index]]['line'] = $line_number;
                    $data[$row_data[$key_index]]['value'] = isset($row_data[$value_index]) ? $row_data[$value_index] : null;
                }
            }
        }
        return $data;
    }
}