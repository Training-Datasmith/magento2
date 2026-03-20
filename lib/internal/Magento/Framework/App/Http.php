<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Response\Http_Interface;
use Magento\Framework\Controller\Result_Interface;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Object_Manager\Config_Loader_Interface;
use Magento\Framework\Object_Manager_Interface;
use Magento\Framework\Registry;
/**
 * HTTP web application. Called from webroot index.php to serve web requests.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Http implements \Magento\Framework\App_Interface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_object_manager;
    /**
     * @var Manager
     */
    protected $_event_manager;
    /**
     * @var AreaList
     */
    protected $_area_list;
    /**
     * @var Request\Http
     */
    protected $_request;
    /**
     * @var ConfigLoaderInterface
     */
    protected $_config_loader;
    /**
     * @var State
     */
    protected $_state;
    /**
     * @var ResponseHttp
     */
    protected $_response;
    /**
     * @var Registry
     */
    protected $registry;
    /**
     * @var ExceptionHandlerInterface
     */
    private $exception_handler;
    /**
     * @param ObjectManagerInterface $objectManager
     * @param Manager $eventManager
     * @param AreaList $areaList
     * @param RequestHttp $request
     * @param ResponseHttp $response
     * @param ConfigLoaderInterface $configLoader
     * @param State $state
     * @param Registry $registry
     * @param ExceptionHandlerInterface $exceptionHandler
     */
    public function __construct(Object_Manager_Interface $object_manager, Manager $event_manager, Area_List $area_list, Request_Http $request, Response_Http $response, Config_Loader_Interface $config_loader, State $state, Registry $registry, ?Exception_Handler_Interface $exception_handler = null)
    {
        $this->_object_manager = $object_manager;
        $this->_event_manager = $event_manager;
        $this->_area_list = $area_list;
        $this->_request = $request;
        $this->_response = $response;
        $this->_config_loader = $config_loader;
        $this->_state = $state;
        $this->registry = $registry;
        $this->exception_handler = $exception_handler ?: $this->_object_manager->get(Exception_Handler_Interface::class);
    }
    /**
     * Run application
     *
     * @return ResponseInterface
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function launch()
    {
        $area_code = $this->_area_list->get_code_by_front_name($this->_request->get_front_name());
        $this->_state->set_area_code($area_code);
        $this->_object_manager->configure($this->_config_loader->load($area_code));
        /** @var \Magento\Framework\App\FrontControllerInterface $frontController */
        $front_controller = $this->_object_manager->get(\Magento\Framework\App\Front_Controller_Interface::class);
        $result = $front_controller->dispatch($this->_request);
        // TODO: Temporary solution until all controllers return ResultInterface (MAGETWO-28359)
        if ($result instanceof Result_Interface) {
            $this->registry->register('use_page_cache_plugin', true, true);
            $result->render_result($this->_response);
        } elseif ($result instanceof Http_Interface) {
            $this->_response = $result;
        } else {
            throw new \InvalidArgumentException('Invalid return type');
        }
        if ($this->_request->is_head() && $this->_response->get_http_response_code() == 200) {
            $this->handle_head_request();
        }
        // This event gives possibility to launch something before sending output (allow cookie setting)
        $event_params = ['request' => $this->_request, 'response' => $this->_response];
        $this->_event_manager->dispatch('controller_front_send_response_before', $event_params);
        return $this->_response;
    }
    /**
     * Handle HEAD requests by adding the Content-Length header and removing the body from the response.
     *
     * @return void
     */
    private function handle_head_request()
    {
        // It is possible that some PHP installations have overloaded strlen to use mb_strlen instead.
        // This means strlen might return the actual number of characters in a non-ascii string instead
        // of the number of bytes. Use mb_strlen explicitly with a single byte character encoding to ensure
        // that the content length is calculated in bytes.
        $content_length = mb_strlen($this->_response->get_content(), '8bit');
        $this->_response->clear_body();
        $this->_response->set_header('Content-Length', $content_length);
    }
    /**
     * @inheritdoc
     */
    public function catch_exception(Bootstrap $bootstrap, \Exception $exception): bool
    {
        return $this->exception_handler->handle($bootstrap, $exception, $this->_response, $this->_request);
    }
}