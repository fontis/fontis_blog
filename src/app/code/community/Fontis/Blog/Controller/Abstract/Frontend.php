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

abstract class Fontis_Blog_Controller_Abstract_Frontend extends Mage_Core_Controller_Front_Action
{
    const FLAG_NO_DISPATCH_PREPARE_LAYOUT_EVENT = 'no-prepareLayoutEvent';

    /**
     * @param array|string $layoutHandle
     * @return Mage_Core_Model_Layout
     */
    protected function prepareLayout($layoutHandle)
    {
        $blog = $this->getBlog();
        $layout = $this->getLayout();

        if (($customTheme = $blog->getSetting("blog/custom_theme")) && (strpos($customTheme, "/") !== false)) {
            list($package, $theme) = explode("/", $customTheme);
            Mage::getSingleton("core/design_package")
                ->setPackageName($package)
                ->setTheme($theme);
        }

        $defaultLayoutHandles = array("default");
        if ($blog->getSettingFlag("advanced/add_default_layout_handles")) {
            if (is_array($layoutHandle)) {
                $defaultLayoutHandles = array_merge($defaultLayoutHandles, $layoutHandle);
            } else {
                $defaultLayoutHandles[] = $layoutHandle;
            }
        }
        $this->loadLayout($defaultLayoutHandles);

        if ($rootBlock = $layout->getBlock("root")) {
            /** @var $rootBlock Mage_Page_Block_Html */
            $rootBlock->setTemplate($blog->getSetting("blog/layout"));
            $rootBlock->addBodyClass("blog-page-" . $blog->getId());
        }

        if (($blog->getSettingFlag("blog/blogcrumbs") === true) && ($breadcrumbsBlock = $layout->getBlock("breadcrumbs"))) {
            /** @var $breadcrumbsBlock Mage_Page_Block_Html_Breadcrumbs */
            foreach ($this->getBreadcrumbs() as $key => $crumb) {
                $breadcrumbsBlock->addCrumb($key, $crumb);
            }
        }

        if (!$this->getFlag('', self::FLAG_NO_DISPATCH_PREPARE_LAYOUT_EVENT)) {
            Mage::dispatchEvent(
                'fontis_blog_after_prepare_layout',
                array('controller' => $this, 'blog' => $blog)
            );

            Mage::dispatchEvent(
                'fontis_blog_after_prepare_layout_' . $this->getFullActionName(),
                array('controller' => $this, 'blog' => $blog)
            );
        }

        return $layout;
    }

    /**
     * @param bool $linkifyBlogCrumb Whether or not to make the main "blog" breadcrumb a link.
     * @return array
     */
    protected function getBreadcrumbs($linkifyBlogCrumb = false)
    {
        $blog = $this->getBlog();
        $blogTitle = $blog->getTitle();
        $breadcrumbs = array();
        $breadcrumbs["home"] = array(
            "label" => Mage::helper("cms")->__("Home"),
            "title" => Mage::helper("cms")->__("Go to Home Page"),
            "link"  => Mage::getBaseUrl(),
        );

        $blogRoutePrefix = $this->getRequest()->getUserParam("blog_route_prefix");
        if (isset($blogRoutePrefix[0])) {
            $pageTitle = $this->checkForParentCmsPage($blog->getStoreId(), $blogRoutePrefix[0]);

            if ($pageTitle) {
                $breadcrumbs["cms_page"] = array(
                    "label" => $pageTitle,
                    "title" => $pageTitle,
                    "link"  => Mage::getUrl("", array("_direct" => $blogRoutePrefix[0])),
                );
            }
        }

        $breadcrumbs["blog"] = array(
            "label" => $blogTitle,
            "title" => $blogTitle,
            "link"  => ($linkifyBlogCrumb === true ? $this->getBlog()->getBlogUrl() : null),
        );
        return $breadcrumbs;
    }

    /**
     * Check to see if this blog is being used as a "sub-blog" of a CMS page. If so, it is
     * inserted as a breadcrumb before the blog link. This check is only performed if there
     * is more than one segment to the blog's URL key. The check is performed by taking the
     * first segment in the path and checking to see if it exists as an identifier for a
     * CMS page in the current store.
     * Currently, this check does not work with any sort of CMS hierarchy. It will only ever
     * look for the top-level CMS page, if there is a hierarchy.
     *
     * @param int $storeId
     * @param string $identifier
     * @return string|null
     */
    protected function checkForParentCmsPage($storeId, $identifier)
    {
        /** @var $cmsPageResource Mage_Cms_Model_Mysql4_Page */
        $cmsPageResource = Mage::getResourceSingleton("cms/page")->setStore($storeId);
        $pageTitle = $cmsPageResource->getCmsPageTitleByIdentifier($identifier);
        if ($pageTitle) {
            return $pageTitle;
        } else {
            return null;
        }
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    protected function getBlog()
    {
        return Mage::registry("current_blog_object");
    }
}
