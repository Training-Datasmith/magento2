<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\File\Transfer\Adapter;

use Laminas\Http\Headers;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\File\Mime;
use Magento\Framework\HTTP\Php_Environment\Response;
/**
 * File adapter to send the file to the client.
 */
class Http
{
    /**
     * @var Response
     */
    private $response;
    /**
     * @var Mime
     */
    private $mime;
    /**
     * @var HttpRequest
     */
    private $request;
    /**
     * @param Response $response
     * @param Mime $mime
     * @param HttpRequest|null $request
     */
    public function __construct(Response $response, Mime $mime, ?Http_Request $request = null)
    {
        $this->response = $response;
        $this->mime = $mime;
        $object_manager = Object_Manager::get_instance();
        $this->request = $request ?: $object_manager->get(Http_Request::class);
    }
    /**
     * Send the file to the client (Download)
     *
     * @param  string|array $options Options for the file(s) to send
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @return void
     */
    public function send($options = null)
    {
        $filepath = $this->get_file_path($options);
        if (!is_file($filepath) || !is_readable($filepath)) {
            throw new \InvalidArgumentException("File '{$filepath}' does not exists.");
        }
        $this->prepare_response($options, $filepath);
        if ($this->request->is_head()) {
            // Do not send the body on HEAD requests.
            return;
        }
        $handle = fopen($filepath, 'r');
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
                echo $buffer;
            }
            if (!feof($handle)) {
                throw new \UnexpectedValueException('Unexpected end of file');
            }
            fclose($handle);
        }
    }
    /**
     * Get filepath by provided parameter $optons.
     * If the $options is a string it assumes it's a file path. If the option is an array method will look for the
     * 'filepath' key and return it's value.
     *
     * @param string|array|null $options
     * @return string
     * @throws \InvalidArgumentException
     */
    private function get_file_path($options): string
    {
        if (is_string($options)) {
            $file_path = $options;
        } elseif (is_array($options) && isset($options['filepath'])) {
            $file_path = $options['filepath'];
        } else {
            throw new \InvalidArgumentException('Filename is not set.');
        }
        return $file_path;
    }
    /**
     * Set and send all necessary headers.
     *
     * @param array $options
     * @param string $filepath
     */
    private function prepare_response($options, string $filepath): void
    {
        $mime_type = $this->mime->get_mime_type($filepath);
        if (is_array($options) && isset($options['headers']) && $options['headers'] instanceof Headers) {
            $this->response->set_headers($options['headers']);
        }
        $this->response->set_header('Content-length', filesize($filepath));
        $this->response->set_header('Content-Type', $mime_type);
        $this->response->send_headers();
    }
}