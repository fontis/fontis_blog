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

class Fontis_Blog_Model_Mysql4_Cat extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "cat_id";

    public function _construct()
    {
        // Note that cat_id refers to the primary key field in your database table.
        $this->_init("blog/cat", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Fontis_Blog_Model_Mysql4_Cat
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if ($field == null) {
            /**
             * If a fieldname wasn't explicitly specified, check to see if the value
             * is an integer. If not, assume it is the category identifier, and use
             * that as the fieldname.
             */
            if (strcmp($value, (int) $value) !== 0) {
                $field = "identifier";
            }
        }
        return parent::load($object, $value, $field);
    }

    /**
     * @param Mage_Core_Model_Abstract $cat
     * @return Fontis_Blog_Model_Mysql4_Cat
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $cat)
    {
        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");

        if (!$this->getIsUniqueIdentifier($cat)) {
            Mage::throwException($helper->__("Category identifier already exists."));
        }

        if ($this->isNumericIdentifier($cat)) {
            Mage::throwException($helper->__("Category identifier cannot consist only of numbers."));
        }

        if ($helper->checkForReservedWord($cat->getData("identifier"))) {
            Mage::throwException($helper->__("Category identifier cannot be a reserved word."));
        }

        if (!$cat->getData("sort_order")) {
            // If no sort order was set, automatically set it by getting the largest current sort order and adding one to it.
            $largestSortOrder = $this->getLargestSortOrderValue($cat->getData("blog_id"));
            $cat->setData("sort_order", $largestSortOrder + 1);
        }

        return $this;
    }

    /**
     * @param int $blogId
     * @return int
     */
    protected function getLargestSortOrderValue($blogId = null)
    {
        $mainTable = $this->getMainTable();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(new Zend_Db_Expr("max(sort_order)")));
        if ($blogId !== null) {
            $select->where($mainTable . ".blog_id = ?", $blogId);
        }
        return (int) $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * @param Mage_Core_Model_Abstract $cat
     * @return bool
     */
    public function getIsUniqueIdentifier(Mage_Core_Model_Abstract $cat)
    {
        $mainTable = $this->getMainTable();
        $select = $this->_getReadAdapter()->select()
            ->from($mainTable)
            ->where($mainTable . ".identifier = ?", $cat->getData("identifier"))
            ->where($mainTable . ".blog_id = ?", $cat->getData("blog_id"));
        if ($cat->getId()) {
            $select->where($mainTable . ".cat_id <> ?", $cat->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Core_Model_Abstract $cat
     * @return int|bool
     */
    protected function isNumericIdentifier(Mage_Core_Model_Abstract $cat)
    {
        return preg_match("/^[0-9]+$/", $cat->getData("identifier"));
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $cat
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $cat)
    {
        $select = parent::_getLoadSelect($field, $value, $cat);

        if ($blogId = $cat->getBlogId()) {
            $select->where($this->getMainTable() . ".blog_id = ?", $blogId);
        }

        return $select;
    }
}
