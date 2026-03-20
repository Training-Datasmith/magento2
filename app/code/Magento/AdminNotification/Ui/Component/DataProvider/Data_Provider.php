<?php

declare (strict_types=1);
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Ui\Component\Data_Provider;

use Magento\Admin_Notification\Model\Resource_Model\System\Message\Collection\Synchronized_Factory;
/**
 * @api
 * @since 100.2.0
 */
class Data_Provider extends \Magento\Ui\Data_Provider\Abstract_Data_Provider
{
    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     */
    public function __construct($name, $primary_field_name, $request_field_name, Synchronized_Factory $message_collection_factory, array $meta = [], array $data = [])
    {
        $this->collection = $message_collection_factory->create();
        parent::__construct($name, $primary_field_name, $request_field_name, $meta, $data);
    }
}