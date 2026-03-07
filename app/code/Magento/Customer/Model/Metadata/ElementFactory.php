<?php

declare(strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * Customer Form Element Factory
 */

namespace Magento\Customer\Model\Metadata;

class ElementFactory
{
    public const OUTPUT_FORMAT_JSON = 'json';
    public const OUTPUT_FORMAT_TEXT = 'text';
    public const OUTPUT_FORMAT_HTML = 'html';
    public const OUTPUT_FORMAT_PDF = 'pdf';
    public const OUTPUT_FORMAT_ONELINE = 'oneline';
    public const OUTPUT_FORMAT_ARRAY = 'array';

    // available only for multiply attributes

    // available only for multiply attributes
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\StringUtils $string
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\StringUtils $string
    ) {
        $this->_objectManager = $objectManager;
        $this->_string = $string;
    }

    /**
     * Create Form Element
     *
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute
     * @param string|int|bool $value
     * @param string $entityTypeCode
     * @param bool $isAjax
     * @return \Magento\Customer\Model\Metadata\Form\AbstractData
     */
    public function create(
        \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute,
        $value,
        $entityTypeCode,
        $isAjax = false
    ) {
        $dataModelClass = $attribute->getDataModel();
        $params = [
            'entityTypeCode' => $entityTypeCode,
            'value' => $value === null ? false : $value,
            'isAjax' => $isAjax,
            'attribute' => $attribute,
        ];
        /** TODO fix when Validation is implemented MAGETWO-17341 */
        if ($dataModelClass == \Magento\Customer\Model\Attribute\Data\Postcode::class) {
            $dataModelClass = \Magento\Customer\Model\Metadata\Form\Postcode::class;
        }
        if (!empty($dataModelClass)) {
            $dataModel = $this->_objectManager->create($dataModelClass, $params);
        } else {
            $dataModelClass = sprintf(
                'Magento\Customer\Model\Metadata\Form\%s',
                $this->_string->upperCaseWords($attribute->getFrontendInput())
            );
            $dataModel = $this->_objectManager->create($dataModelClass, $params);
        }

        return $dataModel;
    }
}
