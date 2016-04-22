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

class Fontis_Blog_Block_Manage_Blog_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * @return Fontis_Blog_Block_Manage_Blog_Edit_Form
     */
    protected function _prepareForm()
    {
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form", array(
            "id"        => "edit_form",
            "action"    => $this->getUrl("*/*/save", array("id" => $this->getRequest()->getParam("id"))),
            "method"    => "post",
            "enctype"   => "multipart/form-data",
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
