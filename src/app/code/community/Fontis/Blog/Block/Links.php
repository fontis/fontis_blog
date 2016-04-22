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

class Fontis_Blog_Block_Links extends Mage_Core_Block_Abstract
{
    /**
     * @param int $position
     * @param string $liParams
     * @param string $aParams
     * @param string $beforeText
     * @param string $afterText
     */
    public function addTopLink($position = 15, $liParams = null, $aParams = 'class="top-link-blog"', $beforeText = "", $afterText = "")
    {
        if ($parentBlock = $this->getParentBlock()) {
            /** @var $parentBlock Mage_Page_Block_Template_Links */

            $blogCollection = $this->getBlogCollection();
            foreach ($blogCollection as $blog) {
                if ($blog->getSettingFlag("menu/top")) {
                    $label = $blog->getLinkLabel();
                    $parentBlock->addLink($label, $blog->getBlogUrl(), $label, false, array(), $position, $liParams, $aParams, $beforeText, $afterText);
                }
            }
        }
    }

    /**
     * @param int $position
     * @param string $liParams
     * @param string $aParams
     * @param string $beforeText
     * @param string $afterText
     */
    public function addFooterLink($position = 50, $liParams = null, $aParams = null, $beforeText = "", $afterText = "")
    {
        if ($parentBlock = $this->getParentBlock()) {
            /** @var $parentBlock Mage_Page_Block_Template_Links */

            $blogCollection = $this->getBlogCollection();
            foreach ($blogCollection as $blog) {
                if ($blog->getSettingFlag("menu/footer")) {
                    $label = $blog->getLinkLabel();
                    $parentBlock->addLink($label, $blog->getBlogUrl(), $label, false, array(), $position, $liParams, $aParams, $beforeText, $afterText);
                }
            }
        }
    }

    /**
     * @param int $scope
     */
    public function addRssFeed($scope = Fontis_Blog_Model_System_Scope::SCOPE_SITE)
    {
        if ($head = $this->getLayout()->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */

            $blogCollection = $this->getBlogCollection();
            $curBlog = Mage::registry("current_blog_object");
            foreach ($blogCollection as $blog) {
                $blogScope = $blog->getSetting("rss/visible");
                if ($blogScope >= $scope) {
                    if ($blogScope < Fontis_Blog_Model_System_Scope::SCOPE_SITE && isset($curBlog) && $curBlog->getId() != $blog->getId()) {
                        continue;
                    }
                    $title = 'title="' . $blog->getRssFeedTitle() . '"';
                    $head->addItem("rss", $blog->getBlogUrl("rss"), $title);
                }
            }
        }
    }

    /**
     * @param int|null $storeId
     * @return Fontis_Blog_Model_Blog[]|Fontis_Blog_Model_Mysql4_Blog_Collection
     */
    protected function getBlogCollection($storeId = null)
    {
        if ($storeId === null) {
            $storeId = Mage::app()->getStore()->getId();
        }
        /** @var $blogCollection Fontis_Blog_Model_Mysql4_Blog_Collection */
        $blogCollection = Mage::getModel("blog/blog")->getCollection();
        $blogCollection->addStoreFilter($storeId)
            ->addEnableFilter();

        return $blogCollection;
    }
}
