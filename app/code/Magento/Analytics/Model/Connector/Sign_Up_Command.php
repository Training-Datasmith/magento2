<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\Model\Connector;

use Laminas\Http\Request;
use Magento\Analytics\Model\Connector\Http\Response_Resolver;
use Magento\Analytics\Model\Integration_Manager;
use Magento\Framework\App\Config\Scope_Config_Interface;
use Magento\Store\Model\Store;
use Psr\Log\Logger_Interface;
/**
 * SignUp merchant for Free Tier project
 */
class Sign_Up_Command implements Command_Interface
{
    private string $sign_up_url_path = 'analytics/url/signup';
    /**
     * SignUpCommand constructor.
     */
    public function __construct(private readonly Integration_Manager $integration_manager, private readonly Scope_Config_Interface $config, private readonly Http\Client_Interface $http_client, private readonly Logger_Interface $logger, private readonly Response_Resolver $response_resolver)
    {
    }
    /**
     * Executes signUp command
     *
     * During this call Magento generates or retrieves access token for the integration user
     * In case successful generation Magento activates user and sends access token to MA
     * As the response, Magento receives a token to MA
     * Magento stores this token in System Configuration
     *
     * This method returns true in case of success
     */
    public function execute(): bool
    {
        $result = false;
        $integration_token = $this->integration_manager->generate_token();
        if ($integration_token) {
            $this->integration_manager->activate_integration();
            $response = $this->http_client->request(Request::METHOD_POST, $this->config->get_value($this->sign_up_url_path), ['token' => $integration_token->get_data('token'), 'url' => $this->config->get_value(Store::XML_PATH_SECURE_BASE_URL)]);
            $result = $this->response_resolver->get_result($response);
            if (!$result) {
                $this->logger->warning(sprintf('Subscription for MBI service has been failed. An error occurred during token exchange: %s.' . ' Content-Type: %s', !empty($response->get_body()) ? $response->get_body() : 'Response body is empty', $response->get_headers()->has('Content-Type') ? $response->get_headers()->get('Content-Type')->get_field_value() : ''));
            }
        }
        return (bool) $result;
    }
}