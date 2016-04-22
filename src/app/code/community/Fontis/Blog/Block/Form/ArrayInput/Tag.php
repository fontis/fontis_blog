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

class Fontis_Blog_Block_Form_ArrayInput_Tag extends Fontis_Blog_Block_Form_ArrayInput
{
    const FIELD_NAME = "tag_id";

    /**
     * @var string
     */
    protected $fieldName = self::FIELD_NAME;

    /**
     * @return string
     */
    protected function _getLabelHtml()
    {
        return Mage::helper("blog")->__("Tags");
    }

    /**
     * @param Fontis_Blog_Block_Form_ArrayInputRenderer $block
     */
    protected function _addDataToBlock(Fontis_Blog_Block_Form_ArrayInputRenderer $block)
    {
        $helper = Mage::helper("blog");

        $block->addColumn(self::FIELD_NAME, array(
            "label" => $helper->__("Tag"),
            "size"  => 40,
        ));

        $block->setAddButtonLabel($helper->__("Add Tag"));
        $block->setRemoveButtonLabel($helper->__("Remove"));
    }
}
