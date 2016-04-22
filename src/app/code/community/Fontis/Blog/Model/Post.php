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
 * TODO: Post Image and Small Image now have enough behaviour scattered around the extension that there should be an Image model.
 *
 * @method int getPostId()
 * @method Fontis_Blog_Model_Post setBlogId(int $blogId)
 * @method int getBlogId()
 * @method Fontis_Blog_Model_Post setBlog(Fontis_Blog_Model_Blog $blog)
 * @method Fontis_Blog_Model_Post setTitle(string $value)
 * @method string getTitle()
 * @method string getIdentifier()
 * @method Fontis_Blog_Model_Post setUpdateUser(string $value)
 * @method string getUpdateUser()
 * @method Fontis_Blog_Model_Post setStatus(int $status)
 * @method int getStatus()
 * @method string getMetaKeywords()
 * @method string getMetaDescription()
 * @method Fontis_Blog_Model_Post setCreatedTime(string $value)
 * @method Fontis_Blog_Model_Post setUpdateTime(string $value)
 * @method Fontis_Blog_Model_Post setCatIds(array $catIds)
 * @method array getCatIds()
 * @method Fontis_Blog_Model_Post setCategories(Fontis_Blog_Model_Mysql4_Cat_Collection $categories)
 * @method Fontis_Blog_Model_Cat[]|Fontis_Blog_Model_Mysql4_Cat_Collection getCategories()
 * @method Fontis_Blog_Model_Post setTagIds(array $tagIds)
 * @method array getTagIds()
 * @method Fontis_Blog_Model_Post setTags(Fontis_Blog_Model_Mysql4_Tag_Collection $tags)
 * @method Fontis_Blog_Model_Tag[]|Fontis_Blog_Model_Mysql4_Tag_Collection getTags()
 * @method int getAuthorId()
 * @method Fontis_Blog_Model_Post setAuthor(Fontis_Blog_Model_Author $author)
 * @method Fontis_Blog_Model_Author getAuthor()
 */
class Fontis_Blog_Model_Post extends Mage_Core_Model_Abstract
{
    const CACHE_TAG = "blog_post";

    const POST_IMAGE_FIELDNAME = "image";
    const POST_SMALLIMAGE_FIELDNAME = "small_image";
    const POST_IMAGE_BASEPATH = "fontis_blog/posts/";

    /**
     * @var string
     */
    protected $_idFieldName = "post_id";

    /**
     * @var string
     */
    protected $_eventPrefix = "blog_post";

    /**
     * @var string
     */
    protected $_eventObject = "blog_post";

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var bool
     */
    protected $_canPostNewComments = null;

    /**
     * @var Fontis_Blog_Model_Comment[]|Fontis_Blog_Model_Mysql4_Comment_Collection
     */
    protected $comments = null;

    protected function _construct()
    {
        $this->_init("blog/post");
    }

    /**
     * @return Fontis_Blog_Model_Blog
     */
    public function getBlog()
    {
        if (!$this->hasData("blog")) {
            if (!$blog = Mage::registry("current_blog_object")) {
                $blog = Mage::getModel("blog/blog")->load($this->getBlogId());
            }
            if ($storeId = $this->getStoreId()) {
                $blog->setStoreId($storeId);
            }
            $this->setData("blog", $blog);
        }
        return $this->getData("blog");
    }

