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

class Fontis_Blog_TagController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function listAction()
    {
        $pageTitle = $this->getBlog()->getTagLabel();
        $this->renderPage($pageTitle, "blog-tag-list");
    }

    public function viewAction()
    {
        $pageTitle = $this->getBlog()->getTagLabel() . " - " . Mage::registry("current_blog_tag")->getName();
        $this->renderPage($pageTitle, "blog-tag-view");
    }

    /**
     * @param string $pageTitle
     * @param array|string $layoutHandle
     * @return Fontis_Blog_TagController
     */
    protected function renderPage($pageTitle, $layoutHandle)
    {
        $layout = $this->prepareLayout($layoutHandle);

        if ($head = $layout->getBlock("head")) {
            /** @var $head Mage_Page_Block_Html_Head */
            $head->setTitle($this->getBlog()->getTitle() . " - " . $pageTitle);
        }
        return $this->renderLayout();
    }

    /**
     * @param bool $linkifyBlogCrumb
     * @return array
     */
    protected function getBreadcrumbs($linkifyBlogCrumb = true)
    {
        $breadcrumbs = parent::getBreadcrumbs(true);
        $blog = $this->getBlog();

        /** @var $tag Fontis_Blog_Model_Tag */
        if ($tag = Mage::registry("current_blog_tag")) {
            $tagLink = $blog->getTagIndexUrl();
        } else {
            $tagLink = null;
        }

        $breadcrumbs["tag"] = array(
            "label" => $blog->getTagLabel(),
            "title" => $blog->getTagLabel(),
            "link"  => $tagLink,
        );

        if ($tag) {
            $breadcrumbs["tagname"] = array(
                "label" => $tag->getName(),
                "title" => $tag->getName(),
            );
        }

        return $breadcrumbs;
    }
}
