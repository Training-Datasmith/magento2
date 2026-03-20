<?php

/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App\Request;

use Magento\Framework\App\Action_Interface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Backpressure\Backpressure_Exceeded_Exception;
use Magento\Framework\App\Backpressure_Enforcer_Interface;
use Magento\Framework\App\Request\Backpressure\Context_Factory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Request_Interface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\Localized_Exception;
/**
 * Enforces backpressure for non-webAPI requests
 */
class Backpressure_Validator implements Validator_Interface
{
    /**
     * @var ContextFactory
     */
    private Context_Factory $context_factory;
    /**
     * @var BackpressureEnforcerInterface
     */
    private Backpressure_Enforcer_Interface $enforcer;
    /**
     * @var AppState
     */
    private App_State $app_state;
    /**
     * @param ContextFactory $contextFactory
     * @param BackpressureEnforcerInterface $enforcer
     * @param AppState $appState
     */
    public function __construct(Context_Factory $context_factory, Backpressure_Enforcer_Interface $enforcer, App_State $app_state)
    {
        $this->context_factory = $context_factory;
        $this->enforcer = $enforcer;
        $this->app_state = $app_state;
    }
    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function validate(Request_Interface $request, Action_Interface $action): void
    {
        if ($request instanceof Http_Request && in_array($this->get_area_code(), [Area::AREA_FRONTEND, Area::AREA_ADMINHTML], true)) {
            $context = $this->context_factory->create($action);
            if ($context) {
                try {
                    $this->enforcer->enforce($context);
                } catch (Backpressure_Exceeded_Exception $exception) {
                    throw new Localized_Exception(__('Too Many Requests'), $exception);
                }
            }
        }
    }
    /**
     * Returns area code
     *
     * @return string|null
     */
    private function get_area_code(): ?string
    {
        try {
            return $this->app_state->get_area_code();
        } catch (Localized_Exception $exception) {
            return null;
        }
    }
}