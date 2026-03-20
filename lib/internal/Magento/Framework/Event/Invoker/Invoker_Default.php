<?php

declare (strict_types=1);
/**
 * Default event invoker
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event\Invoker;

use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Psr\Log\Logger_Interface;
/**
 * Default Invoker.
 */
class Invoker_Default implements \Magento\Framework\Event\Invoker_Interface
{
    /**
     * Observer model factory
     *
     * @var \Magento\Framework\Event\ObserverFactory
     */
    protected $_observer_factory;
    /**
     * Application state
     *
     * @var State
     */
    protected $_app_state;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @param \Magento\Framework\Event\ObserverFactory $observerFactory
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(\Magento\Framework\Event\Observer_Factory $observer_factory, State $app_state, ?Logger_Interface $logger = null)
    {
        $this->_observer_factory = $observer_factory;
        $this->_app_state = $app_state;
        $this->logger = $logger ?: \Magento\Framework\App\Object_Manager::get_instance()->get(Logger_Interface::class);
    }
    /**
     * Dispatch event
     *
     * @param array $configuration
     * @param Observer $observer
     * @return void
     */
    public function dispatch(array $configuration, Observer $observer)
    {
        /** Check whether event observer is disabled */
        if (isset($configuration['disabled']) && true === $configuration['disabled']) {
            return;
        }
        if (isset($configuration['shared']) && false === $configuration['shared']) {
            $object = $this->_observer_factory->create($configuration['instance']);
        } else {
            $object = $this->_observer_factory->get($configuration['instance']);
        }
        $this->_call_observer_method($object, $observer);
    }
    /**
     * Execute Observer.
     *
     * @param \Magento\Framework\Event\ObserverInterface $object
     * @param Observer $observer
     * @return $this
     * @throws \LogicException
     */
    protected function _call_observer_method($object, $observer)
    {
        if ($object instanceof \Magento\Framework\Event\Observer_Interface) {
            $object->execute($observer);
        } elseif ($this->_app_state->get_mode() == State::MODE_DEVELOPER) {
            throw new \LogicException(sprintf('Observer "%s" must implement interface "%s"', get_class($object), \Magento\Framework\Event\Observer_Interface::class));
        } else {
            $this->logger->warning(sprintf('Observer "%s" must implement interface "%s"', get_class($object), \Magento\Framework\Event\Observer_Interface::class));
        }
        return $this;
    }
}