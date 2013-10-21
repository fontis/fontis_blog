<?php
/**
 * Fontis Blog Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Parts of this software are derived from code originally developed by
 * Robert Chambers <magento@robertchambers.co.uk>
 * and released as "Lazzymonk's Blog" 0.5.8 in 2009.
 *
 * @category   Fontis
 * @package    Fontis_Blog
 * @copyright  Copyright (c) 2013 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Blog_Model_System_Wysiwygenabled extends Mage_Adminhtml_Model_System_Config_Source_Cms_Wysiwyg_Enabled
{
    const WYSIWYG_DEFAULT = "default";

    public function toOptionArray()
    {
        return array_merge(parent::toOptionArray(), array(array(
            "value" => self::WYSIWYG_DEFAULT,
            "label" => Mage::helper("blog")->__("System Default")
        )));
    }
}
