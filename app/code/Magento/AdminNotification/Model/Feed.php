<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\AdminNotification\Model;

use Laminas\Http\Request;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use SimpleXMLElement;

/**
 * AdminNotification Feed model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @api
 * @since 100.0.2
 */
class Feed extends AbstractModel
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
    protected $_feedUrl;

    /**
     * @var InboxFactory
     */
    protected $_inboxFactory;

    /**
     * @var CurlFactory
     *
     */
    protected $curlFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected \Magento\Backend\App\ConfigInterface $_backendConfig,
        InboxFactory $inboxFactory,
        CurlFactory $curlFactory,
        /**
         * Deployment configuration
         */
        protected \Magento\Framework\App\DeploymentConfig $_deploymentConfig,
        protected \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        protected \Magento\Framework\UrlInterface $urlBuilder,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?Escaper $escaper = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_inboxFactory = $inboxFactory;
        $this->curlFactory = $curlFactory;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(
            Escaper::class
        );
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
    public function getFeedUrl()
    {
        $httpPath = $this->_backendConfig->isSetFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        if ($this->_feedUrl === null) {
            $this->_feedUrl = $httpPath . $this->_backendConfig->getValue(self::XML_FEED_URL_PATH);
        }
        return $this->_feedUrl;
    }

    /**
     * Check feed for modification
     *
     * @return $this
     */
    public function checkUpdate(): static
    {
        if ($this->getFrequency() + $this->getLastUpdate() > time()) {
            return $this;
        }

        $feedData = [];

        $feedXml = $this->getFeedData();

        $installDate = strtotime((string) $this->_deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE));

        if ($feedXml && $feedXml->channel && $feedXml->channel->item) {
            foreach ($feedXml->channel->item as $item) {
                $itemPublicationDate = strtotime((string)$item->pubDate);
                if ($installDate <= $itemPublicationDate) {
                    $feedData[] = [
                        'severity' => (int)$item->severity,
                        'date_added' => date('Y-m-d H:i:s', $itemPublicationDate),
                        'title' => $this->escapeString($item->title),
                        'description' => $this->escapeString($item->description),
                        'url' => $this->escapeString($item->link),
                    ];
                }
            }

            if ($feedData) {
                $this->_inboxFactory->create()->parse(array_reverse($feedData));
            }
        }
        $this->setLastUpdate();

        return $this;
    }

    /**
     * Retrieve Update Frequency
     *
     * @return int
     */
    public function getFrequency(): int|float
    {
        return $this->_backendConfig->getValue(self::XML_FREQUENCY_PATH) * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastUpdate()
    {
        return $this->_cacheManager->load('admin_notifications_lastcheck');
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function setLastUpdate(): static
    {
        $this->_cacheManager->save(time(), 'admin_notifications_lastcheck');
        return $this;
    }

    /**
     * Retrieve feed data as XML element
     *
     * @return SimpleXMLElement
     */
    public function getFeedData(): false|\SimpleXMLElement
    {
        /** @var Curl $curl */
        $curl = $this->curlFactory->create();
        $curl->setOptions(
            [
                'timeout'   => 2,
                'useragent' => $this->productMetadata->getName()
                    . '/' . $this->productMetadata->getVersion()
                    . ' (' . $this->productMetadata->getEdition() . ')',
                'referer'   => $this->urlBuilder->getUrl('*/*/*'),
            ]
        );
        $curl->write(Request::METHOD_GET, $this->getFeedUrl(), '1.0');
        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1] ?? '');
        $curl->close();

        try {
            $xml = new SimpleXMLElement($data);
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
    public function getFeedXml()
    {
        try {
            $data = $this->getFeedData();
            $xml = new SimpleXMLElement($data);
        } catch (\Exception) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?>');
        }

        return $xml;
    }

    /**
     * Converts incoming data to string format and escapes special characters.
     *
     * @return string
     */
    private function escapeString(SimpleXMLElement $data)
    {
        return $this->escaper->escapeHtml((string)$data);
    }
}
