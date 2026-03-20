<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App;

use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Console\Response;
use Magento\Framework\App_Interface;
/**
 * @api
 * @since 100.0.2
 */
class User_Config implements App_Interface
{
    /**
     * Console response
     *
     * @var Response
     */
    private $response;
    /**
     * Requested changes
     *
     * @var array
     */
    private $request;
    /**
     * Factory for config models
     *
     * @var Factory
     */
    private $config_factory;
    /**
     * Constructor
     *
     * @param Factory $configFactory
     * @param Response $response
     * @param array $request
     */
    public function __construct(Factory $config_factory, Response $response, array $request)
    {
        $this->response = $response;
        $this->request = $request;
        $this->config_factory = $config_factory;
    }
    /**
     * Run application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        $this->response->terminate_on_send(false);
        $this->update_user_config_data();
        return $this->response;
    }
    /**
     * Inserts provided user configuration data into database
     *
     * @return void
     */
    private function update_user_config_data()
    {
        foreach ($this->request as $key => $val) {
            $config_model = $this->config_factory->create();
            $config_model->set_data_by_path($key, $val);
            $config_model->save();
        }
    }
    /**
     * {@inheritdoc}
     */
    public function catch_exception(Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}