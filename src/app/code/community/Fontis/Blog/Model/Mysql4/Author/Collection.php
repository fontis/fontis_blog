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

class Fontis_Blog_Model_Mysql4_Author_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * @var string
     */
    protected $_eventPrefix = "fontis_blog_author_collection";

    /**
     * @var string
     */
    protected $_eventObject = "collection";

    public function _construct()
    {
        $this->_init("blog/author");
    }

    /**
     * @param Fontis_Blog_Model_Blog|int $blog
     * @param bool $onlyEnabled
     * @return Fontis_Blog_Model_Author[]|Fontis_Blog_Model_Mysql4_Author_Collection
     */
    public function addBlogFilter($blog, $onlyEnabled = false)
    {
        if ($blog instanceof Fontis_Blog_Model_Blog) {
            $blog = (int) $blog->getId();
        }
        if (!$blog) {
            return $this;
        }

        $select = $this->getSelect();
        $select->distinct(true);
        $select->from(array("post_table" => $this->getTable("blog/post")), array())
            ->where("main_table.author_id = post_table.author_id")
            ->where("post_table.blog_id = ?", $blog);

        if ($onlyEnabled === true) {
            $select->where("post_table.status IN (?)", Mage::getSingleton("blog/status")->getEnabledStatusIds());
        }

        return $this;
    }

    /**
     * @param string $idField
     * @return array
     */
    public function toOptionArray($idField = "identifier")
    {
        return $this->_toOptionArray($idField, "name");
    }
}
