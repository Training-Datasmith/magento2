<?php

/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\Directory_List;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Debug;
use Magento\Framework\Encryption\Encryptor_Interface;
use Magento\Framework\Exception\Session_Exception;
use Magento\Framework\Exception\State\Init_Exception;
use Magento\Framework\Filesystem;
use Psr\Log\Logger_Interface;
/**
 * Handler of HTTP web application exception
 */
class Exception_Handler implements Exception_Handler_Interface
{
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @param EncryptorInterface $encryptor
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(Encryptor_Interface $encryptor, Filesystem $filesystem, Logger_Interface $logger)
    {
        $this->encryptor = $encryptor;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }
    /**
     * Handles exception of HTTP web application
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    public function handle(Bootstrap $bootstrap, \Exception $exception, Response_Http $response, Request_Http $request): bool
    {
        $result = $this->handle_developer_mode($bootstrap, $exception, $response) || $this->handle_bootstrap_errors($bootstrap, $exception, $response) || $this->handle_session_exception($exception, $response, $request) || $this->handle_init_exception($exception) || $this->handle_generic_report($bootstrap, $exception);
        return $result;
    }
    /**
     * Error handler for developer mode
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return bool
     */
    private function handle_developer_mode(Bootstrap $bootstrap, \Exception $exception, Response_Http $response): bool
    {
        if ($bootstrap->is_developer_mode()) {
            if (Bootstrap::ERR_IS_INSTALLED == $bootstrap->get_error_code()) {
                try {
                    $this->redirect_to_setup($bootstrap, $exception, $response);
                    return true;
                } catch (\Exception $e) {
                    $exception = $e;
                }
            }
            $response->clear_header('Location');
            $response->set_http_response_code(500);
            $response->set_header('Content-Type', 'text/plain');
            $response->set_body($this->build_content_from_exception($exception));
            $response->send_response();
            return true;
        }
        return false;
    }
    /**
     * Build content based on an exception
     *
     * @param \Exception $exception
     * @return string
     */
    private function build_content_from_exception(\Exception $exception): string
    {
        /** @var \Exception[] $exceptions */
        $exceptions = [];
        do {
            $exceptions[] = $exception;
        } while ($exception = $exception->get_previous());
        $buffer = sprintf("%d exception(s):\n", count($exceptions));
        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf("Exception #%d (%s): %s\n", $index, get_class($exception), $exception->get_message());
        }
        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf("\nException #%d (%s): %s\n%s\n", $index, get_class($exception), $exception->get_message(), Debug::trace($exception->get_trace(), true, true, (bool) getenv('MAGE_DEBUG_SHOW_ARGS')));
        }
        return $buffer;
    }
    /**
     * Handler for bootstrap errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return bool
     */
    private function handle_bootstrap_errors(Bootstrap $bootstrap, \Exception &$exception, Response_Http $response): bool
    {
        $bootstrap_code = $bootstrap->get_error_code();
        if (Bootstrap::ERR_MAINTENANCE == $bootstrap_code) {
            // phpcs:ignore Magento2.Security.IncludeFile
            require $this->filesystem->get_directory_read(Directory_List::PUB)->get_absolute_path('errors/503.php');
            return true;
        }
        if (Bootstrap::ERR_IS_INSTALLED == $bootstrap_code) {
            try {
                $this->redirect_to_setup($bootstrap, $exception, $response);
                return true;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        return false;
    }
    /**
     * Handler for session errors
     *
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    private function handle_session_exception(\Exception $exception, Response_Http $response, Request_Http $request): bool
    {
        if ($exception instanceof Session_Exception) {
            $response->set_redirect($request->get_distro_base_url());
            $response->send_headers();
            return true;
        }
        return false;
    }
    /**
     * Handler for application initialization errors
     *
     * @param \Exception $exception
     * @return bool
     */
    private function handle_init_exception(\Exception $exception): bool
    {
        if ($exception instanceof Init_Exception) {
            $this->logger->critical($exception);
            // phpcs:ignore Magento2.Security.IncludeFile
            require $this->filesystem->get_directory_read(Directory_List::PUB)->get_absolute_path('errors/404.php');
            return true;
        }
        return false;
    }
    /**
     * Handle for any other errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    private function handle_generic_report(Bootstrap $bootstrap, \Exception $exception): bool
    {
        $report_data = [$exception->get_message(), Debug::trace($exception->get_trace(), true, false, (bool) getenv('MAGE_DEBUG_SHOW_ARGS'))];
        $params = $bootstrap->get_params();
        if (isset($params['REQUEST_URI'])) {
            $report_data['url'] = $params['REQUEST_URI'];
        }
        if (isset($params['SCRIPT_NAME'])) {
            $report_data['script_name'] = $params['SCRIPT_NAME'];
        }
        $report_data['report_id'] = $this->encryptor->get_hash(implode('', $report_data));
        $this->logger->critical($exception, ['report_id' => $report_data['report_id']]);
        // phpcs:ignore Magento2.Security.IncludeFile
        require $this->filesystem->get_directory_read(Directory_List::PUB)->get_absolute_path('errors/report.php');
        return true;
    }
    /**
     * If not installed, try to redirect to installation wizard
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return void
     * @throws \Exception
     */
    private function redirect_to_setup(Bootstrap $bootstrap, \Exception $exception, Response_Http $response)
    {
        $setup_info = new Setup_Info($bootstrap->get_params());
        $project_root = $this->filesystem->get_directory_read(Directory_List::ROOT)->get_absolute_path();
        if ($setup_info->is_available()) {
            $response->set_redirect($setup_info->get_url());
            $response->send_headers();
        } else {
            $new_message = $exception->get_message() . "\nNOTE: You cannot install Magento using the Setup Wizard " . "because the Magento setup directory cannot be accessed. \n" . 'You can install Magento using either the command line or you must restore access ' . 'to the following directory: ' . $setup_info->get_dir($project_root) . "\n";
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($new_message, 0, $exception);
        }
    }
}