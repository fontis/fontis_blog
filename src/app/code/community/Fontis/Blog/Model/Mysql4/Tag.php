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

class Fontis_Blog_Model_Mysql4_Tag extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "tag_id";

    public function _construct()
    {
        // Note that tag_id refers to the primary key field in your database table.
        $this->_init("blog/tag", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Fontis_Blog_Model_Mysql4_Tag
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if ($field == null) {
            /**
             * If a fieldname wasn't explicitly specified, check to see if the value
             * is an integer. If not, assume it is the tag identifier, and use that
             * as the fieldname.
             */
            if (strcmp($value, (int) $value) !== 0) {
                $field = "identifier";
            }
        }
        return parent::load($object, $value, $field);
    }

    /**
     * Checks the tags tables in the database to make sure each tag is assigned to
     * at least one post. If a tag isn't, it is deleted from the database.
     */
    public function clearStaleTags()
    {
        $tagTable = $this->getTable("blog/tag");
        $postTagTable = $this->getTable("blog/post_tag");
        $writeAdapter = $this->_getWriteAdapter();

        $query = "SELECT `$tagTable`.`tag_id` FROM `$tagTable` WHERE `$tagTable`.`tag_id` NOT IN ";
        $query .= "(SELECT `$postTagTable`.`tag_id` FROM `$postTagTable`)";
        $staleTags = $writeAdapter->fetchCol($query);

        $condition = $writeAdapter->quoteInto("tag_id IN (?)", $staleTags);
        $writeAdapter->delete($tagTable, $condition);
    }

    /**
     * Retrieve select object for load object data.
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $tag
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $tag)
    {
        $select = parent::_getLoadSelect($field, $value, $tag);

        if ($blogId = $tag->getBlogId()) {
            $select->where($this->getMainTable() . ".blog_id = ?", $blogId);
        }

        return $select;
    }
}
