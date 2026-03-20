<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication\Config;

use Magento\Framework\Config\Reader_Interface;
/**
 * Composite reader for communication config.
 */
class Composite_Reader implements Reader_Interface
{
    /**
     * @var ReaderInterface[]
     */
    private $readers;
    /**
     * Initialize dependencies.
     *
     * @param array $readers
     */
    public function __construct(array $readers)
    {
        usort($readers, function ($first_item, $second_item) {
            if (!isset($first_item['sortOrder']) || !isset($second_item['sortOrder'])) {
                return 0;
            }
            return $first_item['sortOrder'] <=> $second_item['sortOrder'];
        });
        $this->readers = [];
        foreach ($readers as $reader_info) {
            if (!isset($reader_info['reader'])) {
                continue;
            }
            $this->readers[] = $reader_info['reader'];
        }
    }
    /**
     * Read config.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $result = [];
        foreach ($this->readers as $reader) {
            $result = array_replace_recursive($result, $reader->read($scope));
        }
        return $result;
    }
}