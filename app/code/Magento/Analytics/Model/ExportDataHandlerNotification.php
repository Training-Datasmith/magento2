<?php

declare(strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Analytics\Model;

/**
 * Class which add notification behaviour to classes that handling of a new data collection for MBI.
 */
class ExportDataHandlerNotification implements ExportDataHandlerInterface
{
    /**
     * @param ExportDataHandlerInterface $exportDataHandler
     */
    public function __construct(private readonly ExportDataHandler $exportDataHandler, private readonly Connector $analyticsConnector)
    {
    }

    /**
     * @inheritdoc
     */
    public function prepareExportData()
    {
        $result = $this->exportDataHandler->prepareExportData();
        $this->analyticsConnector->execute('notifyDataChanged');
        return $result;
    }
}
