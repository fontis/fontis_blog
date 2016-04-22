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

class Fontis_Blog_Block_Archive extends Fontis_Blog_Block_Abstract
{
    const CACHE_TAG = "fontis_blog_archives";

    protected $_date = null;
    protected $_type = null;

    /**
     * @return Fontis_Blog_Block_Archive
     */
    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection
     */
    public function getPosts()
    {
        /** @var $postCollection Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection */
        $postCollection = Mage::getModel("blog/post")->getCollection()
            ->setOrder("created_time", "desc");

        $dateParts = $this->getDate();
        $archiveType = $this->getType();
        $select = $postCollection->getSelect();
        $select->reset(Zend_Db_Select::WHERE);
        switch ($archiveType)
        {
            case Fontis_Blog_Model_System_ArchiveType::DAILY:
                $select->where(new Zend_Db_Expr("day(created_time) = " . $dateParts[2]));
            case Fontis_Blog_Model_System_ArchiveType::MONTHLY:
                $select->where(new Zend_Db_Expr("month(created_time) = " . $dateParts[1]));
            case Fontis_Blog_Model_System_ArchiveType::YEARLY:
            default:
                $select->where(new Zend_Db_Expr("year(created_time) = " . $dateParts[0]));
                break;
        }
        $postCollection->addBlogFilter($this->getBlog());
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

        return $postCollection;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $date = $this->getDate();
        switch ($this->getType())
        {
            case Fontis_Blog_Model_System_ArchiveType::DAILY:
                $dateString = date("l jS, F Y", strtotime(implode("-", $date)));
                break;
            case Fontis_Blog_Model_System_ArchiveType::MONTHLY:
                $dateString = date("F Y", strtotime(implode("-", $date) . "-01"));
                break;
            case Fontis_Blog_Model_System_ArchiveType::YEARLY:
            default:
                $dateString = date("Y", strtotime($date[0]));
                break;
        }
        return $this->__("Archives for %s", $dateString);
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if ($this->_date === null) {
            $this->_date = Mage::registry("current_blog_archive_date");
        }
        return $this->_date;
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this->_type === null) {
            $this->_type = Mage::registry("current_blog_archive_type");
        }
        return $this->_type;
    }
}
