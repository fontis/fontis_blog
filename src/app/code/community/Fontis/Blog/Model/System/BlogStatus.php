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

class Fontis_Blog_Model_System_BlogStatus
{
    const ENABLED = 1;
    const DISABLED = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $helper = Mage::helper("blog");
        return array(
            array(
                "value" => self::DISABLED,
                "label" => $helper->__("Disabled"),
            ),
            array(
                "value" => self::ENABLED,
                "label" => $helper->__("Enabled"),
            ),
        );
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        $helper = Mage::helper("blog");
        return array(
            self::ENABLED => $helper->__("Enabled"),
            self::DISABLED => $helper->__("Disabled"),
        );
    }

    /**
     * @return array
     */
    public function getEnabledStatuses()
    {
        return array(self::ENABLED);
    }

    /**
     * @return array
     */
    public function getDisabledStatuses()
    {
        return array(self::DISABLED);
    }
}
