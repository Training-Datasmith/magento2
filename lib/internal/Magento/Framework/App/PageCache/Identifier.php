<?php

declare (strict_types=1);
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Page_Cache;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Page unique identifier
 */
class Identifier implements Identifier_Interface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     */
    public function __construct(\Magento\Framework\App\Request\Http $request, \Magento\Framework\App\Http\Context $context, ?Json $serializer = null)
    {
        $this->request = $request;
        $this->context = $context;
        $this->serializer = $serializer ?: Object_Manager::get_instance()->get(Json::class);
    }
    /**
     * Return unique page identifier
     *
     * @return string
     */
    public function get_value()
    {
        $pattern = $this->get_marketing_parameter_patterns();
        $replace = array_fill(0, count($pattern), '');
        $url = preg_replace($pattern, $replace, (string) $this->request->get_uri_string());
        list($base_url, $query) = $this->reconstruct_url($url);
        $data = [$this->request->is_secure(), $base_url, $query, $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING) ?: $this->context->get_vary_string()];
        return sha1($this->serializer->serialize($data));
    }
    /**
     * Pattern detect marketing parameters
     *
     * @return array
     */
    public function get_marketing_parameter_patterns(): array
    {
        return ['/&?gad_source\=[^&]+/', '/&?gbraid\=[^&]+/', '/&?wbraid\=[^&]+/', '/&?_gl\=[^&]+/', '/&?dclid\=[^&]+/', '/&?gclsrc\=[^&]+/', '/&?srsltid\=[^&]+/', '/&?msclkid\=[^&]+/', '/&?_kx\=[^&]+/', '/&?gclid\=[^&]+/', '/&?cx\=[^&]+/', '/&?ie\=[^&]+/', '/&?cof\=[^&]+/', '/&?siteurl\=[^&]+/', '/&?zanpid\=[^&]+/', '/&?origin\=[^&]+/', '/&?fbclid\=[^&]+/', '/&?mc_(.*?)\=[^&]+/', '/&?utm_(.*?)\=[^&]+/', '/&?_bta_(.*?)\=[^&]+/'];
    }
    /**
     * Reconstruct url and sort query
     *
     * @param string $url
     * @return array
     */
    private function reconstruct_url(string $url): array
    {
        if (empty($url)) {
            return [$url, ''];
        }
        $base_url = strtok($url, '?');
        $query = $this->request->get_uri()->get_query_as_array();
        if (!empty($query)) {
            ksort($query);
            $query = http_build_query($query);
        } else {
            $query = '';
        }
        return [$base_url, $query];
    }
}