<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App\Action\Abstract_Action;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\Invalid_Request_Exception;
use Magento\Framework\App\Request\Validator_Interface as RequestValidator;
use Magento\Framework\Controller\Result_Interface;
use Magento\Framework\Event\Manager_Interface as EventManagerInterface;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Exception\Not_Found_Exception;
use Magento\Framework\Message\Manager_Interface as MessageManager;
use Magento\Framework\Profiler;
use Psr\Log\Logger_Interface;
/**
 * Front controller responsible for dispatching application requests
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Front_Controller implements Front_Controller_Interface
{
    /**
     * @var RouterListInterface
     */
    protected $_router_list;
    /**
     * @var ResponseInterface
     */
    protected $response;
    /**
     * @var RequestValidator
     */
    private $request_validator;
    /**
     * @var MessageManager
     */
    private $messages;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var bool
     */
    private $validated_request = false;
    /**
     * @var State
     */
    private $app_state;
    /**
     * @var AreaList
     */
    private $area_list;
    /**
     * @var ActionFlag
     */
    private $action_flag;
    /**
     * @var EventManagerInterface
     */
    private $event_manager;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @param RouterListInterface $routerList
     * @param ResponseInterface $response
     * @param RequestValidator|null $requestValidator
     * @param MessageManager|null $messageManager
     * @param LoggerInterface|null $logger
     * @param State|null $appState
     * @param AreaList|null $areaList
     * @param ActionFlag|null $actionFlag
     * @param EventManagerInterface|null $eventManager
     * @param RequestInterface|null $request
     */
    public function __construct(Router_List_Interface $router_list, Response_Interface $response, ?Request_Validator $request_validator = null, ?Message_Manager $message_manager = null, ?Logger_Interface $logger = null, ?State $app_state = null, ?Area_List $area_list = null, ?Action_Flag $action_flag = null, ?Event_Manager_Interface $event_manager = null, ?Request_Interface $request = null)
    {
        $this->_router_list = $router_list;
        $this->response = $response;
        $this->request_validator = $request_validator ?? Object_Manager::get_instance()->get(Request_Validator::class);
        $this->messages = $message_manager ?? Object_Manager::get_instance()->get(Message_Manager::class);
        $this->logger = $logger ?? Object_Manager::get_instance()->get(Logger_Interface::class);
        $this->app_state = $app_state ?? Object_Manager::get_instance()->get(State::class);
        $this->area_list = $area_list ?? Object_Manager::get_instance()->get(Area_List::class);
        $this->action_flag = $action_flag ?? Object_Manager::get_instance()->get(Action_Flag::class);
        $this->event_manager = $event_manager ?? Object_Manager::get_instance()->get(Event_Manager_Interface::class);
        $this->request = $request ?? Object_Manager::get_instance()->get(Request_Interface::class);
    }
    /**
     * Perform action and generate response
     *
     * @param RequestInterface|HttpRequest $request
     * @return ResponseInterface|ResultInterface
     * @throws \LogicException
     * @throws LocalizedException
     */
    public function dispatch(Request_Interface $request)
    {
        Profiler::start('routers_match');
        $this->validated_request = false;
        $routing_cycle_counter = 0;
        $result = null;
        while (!$request->is_dispatched() && $routing_cycle_counter++ < 100) {
            /** @var \Magento\Framework\App\RouterInterface $router */
            foreach ($this->_router_list as $router) {
                try {
                    $action_instance = $router->match($request);
                    if ($action_instance) {
                        $result = $this->process_request($request, $action_instance);
                        break;
                    }
                } catch (\Magento\Framework\Exception\Not_Found_Exception $e) {
                    $request->init_forward();
                    $request->set_action_name('noroute');
                    $request->set_dispatched(false);
                    break;
                }
            }
        }
        Profiler::stop('routers_match');
        if ($routing_cycle_counter > 100) {
            throw new \LogicException('Front controller reached 100 router match iterations');
        }
        return $result;
    }
    /**
     * Process (validate and dispatch) the incoming request
     *
     * @param RequestInterface $request
     * @param ActionInterface $actionInstance
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     *
     * @throws NotFoundException
     */
    private function process_request(Request_Interface $request, Action_Interface $action_instance)
    {
        $request->set_dispatched(true);
        $this->response->set_no_cache_headers();
        $result = null;
        //Validating a request only once.
        if (!$this->validated_request) {
            $area = $this->area_list->get_area($this->app_state->get_area_code());
            $area->load(Area::PART_DESIGN);
            $area->load(Area::PART_TRANSLATE);
            try {
                $this->request_validator->validate($request, $action_instance);
            } catch (Invalid_Request_Exception $exception) {
                //Validation failed - processing validation results.
                $this->logger->debug(sprintf('Request validation failed for action "%s"', get_class($action_instance)), ['exception' => $exception]);
                $result = $exception->get_replace_result();
                if ($messages = $exception->get_messages()) {
                    foreach ($messages as $message) {
                        $this->messages->add_error_message($message);
                    }
                }
            }
            $this->validated_request = true;
        }
        // Validation did not produce a result to replace the action's.
        if (!$result) {
            $this->dispatch_pre_dispatch_events($action_instance, $request);
            $result = $this->get_action_response($action_instance, $request);
            if (!$this->is_set_action_no_post_dispatch_flag()) {
                $this->dispatch_post_dispatch_events($action_instance, $request);
            }
        }
        //handling redirect to 404
        if ($result instanceof Not_Found_Exception) {
            throw $result;
        }
        return $result;
    }
    /**
     * Return the result of processed request
     *
     * There are 3 ways of handling requests:
     * - Result without dispatching event when FLAG_NO_DISPATCH is set, just return ResponseInterface
     * - Backwards-compatible way using `AbstractAction::dispatch` which is deprecated
     * - Correct way for handling requests with `ActionInterface::execute`
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @throws NotFoundException
     */
    private function get_action_response(Action_Interface $action_instance, Request_Interface $request)
    {
        if ($this->action_flag->get('', Action_Interface::FLAG_NO_DISPATCH)) {
            return $this->response;
        }
        if ($action_instance instanceof Abstract_Action) {
            return $action_instance->dispatch($request);
        }
        return $action_instance->execute();
    }
    /**
     * Check if action flags are set that would suppress the post dispatch events.
     *
     * @return bool
     */
    private function is_set_action_no_post_dispatch_flag(): bool
    {
        return $this->action_flag->get('', Action_Interface::FLAG_NO_DISPATCH) || $this->action_flag->get('', Action_Interface::FLAG_NO_POST_DISPATCH);
    }
    /**
     * Dispatch the controller_action_predispatch events.
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return void
     */
    private function dispatch_pre_dispatch_events(Action_Interface $action_instance, Request_Interface $request): void
    {
        $this->event_manager->dispatch('controller_action_predispatch', $this->get_event_parameters($action_instance));
        if ($this->request instanceof Http_Request) {
            $this->event_manager->dispatch('controller_action_predispatch_' . $request->get_route_name(), $this->get_event_parameters($action_instance));
            $this->event_manager->dispatch('controller_action_predispatch_' . $request->get_full_action_name(), $this->get_event_parameters($action_instance));
        }
    }
    /**
     * Dispatch the controller_action_postdispatch events.
     *
     * @param ActionInterface $actionInstance
     * @param RequestInterface $request
     * @return void
     */
    private function dispatch_post_dispatch_events(Action_Interface $action_instance, Request_Interface $request): void
    {
        Profiler::start('postdispatch');
        if ($this->request instanceof Http_Request) {
            $this->event_manager->dispatch('controller_action_postdispatch_' . $request->get_full_action_name(), $this->get_event_parameters($action_instance));
            $this->event_manager->dispatch('controller_action_postdispatch_' . $request->get_route_name(), $this->get_event_parameters($action_instance));
        }
        $this->event_manager->dispatch('controller_action_postdispatch', $this->get_event_parameters($action_instance));
        Profiler::stop('postdispatch');
    }
    /**
     * Build the event parameter array
     *
     * @param ActionInterface $subject
     * @return array
     */
    private function get_event_parameters(Action_Interface $subject): array
    {
        return ['controller_action' => $subject, 'request' => $this->request];
    }
}