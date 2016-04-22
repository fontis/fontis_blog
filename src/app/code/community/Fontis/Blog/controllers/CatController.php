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

class Fontis_Blog_CatController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function viewAction()
    {
        $layout = $this->prepareLayout("blog-cat-view");
        $blog = $this->getBlog();
        /** @var $cat Fontis_Blog_Model_Cat */
        $cat = Mage::registry("current_blog_category");

        if ($head = $layout->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */
            $head->setTitle($blog->getTitle() . " - " . $cat->getTitle());
            $head->setKeywords($cat->getMetaKeywords());
            $head->setDescription($cat->getMetaDescription());
            if ($blog->getSetting("rss/enabled")) {
                $head->addItem("rss", $cat->getCatUrl() . "/rss");
            }
        }

        $this->renderLayout();
    }

    /**
     * @param bool $linkifyBlogCrumb
     * @return array
     */
    protected function getBreadcrumbs($linkifyBlogCrumb = true)
    {
        $breadcrumbs = parent::getBreadcrumbs(true);
        $cat = Mage::registry("current_blog_category");
        $breadcrumbs["cat"] = array(
            "label" => $cat->getTitle(),
            "title" => $cat->getTitle(),
        );
        return $breadcrumbs;
    }
}
