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

class Fontis_Blog_Block_Menu extends Mage_Core_Block_Template
{
    const CACHE_TAG = "fontis_blog_menu";

    const RECENT_POSTS_DEFAULT = 5;

    /**
     * @var Fontis_Blog_Helper_Data
     */
    protected $_blogHelper = null;

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        return Mage::registry("current_blog_object");
    }

    /**
     * @return Fontis_Blog_Helper_Data
     */
    public function getBlogHelper()
    {
        if ($this->_blogHelper === null) {
            $this->_blogHelper = Mage::helper("blog");
        }
        return $this->_blogHelper;
    }

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->getBlogHelper()->addTagToFpc(array(self::CACHE_TAG, Fontis_Blog_Helper_Data::GLOBAL_CACHE_TAG));
        return parent::_prepareLayout();
    }

    /**
     * @param string $template
     * @param string|null $test
     * @return Fontis_Blog_Block_Menu
     */
    public function setTemplate($template, $test = null)
    {
        if ($test === null) {
            parent::setTemplate($template);
        } else {
            if ($this->getBlog()->getSetting($test)) {
                parent::setTemplate($template);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getArchiveLabel()
    {
        return Fontis_Blog_Model_System_ArchiveType::getTypeLabel($this->getBlog()->getSetting("archives/type"));
    }

    /**
     * @param int $useLimit whether or not to use the limit in the system config
     * @return Fontis_Blog_Model_Mysql4_Post_Collection|bool
     */
    public function getArchives($useLimit = 1)
    {
        $blog = $this->getBlog();
        if ($blog->getSetting("archives/enabled")) {
            /** @var $postCollection Fontis_Blog_Model_Mysql4_Post_Collection */
            $postCollection = Mage::getModel("blog/post")->getCollection()
                ->addBlogFilter($this->getBlog())
                ->setOrder("created_time", $blog->getSetting("archives/order"));
            Mage::getSingleton("blog/status")->addEnabledFilterToCollection($postCollection);

            $archiveType = $blog->getSetting("archives/type");
            if ($archiveType == Fontis_Blog_Model_System_ArchiveType::YEARLY) {
                $columns = array(new Zend_Db_Expr("year(created_time) as year"));
                $group = "year(created_time)";
            } elseif ($archiveType == Fontis_Blog_Model_System_ArchiveType::MONTHLY) {
                $columns = array(
                    new Zend_Db_Expr("year(created_time) as year"),
                    new Zend_Db_Expr("month(created_time) as month")
                );
                $group = "year(created_time), month(created_time)";
            } elseif ($archiveType == Fontis_Blog_Model_System_ArchiveType::DAILY) {
                $columns = array(
                    new Zend_Db_Expr("year(created_time) as year"),
                    new Zend_Db_Expr("month(created_time) as month"),
                    new Zend_Db_Expr("day(created_time) as day"),
                );
                $group = "year(created_time), month(created_time), day(created_time)";
            }
            if ($this->showPostCount()) {
                $columns[] = new Zend_Db_Expr("count(main_table.post_id) as postcount");
            }
            $postCollection->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns($columns)
                ->group($group);
            if ($useLimit && $limit = $blog->getSetting("archives/limit")) {
                $postCollection->getSelect()->limit($limit);
            }

            $helper = $this->getBlogHelper();
            $dateString = Fontis_Blog_Model_System_ArchiveType::getTypeFormat($archiveType);
            foreach ($postCollection as $item) {
                /** @var $item Varien_Object */
                $dateParts = $item->toArray(array("year", "month", "day"));
                $item->setAddress($blog->getBlogUrl($helper->getArchiveUrlPath($dateParts)));
                if ($dateParts["month"] === null) {
                    $dateParts["month"] = 1;
                }
                if ($dateParts["day"] === null) {
                    $dateParts["day"] = 1;
                }
                $item->setDateString(date($dateString, mktime(0, 0, 0, $dateParts["month"], $dateParts["day"], $dateParts["year"])));
            }
            return $postCollection;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function showPostCount()
    {
        return $this->getBlog()->getSettingFlag("archives/showcount");
    }

    /**
     * @param int $recentCount
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection|null
     */
    public function getRecent($recentCount = -1)
    {
        $blog = $this->getBlog();
        /** @var $posts Fontis_Blog_Model_Mysql4_Post_Collection */
        $posts = Mage::getModel("blog/post")->getCollection()
            ->addBlogFilter($blog)
            ->setOrder("created_time", "desc");
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($posts);

        if ($recentCount !== null) {
            if (!is_numeric($recentCount) || $recentCount < 1) {
                $recentCount = $blog->getSetting("menu/recent");
            }
            if (is_numeric($recentCount)) {
                if ($recentCount == 0) {
                    return null;
                } elseif ($recentCount < 1) {
                    $recentCount = self::RECENT_POSTS_DEFAULT;
                }
            } else {
                $recentCount = self::RECENT_POSTS_DEFAULT;
            }

            $posts->getSelect()->limit($recentCount);
        }

        return $posts;
    }

    /**
     * @return Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection
     */
    public function getCategories()
    {
        /** @var $categories Fontis_Blog_Model_Mysql4_Cat_Collection */
        $categories = Mage::getModel("blog/cat")->getCollection()
            ->addBlogFilter($this->getBlog())
            ->setOrder("sort_order", "asc");
        return $categories;
    }

    /**
     * @param int $count
     * @return Fontis_Blog_Model_Tag[]|Fontis_Blog_Model_Mysql4_Tag_Collection|null
     */
    public function getTags($count = -1)
    {
        $blog = $this->getBlog();
        if ($blog->getSetting("menu/tags")) {
            $tags = Mage::getModel("blog/tag")->getCollection()
                ->addBlogFilter($blog)
                ->setOrder("name", "asc");
            if (is_numeric($count) && $count > 0) {
                $tags->getSelect()->limit($count);
            }
            return $tags;
        }
        return null;
    }
}
