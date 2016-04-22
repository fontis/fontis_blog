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

class Fontis_Blog_Helper_Provider extends Mage_Core_Helper_Abstract
{
    /** Providers for the Fontis_Canonical extension **/

    /**
     * Used for when generating a canonical URL for the main blog list pages.
     * If we're on the first page, and the URL contains the page number, the canonical
     * URL should point to the blog list page with no page number.
     *
     * @return string|null
     */
    public function getBlogUrlPath()
    {
        if (($page = $this->_getRequest()->getParam("page")) && $page == 1) {
            return Mage::registry("current_blog_object")->getRoute();
        } else {
            return null;
        }
    }

    /**
     * Used for when generating a canonical URL for category pages.
     * If we're on the first page, and the URL contains the page number, the canonical
     * URL should point to the category page with no page number.
     *
     * @param Fontis_Blog_Model_Cat $cat
     * @return string|null
     */
    public function getBlogCatUrlPath(Fontis_Blog_Model_Cat $cat)
    {
        if (($page = $this->_getRequest()->getParam("page")) && $page == 1) {
            return $cat->getCatUrlPath();
        } else {
            return null;
        }
    }

    /** Providers for the Fontis_CmsDirectives extension **/

    /**
     * For use with the "blog" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogUrl(array $parameters)
    {
        if (!isset($parameters["id"])) {
            return "";
        }

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($parameters["id"]);
        return $blog->getBlogUrl();
    }

    /**
     * For use with the "blogpost" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogPostUrl(array $parameters)
    {
        /** @var $post Fontis_Blog_Model_Post */
        if (isset($parameters["id"])) {
            $post = Mage::getModel("blog/post")->load($parameters["id"]);
        } elseif (isset($parameters["identifier"])) {
            $post = Mage::getModel("blog/post");
            if (isset($parameters["blog_id"])) {
                // If you have more than one post in your database with the same identifier and you
                // don't specify the blog ID, you're setting yourself up for problems. Always specify
                // the blog ID!
                $post->setBlogId($parameters["blog_id"]);
            }
            $post->load($parameters["identifier"]);
        } else {
            return "";
        }
        return $post->getPostUrl();
    }

    /**
     * For use with the "blogcat" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogCatUrl(array $parameters)
    {
        /** @var $cat Fontis_Blog_Model_Cat */
        if (isset($parameters["id"])) {
            $cat = Mage::getModel("blog/cat")->load($parameters["id"]);
        } elseif (isset($parameters["identifier"])) {
            $cat = Mage::getModel("blog/cat");
            if (isset($parameters["blog_id"])) {
                // If you have more than one category in your database with the same identifier and you
                // don't specify the blog ID, you're setting yourself up for problems. Always specify
                // the blog ID!
                $cat->setBlogId($parameters["blog_id"]);
            }
            $cat->load($parameters["identifier"]);
        } else {
            return "";
        }
        return $cat->getCatUrl();
    }

    /**
     * For use with the "blogtag" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogTagUrl(array $parameters)
    {
        /** @var $tag Fontis_Blog_Model_Tag */
        if (isset($parameters["id"])) {
            $tag = Mage::getModel("blog/tag")->load($parameters["id"]);
        } elseif (isset($parameters["identifier"])) {
            $tag = Mage::getModel("blog/tag");
            if (isset($parameters["blog_id"])) {
                // If you have more than one tag in your database with the same identifier and you
                // don't specify the blog ID, you're setting yourself up for problems. Always specify
                // the blog ID!
                $tag->setBlogId($parameters["blog_id"]);
            }
            $tag->load($parameters["identifier"]);
        } else {
            return "";
        }
        return $tag->getTagUrl();
    }

    /**
     * For use with the "blogarchv" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogArchiveUrl(array $parameters)
    {
        if (!isset($parameters["blog_id"]) || !isset($parameters["year"])) {
            return "";
        }

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($parameters["blog_id"]);
        if (!$blog->getSettingFlag("archives/enabled")) {
            return "";
        }

        $archiveUrlPath = Mage::helper("blog")->getArchiveUrlPath($parameters);
        return $blog->getBlogUrl($archiveUrlPath);
    }

    /**
     * For use with the "blogauthor" CMS directive.
     *
     * @param array $parameters
     * @return string
     */
    public function getBlogAuthorUrl(array $parameters)
    {
        if (!isset($parameters["blog_id"])) {
            return "";
        }

        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::getModel("blog/blog")->load($parameters["blog_id"]);
        if (!$blog->getSettingFlag("authors/enabled")) {
            return "";
        }

        /** @var $author Fontis_Blog_Model_Author */
        if (isset($parameters["id"])) {
            $author = Mage::getModel("blog/author")->load($parameters["id"]);
        } elseif (isset($parameters["identifier"])) {
            $author = Mage::getModel("blog/author")->load($parameters["identifier"]);
        }
        $author->setBlog($blog);

        return $author->getAuthorUrl();
    }
}
