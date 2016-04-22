<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2016 Fontis Pty. Ltd. (https://www.fontis.com.au)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Block_Manage_Blog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = "id";
        $this->_blockGroup = "blog";
        $this->_controller = "manage_blog";

        $this->_updateButton("save", "label", Mage::helper("blog")->__("Save Blog"));
        //$this->_updateButton("delete", "label", Mage::helper("blog")->__("Delete Blog"));
        $this->_removeButton("delete");

        $this->_addButton(
            "saveandcontinue",
            array(
                "label"     => Mage::helper("adminhtml")->__("Save And Continue Edit"),
                "onclick"   => "saveAndContinueEdit()",
                "class"     => "save",
            ),
            -100
        );

        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action + 'back/edit/');
            }
        ";
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry("blog_data") && Mage::registry("blog_data")->getId()) {
            return Mage::helper("blog")->__("Edit Blog '%s'", $this->escapeHtml(Mage::registry("blog_data")->getTitle()));
        } else {
            return Mage::helper("blog")->__("Add New Blog");
        }
    }
}
