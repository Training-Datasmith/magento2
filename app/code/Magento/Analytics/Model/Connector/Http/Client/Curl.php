<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector\Http\Client;

use Laminas\Http\Response;
use Magento\Analytics\Model\Connector\Http\Converter_Interface;
use Magento\Framework\HTTP\Adapter\Curl_Factory;
use Magento\Framework\HTTP\Response_Factory;
use Psr\Log\Logger_Interface;
/**
 * A CURL HTTP client.
 *
 * Sends requests via a CURL adapter.
 */
class Curl implements \Magento\Analytics\Model\Connector\Http\Client_Interface
{
    /**
     * @var CurlFactory
     */
    private $curl_factory;
    public function __construct(Curl_Factory $curl_factory, private readonly Response_Factory $response_factory, private readonly Converter_Interface $converter, private readonly Logger_Interface $logger)
    {
        $this->curl_factory = $curl_factory;
    }
    /**
     * @inheritdoc
     */
    public function request($method, $url, array $body = [], array $headers = [], $version = '1.1')
    {
        $response = new Response();
        $response->set_custom_status_code(Response::STATUS_CODE_CUSTOM);
        try {
            $curl = $this->curl_factory->create();
            $headers = $this->apply_content_type_header_from_converter($headers);
            $curl->write($method, $url, $version, $headers, $this->converter->to_body($body));
            $result = $curl->read();
            if ($curl->get_errno()) {
                $this->logger->critical(new \Exception(sprintf('MBI service CURL connection error #%s: %s', $curl->get_errno(), $curl->get_error())));
                return $response;
            }
            $response = $this->response_factory->create($result);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $response;
    }
    /**
     * Apply content type header from converter
     *
     *
     */
    private function apply_content_type_header_from_converter(array $headers): array
    {
        $content_type_header_key = array_search($this->converter->get_content_type_header(), $headers);
        if ($content_type_header_key === false) {
            $headers[] = $this->converter->get_content_type_header();
        }
        return $headers;
    }
}