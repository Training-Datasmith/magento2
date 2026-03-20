<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Report_Xml\Config;

use Magento\Framework\Config\Reader_Interface;
/**
 * A composite reader of reports configuration.
 *
 * Reads configuration data using declared readers.
 */
class Reader implements Reader_Interface
{
    /**
     * @param array $readers
     */
    public function __construct(
        private readonly Mapper $mapper,
        /**
         * A list of declared readers.
         *
         * The list may be configured in each module via '/etc/di.xml'.
         */
        private $readers = []
    )
    {
    }
    /**
     * Reads configuration according to the given scope.
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $data = [];
        foreach ($this->readers as $reader) {
            $data = array_merge_recursive($data, $reader->read($scope));
        }
        return $this->mapper->execute($data);
    }
}