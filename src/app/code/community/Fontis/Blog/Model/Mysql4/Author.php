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

class Fontis_Blog_Model_Mysql4_Author extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "author_id";

    public function _construct()
    {
        // Note that author_id refers to the primary key field in your database table.
        $this->_init("blog/author", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Fontis_Blog_Model_Mysql4_Author
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if ($field == null) {
            /**
             * If a fieldname wasn't explicitly specified, check to see if the value
             * is an integer. If not, assume it is the author identifier, and use that
             * as the fieldname.
             */
            if (strcmp($value, (int) $value) !== 0) {
                $field = "identifier";
            }
        }
        return parent::load($object, $value, $field);
    }

    /**
     * @param Mage_Core_Model_Abstract $author
     * @return Fontis_Blog_Model_Mysql4_Author
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $author)
    {
        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");

        if (!$this->getIsUniqueIdentifier($author)) {
            Mage::throwException($helper->__("Author identifier already exists."));
        }

        if ($this->isNumericIdentifier($author)) {
            Mage::throwException($helper->__("Author identifier cannot consist only of numbers."));
        }

        if ($helper->checkForReservedWord($author->getData("identifier"))) {
            Mage::throwException($helper->__("Author identifier cannot be a reserved word."));
        }

        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $author
     * @return bool
     */
    public function getIsUniqueIdentifier(Mage_Core_Model_Abstract $author)
    {
        $mainTable = $this->getMainTable();
        $select = $this->_getReadAdapter()->select()
            ->from($mainTable)
            ->where($mainTable . ".identifier = ?", $author->getData("identifier"));
        if ($author->getId()) {
            $select->where($mainTable . ".author_id <> ?", $author->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Core_Model_Abstract $author
     * @return int|bool
     */
    protected function isNumericIdentifier(Mage_Core_Model_Abstract $author)
    {
        return preg_match("/^[0-9]+$/", $author->getData("identifier"));
    }

    /**
     * @param Fontis_Blog_Model_Author $author
     * @param bool $onlyEnabled
     * @param Fontis_Blog_Model_Blog|int $blog
     * @return int
     */
    public function getPostCount(Fontis_Blog_Model_Author $author, $onlyEnabled = true, $blog = null)
    {
        /** @var $collection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $collection = Mage::getModel("blog/post")->getCollection();
        $collection->addAuthorFilter($author)
            ->setOrder("created_time", "desc");
        if ($onlyEnabled === true) {
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);
        }

        if ($blog !== null) {
            $collection->addBlogFilter($blog);
        }

        return (int) $collection->getSize();
    }
}
