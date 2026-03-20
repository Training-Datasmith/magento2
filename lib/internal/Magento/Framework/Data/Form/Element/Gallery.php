<?php

declare (strict_types=1);
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/**
 * Category form input image element
 */
namespace Magento\Framework\Data\Form\Element;

use Magento\Framework\App\Object_Manager;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\View\Helper\Secure_Html_Renderer;
/**
 * Gallery form element widget.
 */
class Gallery extends Abstract_Element
{
    /**
     * @var SecureHtmlRenderer
     */
    private $secure_renderer;
    /**
     * @var Random
     */
    private $random;
    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     * @param Random|null $random
     */
    public function __construct(Factory $factory_element, Collection_Factory $factory_collection, Escaper $escaper, $data = [], ?Secure_Html_Renderer $secure_renderer = null, ?Random $random = null)
    {
        $secure_renderer = $secure_renderer ?? Object_Manager::get_instance()->get(Secure_Html_Renderer::class);
        $random = $random ?? Object_Manager::get_instance()->get(Random::class);
        parent::__construct($factory_element, $factory_collection, $escaper, $data, $secure_renderer, $random);
        $this->set_type('file');
        $this->secure_renderer = $secure_renderer;
        $this->random = $random;
    }
    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function get_element_html()
    {
        $gallery = $this->get_value();
        $html = '<table id="gallery" class="gallery" border="0" cellspacing="3" cellpadding="0">';
        $html .= '<thead id="gallery_thead" class="gallery">' . '<tr class="gallery">' . '<td class="gallery" valign="middle" align="center">Big Image</td>' . '<td class="gallery" valign="middle" align="center">Thumbnail</td>' . '<td class="gallery" valign="middle" align="center">Small Thumb</td>' . '<td class="gallery" valign="middle" align="center">Sort Order</td>' . '<td class="gallery" valign="middle" align="center">Delete</td>' . '</tr>' . '</thead>';
        $widget_button = $this->get_form()->get_parent()->get_layout();
        $button_html = $widget_button->create_block(\Magento\Backend\Block\Widget\Button::class)->set_data(['label' => 'Add New Image', 'onclick' => 'addNewImg()', 'class' => 'add'])->to_html();
        $html .= '<tfoot class="gallery">';
        $html .= '<tr class="gallery">';
        $html .= '<td class="gallery" valign="middle" align="left" colspan="5">' . $button_html . '</td>';
        $html .= '</tr>';
        $html .= '</tfoot>';
        $html .= '<tbody class="gallery">';
        $i = 0;
        if ($this->get_value() !== null) {
            foreach ($this->get_value() as $image) {
                $i++;
                $html .= '<tr class="gallery">';
                foreach ($this->get_value()->get_attribute_backend()->get_image_types() as $type) {
                    $link_id = 'linkId' . $this->random->get_random_string(8);
                    $url = $image->set_type($type)->get_source_url();
                    $html .= '<td class="gallery vertical-gallery-cell" align="center">';
                    $html .= '<a previewlinkid="' . $link_id . '" href="' . $url . '" target="_blank" ' . $this->_get_ui_id('image-' . $image->get_value_id()) . '>
                    <img id="' . $this->get_html_id() . '_image_' . $type . '_' . $image->get_value_id() . '" src="' . $url . '" alt="' . $image->get_value() . '" height="25" align="absmiddle" class="small-image-preview"></a><br/>';
                    $html .= '<input type="file" name="' . $this->get_name() . '_' . $type . '[' . $image->get_value_id() . ']" size="1"' . $this->_get_ui_id('file') . ' ></td>';
                    $html .= $this->secure_renderer->render_event_listener_as_tag('onclick', "imagePreview('{$this->get_html_id()}_image_{$type}_{$image->get_value_id()}');\nreturn false;", "*[previewlinkid='{$link_id}']");
                }
                $html .= '<td class="gallery vertical-gallery-cell" align="center">' . '<input type="input" name="' . parent::get_name() . '[position][' . $image->get_value_id() . ']" value="' . $image->get_position() . '" id="' . $this->get_html_id() . '_position_' . $image->get_value_id() . '" size="3" ' . $this->_get_ui_id('position-' . $image->get_value_id()) . '/></td>';
                $html .= '<td class="gallery vertical-gallery-cell" align="center">' . '<input type="checkbox" name="' . parent::get_name() . '[delete][' . $image->get_value_id() . ']" value="' . $image->get_value_id() . '" id="' . $this->get_html_id() . '_delete_' . $image->get_value_id() . '" ' . $this->_get_ui_id('delete-button-' . $image->get_value_id()) . '/></td>';
                $html .= '</tr>';
            }
            $html .= $this->secure_renderer->render_tag('style', [], <<<style
                                .vertical-gallery-cell {
                                    vertical-align:bottom;
                                }
            style, false);
        }
        if ($i == 0) {
            $html .= $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], 'document.getElementById("gallery_thead").style.visibility="hidden";', false);
        }
        $html .= '</tbody></table>';
        $name = $this->get_name();
        $parent_name = parent::get_name();
        $html .= $this->secure_renderer->render_tag('script', ['type' => 'text/javascript'], <<<EndSCRIPT
                id = 0;
        
                function addNewImg(){
        
                    document.getElementById("gallery_thead").style.visibility="visible";
        
                    id--;
                    new_file_input = '<input type="file" name="{$name}_%j%[%id%]" size="1" />';
        
        \t\t    // Sort order input
        \t\t    var new_row_input = document.createElement( 'input' );
        \t\t    new_row_input.type = 'text';
        \t\t    new_row_input.name = '{$parent_name}[position]['+id+']';
        \t\t    new_row_input.size = '3';
        \t\t    new_row_input.value = '0';
        
        \t\t    // Delete button
        \t\t    var new_row_button = document.createElement( 'input' );
        \t\t    new_row_button.type = 'checkbox';
        \t\t    new_row_button.value = 'Delete';
        
                    table = document.getElementById( "gallery" );
        
                    // no of rows in the table:
                    noOfRows = table.rows.length;
        
                    // no of columns in the pre-last row:
                    noOfCols = table.rows[noOfRows-2].cells.length;
        
                    // insert row at pre-last:
                    var x=table.insertRow(noOfRows-1);
        
                    // insert cells in row.
                    for (var j = 0; j < noOfCols; j++) {
        
                        newCell = x.insertCell(j);
                        newCell.align = "center";
                        newCell.valign = "middle";
        
                        if (j==3) {
        \t\t            newCell.appendChild( new_row_input );
                        }
                        else if (j==4) {
        \t\t            newCell.appendChild( new_row_button );
                        }
                        else {
                            newCell.innerHTML = new_file_input.replace(/%j%/g, j).replace(/%id%/g, id);
                        }
        
                    }
        
        \t\t    // Delete function
        \t\t    new_row_button.onclick= function(){
        
                        this.parentNode.parentNode.parentNode.removeChild( this.parentNode.parentNode );
        
        \t\t\t    // Appease Safari
        \t\t\t    //    without it Safari wants to reload the browser window
        \t\t\t    //    which nixes your already queued uploads
        \t\t\t    return false;
        \t\t    };
        
        \t    }
        EndSCRIPT, false);
        $html .= $this->get_after_element_html();
        return $html;
    }
    /**
     * @inheritDoc
     */
    public function get_name()
    {
        return $this->get_data('name');
    }
    /**
     * Get name in the usual way.
     *
     * @return string|null
     */
    public function get_parent_name()
    {
        return parent::get_name();
    }
}