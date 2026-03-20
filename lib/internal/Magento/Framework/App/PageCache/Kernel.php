<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Page_Cache;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Stdlib\Cookie_Disabler_Interface;
/**
 * Builtin cache processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Kernel
{
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     *
     * @deprecated 100.1.0
     * @see Nothing
     */
    protected $cache;
    /**
     * @var \Magento\Framework\App\PageCache\IdentifierInterface
     */
    protected $identifier;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $full_page_cache;
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $context;
    /**
     * @var \Magento\Framework\App\Http\ContextFactory
     */
    private $context_factory;
    /**
     * @var \Magento\Framework\App\Response\HttpFactory
     */
    private $http_factory;
    /**
     * @var AppState
     */
    private $state;
    /**
     * @var \Magento\Framework\App\PageCache\IdentifierInterface
     */
    private $identifier_for_save;
    // phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
    private readonly Cookie_Disabler_Interface $cookie_disabler;
    /**
     * @param Cache $cache
     * @param \Magento\Framework\App\PageCache\IdentifierInterface $identifier
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context|null $context
     * @param \Magento\Framework\App\Http\ContextFactory|null $contextFactory
     * @param \Magento\Framework\App\Response\HttpFactory|null $httpFactory
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     * @param AppState|null $state
     * @param \Magento\PageCache\Model\Cache\Type|null $fullPageCache
     * @param  \Magento\Framework\App\PageCache\IdentifierInterface|null $identifierForSave
     * @param CookieDisablerInterface|null $cookieDisabler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(\Magento\Framework\App\Page_Cache\Cache $cache, \Magento\Framework\App\Page_Cache\Identifier_Interface $identifier, \Magento\Framework\App\Request\Http $request, ?\Magento\Framework\App\Http\Context $context = null, ?\Magento\Framework\App\Http\Context_Factory $context_factory = null, ?\Magento\Framework\App\Response\Http_Factory $http_factory = null, ?\Magento\Framework\Serialize\Serializer_Interface $serializer = null, ?App_State $state = null, ?\Magento\Page_Cache\Model\Cache\Type $full_page_cache = null, ?\Magento\Framework\App\Page_Cache\Identifier_Interface $identifier_for_save = null, ?Cookie_Disabler_Interface $cookie_disabler = null)
    {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;
        $this->context = $context ?? Object_Manager::get_instance()->get(\Magento\Framework\App\Http\Context::class);
        $this->context_factory = $context_factory ?? Object_Manager::get_instance()->get(\Magento\Framework\App\Http\Context_Factory::class);
        $this->http_factory = $http_factory ?? Object_Manager::get_instance()->get(\Magento\Framework\App\Response\Http_Factory::class);
        $this->serializer = $serializer ?? Object_Manager::get_instance()->get(\Magento\Framework\Serialize\Serializer_Interface::class);
        $this->state = $state ?? Object_Manager::get_instance()->get(App_State::class);
        $this->full_page_cache = $full_page_cache ?? Object_Manager::get_instance()->get(\Magento\Page_Cache\Model\Cache\Type::class);
        $this->identifier_for_save = $identifier_for_save ?? Object_Manager::get_instance()->get(\Magento\Framework\App\Page_Cache\Identifier_Interface::class);
        $this->cookie_disabler = $cookie_disabler ?? Object_Manager::get_instance()->get(Cookie_Disabler_Interface::class);
    }
    /**
     * Load response from cache
     *
     * @return \Magento\Framework\App\Response\Http|false
     */
    public function load()
    {
        if ($this->request->is_get() || $this->request->is_head()) {
            $response_data = $this->full_page_cache->load($this->identifier->get_value());
            if (!$response_data) {
                return false;
            }
            $response_data = $this->serializer->unserialize($response_data);
            if (!$response_data) {
                return false;
            }
            return $this->build_response($response_data);
        }
        return false;
    }
    /**
     * Modify and cache application response
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        $cache_control_header = $response->get_header('Cache-Control');
        if ($cache_control_header && preg_match('/public.*s-maxage=(\d+)/', $cache_control_header->get_field_value(), $matches)) {
            $max_age = (int) $matches[1];
            $response->set_no_cache_headers();
            if (($response->get_http_response_code() == 200 || $response->get_http_response_code() == 404) && !$response instanceof Not_Cacheable_Interface && ($this->request->is_get() || $this->request->is_head())) {
                $tags_header = $response->get_header('X-Magento-Tags');
                $tags = $tags_header ? explode(',', $tags_header->get_field_value() ?? '') : [];
                $response->clear_header('Set-Cookie');
                if ($this->state->get_mode() != App_State::MODE_DEVELOPER) {
                    $response->clear_header('X-Magento-Tags');
                }
                $this->cookie_disabler->set_cookies_disabled(true);
                $this->full_page_cache->save($this->serializer->serialize($this->get_prepared_data($response)), $this->identifier_for_save->get_value(), $tags, $max_age);
            }
        }
    }
    /**
     * Get prepared data for storage in the cache.
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return array
     */
    private function get_prepared_data(\Magento\Framework\App\Response\Http $response)
    {
        return ['content' => $response->get_content(), 'status_code' => $response->get_status_code(), 'headers' => $response->get_headers()->to_array(), 'context' => $this->context->to_array()];
    }
    /**
     * Build response using response data.
     *
     * @param array $responseData
     * @return \Magento\Framework\App\Response\Http
     */
    private function build_response($response_data)
    {
        $context = $this->context_factory->create(['data' => $response_data['context']['data'], 'default' => $response_data['context']['default']]);
        $response = $this->http_factory->create(['context' => $context]);
        $response->set_status_code($response_data['status_code']);
        $response->set_content($response_data['content']);
        foreach ($response_data['headers'] as $header_key => $header_value) {
            $response->set_header($header_key, $header_value, true);
        }
        return $response;
    }
}