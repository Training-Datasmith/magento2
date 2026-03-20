<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Ui\Component\Control;

use Magento\Framework\View\Element\Ui_Component\Control\Button_Provider_Interface;
use Magento\Ui\Component\Control\Container;
/**
 * Represents split-button with pre-configured options
 * Provide an ability to show drop-down list with options clicking on the "Save" button
 *
 * @api
 * @since 101.0.0
 */
class Save_Split_Button implements Button_Provider_Interface
{
    /**
     * @var string
     */
    private $target_name;
    /**
     * @param string $targetName
     */
    public function __construct(string $target_name)
    {
        $this->target_name = $target_name;
    }
    /**
     * {@inheritdoc}
     * @since 101.0.0
     */
    public function get_button_data()
    {
        return ['label' => __('Save &amp; Continue'), 'class' => 'save primary', 'data_attribute' => ['mage-init' => ['buttonAdapter' => ['actions' => [['targetName' => $this->target_name, 'actionName' => 'save', 'params' => [
            // first param is redirect flag
            false,
        ]]]]]], 'class_name' => Container::SPLIT_BUTTON, 'options' => $this->get_options(), 'sort_order' => 40];
    }
    /**
     * @return array
     */
    private function get_options(): array
    {
        $options = [['label' => __('Save &amp; Close'), 'data_attribute' => ['mage-init' => ['buttonAdapter' => ['actions' => [['targetName' => $this->target_name, 'actionName' => 'save', 'params' => [
            // first param is redirect flag
            true,
        ]]]]]], 'sort_order' => 10], ['label' => __('Save &amp; New'), 'data_attribute' => ['mage-init' => ['buttonAdapter' => ['actions' => [['targetName' => $this->target_name, 'actionName' => 'save', 'params' => [
            // first param is redirect flag, second is data that will be added to post
            // request
            true,
            ['redirect_to_new' => 1],
        ]]]]]], 'sort_order' => 20]];
        return $options;
    }
}