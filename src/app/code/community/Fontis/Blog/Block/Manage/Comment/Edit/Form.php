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

class Fontis_Blog_Block_Manage_Comment_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Fontis_Blog_Block_Manage_Comment_Edit_Form
     */
    protected function _prepareForm()
    {
        /** @var $blogHelper Fontis_Blog_Helper_Data */
        $blogHelper = Mage::helper("blog");
        /** @var $comment Fontis_Blog_Model_Comment */
        $comment = Mage::registry("current_blog_comment");

        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form", array(
            "id" => "edit_form",
            "action" => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
            "method" => "post",
        ));
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset("comment_form", array("legend" => $blogHelper->__("Comment Information")));
        $fieldset->addType("link_text", Mage::getConfig()->getBlockClassName("blog/form_link"));

        $fieldset->addField("post", "link_text", array(
            "label"     => $blogHelper->__("Post"),
            "text"      => sprintf("%s (ID: %s)", $comment->getPost()->getTitle(), $comment->getPostId()),
            "href"      => $this->getUrl("*/blog_post/edit", array("id" => $comment->getPostId())),
            "target"    => "_blank",
        ));

        $fieldset->addField("user", "text", array(
            "label"     => $blogHelper->__("User"),
            "name"      => "user",
        ));

        $fieldset->addField("email", "text", array(
            "label"     => $blogHelper->__("Email Address"),
            "name"      => "email",
        ));

        $fieldset->addField("status", "select", array(
            "label"     => $blogHelper->__("Status"),
            "name"      => "status",
            "values"    => array(
                array(
                    "value"     => Fontis_Blog_Model_Comment::COMMENT_UNAPPROVED,
                    "label"     => $blogHelper->__("Unapproved"),
                ),
                array(
                    "value"     => Fontis_Blog_Model_Comment::COMMENT_APPROVED,
                    "label"     => $blogHelper->__("Approved"),
                ),
            ),
        ));

        $fieldset->addField("comment", "editor", array(
            "name"      => "comment",
            "label"     => $blogHelper->__("Comment"),
            "title"     => $blogHelper->__("Comment"),
            "style"     => "width: 700px; height: 500px;",
            "wysiwyg"   => false,
            "required"  => false,
        ));

        $form->setValues($comment->getData());
        return parent::_prepareForm();
    }
}
