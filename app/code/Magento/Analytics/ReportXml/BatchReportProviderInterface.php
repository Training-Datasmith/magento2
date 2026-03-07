<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Analytics\ReportXml;

interface BatchReportProviderInterface
{
    public const BATCH_SIZE = 10000;

    /**
     * Returns one batch of the report data
     */
    public function getBatchReport(string $name): \IteratorIterator;
}
