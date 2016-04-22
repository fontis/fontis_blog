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

/**
 * @method int getBlogId()
 * @method int getRecentCount()
 * @method Fontis_Blog_Model_Cat|int|int[] getCategoryFilter()
 * @method Fontis_Blog_Model_Tag|int|int[] getTagFilter()
 * @method Fontis_Blog_Model_Author|int|int[]|string getAuthorFilter()
 */
class Fontis_Blog_Block_Recent extends Mage_Core_Block_Template
{
    /**
     * @var Fontis_Blog_Model_Blog
     */
    protected $blog = null;

    /**
     * @return Fontis_Blog_Block_Recent
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!$this->getBlogId()) {
            Mage::throwException(Mage::helper("blog")->__("No blog ID specified for recent blog posts."));
        }
        if (!$this->getTemplate()) {
            $this->setTemplate("fontis/blog/recent.phtml");
        }

        return $this;
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        if ($this->blog === null) {
            $this->blog = Mage::getModel("blog/blog")->setStoreId(Mage::app()->getStore()->getId())->load($this->getBlogId());
        }
        return $this->blog;
    }

    /**
     * @param bool $applyExtraFilters
     * @return Fontis_Blog_Model_Post[]|Fontis_Blog_Model_Mysql4_Post_Collection|null
     */
    public function getRecent($applyExtraFilters = true)
    {
        $blog = $this->getBlog();
        $recentCount = $this->getRecentCount();
        if (!is_numeric($recentCount) || $recentCount < 1) {
            $recentCount = $blog->getSetting("menu/recent");
        }
        if (is_numeric($recentCount)) {
            if ($recentCount == 0) {
                return null;
            } elseif ($recentCount < 1) {
                $recentCount = Fontis_Blog_Block_Menu::RECENT_POSTS_DEFAULT;
            }
        } else {
            $recentCount = Fontis_Blog_Block_Menu::RECENT_POSTS_DEFAULT;
        }

        /** @var $posts Fontis_Blog_Model_Mysql4_Post_Collection */
        $posts = Mage::getModel("blog/post")->getCollection()
            ->addBlogFilter($blog)
            ->setOrder("created_time", "desc");
        Mage::getSingleton("blog/status")->addEnabledFilterToCollection($posts);
        $posts->getSelect()->limit($recentCount);

        if ($applyExtraFilters === true) {
            $this->applyExtraFilters($posts);
        }

        return $posts;
    }

    /**
     * @param Fontis_Blog_Model_Mysql4_Post_Collection $posts
     */
    protected function applyExtraFilters(Fontis_Blog_Model_Mysql4_Post_Collection $posts)
    {
        if ($categoryFilter = $this->getCategoryFilter()) {
            $posts->addCatFilter($this->explodeIntegerCsv($categoryFilter));
        }
        if ($tagFilter = $this->getTagFilter()) {
            $posts->addTagFilter($this->explodeIntegerCsv($tagFilter));
        }
        if ($authorFilter = $this->getAuthorFilter()) {
            $posts->addAuthorFilter($this->explodeIntegerCsv($authorFilter));
        }
    }

    /**
     * @param $potentialCsv
     * @return array|string
     */
    protected function explodeIntegerCsv($potentialCsv)
    {
        if (strpos($potentialCsv, ",") === false) {
            return $potentialCsv;
        } else {
            $ints = explode(",", $potentialCsv);
            return array_map("intval", $ints);
        }
    }
}
