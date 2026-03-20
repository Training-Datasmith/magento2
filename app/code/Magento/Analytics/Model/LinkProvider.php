<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Api\Data\Link_Interface_Factory;
use Magento\Analytics\Api\Link_Provider_Interface;
use Magento\Framework\Exception\No_Such_Entity_Exception;
use Magento\Framework\Url_Interface;
use Magento\Store\Model\Store_Manager_Interface;
/**
 * Provides link to file with collected report data.
 */
class Link_Provider implements Link_Provider_Interface
{
    /**
     * @var LinkInterfaceFactory
     */
    private $link_factory;
    public function __construct(Link_Interface_Factory $link_factory, private readonly File_Info_Manager $file_info_manager, private readonly Store_Manager_Interface $store_manager)
    {
        $this->link_factory = $link_factory;
    }
    /**
     * Returns base url to file according to store configuration
     */
    private function get_base_url(File_Info $file_info): string
    {
        return $this->store_manager->get_store()->get_base_url(Url_Interface::URL_TYPE_MEDIA) . $file_info->get_path();
    }
    /**
     * Verify is requested file ready
     */
    private function is_file_ready(File_Info $file_info): bool
    {
        return $file_info->get_path() && $file_info->get_initialization_vector();
    }
    /**
     * @inheritdoc
     */
    public function get()
    {
        $file_info = $this->file_info_manager->load();
        if (!$this->is_file_ready($file_info)) {
            throw new No_Such_Entity_Exception(__('File is not ready yet.'));
        }
        return $this->link_factory->create(['url' => $this->get_base_url($file_info), 'initializationVector' => base64_encode($file_info->get_initialization_vector())]);
    }
}