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

class Fontis_Blog_AuthorController extends Fontis_Blog_Controller_Abstract_Frontend
{
    public function listAction()
    {
        $pageTitle = $this->getBlog()->getAuthorLabel();
        $this->renderPage($pageTitle, "blog-author-list");
    }

    public function viewAction()
    {
        $pageTitle = $this->getBlog()->getAuthorLabel() . " - " . Mage::registry("current_blog_author")->getName();
        $this->renderPage($pageTitle, "blog-author-view");
    }

    /**
     * @param string $pageTitle
     * @param array|string $layoutHandle
     * @return Fontis_Blog_AuthorController
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

        /** @var $author Fontis_Blog_Model_Author */
        if ($author = Mage::registry("current_blog_author")) {
            $authorLink = $blog->getAuthorIndexUrl();
        } else {
            $authorLink = null;
        }

        $breadcrumbs["author"] = array(
            "label" => $blog->getAuthorLabel(),
            "title" => $blog->getAuthorLabel(),
            "link"  => $authorLink,
        );

        if ($author) {
            $breadcrumbs["authorname"] = array(
                "label" => $author->getName(),
                "title" => $author->getName(),
            );
        }

        return $breadcrumbs;
    }
}
