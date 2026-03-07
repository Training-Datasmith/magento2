<?php

declare(strict_types=1);
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Eav\Model;

/**
 * EAV entity model
 *
 */
class Entity extends \Magento\Eav\Model\Entity\AbstractEntity
{
    public const DEFAULT_ENTITY_MODEL = \Magento\Eav\Model\Entity::class;

    public const DEFAULT_ATTRIBUTE_MODEL = \Magento\Eav\Model\Entity\Attribute::class;

    public const DEFAULT_BACKEND_MODEL = \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class;

    public const DEFAULT_FRONTEND_MODEL = \Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend::class;

    public const DEFAULT_SOURCE_MODEL = \Magento\Eav\Model\Entity\Attribute\Source\Config::class;

    public const DEFAULT_ENTITY_TABLE = 'eav_entity';

    public const DEFAULT_ENTITY_ID_FIELD = 'entity_id';

    /**
     * @param Entity\Context $context
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(\Magento\Eav\Model\Entity\Context $context, $data = [])
    {
        parent::__construct($context, $data);
        $this->setConnection($this->_resource->getConnection('eav'));
    }
}