    /**
     * @return string
     */
    public function getPostUrl()
    {
        if ($identifier = $this->getIdentifier()) {
            return $this->getBlog()->getBlogUrl($identifier);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getPostUrlPath()
    {
        if ($identifier = $this->getIdentifier()) {
            return $this->getBlog()->getRoute() . "/" . $identifier;
        }
        return "";
    }

    /**
     * Returns the time at which the post was created, formatted according to
     * the blog's date format setting.
     *
     * @param bool $includeTime
     * @return string
     */
    public function getCreatedTime($includeTime = true)
    {
        return Mage::helper("core")->formatDate($this->getData("created_time"), $this->getBlog()->getSetting("blog/dateformat"), $includeTime);
    }

    /**
     * Returns the time at which the post was last updated, formatted according
     * to the blog's date format setting.
     *
     * @param bool $includeTime
     * @return string
     */
    public function getUpdateTime($includeTime = true)
    {
        return Mage::helper("core")->formatDate($this->getData("update_time"), $this->getBlog()->getSetting("blog/dateformat"), $includeTime);
    }

    public function getCommentsDisabled()
    {
        return $this->getData("comments");
    }

    /**
     * @return bool
     */
    public function canPostNewComments()
    {
        if ($this->_canPostNewComments !== null) {
            return $this->_canPostNewComments;
        }
        if ($this->getCommentsDisabled()) {
            $this->_canPostNewComments = false;
            return false;
        }
        if ($this->getBlog()->getSettingFlag("comments/login")) {
            if (!Mage::helper("customer")->isLoggedIn()) {
                $this->_canPostNewComments = false;
                return false;
            }
        }
        $this->_canPostNewComments = true;
        return true;
    }

    /**
     * @return Fontis_Blog_Model_Comment[]|Fontis_Blog_Model_Mysql4_Comment_Collection
     */
    public function getComments()
    {
        if ($this->comments === null) {
            $this->comments = Mage::getModel("blog/comment")->getCollection()
                ->addPostFilter($this->getId())
                ->setOrder("created_time", "asc")
                ->addApproveFilter();
        }
        return $this->comments;
    }

    /**
     * @return int
     */
    public function getCommentCount()
    {
        return $this->getComments()->getSize();
    }

    /**
     * This algorithm relies on the fact that comments are in the database
     * in numerical order. Don't go mucking with them!
     *
     * @return array
     */
    public function getThreadedComments()
    {
        $thread = array();
        foreach ($this->getComments() as $key => $comment) {
            /** @var $comment Fontis_Blog_Model_Comment */
            if ($repliedTo = $comment->getInReplyTo()) {
                $repliedToArray = &$this->array_search_recursive($repliedTo, $thread);
                $repliedToArray["children"][$key] = array("comment" => $comment, "children" => array());
            } else {
                $thread[$key] = array("comment" => $comment, "children" => array());
            }
        }
        return $thread;
    }

    /**
     * @param int $keyneedle
     * @param array $haystack
     * @return null
     */
    protected function &array_search_recursive($keyneedle, &$haystack)
    {
        $poster = null;
        foreach ($haystack as $key => &$value) {
            if ($key == $keyneedle) {
                return $value;
            } else if (is_array($value["children"])) {
                $poster = &$this->array_search_recursive($keyneedle, $value["children"]);
                if (isset($poster)) {
                    break;
                }
            }
        }
        return $poster;
    }

    /**
     * @return string|null
     */
    public function getImageBasePath()
    {
        if ($createdTime = $this->getData("created_time")) {
            return self::POST_IMAGE_BASEPATH . date("Ym", strtotime($createdTime));
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        if ($imageFile = $this->getData(self::POST_IMAGE_FIELDNAME)) {
            return $this->_imageUrl($imageFile);
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getSmallImageUrl()
    {
        if ($imageFile = $this->getData(self::POST_SMALLIMAGE_FIELDNAME)) {
            return $this->_imageUrl($imageFile);
        } else {
            return null;
        }
    }

    /**
     * @param string $imageFile
     * @return string|null
     */
    protected function _imageUrl($imageFile)
    {
        if ($imageBasePath = $this->getImageBasePath()) {
            return Mage::getBaseUrl("media") . $imageBasePath . "/" . $imageFile;
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getImageFile()
    {
        if ($imageFile = $this->getData(self::POST_IMAGE_FIELDNAME)) {
            return $this->_imageFile($imageFile);
        } else {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getSmallImageFile()
    {
        if ($imageFile = $this->getData(self::POST_SMALLIMAGE_FIELDNAME)) {
            return $this->_imageFile($imageFile);
        } else {
            return null;
        }
    }

    /**
     * @param string $imageFile
     * @return string|null
     */
    protected function _imageFile($imageFile)
    {
        if ($imageBasePath = $this->getImageBasePath()) {
            return Mage::getBaseDir("media") . "/" . $imageBasePath . "/" . $imageFile;
        } else {
            return null;
        }
    }

    /**
     * @param bool $filtered
     * @param bool $includeReadMoreLink
     * @return string
     */
    public function getPostSummary($filtered = true, $includeReadMoreLink = true)
    {
        // Check if we need to use summary content and make adjustments as necessary
        if ($this->getBlog()->getSetting("lists/usesummary") && ($summaryContent = $this->getData("summary_content"))) {
            if ($includeReadMoreLink === true) {
                $summaryContent .= $this->getReadMoreLink();
            }
        } else {
            $summaryContent = $this->getPostContent($filtered);
            if ($filtered === true) {
                // Don't put the content through the CMS processor twice.
                $filtered = false;
            }
            if (($readMore = (int) $this->getBlog()->getSetting("lists/readmore")) && strlen($summaryContent) >= $readMore) {
                $summaryContent = substr($summaryContent, 0, $readMore);
                $summaryContent = substr($summaryContent, 0, strrpos($summaryContent, ". ") + 1);
                $summaryContent = Mage::helper("blog")->closeTags($summaryContent);
                if ($includeReadMoreLink === true) {
                    $summaryContent .= $this->getReadMoreLink();
                }
            }
        }
        if ($filtered === true) {
            $summaryContent = Mage::helper("cms")->getPageTemplateProcessor()->filter($summaryContent);
        }

        return $summaryContent;
    }

    /**
     * @return string
     */
    public function getReadMoreLink()
    {
        $readMoreText = Mage::helper("blog")->__("Read More");
        return ' ...&nbsp;&nbsp;<a href="' . $this->getPostUrl() . '">' . $readMoreText . "</a>";
    }

    /**
     * @param bool $filtered
     * @return string
     */
    public function getPostContent($filtered = true)
    {
        $content = $this->getData("post_content");
        if ($filtered === true) {
            return Mage::helper("cms")->getPageTemplateProcessor()->filter($content);
        } else {
            return $content;
        }
    }

    /**
     * @return Fontis_Blog_Model_Post
     */
    protected function _afterLoad()
    {
        /** @var $cats Fontis_Blog_Model_Mysql4_Cat_Collection */
        $cats = Mage::getModel("blog/cat")->getCollection();
        $cats->addPostFilter($this->getPostId());
        $this->setCatIds($cats->getAllIds());
        $this->setCategories($cats);

        /** @var $tags Fontis_Blog_Model_Mysql4_Tag_Collection */
        $tags = Mage::getModel("blog/tag")->getCollection();
        $tags->addPostFilter($this->getPostId());
        $this->setTagIds($tags->getAllIds());
        $this->setTags($tags);

        /** @var $author Fontis_Blog_Model_Author */
        $author = Mage::getModel("blog/author")->load($this->getAuthorId());
        $this->setAuthor($author);

        return parent::_afterLoad();
    }

    /**
     * @return Fontis_Blog_Model_Post
     */
    protected function _beforeDelete()
    {
        $this->_protectFromNonAdmin();
        return parent::_beforeDelete();
    }
}
