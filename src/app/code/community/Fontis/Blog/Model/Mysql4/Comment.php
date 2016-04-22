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

class Fontis_Blog_Model_Mysql4_Comment extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "comment_id";

    public function _construct()
    {    
        // Note that comment_id refers to the primary key field in your database table.
        $this->_init("blog/comment", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param mixed $value
     * @param string $field
     * @return Fontis_Blog_Model_Mysql4_Comment
     */
    public function load(Mage_Core_Model_Abstract $object, $value, $field = null)
    {
        if (strcmp($value, (int) $value) !== 0) {
            $field = "post_id";
        }
        return parent::load($object, $value, $field);
    }

    /**
     * @param int $commentId
     * @return bool
     */
    public function doesCommentExist($commentId)
    {
        $conn = $this->getReadConnection();
        $select = $conn->select();
        $select->from($this->getMainTable(), self::PK_FIELD)
            ->where(self::PK_FIELD . " = ?", $commentId);

        $dbCommentId = $conn->fetchOne($select);
        if (!empty($dbCommentId)) {
            return true;
        } else {
            return false;
        }
    }
}
