<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Analytics\Report_Xml;

interface Batch_Report_Provider_Interface
{
    public const BATCH_SIZE = 10000;
    /**
     * Returns one batch of the report data
     */
    public function get_batch_report(string $name): \Iterator_Iterator;
}