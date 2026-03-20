<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

/**
 * Class which add notification behaviour to classes that handling of a new data collection for MBI.
 */
class Export_Data_Handler_Notification implements Export_Data_Handler_Interface
{
    /**
     * @param ExportDataHandlerInterface $exportDataHandler
     */
    public function __construct(private readonly Export_Data_Handler $export_data_handler, private readonly Connector $analytics_connector)
    {
    }
    /**
     * @inheritdoc
     */
    public function prepare_export_data()
    {
        $result = $this->export_data_handler->prepare_export_data();
        $this->analytics_connector->execute('notifyDataChanged');
        return $result;
    }
}