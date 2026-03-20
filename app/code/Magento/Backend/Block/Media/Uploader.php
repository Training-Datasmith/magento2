<?php

/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Block\Media;

use Magento\Backend\Model\Image\Upload_Resize_Config_Interface;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Image\Adapter\Upload_Config_Interface;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Adminhtml media library uploader
 * @api
 * @since 100.0.2
 */
class Uploader extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_config;
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::media/uploader.phtml';
    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_file_size_service;
    /**
     * @var Json
     */
    private $json_encoder;
    /**
     * @var UploadResizeConfigInterface
     */
    private $image_upload_config;
    /**
     * @var UploadConfigInterface
     * @deprecated 101.0.1
     * @see \Magento\Backend\Model\Image\UploadResizeConfigInterface
     */
    private $image_config;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\File\Size $fileSize
     * @param array $data
     * @param Json|null $jsonEncoder
     * @param UploadConfigInterface|null $imageConfig
     * @param UploadResizeConfigInterface|null $imageUploadConfig
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, \Magento\Framework\File\Size $file_size, array $data = [], ?Json $json_encoder = null, ?Upload_Config_Interface $image_config = null, ?Upload_Resize_Config_Interface $image_upload_config = null)
    {
        $this->_file_size_service = $file_size;
        $this->json_encoder = $json_encoder ?: Object_Manager::get_instance()->get(Json::class);
        $this->image_config = $image_config ?: Object_Manager::get_instance()->get(Upload_Config_Interface::class);
        $this->image_upload_config = $image_upload_config ?: Object_Manager::get_instance()->get(Upload_Resize_Config_Interface::class);
        parent::__construct($context, $data);
    }
    /**
     * Initialize block.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->set_id($this->get_id() . '_Uploader');
        $upload_url = $this->_url_builder->get_url('adminhtml/*/upload');
        $this->get_config()->set_url($upload_url);
        $this->get_config()->set_params(['form_key' => $this->get_form_key()]);
        $this->get_config()->set_file_field('file');
        $this->get_config()->set_filters(['images' => ['label' => __('Images (.gif, .jpg, .png)'), 'files' => ['*.gif', '*.jpg', '*.png']], 'media' => ['label' => __('Media (.avi, .flv, .swf)'), 'files' => ['*.avi', '*.flv', '*.swf']], 'all' => ['label' => __('All Files'), 'files' => ['*.*']]]);
    }
    /**
     * Get file size
     *
     * @return \Magento\Framework\File\Size
     */
    public function get_file_size_service()
    {
        return $this->_file_size_service;
    }
    /**
     * Get Image Upload Maximum Width Config.
     *
     * @return int
     * @since 100.2.7
     */
    public function get_image_upload_max_width()
    {
        return $this->image_upload_config->get_max_width();
    }
    /**
     * Get Image Upload Maximum Height Config.
     *
     * @return int
     * @since 100.2.7
     */
    public function get_image_upload_max_height()
    {
        return $this->image_upload_config->get_max_height();
    }
    /**
     * Prepares layout and set element renderer
     *
     * @return $this
     */
    protected function _prepare_layout()
    {
        $this->page_config->add_page_asset('jquery/uppy/dist/uppy.fileupload-ui.css');
        return parent::_prepare_layout();
    }
    /**
     * Retrieve uploader js object name
     *
     * @return string
     */
    public function get_js_object_name()
    {
        return $this->get_html_id() . 'JsObject';
    }
    /**
     * Retrieve config json
     *
     * @return string
     */
    public function get_config_json()
    {
        return $this->json_encoder->encode($this->get_config()->get_data());
    }
    /**
     * Retrieve config object
     *
     * @return \Magento\Framework\DataObject
     */
    public function get_config()
    {
        if (null === $this->_config) {
            $this->_config = new \Magento\Framework\Data_Object();
        }
        return $this->_config;
    }
    /**
     * Retrieve full uploader SWF's file URL
     * Implemented to solve problem with cross domain SWFs
     * Now uploader can be only in the same URL where backend located
     *
     * @param string $url url to uploader in current theme
     * @return string full URL
     */
    public function get_uploader_url($url)
    {
        return $this->_asset_repo->get_url($url);
    }
}