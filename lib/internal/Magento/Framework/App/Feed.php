<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Framework\App;

use Laminas\Feed\Writer\Feed_Factory;
/**
 * Default XML feed class
 */
class Feed implements Feed_Interface
{
    /**
     * @var array
     */
    private $feeds;
    /**
     * Feed constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->feeds = $data;
    }
    /**
     * @inheritDoc
     */
    public function get_formatted_content(): string
    {
        return Feed_Factory::factory($this->feeds)->export(Feed_Factory_Interface::FORMAT_RSS);
    }
}