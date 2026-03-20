<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Captcha\Cron;

use Magento\Captcha\Cron\Magento\Framework\Filesystem\Io\File;
use Magento\Captcha\Helper\Data;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver_Pool;
use Magento\Store\Model\Store_Manager;
/**
 * Captcha cron actions
 */
class Delete_Expired_Images
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    protected $_helper;
    /**
     * CAPTCHA helper
     *
     * @var \Magento\Captcha\Helper\Adminhtml\Data
     */
    protected $_admin_helper;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_media_directory;
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_store_manager;
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_file_info;
    /**
     * @param Data $helper
     * @param \Magento\Captcha\Helper\Adminhtml\Data $adminHelper
     * @param Filesystem $filesystem
     * @param StoreManager $storeManager
     * @param File $fileInfo
     */
    public function __construct(\Magento\Captcha\Helper\Data $helper, \Magento\Captcha\Helper\Adminhtml\Data $admin_helper, \Magento\Framework\Filesystem $filesystem, \Magento\Store\Model\Store_Manager $store_manager, \Magento\Framework\Filesystem\Io\File $file_info)
    {
        $this->_helper = $helper;
        $this->_admin_helper = $admin_helper;
        $this->_media_directory = $filesystem->get_directory_write(Directory_List::MEDIA, Driver_Pool::FILE);
        $this->_store_manager = $store_manager;
        $this->_file_info = $file_info;
    }
    /**
     * Delete Expired Captcha Images
     *
     * @return \Magento\Captcha\Cron\DeleteExpiredImages
     * @throws FileSystemException
     */
    public function execute()
    {
        foreach ($this->_store_manager->get_websites() as $website) {
            $this->_delete_expired_images_for_website($this->_helper, $website, $website->get_default_store());
        }
        $this->_delete_expired_images_for_website($this->_admin_helper);
        return $this;
    }
    /**
     * Delete Expired Captcha Images for specific website
     *
     * @param \Magento\Captcha\Helper\Data $helper
     * @param \Magento\Store\Model\Website|null $website
     * @param \Magento\Store\Model\Store|null $store
     * @return void
     * @throws FileSystemException
     */
    protected function _delete_expired_images_for_website(\Magento\Captcha\Helper\Data $helper, ?\Magento\Store\Model\Website $website = null, ?\Magento\Store\Model\Store $store = null)
    {
        $expire = time() - (int) $helper->get_config('timeout', $store) * 60;
        $image_directory = $this->_media_directory->get_relative_path($helper->get_img_dir($website));
        foreach ($this->_media_directory->read($image_directory) as $file_path) {
            if ($this->_media_directory->is_file($file_path) && $this->_file_info->get_path_info($file_path)['extension'] === 'png' && $this->_media_directory->stat($file_path)['mtime'] < $expire) {
                $this->_media_directory->delete($file_path);
            }
        }
    }
}