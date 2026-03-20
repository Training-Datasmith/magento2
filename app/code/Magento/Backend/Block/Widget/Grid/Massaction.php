<?php

declare (strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Block\Widget\Grid;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\Object_Manager;
use Magento\Framework\Authorization_Interface;
use Magento\Framework\Data_Object;
use Magento\Framework\Json\Encoder_Interface;
/**
 * Grid widget massaction default block
 *
 * @api
 * @deprecated 100.2.0 in favour of UI component implementation
 * @since 100.0.2
 * @see MAGETWO-67718
 */
class Massaction extends \Magento\Backend\Block\Widget\Grid\Massaction\Abstract_Massaction
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;
    /**
     * Map bind item id to a particular acl type
     * itemId => acl
     *
     * @var array
     */
    private $restrictions = ['enable' => 'Magento_Backend::toggling_cache_type', 'disable' => 'Magento_Backend::toggling_cache_type', 'refresh' => 'Magento_Backend::refresh_cache_type'];
    /**
     * Massaction constructor.
     *
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     * @param AuthorizationInterface $authorization
     */
    public function __construct(Context $context, Encoder_Interface $json_encoder, array $data = [], ?Authorization_Interface $authorization = null)
    {
        $this->authorization = $authorization ?: Object_Manager::get_instance()->get(Authorization_Interface::class);
        parent::__construct($context, $json_encoder, $data);
    }
    /**
     * @inheritdoc
     *
     * @param string $itemId
     * @param array|DataObject $item
     *
     * @return $this
     * @since 100.2.3
     */
    public function add_item($item_id, $item)
    {
        if (!$this->is_restricted($item_id)) {
            parent::add_item($item_id, $item);
        }
        return $this;
    }
    /**
     * Check if access to action restricted
     *
     * @param string $itemId
     *
     * @return bool
     */
    private function is_restricted(string $item_id): bool
    {
        if (!key_exists($item_id, $this->restrictions)) {
            return false;
        }
        return !$this->authorization->is_allowed($this->restrictions[$item_id]);
    }
}