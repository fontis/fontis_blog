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

class Fontis_Blog_Model_Mysql4_Blog_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = "fontis_blog_blog_collection";

    /**
     * @var string
     */
    protected $_eventObject = "collection";

    protected function _construct()
    {
        $this->_init("blog/blog");
    }

    /**
     * @param Mage_Core_Model_Store|int|int[] $store
     * @return Fontis_Blog_Model_Mysql4_Blog_Collection
     */
    public function addStoreFilter($store)
    {
        if (Mage::app()->isSingleStoreMode()) {
            return $this;
        }

        if ($store instanceof Mage_Core_Model_Store) {
            $store = (int) $store->getId();
        }
        if (is_array($store)) {
            $store[] = Mage_Core_Model_App::ADMIN_STORE_ID;
        } else {
            $store = array(
                $store,
                Mage_Core_Model_App::ADMIN_STORE_ID,
            );
        }

        $this->getSelect()->join(
            array("store_table" => $this->getTable("blog/store")),
            "main_table.blog_id = store_table.blog_id",
            array()
        )
        ->where("store_table.store_id IN (?)", $store);

        return $this;
    }

    /**
     * @return Fontis_Blog_Model_Mysql4_Blog_Collection
     */
    public function addEnableFilter()
    {
        $enabledStatuses = Mage::getModel("blog/system_blogStatus")->getEnabledStatuses();
        $this->getSelect()->where("status = ?", $enabledStatuses);
        return $this;
    }

    /**
     * @param bool $includeEmpty
     * @param array $additional
     * @return array
     */
    public function toDisplayOptionArray($includeEmpty = false, array $additional = array())
    {
        $res = array();
        $values = array(
            "value" => "id",
            "label" => "label",
        );
        $values = array_merge($values, $additional);

        if ($includeEmpty === true) {
            $res[] = array("value" => "", "label" => "");
        }

        foreach ($this as $item) {
            /** @var $item Fontis_Blog_Model_Blog */
            $data = array();
            foreach ($values as $code => $field) {
                $data[$code] = $item->getDataUsingMethod($field);
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * @return Fontis_Blog_Model_Mysql4_Blog_Collection
     */
    protected function _afterLoad()
    {
        if (count($this) > 0) {
            Mage::dispatchEvent("fontis_blog_blog_collection_load_after", array("collection" => $this));
            /** @var $blogResourceModel Fontis_Blog_Model_Mysql4_Blog */
            $blogResourceModel = Mage::getResourceModel("blog/blog");
            foreach ($this as $blog) {
                $blogResourceModel->afterLoad($blog);
            }
        }

        return parent::_afterLoad();
    }
}
