<?php

declare (strict_types=1);
/**
 * Cron application
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\App;
use Magento\Framework\Object_Manager_Interface;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cron implements \Magento\Framework\App_Interface
{
    /**
     * @var State
     */
    protected $_state;
    /**
     * @var Console\Request
     */
    protected $_request;
    /**
     * @var Console\Response
     */
    protected $_response;
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $object_manager;
    /**
     * @var \Magento\Framework\App\AreaList
     */
    private $area_list;
    /**
     * Inject dependencies
     *
     * @param State $state
     * @param Console\Request $request
     * @param Console\Response $response
     * @param ObjectManagerInterface $objectManager
     * @param array $parameters
     * @param AreaList|null          $areaList
     */
    public function __construct(State $state, Console\Request $request, Console\Response $response, Object_Manager_Interface $object_manager, array $parameters = [], ?\Magento\Framework\App\Area_List $area_list = null)
    {
        $this->_state = $state;
        $this->_request = $request;
        $this->_request->set_params($parameters);
        $this->_response = $response;
        $this->object_manager = $object_manager;
        $this->area_list = $area_list ? $area_list : $this->object_manager->get(\Magento\Framework\App\Area_List::class);
    }
    /**
     * Run application
     *
     * @return ResponseInterface
     */
    public function launch()
    {
        $this->_state->set_area_code(Area::AREA_CRONTAB);
        $config_loader = $this->object_manager->get(\Magento\Framework\Object_Manager\Config_Loader_Interface::class);
        $this->object_manager->configure($config_loader->load(Area::AREA_CRONTAB));
        $this->area_list->get_area(Area::AREA_CRONTAB)->load(Area::PART_TRANSLATE);
        /** @var \Magento\Framework\Event\ManagerInterface $eventManager */
        $event_manager = $this->object_manager->get(\Magento\Framework\Event\Manager_Interface::class);
        $event_manager->dispatch('default');
        $this->_response->set_code(0);
        return $this->_response;
    }
    /**
     * {@inheritdoc}
     */
    public function catch_exception(App\Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}