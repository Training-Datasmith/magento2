<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\App\Request;

use Magento\Backend\App\Abstract_Action;
use Magento\Backend\Model\Auth;
use Magento\Backend\Model\Url_Interface as BackendUrl;
use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Csrf_Aware_Action_Interface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request\Invalid_Request_Exception;
use Magento\Framework\App\Request\Validator_Interface;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\Controller\Result\Raw as RawResult;
use Magento\Framework\Controller\Result\Raw_Factory;
use Magento\Framework\Controller\Result\Redirect_Factory;
use Magento\Framework\Data\Form\Form_Key\Validator as FormKeyValidator;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Phrase;
/**
 * Do backend validations.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Backend_Validator implements Validator_Interface
{
    /**
     * @var RawFactory
     */
    private $raw_result_factory;
    public function __construct(private readonly Auth $auth, private readonly Form_Key_Validator $form_key_validator, private readonly Backend_Url $backend_url, private readonly Redirect_Factory $redirect_factory, Raw_Factory $raw_result_factory)
    {
        $this->raw_result_factory = $raw_result_factory;
    }
    /**
     * Validate request
     *
     *
     */
    private function validate_request(Request_Interface $request, Action_Interface $action): bool
    {
        /** @var bool|null $valid */
        $valid = null;
        if ($action instanceof Csrf_Aware_Action_Interface) {
            $valid = $action->validate_for_csrf($request);
        }
        if ($valid === null) {
            $valid_form_key = true;
            $valid_secret_key = true;
            if ($request instanceof Http_Request && $request->is_post()) {
                $valid_form_key = $this->form_key_validator->validate($request);
            } elseif ($this->auth->is_logged_in() && $this->backend_url->use_secret_key()) {
                $secret_key_value = (string) $request->get_param(Backend_Url::SECRET_KEY_PARAM_NAME);
                $secret_key = $this->backend_url->get_secret_key();
                $valid_secret_key = Security::compare_strings($secret_key_value, $secret_key);
            }
            $valid = $valid_form_key && $valid_secret_key;
        }
        return $valid;
    }
    /**
     * Create exception
     *
     *
     */
    private function create_exception(Request_Interface $request, Action_Interface $action): Invalid_Request_Exception
    {
        /** @var InvalidRequestException|null $exception */
        $exception = null;
        if ($action instanceof Csrf_Aware_Action_Interface) {
            $exception = $action->create_csrf_validation_exception($request);
        }
        if ($exception === null) {
            if ($request instanceof Http_Request && $request->is_ajax()) {
                //Sending empty response for AJAX request since we don't know
                //the expected response format and it's pointless to redirect.
                /** @var RawResult $response */
                $response = $this->raw_result_factory->create();
                $response->set_http_response_code(401);
                $response->set_contents('');
                $exception = new Invalid_Request_Exception($response);
            } else {
                //For regular requests.
                $start_page_url = $this->backend_url->get_startup_page_url();
                $response = $this->redirect_factory->create()->set_url($this->backend_url->get_url($start_page_url));
                $exception = new Invalid_Request_Exception($response, [new Phrase('Invalid security or form key. Please refresh the page.')]);
            }
        }
        return $exception;
    }
    /**
     * @inheritDoc
     */
    public function validate(Request_Interface $request, Action_Interface $action): void
    {
        if ($action instanceof Abstract_Action) {
            //Abstract Action has built-in validation.
            if (!$action->_process_url_keys()) {
                throw new Invalid_Request_Exception($action->get_response());
            }
        } else if (!$this->validate_request($request, $action)) {
            throw $this->create_exception($request, $action);
        }
    }
}