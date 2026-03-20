<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller;

use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
use Magento\Framework\App\Response_Interface;
abstract class Abstract_Result implements Result_Interface
{
    /**
     * @var int
     */
    protected $http_response_code;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $status_header_code;
    /**
     * @var string
     */
    protected $status_header_version;
    /**
     * @var string
     */
    protected $status_header_phrase;
    /**
     * Set response code to result
     *
     * @param int $httpCode
     * @return $this
     */
    public function set_http_response_code($http_code)
    {
        $this->http_response_code = $http_code;
        return $this;
    }
    /**
     * Set a header
     *
     * If $replace is true, replaces any headers already defined with that
     * $name.
     *
     * @param string $name
     * @param string $value
     * @param boolean $replace
     * @return $this
     */
    public function set_header($name, $value, $replace = false)
    {
        $this->headers[] = ['name' => $name, 'value' => $value, 'replace' => $replace];
        return $this;
    }
    /**
     * @param int|string $httpCode
     * @param null|int|string $version
     * @param null|string $phrase
     * @return $this
     */
    public function set_status_header($http_code, $version = null, $phrase = null)
    {
        $this->status_header_code = $http_code;
        $this->status_header_version = $version;
        $this->status_header_phrase = $phrase;
        return $this;
    }
    /**
     * @param HttpResponseInterface $response
     * @return $this
     */
    protected function apply_http_headers(Http_Response_Interface $response)
    {
        if (!empty($this->http_response_code)) {
            $response->set_http_response_code($this->http_response_code);
        }
        if ($this->status_header_code) {
            $response->set_status_header($this->status_header_code, $this->status_header_version, $this->status_header_phrase);
        }
        if (!empty($this->headers)) {
            foreach ($this->headers as $header_data) {
                $response->set_header($header_data['name'], $header_data['value'], $header_data['replace']);
            }
        }
        return $this;
    }
    /**
     * @param HttpResponseInterface $response
     * @return $this
     */
    abstract protected function render(Http_Response_Interface $response);
    /**
     * Render content
     *
     * @param HttpResponseInterface|ResponseInterface $response
     * @return $this
     */
    public function render_result(Response_Interface $response)
    {
        $this->apply_http_headers($response);
        return $this->render($response);
    }
}