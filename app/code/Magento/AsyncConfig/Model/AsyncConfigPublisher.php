<?php

/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Async_Config\Model;

use Magento\Async_Config\Api\Data\Async_Config_Message_Interface_Factory;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Message_Queue\Publisher_Interface;
use Magento\Framework\Serialize\Serializer\Json;
class Async_Config_Publisher implements \Magento\Async_Config\Api\Async_Config_Publisher_Interface
{
    /**
     * @var AsyncConfigMessageInterfaceFactory
     */
    private $async_config_factory;
    public function __construct(Async_Config_Message_Interface_Factory $async_config_factory, private readonly Publisher_Interface $message_publisher, private readonly Json $serializer, private readonly \Magento\Framework\Filesystem\Directory_List $dir, private readonly File $file)
    {
        $this->async_config_factory = $async_config_factory;
    }
    /**
     * @inheritDoc
     */
    public function save_config_data(array $config_data): void
    {
        $async_config = $this->async_config_factory->create();
        $this->save_images($config_data);
        $async_config->set_config_data($this->serializer->serialize($config_data));
        $this->message_publisher->publish('async_config.saveConfig', $async_config);
    }
    /**
     * Save Images to temporary Path
     *
     * @throws FileSystemException
     */
    private function save_images(array &$config_data): void
    {
        if (isset($config_data['groups']['placeholder'])) {
            $this->change_image_path($config_data['groups']['placeholder']['fields']);
        } elseif (isset($config_data['groups']['identity'])) {
            $this->change_image_path($config_data['groups']['identity']['fields']);
        }
    }
    /**
     * Change Placeholder Data path if exists
     *
     * @throws FileSystemException
     */
    private function change_image_path(array &$fields): void
    {
        foreach ($fields as &$data) {
            if (!empty($data['value']['tmp_name'])) {
                $new_path = $this->dir->get_path(Directory_List::MEDIA) . '/' . pathinfo((string) $data['value']['tmp_name'])['filename'];
                $this->file->mv($data['value']['tmp_name'], $new_path);
                $data['value']['tmp_name'] = $new_path;
            }
        }
    }
}