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

class Fontis_Blog_Model_Mysql4_Cat_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = "fontis_blog_cat_collection";

    /**
     * @var string
     */
    protected $_eventObject = "collection";

    public function _construct()
    {
        $this->_init("blog/cat");
    }

    /**
     * @param Fontis_Blog_Model_Post|int|int[] $post
     * @return Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection
     */
    public function addPostFilter($post)
    {
        if ($post instanceof Fontis_Blog_Model_Post) {
            $post = (int) $post->getId();
        }
        $this->getSelect()->join(
            array("cat_table" => $this->getTable("blog/post_cat")),
            "main_table.cat_id = cat_table.cat_id",
            array()
        );

        if (is_array($post)) {
            $this->getSelect()->where("cat_table.post_id IN (?)", $post);
        } else {
            $this->getSelect()->where("cat_table.post_id = ?", $post);
        }

        return $this;
    }

    /**
     * @param Fontis_Blog_Model_Blog|int|int[] $blog
     * @return Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection
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
     * @param string $idField
     * @return array
     */
    public function toOptionArray($idField = "identifier")
    {
        return $this->_toOptionArray($idField, "title");
    }
}
