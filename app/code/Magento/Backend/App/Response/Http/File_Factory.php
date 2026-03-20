<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Response\Http;

use Magento\Framework\App\Filesystem\Directory_List;
/**
 * @api
 * @since 100.0.2
 */
class File_Factory extends \Magento\Framework\App\Response\Http\File_Factory
{
    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backend_url;
    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;
    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_flag;
    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $_helper;
    /**
     * @param \Magento\Framework\App\ResponseInterface $response
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Framework\App\ActionFlag $flag
     * @param \Magento\Backend\Helper\Data $helper
     */
    public function __construct(\Magento\Framework\App\Response_Interface $response, \Magento\Framework\Filesystem $filesystem, \Magento\Backend\Model\Auth $auth, \Magento\Backend\Model\Url_Interface $backend_url, \Magento\Backend\Model\Session $session, \Magento\Framework\App\Action_Flag $flag, \Magento\Backend\Helper\Data $helper)
    {
        $this->_auth = $auth;
        $this->_backend_url = $backend_url;
        $this->_session = $session;
        $this->_flag = $flag;
        $this->_helper = $helper;
        parent::__construct($response, $filesystem);
    }
    /**
     * Set redirect into response
     *
     * @param   string $path
     * @param   array $arguments
     * @return \Magento\Framework\App\ResponseInterface
     * @TODO move method
     */
    protected function _redirect($path, $arguments = [])
    {
        $this->_session->set_is_url_notice($this->_flag->get('', \Magento\Backend\App\Abstract_Action::FLAG_IS_URLS_CHECKED));
        $this->_response->set_redirect($this->_helper->get_url($path, $arguments));
        return $this->_response;
    }
    /**
     * Declare headers and content file in response for file download
     *
     * @param string $fileName
     * @param string|array $content set to null to avoid starting output, $contentLength should be set explicitly in
     * that case
     * @param string $baseDir
     * @param string $contentType
     * @param int $contentLength    explicit content length, if strlen($content) isn't applicable
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function create($file_name, $content, $base_dir = Directory_List::ROOT, $content_type = 'application/octet-stream', $content_length = null)
    {
        if ($this->_auth->get_auth_storage()->is_first_page_after_login()) {
            return $this->_redirect($this->_backend_url->get_startup_page_url());
        }
        return parent::create($file_name, $content, $base_dir, $content_type, $content_length);
    }
}