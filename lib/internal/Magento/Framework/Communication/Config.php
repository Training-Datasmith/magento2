<?php

declare (strict_types=1);
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Communication;

use Magento\Framework\Communication\Config\Data as ConfigData;
use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
/**
 * Class for accessing to communication configuration.
 */
class Config implements Config_Interface
{
    /**
     * @var ConfigData
     */
    protected $data;
    /**
     * Initialize dependencies.
     *
     * @param ConfigData $configData
     */
    public function __construct(Config_Data $config_data)
    {
        $this->data = $config_data;
    }
    /**
     * {@inheritdoc}
     */
    public function get_topic($topic_name)
    {
        $data = $this->data->get(self::TOPICS . '/' . $topic_name);
        if ($data === null) {
            throw new Localized_Exception(new Phrase('Topic "%topic" is not configured.', ['topic' => $topic_name]));
        }
        return $data;
    }
    /**
     * {@inheritdoc}
     */
    public function get_topic_handlers($topic_name)
    {
        $topic_data = $this->get_topic($topic_name);
        return $topic_data[self::TOPIC_HANDLERS];
    }
    /**
     * {@inheritdoc}
     */
    public function get_topics()
    {
        return $this->data->get(self::TOPICS) ?: [];
    }
}