<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Csrf_Aware_Action_Interface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Controller\Result\Redirect_Factory;
use Magento\Framework\Data\Form\Form_Key\Validator as FormKeyValidator;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Validate request for being CSRF protected.
 */
class Csrf_Validator implements Validator_Interface
{
    /**
     * @var FormKeyValidator
     */
    private $form_key_validator;
    /**
     * @var RedirectFactory
     */
    private $redirect_factory;
    /**
     * @var AppState
     */
    private $app_state;
    /**
     * @param FormKeyValidator $formKeyValidator
     * @param RedirectFactory $redirectFactory
     * @param AppState $appState
     */
    public function __construct(Form_Key_Validator $form_key_validator, Redirect_Factory $redirect_factory, App_State $app_state)
    {
        $this->form_key_validator = $form_key_validator;
        $this->redirect_factory = $redirect_factory;
        $this->app_state = $app_state;
    }
    /**
     * Validate given request.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return bool
     */
    private function validate_request(Http_Request $request, Action_Interface $action): bool
    {
        $valid = null;
        if ($action instanceof Csrf_Aware_Action_Interface) {
            $valid = $action->validate_for_csrf($request);
        }
        if ($valid === null) {
            $valid = !$request->is_post() || $request->is_xml_http_request() || $this->form_key_validator->validate($request);
        }
        return $valid;
    }
    /**
     * Create exception for when incoming request failed validation.
     *
     * @param HttpRequest $request
     * @param ActionInterface $action
     *
     * @return InvalidRequestException
     */
    private function create_exception(Http_Request $request, Action_Interface $action): Invalid_Request_Exception
    {
        $exception = null;
        if ($action instanceof Csrf_Aware_Action_Interface) {
            $exception = $action->create_csrf_validation_exception($request);
        }
        if (!$exception) {
            $response = $this->redirect_factory->create()->set_referer_or_base_url()->set_http_response_code(302);
            $messages = [new Phrase('Invalid Form Key. Please refresh the page.')];
            $exception = new Invalid_Request_Exception($response, $messages);
        }
        return $exception;
    }
    /**
     * @inheritDoc
     */
    public function validate(Request_Interface $request, Action_Interface $action): void
    {
        try {
            $area_code = $this->app_state->get_area_code();
        } catch (Localized_Exception $exception) {
            $area_code = null;
        }
        if ($request instanceof Http_Request && in_array($area_code, [Area::AREA_FRONTEND, Area::AREA_ADMINHTML], true)) {
            $valid = $this->validate_request($request, $action);
            if (!$valid) {
                throw $this->create_exception($request, $action);
            }
        }
    }
}