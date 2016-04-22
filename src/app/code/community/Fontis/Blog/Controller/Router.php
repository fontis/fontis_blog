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

class Fontis_Blog_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * @listen controller_front_init_routers
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters(Varien_Event_Observer $observer)
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return;
        }
        /** @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();
        $front->addRouter("blog", $this);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl("install"))
                ->sendResponse();
            exit;
        }

        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");
        $storeId = Mage::app()->getStore()->getId();
        $pathInfo = trim($request->getPathInfo(), "/");
        $pathInfoParts = explode("/", $pathInfo);
        if (!isset($pathInfoParts[0])) {
            // If the request URI consisted of nothing but a forward-slash, it's actually the homepage.
            return false;
        }

        // Attempt to load a blog that's in the current store and whose route matches the first segment
        // of the request URI.
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->setStoreId($storeId);
        $partCount = $blog->loadByPathInfo($pathInfoParts);
        if (!$blog->getId()) {
            return false;
        }
        if ($blog->getStatus() == Fontis_Blog_Model_System_BlogStatus::DISABLED) {
            return false;
        }
        Mage::register("current_blog_object", $blog);
        $request->setParam("fontis_frontname", $blog->getRoute());
        $blogRoutePrefix = array_splice($pathInfoParts, 0, $partCount);
        array_pop($blogRoutePrefix);
        $request->setParam("blog_route_prefix", $blogRoutePrefix);

        if (!isset($pathInfoParts[0])) {
            // If there is only one segment in the request URI, it's the main blog index page.
            $request->setModuleName("blog")
                ->setControllerName("home")
                ->setActionName("index");
            return true;
        }

        // Currently this isn't configurable on a per-blog basis.
        $routeArchive = $helper->getBlogArchiveRoute();

        switch ($pathInfoParts[0])
        {
            case "page":
                // This is a specific page of the main blog index page.
                if (!isset($pathInfoParts[1])) {
                    // This means no page number was specified.
                    return false;
                }
                if (isset($pathInfoParts[2])) {
                    // This means the URL has too many segments for this to be a valid path.
                    // You can't just tack whatever you want on the end of the URL and expect it to work.
                    return false;
                }
                if (($pageNumber = $this->verifyPageNumber($pathInfoParts[1])) === null) {
                    // This means the specified page number was not a valid page number.
                    return false;
                }
                $request->setModuleName("blog")
                    ->setControllerName("home")
                    ->setActionName("index")
                    ->setParam("page", $pageNumber);
                return true;
            case "rss":
                // This is the blog's main RSS feed.
                if (isset($pathInfoParts[1])) {
                    // This means the URL has too many segments for this to be a valid path.
                    // You can't just tack whatever you want on the end of the URL and expect it to work.
                    return false;
                }
                if (!$blog->getSetting("rss/enabled")) {
                    return false;
                }
                $request->setModuleName("blog")
                    ->setControllerName("rss")
                    ->setActionName("feed");
                return true;
            case $blog->getCatRoute():
                if (!isset($pathInfoParts[1])) {
                    // This means no category identifier was specified.
                    return false;
                }
                $identifier = $pathInfoParts[1];

                // Check for a page number, or RSS feed.
                if (isset($pathInfoParts[2])) {
                    if ($pathInfoParts[2] === "rss") {
                        if (isset($pathInfoParts[3])) {
                            // This means the URL has too many segments for this to be a valid path.
                            // You can't just tack whatever you want on the end of the URL and expect it to work.
                            return false;
                        } else {
                            // This is an RSS feed for a specific category.
                            $rssFeed = true;
                        }
                    } elseif ($pathInfoParts[2] === "page") {
                        if (!isset($pathInfoParts[3])) {
                            // This means no page number was specified.
                            return false;
                        }
                        if (isset($pathInfoParts[4])) {
                            // This means the URL has too many segments for this to be a valid path.
                            // You can't just tack whatever you want on the end of the URL and expect it to work.
                            return false;
                        }
                        if (($pageNumber = $this->verifyPageNumber($pathInfoParts[3])) === null) {
                            // This means the specified page number was not a valid page number.
                            return false;
                        }
                    } else {
                        // This means the URL has too many invalid segments for this to be a valid path.
                        // You can't just tack whatever you want on the end of the URL and expect it to work.
                        return false;
                    }
                }

                // Check to see if the specified category exists in the current blog.
                /** @var $cat Fontis_Blog_Model_Cat */
                $cat = Mage::getModel("blog/cat")->setBlogId($blog->getId());
                $cat->load($identifier, "identifier");
                if (!$cat->getId()) {
                    // No category with the specified identifier exists for the current blog.
                    return false;
                }
                Mage::register("current_blog_category", $cat);

                $request->setModuleName("blog");
                if (isset($rssFeed)) {
                    $request->setControllerName("rss")
                        ->setActionName("feed");
                } else {
                    $request->setControllerName("cat")
                        ->setActionName("view")
                        ->setParam("identifier", $identifier);
                    if (isset($pageNumber)) {
                        $request->setParam("page", $pageNumber);
                    }
                }
                return true;
            case $blog->getTagRoute():
                if (!isset($pathInfoParts[1])) {
                    $request->setModuleName("blog")
                        ->setControllerName("tag")
                        ->setActionName("list");
                    return true;
                }

                if (isset($pathInfoParts[2])) {
                    // This means the URL has too many segments for this to be a valid path.
                    // You can't just tack whatever you want on the end of the URL and expect it to work.
                    return false;
                }

                $identifier = $pathInfoParts[1];

                // Check to see if the specified tag exists in the current blog.
                /** @var $tag Fontis_Blog_Model_Tag */
                $tag = Mage::getModel("blog/tag")->setBlogId($blog->getId());
                $tag->load($identifier, "identifier");
                if (!$tag->getId()) {
                    // No tag with the specified identifier exists for the current blog.
                    return false;
                }
                Mage::register("current_blog_tag", $tag);

                $request->setModuleName("blog")
                    ->setControllerName("tag")
                    ->setActionName("view")
                    ->setParam("identifier", $identifier);
                return true;
            case $routeArchive:
                if (!$blog->getSettingFlag("archives/enabled")) {
                    return false;
                }
                if (!isset($pathInfoParts[1])) {
                    $request->setModuleName("blog")
                        ->setControllerName("archive")
                        ->setActionName("list");
                    return true;
                }

                $dateParts = array_slice($pathInfoParts, 1);
                if (count($dateParts) > 3) {
                    // This means the URL has too many segments for this to be a valid path.
                    // You can't just tack whatever you want on the end of the URL and expect it to work.
                    return false;
                }
                if ($helper->verifyArchiveDateSegments($dateParts) === false) {
                    return false;
                }

                Mage::register("current_blog_archive_date", $dateParts);

                $request->setModuleName("blog")
                    ->setControllerName("archive")
                    ->setActionName("view");
                return true;
            case $blog->getAuthorRoute():
                if (!$blog->getSettingFlag("authors/enabled")) {
                    return false;
                }
                if (!isset($pathInfoParts[1])) {
                    $request->setModuleName("blog")
                        ->setControllerName("author")
                        ->setActionName("list");
                    return true;
                }

                if (isset($pathInfoParts[2])) {
                    // This means the URL has too many segments for this to be a valid path.
                    // You can't just tack whatever you want on the end of the URL and expect it to work.
                    return false;
                }

                $identifier = $pathInfoParts[1];

                // Check to see if the specified author exists.
                /** @var $author Fontis_Blog_Model_Author */
                $author = Mage::getModel("blog/author");
                $author->load($identifier, "identifier");
                if (!$author->getId()) {
                    // No author with the specified identifier exists.
                    return false;
                }
                if ($author->getPostCount(true, $blog) === 0) {
                    // This author has no posts in the current blog.
                    return false;
                }
                Mage::register("current_blog_author", $author);

                $request->setModuleName("blog")
                    ->setControllerName("author")
                    ->setActionName("view")
                    ->setParam("identifier", $identifier);
                return true;
            default:
                // If we reach this point, it's either a valid blog post, or a 404. Let's find out.
                if (isset($pathInfoParts[1])) {
                    if (isset($pathInfoParts[2]) || $pathInfoParts[1] !== "comment") {
                        // This means the URL has too many segments for this to be a valid path.
                        // You can't just tack whatever you want on the end of the URL and expect it to work.
                        return false;
                    } else {
                        $submitComment = true;
                    }
                }
                $identifier = $pathInfoParts[0];

                // Check to see if the specified post exists in the current blog.
                /** @var $post Fontis_Blog_Model_Post */
                $post = Mage::getModel("blog/post")->setBlogId($blog->getId());
                $post->load($identifier, "identifier");
                if (!$post->getId()) {
                    // No post with the specified identifier exists for the current blog.
                    return false;
                }
                if ($post->getStatus() == Fontis_Blog_Model_Status::STATUS_DISABLED) {
                    // The post should not be visible on the frontend at all.
                    return false;
                }
                if (isset($submitComment) && $blog->getSettingFlag("comments/enabled") === false) {
                    return false;
                }
                if (isset($submitComment) && $post->canPostNewComments() === false) {
                    if ($post->getCommentsDisabled()) {
                        return false;
                    }
                    // Deliberately don't check to see whether or not the user has to be logged in to post here so that
                    // we can provide an error message to the user in the controller.
                }

                Mage::register("current_blog_post", $post);

                $request->setModuleName("blog")
                    ->setControllerName("post");
                if (isset($submitComment)) {
                    $request->setActionName("submitComment");
                } else {
                    $request->setActionName("view")
                        ->setParam("identifier", $identifier);
                }
                return true;
        }

        // Just in case catch-all
        return false;
    }

    /**
     * @param string $page
     * @return int|null
     */
    protected function verifyPageNumber($page)
    {
        $pageNumber = Mage::helper("blog")->verifyNumericRouteSegment($page);
        if ($pageNumber === null || $pageNumber < 1) {
            // You can't visit a page that is before the first page. That doesn't make sense.
            return null;
        }
        return $pageNumber;
    }
}
