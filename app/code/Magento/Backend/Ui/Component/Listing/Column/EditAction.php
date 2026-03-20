<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Ui\Component\Listing\Column;

use Magento\Framework\Url_Interface;
use Magento\Framework\View\Element\Ui_Component\Context_Interface;
use Magento\Framework\View\Element\Ui_Component_Factory;
use Magento\Ui\Component\Listing\Columns\Column;
/**
 * Represents Edit link in grid for entity by its identifier field
 *
 * @api
 * @since 101.0.0
 */
class Edit_Action extends Column
{
    /**
     * @var UrlInterface
     */
    private $url_builder;
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(Context_Interface $context, Ui_Component_Factory $ui_component_factory, Url_Interface $url_builder, array $components = [], array $data = [])
    {
        $this->url_builder = $url_builder;
        parent::__construct($context, $ui_component_factory, $components, $data);
    }
    /**
     * @param array $dataSource
     * @return array
     * @since 101.0.0
     */
    public function prepare_data_source(array $data_source)
    {
        if (isset($data_source['data']['items'])) {
            foreach ($data_source['data']['items'] as &$item) {
                if (isset($item[$item['id_field_name']])) {
                    $edit_url_path = $this->get_data('config/editUrlPath') ?: '#';
                    $item[$this->get_data('name')] = ['edit' => ['href' => $this->url_builder->get_url($edit_url_path, [$item['id_field_name'] => $item[$item['id_field_name']]]), 'label' => __('Edit')]];
                    unset($item);
                }
            }
        }
        return $data_source;
    }
}