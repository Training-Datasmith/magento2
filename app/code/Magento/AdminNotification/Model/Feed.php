<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Admin_Notification\Model;

use Laminas\Http\Request;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Config\Config_Options_List_Constants;
use Magento\Framework\Data\Collection\Abstract_Db;
use Magento\Framework\Escaper;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\Curl_Factory;
use Magento\Framework\Model\Abstract_Model;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\Resource_Model\Abstract_Resource;
use Magento\Framework\Registry;
use Simple_Xml_Element;
/**
 * AdminNotification Feed model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Feed extends Abstract_Model
{
    public const XML_USE_HTTPS_PATH = 'system/adminnotification/use_https';
    public const XML_FEED_URL_PATH = 'system/adminnotification/feed_url';
    public const XML_FREQUENCY_PATH = 'system/adminnotification/frequency';
    public const XML_LAST_UPDATE_PATH = 'system/adminnotification/last_update';
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var string
     */
    protected $_feed_url;
    /**
     * @var InboxFactory
     */
    protected $_inbox_factory;
    /**
     * @var CurlFactory
     *
     */
    protected $curl_factory;
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected \Magento\Backend\App\Config_Interface $_backend_config,
        Inbox_Factory $inbox_factory,
        Curl_Factory $curl_factory,
        /**
         * Deployment configuration
         */
        protected \Magento\Framework\App\Deployment_Config $_deployment_config,
        protected \Magento\Framework\App\Product_Metadata_Interface $product_metadata,
        protected \Magento\Framework\Url_Interface $url_builder,
        ?Abstract_Resource $resource = null,
        ?Abstract_Db $resource_collection = null,
        array $data = [],
        ?Escaper $escaper = null
    )
    {
        parent::__construct($context, $registry, $resource, $resource_collection, $data);
        $this->_inbox_factory = $inbox_factory;
        $this->curl_factory = $curl_factory;
        $this->escaper = $escaper ?? Object_Manager::get_instance()->get(Escaper::class);
    }
    /**
     * Init model
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _construct()
    {
    }
    /**
     * Retrieve feed url
     *
     * @return string
     */
    public function get_feed_url()
    {
        $http_path = $this->_backend_config->is_set_flag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feed_url === null) {
            $this->_feed_url = $http_path . $this->_backend_config->get_value(self::XML_FEED_URL_PATH);
        }
        return $this->_feed_url;
    }
    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function check_update(): static
    {
        if ($this->get_frequency() + $this->get_last_update() > time()) {
            return $this;
        }
        $feed_data = [];
        $feed_xml = $this->get_feed_data();
        $install_date = strtotime((string) $this->_deployment_config->get(Config_Options_List_Constants::CONFIG_PATH_INSTALL_DATE));
        if ($feed_xml && $feed_xml->channel && $feed_xml->channel->item) {
            foreach ($feed_xml->channel->item as $item) {
                $item_publication_date = strtotime((string) $item->pub_date);
                if ($install_date <= $item_publication_date) {
                    $feed_data[] = ['severity' => (int) $item->severity, 'date_added' => date('Y-m-d H:i:s', $item_publication_date), 'title' => $this->escape_string($item->title), 'description' => $this->escape_string($item->description), 'url' => $this->escape_string($item->link)];
                }
            }
            if ($feed_data) {
                $this->_inbox_factory->create()->parse(array_reverse($feed_data));
            }
        }
        $this->set_last_update();
        return $this;
    }
    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function get_frequency(): int|float
    {
        return $this->_backend_config->get_value(self::XML_FREQUENCY_PATH) * 3600;
    }
    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function get_last_update()
    {
        return $this->_cache_manager->load('admin_notifications_lastcheck');
    }
    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function set_last_update(): static
    {
        $this->_cache_manager->save(time(), 'admin_notifications_lastcheck');
        return $this;
    }
    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function get_feed_data(): false|\Simple_Xml_Element
    {
        /** @var Curl $curl */
        $curl = $this->curl_factory->create();
        $curl->set_options(['timeout' => 2, 'useragent' => $this->product_metadata->get_name() . '/' . $this->product_metadata->get_version() . ' (' . $this->product_metadata->get_edition() . ')', 'referer' => $this->url_builder->get_url('*/*/*')]);
        $curl->write(Request::METHOD_GET, $this->get_feed_url(), '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1] ?? '');
        $curl->close();
        try {
            $xml = new Simple_Xml_Element($data);
        } catch (\Exception) {
            return false;
        }
        return $xml;
    }
    /**
     * Retrieve feed as XML element
     *
     * @return SimpleXMLElement
     */
    public function get_feed_xml()
    {
        try {
            $data = $this->get_feed_data();
            $xml = new Simple_Xml_Element($data);
        } catch (\Exception) {
            $xml = new Simple_Xml_Element('<?xml version="1.0" encoding="utf-8" ?>');
        }
        return $xml;
    }
    /**
     * Converts incoming data to string format and escapes special characters.
     *
     * @return string
     */
    private function escape_string(Simple_Xml_Element $data)
    {
        return $this->escaper->escape_html((string) $data);
    }
}