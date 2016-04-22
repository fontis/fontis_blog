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

abstract class Fontis_Blog_Block_Form_ArrayInput extends Varien_Data_Form_Element_Abstract
{
    const FIELD_NAME = "general";

    /**
     * @var string
     */
    protected $fieldName = self::FIELD_NAME;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("fontis/blog/system/form/array_dropdown.phtml");
    }

    /**
     * @return string
     */
    public function getElementHtml()
    {
        $block = Mage::getBlockSingleton("blog/form_arrayInputRenderer")
            ->setTemplate($this->getTemplate())
            ->setValues($this->getValues())
            ->setInputName($this->fieldName)
            ->setHtmlId($this->getHtmlId());
        $this->_addDataToBlock($block);
        $html = $block->toHtml();
        $html .= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @param string $idSuffix
     * @return string
     */
    public function getLabelHtml($idSuffix = "")
    {
        return $this->_getLabelHtml();
    }

    /**
     * @return string
     */
    abstract protected function _getLabelHtml();

    /**
     * @param Fontis_Blog_Block_Form_ArrayInputRenderer $block
     */
    abstract protected function _addDataToBlock(Fontis_Blog_Block_Form_ArrayInputRenderer $block);
}
