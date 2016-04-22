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

class Fontis_Blog_Model_System_Scope
{
    const SCOPE_NONE = 0;
    const SCOPE_BLOG = 1;
    const SCOPE_SITE = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper("blog");

        return array(
            array(
                "label" => $helper->__("Show on all pages"),
                "value" => self::SCOPE_SITE,
            ),
            array(
                "label" => $helper->__("Show on blog pages"),
                "value" => self::SCOPE_BLOG,
            ),
            array(
                "label" => $helper->__("Do not show"),
                "value" => self::SCOPE_NONE,
            ),
        );
    }

    /**
     * @param Mage_Core_Model_Config_Element $element
     * @param mixed $currentValue
     * @return string
     */
    public function getCommentText($element, $currentValue)
    {
        switch ($currentValue)
        {
            case self::SCOPE_NONE:
                return Mage::helper("blog")->__("Not visible on the site. Could still be accessible, if applicable.");
            case self::SCOPE_BLOG:
                return Mage::helper("blog")->__("Will only be visible on pages in this blog.");
            case self::SCOPE_SITE:
                return Mage::helper("blog")->__("Will be visible on all pages on the site.");
            default:
                return "";
        }
    }
}
