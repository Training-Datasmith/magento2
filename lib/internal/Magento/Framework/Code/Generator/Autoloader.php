<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Code\Generator;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Code\Generator;
use Psr\Log\Logger_Interface;
/**
 * Class loader and generator.
 */
class Autoloader
{
    /**
     * @var Generator
     */
    protected $_generator;
    /**
     * Enables guarding against spamming the debug log with duplicate messages, as
     * the generation exception will be thrown multiple times within a single request.
     *
     * @var string
     */
    private $last_generation_error_message;
    /**
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->_generator = $generator;
    }
    /**
     * Load specified class name and generate it if necessary
     *
     * According to PSR-4 section 2.4 an autoloader MUST NOT throw an exception and SHOULD NOT return a value.
     *
     * @see https://www.php-fig.org/psr/psr-4/
     *
     * @param string $className
     * @return void
     */
    public function load($class_name)
    {
        if (!class_exists($class_name)) {
            try {
                $this->_generator->generate_class($class_name);
            } catch (\Exception $exception) {
                $this->try_to_log_exception_message_if_not_duplicate($exception);
            }
        }
    }
    /**
     * Log exception.
     *
     * @param \Exception $exception
     */
    private function try_to_log_exception_message_if_not_duplicate(\Exception $exception): void
    {
        if ($this->last_generation_error_message !== $exception->get_message()) {
            $this->last_generation_error_message = $exception->get_message();
            $this->try_to_log_exception($exception);
        }
    }
    /**
     * Try to capture the exception message.
     *
     * The Autoloader is instantiated before the ObjectManager, so the LoggerInterface can not be injected.
     * The Logger is instantiated in the try/catch block because ObjectManager might still not be initialized.
     * In that case the exception message can not be captured.
     *
     * The debug level is used for logging in case class generation fails for a common class, but a custom
     * autoloader is used later in the stack. A more severe log level would fill the logs with messages on production.
     * The exception message now can be accessed in developer mode if debug logging is enabled.
     *
     * @param \Exception $exception
     * @return void
     */
    private function try_to_log_exception(\Exception $exception): void
    {
        try {
            $logger = Object_Manager::get_instance()->get(Logger_Interface::class);
            $logger->debug($exception->get_message(), ['exception' => $exception]);
        } catch (\Exception $ignore_this_exception) {
            // Do not take an action here, since the original exception might have been caused by logger
        }
    }
}