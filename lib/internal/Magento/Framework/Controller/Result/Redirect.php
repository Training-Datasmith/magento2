<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Controller\Result;

use Magento\Framework\App;
use Magento\Framework\App\Response\Http_Interface as HttpResponseInterface;
use Magento\Framework\App\Response\Redirect_Interface;
use Magento\Framework\Controller\Abstract_Result;
use Magento\Framework\Url_Interface;
/**
 * In many cases controller actions may result in a redirect
 * so this is a result object that implements all necessary properties of a HTTP redirect
 *
 * @api
 * @since 100.0.2
 */
class Redirect extends Abstract_Result
{
    /**
     * @var RedirectInterface
     */
    protected $redirect;
    /**
     * @var UrlInterface
     */
    protected $url_builder;
    /**
     * @var string
     */
    protected $url;
    /**
     * Constructor
     *
     * @param App\Response\RedirectInterface $redirect
     * @param UrlInterface $urlBuilder
     */
    public function __construct(App\Response\Redirect_Interface $redirect, Url_Interface $url_builder)
    {
        $this->redirect = $redirect;
        $this->url_builder = $url_builder;
    }
    /**
     * Set url from referer
     *
     * @return $this
     */
    public function set_referer_url()
    {
        $this->url = $this->redirect->get_referer_url();
        return $this;
    }
    /**
     * Set referer url or base if referer is not exist
     *
     * @return $this
     */
    public function set_referer_or_base_url()
    {
        $this->url = $this->redirect->get_redirect_url();
        return $this;
    }
    /**
     * URL Setter
     * @param string $url
     * @return $this
     */
    public function set_url($url)
    {
        $this->url = $url;
        return $this;
    }
    /**
     * Set url by path
     *
     * @param string $path
     * @param array $params
     * @return $this
     */
    public function set_path($path, array $params = [])
    {
        $this->url = $this->url_builder->get_url($path, $this->redirect->update_path_params($params));
        return $this;
    }
    /**
     * {@inheritdoc}
     */
    protected function render(Http_Response_Interface $response)
    {
        if (empty($this->http_response_code)) {
            $response->set_redirect($this->url);
        } else {
            $response->set_redirect($this->url, $this->http_response_code);
        }
        return $this;
    }
}