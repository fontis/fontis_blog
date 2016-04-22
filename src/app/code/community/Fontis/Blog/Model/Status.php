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

class Fontis_Blog_Model_Status extends Varien_Object
{
    protected $_eventPrefix = "blog_status";

    const STATUS_ENABLED    = 1;
    const STATUS_DISABLED   = 2;
    const STATUS_HIDDEN     = 3;

    /**
     * @param Fontis_Blog_Model_Mysql4_Post_Collection $collection
     * @return Fontis_Blog_Model_Status
     */
    public function addEnabledFilterToCollection($collection)
    {
        $collection->addEnableFilter(array("in" => $this->getEnabledStatusIds()));
        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Mysql4_Post_Collection $collection
     * @param Mage_Catalog_Model_Category $cat
     * @return Fontis_Blog_Model_Status
     */
    public function addCatFilterToCollection($collection, $cat)
    {
        $collection->addCatFilter($cat);
        return $this;
    }

    /**
     * @return array
     */
    public function getEnabledStatusIds()
    {
        return array(self::STATUS_ENABLED);
    }

    /**
     * @return array
     */
    public function getDisabledStatusIds()
    {
        return array(self::STATUS_DISABLED);
    }

    /**
     * @return array
     */
    public function getHiddenStatusIds()
    {
        return array(self::STATUS_HIDDEN);
    }

    /**
     * @return array
     */
    public function getOptionArray()
    {
        $blogHelper = Mage::helper("blog");
        return array(
            self::STATUS_ENABLED    => $blogHelper->__("Enabled"),
            self::STATUS_DISABLED   => $blogHelper->__("Disabled"),
            self::STATUS_HIDDEN     => $blogHelper->__("Hidden"),
        );
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $blogHelper = Mage::helper("blog");
        return array(
            array(
                "value"   => self::STATUS_ENABLED,
                "label"   => $blogHelper->__("Enabled"),
            ),
            array(
                "value"   => self::STATUS_DISABLED,
                "label"   => $blogHelper->__("Disabled"),
            ),
            array(
                "value"   => self::STATUS_HIDDEN,
                "label"   => $blogHelper->__("Hidden"),
            ),
        );
    }
}
