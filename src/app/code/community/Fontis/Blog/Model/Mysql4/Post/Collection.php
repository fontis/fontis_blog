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

class Fontis_Blog_Model_Mysql4_Post_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = "fontis_blog_post_collection";

    /**
     * @var string
     */
    protected $_eventObject = "collection";

    protected function _construct()
    {
        $this->_init("blog/post");
    }

    /**
     * @param int $status
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function addEnableFilter($status)
    {
        $this->getSelect()->where("status = ?", $status);
        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Blog|int|int[] $blog
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function addBlogFilter($blog)
    {
        if ($blog instanceof Fontis_Blog_Model_Blog) {
            $blog = (int) $blog->getId();
        }
        if (!$blog) {
            return $this;
        }

        if (is_array($blog)) {
            $this->getSelect()->where("main_table.blog_id IN (?)", $blog);
        } else {
            $this->getSelect()->where("main_table.blog_id = ?", $blog);
        }

        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Cat|int|int[] $cat
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function addCatFilter($cat)
    {
        if ($cat instanceof Fontis_Blog_Model_Cat) {
            $cat = (int) $cat->getId();
        }
        $this->getSelect()->join(
            array("cat_table" => $this->getTable("blog/post_cat")),
            "main_table.post_id = cat_table.post_id",
            array()
        );

        if (is_array($cat)) {
            $this->getSelect()->where("cat_table.cat_id IN (?)", $cat);
        } else {
            $this->getSelect()->where("cat_table.cat_id = ?", $cat);
        }

        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Tag|int|int[] $tag
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function addTagFilter($tag)
    {
        if ($tag instanceof Fontis_Blog_Model_Tag) {
            $tag = (int) $tag->getId();
        }
        $this->getSelect()->join(
            array("tag_table" => $this->getTable("blog/post_tag")),
            "main_table.post_id = tag_table.post_id",
            array()
        );

        if (is_array($tag)) {
            $this->getSelect()->where("tag_table.tag_id IN (?)", $tag);
        } else {
            $this->getSelect()->where("tag_table.tag_id = ?", $tag);
        }

        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Author|int|int[]|string $author
     * @param bool $matchNameExact If $author is a string, should the match be an SQL = or an SQL LIKE
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function addAuthorFilter($author, $matchNameExact = false)
    {
        if ($author instanceof Fontis_Blog_Model_Author) {
            $author = (int) $author->getId();
        }
        $this->getSelect()->join(
            array("author_table" => $this->getTable("blog/author")),
            "main_table.author_id = author_table.author_id",
            array()
        );

        if (is_numeric($author)) {
            $this->getSelect()->where("author_table.author_id = ?", $author);
        } elseif (is_array($author)) {
            $this->getSelect()->where("author_table.author_id IN (?)", $author);
        } else {
            $operator = $matchNameExact === true ? "=" : "LIKE";
            $this->getSelect()->where("author_table.name $operator ?", $matchNameExact === true ? $author : "%$author%");
        }

        return $this;
    }

    /**
     * Bypass Mage_Core_Model_Resource_Db_Collection_Abstract so we can call afterLoad() on each
     * item in the collection at the same time as everything else, thereby avoiding an extra loop.
     *
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    protected function _afterLoad()
    {
        if (count($this) > 0) {
            foreach ($this->_items as $item) {
                /** @var $item Fontis_Blog_Model_Post */
                $item->afterLoad();
                $item->setOrigData();
                if ($this->_resetItemsDataChanged) {
                    $item->setDataChanges(false);
                }
            }
            Mage::dispatchEvent("core_collection_abstract_load_after", array("collection" => $this));
            if ($this->_eventPrefix && $this->_eventObject) {
                Mage::dispatchEvent($this->_eventPrefix . "_load_after", array(
                    $this->_eventObject => $this
                ));
            }
        }

        return Varien_Data_Collection_Db::_afterLoad();
    }

    /**
     * @param string $idField
     * @return array
     */
    public function toOptionArray($idField = "identifier")
    {
        return $this->_toOptionArray($idField, "title");
    }
}
