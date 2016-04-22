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

class Fontis_Blog_Block_Manage_Post_Edit_Tab_Options extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Fontis_Blog_Block_Manage_Post_Edit_Tab_Options
     */
    protected function _prepareForm()
    {
        /** @var $blogHelper Fontis_Blog_Helper_Data */
        $blogHelper = Mage::helper("blog");
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form");
        $this->setForm($form);
        $fieldset = $form->addFieldset("blog_form", array("legend" => $blogHelper->__("Meta Data")));

        $fieldset->addField("meta_keywords", "editor", array(
            "name"  => "meta_keywords",
            "label" => $blogHelper->__("Keywords"),
            "title" => $blogHelper->__("Meta Keywords"),
            "style" => "width: 350px; height: 80px;",
        ));

        $fieldset->addField("meta_description", "editor", array(
            "name"  => "meta_description",
            "label" => $blogHelper->__("Description"),
            "title" => $blogHelper->__("Meta Description"),
            "style" => "width: 350px; height: 80px;",
        ));

        $fieldset = $form->addFieldset("blog_options", array("legend" => $blogHelper->__("Advanced Post Options")));

        $fieldset->addField("created_time", "text", array(
            "label"                 => $blogHelper->__("Post Date"),
            "name"                  => "created_time",
            "style"                 => "width: 260px;",
            "after_element_html"    => '<span class="hint"><br />eg: YYYY-MM-DD HH:MM:SS<br />Leave blank to use the current date.</span>',
        ));

        if (Mage::getSingleton("adminhtml/session")->getBlogData()) {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getBlogData());
            Mage::getSingleton("adminhtml/session")->setBlogData(null);
        } elseif (Mage::registry("blog_data")) {
            $form->setValues(Mage::registry("blog_data")->getData());
        }
        return parent::_prepareForm();
    }
}
