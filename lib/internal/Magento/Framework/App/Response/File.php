<?php

/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Response;

use InvalidArgumentException;
use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Page_Cache\Not_Cacheable_Interface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\File_System_Exception;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File\Mime;
use Magento\Framework\Session\Config\Config_Interface;
use Magento\Framework\Stdlib\Cookie\Cookie_Metadata_Factory;
use Magento\Framework\Stdlib\Cookie_Manager_Interface;
use Magento\Framework\Stdlib\DateTime;
/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class File extends Http implements Not_Cacheable_Interface
{
    private const DEFAULT_RAW_CONTENT_TYPE = 'application/octet-stream';
    /**
     * @var Http
     */
    private Http $response;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var Mime
     */
    private Mime $mime;
    /**
     * @var array
     */
    private array $options = [
        'directoryCode' => Directory_List::ROOT,
        'filePath' => null,
        // File name to send to the client
        'fileName' => null,
        'contentType' => null,
        'contentLength' => null,
        // Whether to remove the file after it is sent to the client
        'remove' => false,
        // Whether to send the file as attachment
        'attachment' => true,
    ];
    /**
     * @param HttpRequest $request
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Context $context
     * @param DateTime $dateTime
     * @param ConfigInterface $sessionConfig
     * @param Http $response
     * @param Filesystem $filesystem
     * @param Mime $mime
     * @param array $options
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(Http_Request $request, Cookie_Manager_Interface $cookie_manager, Cookie_Metadata_Factory $cookie_metadata_factory, Context $context, DateTime $date_time, Config_Interface $session_config, Http $response, Filesystem $filesystem, Mime $mime, array $options = [])
    {
        parent::__construct($request, $cookie_manager, $cookie_metadata_factory, $context, $date_time, $session_config);
        $this->response = $response;
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->options = array_merge($this->options, $options);
        if (!isset($this->options['filePath'])) {
            if (!isset($this->options['fileName'])) {
                throw new InvalidArgumentException('File name is required.');
            }
            $this->options['contentType'] ??= self::DEFAULT_RAW_CONTENT_TYPE;
        }
    }
    /**
     * @inheritDoc
     */
    public function send_response()
    {
        $dir = $this->filesystem->get_directory_read($this->options['directoryCode']);
        $force_headers = true;
        if (isset($this->options['filePath'])) {
            if (!$dir->is_exist($this->options['filePath'])) {
                throw new InvalidArgumentException("File '{$this->options['filePath']}' does not exists.");
            }
            $file_path = $this->options['filePath'];
            $this->options['contentType'] ??= $dir->stat($file_path)['mimeType'] ?? $this->mime->get_mime_type($dir->get_absolute_path($file_path));
            $this->options['contentLength'] ??= $dir->stat($file_path)['size'];
            $this->options['fileName'] ??= basename($file_path);
        } else {
            $this->options['contentLength'] = mb_strlen((string) $this->response->get_content(), '8bit');
            $force_headers = false;
        }
        $this->response->set_http_response_code(200);
        if ($this->options['attachment']) {
            $this->response->set_header('Content-Disposition', 'attachment; filename="' . $this->options['fileName'] . '"', $force_headers);
        }
        $this->response->set_header('Content-Type', $this->options['contentType'], $force_headers)->set_header('Content-Length', $this->options['contentLength'], $force_headers)->set_header('Pragma', 'public', $force_headers)->set_header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', $force_headers)->set_header('Last-Modified', date('r'), $force_headers);
        if (isset($this->options['filePath'])) {
            $this->response->send_headers();
            if (!$this->request->is_head()) {
                $this->send_file_content();
                $this->after_file_is_sent();
            }
        } else {
            $this->response->send_response();
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function set_header($name, $value, $replace = false)
    {
        $this->response->set_header($name, $value, $replace);
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function get_header($name)
    {
        return $this->response->get_header($name);
    }
    /**
     * @inheritDoc
     */
    public function clear_header($name)
    {
        $this->response->clear_header($name);
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function set_body($value)
    {
        $this->response->set_body($value);
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function append_body($value)
    {
        $this->response->append_body($value);
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function get_content()
    {
        return $this->response->get_content();
    }
    /**
     * @inheritDoc
     */
    public function set_content($value)
    {
        $this->response->set_content($value);
        return $this;
    }
    /**
     * Sends file content to the client
     *
     * @return void
     * @throws FileSystemException
     */
    private function send_file_content(): void
    {
        $dir = $this->filesystem->get_directory_read($this->options['directoryCode']);
        $stream = $dir->open_file($this->options['filePath'], 'r');
        while (!$stream->eof()) {
            // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
            echo $stream->read(1024);
        }
        $stream->close();
    }
    /**
     * Callback after file is sent to the client
     *
     * @return void
     * @throws FileSystemException
     */
    private function after_file_is_sent(): void
    {
        $this->response->clear_body();
        if ($this->options['remove']) {
            $dir = $this->filesystem->get_directory_write($this->options['directoryCode']);
            $dir->delete($this->options['filePath']);
        }
    }
}