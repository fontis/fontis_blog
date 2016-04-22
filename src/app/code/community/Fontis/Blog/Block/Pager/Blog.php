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

class Fontis_Blog_Block_Pager_Blog extends Mage_Core_Block_Template
{
    /**
     * @var bool
     */
    protected $shouldRender = true;

    /**
     * @var int
     */
    protected $currentPage = null;

    /**
     * @var int
     */
    protected $totalPages = null;

    /**
     * @var string
     */
    protected $route = null;

    /**
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        if ($perPage = (int) $this->getBlog()->getSetting("lists/perpage")) {
            $this->initialisePageCount($perPage);
            $this->initialiseCurrentPage();
            $this->createHeadPaginationLinks();
        } else {
            $this->shouldRender = false;
        }
        return parent::_prepareLayout();
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        return Mage::registry("current_blog_object");
    }

    /**
     * @param int $perPage
     * @return int
     */
    protected function initialisePageCount($perPage)
    {
        /** @var $collection Fontis_Blog_Model_Mysql4_Post_Collection */
        $collection = Mage::getModel("blog/post")->getCollection()
            ->addBlogFilter($this->getBlog())
            ->setOrder("created_time", "desc");
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($collection);

        $collection = $this->addCustomFilters($collection);

        $collection->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(new Zend_Db_Expr("count(main_table.post_id) as postcount")));
        $collection->load();
        $this->totalPages = ceil($collection->getFirstItem()->getPostcount() / $perPage);
        unset($collection);
        return $this->totalPages;
    }

    /**
     * @return int
     */
    protected function initialiseCurrentPage()
    {
        if (!$this->currentPage) {
            $this->currentPage = (int) $this->getRequest()->getParam("page");
            if (!$this->currentPage) {
                $this->currentPage = 1;
            }
        }
        return $this->currentPage;
    }

    protected function createHeadPaginationLinks()
    {
        /** @var $headBlock Mage_Page_Block_Html_Head */
        $headBlock = $this->getLayout()->getBlock("head");
        if (!$headBlock) {
            return;
        }

        if ($newerPostsUrl = $this->getNewerPostsUrl()) {
            $headBlock->addLinkRel("prev", $newerPostsUrl);
        }
        if ($olderPostsUrl = $this->getOlderPostsUrl()) {
            $headBlock->addLinkRel("next", $olderPostsUrl);
        }
    }

    /**
     * This should be overridden by subclasses to provide extra filtering.
     *
     * @param Fontis_Blog_Model_Mysql4_Post_Collection $collection
     * @return Fontis_Blog_Model_Mysql4_Post_Collection
     */
    protected function addCustomFilters(Fontis_Blog_Model_Mysql4_Post_Collection $collection)
    {
        return $collection;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->shouldRender) {
            return parent::_toHtml();
        } else {
            return "";
        }
    }

    /**
     * @return string
     */
    protected function getRoute()
    {
        if (!$this->route) {
            $this->route = $this->getBlog()->getBlogUrl("page");
        }
        return $this->route;
    }

    /**
     * @return string|null
     */
    protected function getNewerPostsUrl()
    {
        if ($this->currentPage > 1) {
            return $this->getRoute() . "/" . ($this->currentPage - 1);
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    protected function getOlderPostsUrl()
    {
        if ($this->currentPage < $this->totalPages) {
            return $this->getRoute() . "/" . ($this->currentPage + 1);
        } else {
            return null;
        }
    }
}
