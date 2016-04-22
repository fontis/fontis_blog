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

class Fontis_Blog_Model_Mysql4_Post extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "post_id";

    protected function _construct()
    {
        // Note that post_id refers to the primary key field in your database table.
        $this->_init("blog/post", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Fontis_Blog_Model_Mysql4_Post
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if ($field == null) {
            /**
             * If a fieldname wasn't explicitly specified, check to see if the value
             * is an integer. If not, assume it is the post identifier, and use that
             * as the fieldname.
             */
            if (strcmp($value, (int) $value) !== 0) {
                $field = "identifier";
            }
        }
        return parent::load($object, $value, $field);
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @return Fontis_Blog_Model_Mysql4_Post
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $post)
    {
        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");

        if (!$this->getIsUniqueIdentifier($post)) {
            Mage::throwException($helper->__("Post identifier already exists."));
        }

        if ($this->isNumericIdentifier($post)) {
            Mage::throwException($helper->__("Post identifier cannot consist only of numbers."));
        }

        if ($helper->checkForReservedWord($post->getData("identifier"))) {
            Mage::throwException($helper->__("Post identifier cannot be a reserved word."));
        }

        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @return bool
     */
    public function getIsUniqueIdentifier(Mage_Core_Model_Abstract $post)
    {
        $mainTable = $this->getMainTable();
        $select = $this->_getReadAdapter()->select()
                ->from($mainTable)
                ->where($mainTable . ".identifier = ?", $post->getData("identifier"))
                ->where($mainTable . ".blog_id = ?", $post->getData("blog_id"));
        if ($post->getId()) {
            $select->where($mainTable . ".post_id <> ?", $post->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @return int|bool
     */
    protected function isNumericIdentifier(Mage_Core_Model_Abstract $post)
    {
        return preg_match("/^[0-9]+$/", $post->getData("identifier"));
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @return Fontis_Blog_Model_Mysql4_Post
     */
    protected function _afterSave(Mage_Core_Model_Abstract $post)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $this->saveCats($post, $writeAdapter);
        $this->saveTags($post, $writeAdapter);

        return parent::_afterSave($post);
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @param Varien_Db_Adapter_Interface $writeAdapter
     */
    protected function saveCats(Mage_Core_Model_Abstract $post, $writeAdapter)
    {
        $tableName = $this->getTable("blog/post_cat");
        $condition = $writeAdapter->quoteInto(self::PK_FIELD . " = ?", $post->getId());
        $writeAdapter->delete($tableName, $condition);

        foreach ((array) $post->getData("cat_ids") as $catId) {
            $catArray = array(
                self::PK_FIELD  => $post->getId(),
                "cat_id"        => $catId,
            );
            $writeAdapter->insert($tableName, $catArray);
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $post
     * @param Varien_Db_Adapter_Interface $writeAdapter
     */
    protected function saveTags(Mage_Core_Model_Abstract $post, $writeAdapter)
    {
        $postTagTableName = $this->getTable("blog/post_tag");

        $condition = $writeAdapter->quoteInto(self::PK_FIELD . " = ?", $post->getId());
        $writeAdapter->delete($postTagTableName, $condition);

        $tagIds = $post->getData("tag_ids");
        if (!$tagIds) {
            Mage::getResourceModel("blog/tag")->clearStaleTags();
            return;
        }

        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");
        $blogId = $post->getBlogId();
        foreach ($tagIds as $tagIdentifier => $tagData) {
            $tagName = trim($tagData["tag_id"]);
            if (empty($tagName)) {
                continue;
            }

            /** @var $tag Fontis_Blog_Model_Tag */
            $tag = Mage::getModel("blog/tag")->setBlogId($blogId)->load($tagIdentifier);
            if (!$tag->getId()) {
                // The tag might be new to the post, but still already exist in the database.
                $tag->setBlogId($blogId)->load($helper->toAscii($tagName));
            }
            if ($tag->getName() != $tagName) {
                $tag->setName($tagName);
                $tagIdentifier = $helper->toAscii($tagData["tag_id"]);
                $tag->setIdentifier($tagIdentifier);
                $tag->setBlogId($blogId);
                $tag->save();
            }

            $tagArray = array(
                Fontis_Blog_Model_Mysql4_Tag::PK_FIELD => $tag->getId(),
                self::PK_FIELD => $post->getId(),
            );
            $writeAdapter->insert($postTagTableName, $tagArray);
        }

        Mage::getResourceModel("blog/tag")->clearStaleTags();
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Fontis_Blog_Model_Post $post
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $post)
    {
        $select = parent::_getLoadSelect($field, $value, $post);

        if ($blogId = $post->getBlogId()) {
            $select->where($this->getMainTable() . ".blog_id = ?", $blogId);
        }

        return $select;
    }
}
