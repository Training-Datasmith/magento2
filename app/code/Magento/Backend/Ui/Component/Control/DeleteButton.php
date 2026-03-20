<?php

/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare (strict_types=1);
namespace Magento\Backend\Ui\Component\Control;

use Magento\Framework\App\Request_Interface;
use Magento\Framework\Escaper;
use Magento\Framework\Url_Interface;
use Magento\Framework\View\Element\Ui_Component\Control\Button_Provider_Interface;
/**
 * Represents delete button with pre-configured options
 * Provide an ability to show confirmation message on click on the "Delete" button
 *
 * @api
 * @since 101.0.0
 */
class Delete_Button implements Button_Provider_Interface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var UrlInterface
     */
    private $url_builder;
    /**
     * @var Escaper
     */
    private $escaper;
    /**
     * @var string
     */
    private $confirmation_message;
    /**
     * @var string
     */
    private $id_field_name;
    /**
     * @var string
     */
    private $delete_route_path;
    /**
     * @var int
     */
    private $sort_order;
    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param string $confirmationMessage
     * @param string $idFieldName
     * @param string $deleteRoutePath
     * @param int $sortOrder
     */
    public function __construct(Request_Interface $request, Url_Interface $url_builder, Escaper $escaper, string $confirmation_message, string $id_field_name, string $delete_route_path, int $sort_order)
    {
        $this->request = $request;
        $this->url_builder = $url_builder;
        $this->escaper = $escaper;
        $this->confirmation_message = $confirmation_message;
        $this->id_field_name = $id_field_name;
        $this->delete_route_path = $delete_route_path;
        $this->sort_order = $sort_order;
    }
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function get_button_data()
    {
        $data = [];
        $field_id = $this->escaper->escape_js($this->escaper->escape_html($this->request->get_param($this->id_field_name)));
        if (null !== $field_id) {
            $url = $this->url_builder->get_url($this->delete_route_path);
            $escaped_message = $this->escaper->escape_js($this->escaper->escape_html($this->confirmation_message));
            $data = ['label' => __('Delete'), 'class' => 'delete', 'on_click' => "deleteConfirm('{$escaped_message}', '{$url}', {data:{{$this->id_field_name}:{$field_id}}})", 'sort_order' => $this->sort_order];
        }
        return $data;
    }
}