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

class Fontis_Blog_Migrate
{
    /**
     * @var Mage_Core_Model_Resource_Setup
     */
    protected $installer;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    // Each entry in this array is an array of post IDs, indexed by store ID.
    protected $postIds = array();

    // Each entry in this array is an array of store IDs, indexed by blog ID. For each of the stores in one entry,
    // just one blog will be created.
    // We define a 0 index here to ensure no actual blogs are created with a 0 index. It's unset once the list of blogs
    // to be created has been collated.
    protected $blogs = array(0 => array());

    protected $newCats = array();

    // Keeps track of duplicated tags.
    protected $newTags = array();

    // Keeps track of duplicated posts.
    protected $newPosts = array();

    protected $settingsToCopy = array("blog/header_image", "blog/layout", "blog/dateformat", "blog/blogcrumbs",
        "blog/wysiwyg_post", "blog/wysiwyg_summary", "routing/cat", "menu/left", "menu/right", "menu/recent",
        "menu/tags", "menu/top", "menu/footer", "lists/readmore", "lists/usesummary", "lists/perpage", "comments/loginauto",
        "posts/title_format", "comments/enabled", "comments/login", "comments/approval", "comments/recipient_email",
        "comments/sender_email_identity", "comments/email_template", "comments/recaptcha", "comments/grav_enabled",
        "comments/grav_size", "comments/grav_default", "archives/enabled", "archives/type", "archives/limit",
        "archives/order", "archives/showcount", "rss/enabled", "rss/title", "rss/image", "rss/posts", "rss/usesummary",
    );


    /**
     * @param Mage_Core_Model_Resource_Setup $installer
     */
    public function __construct($installer)
    {
        $this->installer = $installer;
        $this->connection = $installer->getConnection();
    }

    public function run()
    {
        $this->installer->startSetup();
        $this->prepareBlogs();
        $this->createBlogs();
        $this->setBlogsOnCats();
        $this->setBlogsOnTags();
        $this->setBlogsOnPosts();
        $this->installer->endSetup();
    }

    /**
     * Builds a list of blogs to be created, and the stores that that blog should be assigned to.
     * Also builds a list of posts and the stores that post is assigned to.
     */
    protected function prepareBlogs()
    {
        $postStoreTable = $this->installer->getTable("blog/legacy_post_store");

        $getStoreIdsQuery = "SELECT DISTINCT `store_id` FROM $postStoreTable WHERE `store_id` != 0 ORDER BY `store_id` ASC";
        $stores = $this->connection->fetchAll($getStoreIdsQuery);
        foreach ($stores as $store) {
            $storeId = (int) $store["store_id"];
            $getPostIdsQuery = "SELECT `post_id` FROM $postStoreTable WHERE `store_id` = $storeId ORDER BY `post_id` ASC";
            $this->postIds[$storeId] = $this->connection->fetchCol($getPostIdsQuery);
            $assignedToBlog = false;
            foreach ($this->blogs as $blogId => $blogStores) {
                foreach ($blogStores as $blogStoreId) {
                    if ($this->postIds[$storeId] == $this->postIds[$blogStoreId]) {
                        if ($this->checkImportantBlogDetails($storeId, $blogStoreId)) {
                            $this->blogs[$blogId][] = $storeId;
                            $assignedToBlog = true;
                            break 2;
                        }
                    }
                }
            }
            if ($assignedToBlog == false) {
                $this->blogs[] = array($storeId);
            }
        }
        unset($this->blogs[0]);
    }

    protected function createBlogs()
    {
        $blogTable = $this->installer->getTable("blog/blog");
        $insertBlogBaseQuery = "INSERT INTO $blogTable (`blog_id`, `title`, `route`) VALUES ";
        $blogStoreTable = $this->installer->getTable("blog/store");
        $insertBlogStoreBaseQuery = "INSERT INTO $blogStoreTable (`blog_id`, `store_id`) VALUES ";

        foreach ($this->blogs as $blogId => $stores) {
            $blogTitle = Mage::getStoreConfig("fontis_blog/blog/title", $stores[0]);
            $blogTitle = $this->connection->quote($blogTitle);
            $blogRoute = Mage::getStoreConfig("fontis_blog/routing/blog", $stores[0]);
            $blogRoute = $this->connection->quote($blogRoute);
            $insertBlogQuery = $insertBlogBaseQuery . "($blogId, $blogTitle, $blogRoute)";
            $this->connection->query($insertBlogQuery);

            foreach ($stores as $storeId) {
                $insertBlogStoreQuery = $insertBlogStoreBaseQuery . "($blogId, $storeId)";
                $this->connection->query($insertBlogStoreQuery);
            }
        }

        $this->migrateBlogConfig();
    }

    protected function migrateBlogConfig()
    {
        $blogConfigTable = $this->installer->getTable("blog/config");
        $insertBlogConfigBaseQuery = "INSERT INTO $blogConfigTable (`blog_id`, `key`, `value`) VALUES ";
        foreach ($this->blogs as $blogId => $stores) {
            foreach ($this->settingsToCopy as $setting) {
                $configValue = Mage::getStoreConfig("fontis_blog/$setting", $stores[0]);
                $configValue = $this->connection->quote($configValue);
                $insertBlogConfigQuery = $insertBlogConfigBaseQuery . "($blogId, '$setting', $configValue)";
                $this->connection->query($insertBlogConfigQuery);
            }
            // Handle sitemap settings specially
            foreach (array("changefreq_post", "priority_post") as $setting) {
                $configValue = Mage::getStoreConfig("sitemap/blog/$setting", $stores[0]);
                $configValue = $this->connection->quote($configValue);
                $insertBlogConfigQuery = $insertBlogConfigBaseQuery . "($blogId, 'sitemap/$setting', $configValue)";
                $this->connection->query($insertBlogConfigQuery);
            }
            foreach (array("changefreq", "priority") as $setting) {
                $configValue = Mage::getStoreConfig("sitemap/blog/{$setting}_cat", $stores[0]);
                $configValue = $this->connection->quote($configValue);
                $insertBlogConfigQuery = $insertBlogConfigBaseQuery . "($blogId, 'sitemap/{$setting}_list', $configValue)";
                $this->connection->query($insertBlogConfigQuery);
            }
        }
    }

    protected function setBlogsOnCats()
    {
        $blogTable = $this->installer->getTable("blog/blog");
        $blogCatTable = $this->installer->getTable("blog/cat");
        $blogCatStoreTable = $this->installer->getTable("blog/legacy_cat_store");

        $this->installer->run("
            ALTER TABLE $blogCatTable ADD `blog_id` INT(11) UNSIGNED NOT NULL AFTER `cat_id`;
            ALTER TABLE $blogCatTable ADD CONSTRAINT fk_cat_blog FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`);
        ");

        $getCatsQuery = "SELECT * FROM $blogCatTable ORDER BY `cat_id` ASC";
        $cats = $this->connection->fetchAll($getCatsQuery);
        foreach ($cats as $cat) {
            $catId = $cat["cat_id"];
            $getCatStoresQuery = "SELECT `store_id` FROM $blogCatStoreTable WHERE `cat_id` = $catId";
            $catStoreIds = $this->connection->fetchCol($getCatStoresQuery);

            // Handle first store specifically
            $firstCatBlogId = $this->findBlogForStore($catStoreIds[0]);
            $handledBlogs = array($firstCatBlogId);

            $this->connection->query("UPDATE $blogCatTable SET `blog_id` = $firstCatBlogId WHERE `cat_id` = $catId");
            array_shift($catStoreIds);
            $this->newCats[$catId] = array();

            foreach ($catStoreIds as $catStoreId) {
                $newCatBlogId = $this->findBlogForStore($catStoreId);
                if (in_array($newCatBlogId, $handledBlogs)) {
                    continue;
                }
                $insertNewCatQuery = "INSERT INTO $blogCatTable (`blog_id`, `title`, `identifier`, `sort_order`, `meta_keywords`, `meta_description`) VALUES (?)";
                $insertNewCatQuery = $this->connection->quoteInto($insertNewCatQuery, array($newCatBlogId, $cat["title"], $cat["identifier"], $cat["sort_order"], $cat["meta_keywords"], $cat["meta_description"]));
                $this->connection->query($insertNewCatQuery);
                $handledBlogs[] = $newCatBlogId;
                $this->newCats[$catId][] = $this->connection->lastInsertId();
            }
        }

        $this->installer->run("
            ALTER TABLE $blogCatTable ADD UNIQUE KEY `identifier` (`blog_id`, `identifier`);
        ");
    }

    /**
     * There isn't a neat solution to this.
     * We're going to duplicate every tag for all blogs,
     * then clean up unneeded tags later.
     */
    protected function setBlogsOnTags()
    {
        $blogTable = $this->installer->getTable("blog/blog");
        $blogTagTable = $this->installer->getTable("blog/tag");

        $this->installer->run("
            ALTER TABLE $blogTagTable ADD `blog_id` INT(11) UNSIGNED NOT NULL AFTER `tag_id`;
            ALTER TABLE $blogTagTable DROP INDEX `name`;
            ALTER TABLE $blogTagTable DROP INDEX `identifier`;
            ALTER TABLE $blogTagTable ADD CONSTRAINT fk_tag_blog FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`);
        ");

        $getTagsQuery = "SELECT * FROM `$blogTagTable` ORDER BY `tag_id` ASC";
        $tags = $this->connection->fetchAll($getTagsQuery);
        foreach ($tags as $tag) {
            $tagId = (int) $tag["tag_id"];
            $this->newTags[$tagId] = array();
            $firstBlog = true;
            foreach ($this->blogs as $blogId => $stores) {
                if ($firstBlog == true) {
                    $this->connection->query("UPDATE `$blogTagTable` SET `blog_id` = $blogId WHERE `tag_id` = $tagId");
                    $firstBlog = false;
                } else {
                    $insertNewTagQuery = "INSERT INTO `$blogTagTable` (`blog_id`, `name`, `identifier`) VALUES (?)";
                    $this->connection->query($this->connection->quoteInto($insertNewTagQuery, array($blogId, $tag["name"], $tag["identifier"])));
                    $this->newTags[$tagId][] = $this->connection->lastInsertId();
                }
            }
        }

        $this->installer->run("
            ALTER TABLE $blogTagTable ADD UNIQUE KEY `identifier` (`blog_id`, `identifier`);
            ALTER TABLE $blogTagTable ADD UNIQUE KEY `name` (`blog_id`, `name`);
        ");
    }

    protected function setBlogsOnPosts()
    {
        $blogTable = $this->installer->getTable("blog/blog");
        $blogPostTable = $this->installer->getTable("blog/post");
        $blogPostStoreTable = $this->installer->getTable("blog/legacy_post_store");
        $blogCommentTable = $this->installer->getTable("blog/comment");

        $this->installer->run("
            ALTER TABLE $blogPostTable ADD `blog_id` INT(11) UNSIGNED NOT NULL AFTER `post_id`;
            ALTER TABLE $blogCommentTable ADD `blog_id` INT(11) UNSIGNED NOT NULL AFTER `post_id`;
            ALTER TABLE $blogPostTable DROP INDEX `identifier`;
            ALTER TABLE $blogPostTable ADD CONSTRAINT fk_post_blog FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`);
            ALTER TABLE $blogCommentTable ADD CONSTRAINT fk_comment_blog FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`);
            ALTER TABLE $blogCommentTable MODIFY `post_id` INT(11) UNSIGNED NOT NULL;
            ALTER TABLE $blogCommentTable ADD CONSTRAINT fk_comment_post FOREIGN KEY (`post_id`) REFERENCES `$blogPostTable` (`post_id`);
        ");

        $getPostsQuery = "SELECT * FROM $blogPostTable ORDER BY `post_id` ASC";
        $posts = $this->connection->fetchAll($getPostsQuery);
        foreach ($posts as $post) {
            $postId = $post["post_id"];
            $getPostStoresQuery = "SELECT `store_id` FROM $blogPostStoreTable WHERE `post_id` = $postId";
            $postStoreIds = $this->connection->fetchCol($getPostStoresQuery);

            // Handle first store specifically
            $firstPostBlogId = $this->findBlogForStore($postStoreIds[0]);
            $handledBlogs = array($firstPostBlogId);

            $this->connection->query("UPDATE $blogPostTable SET `blog_id` = $firstPostBlogId WHERE `post_id` = $postId");
            array_shift($postStoreIds);
            $this->newPosts[$postId] = array();

            foreach ($postStoreIds as $postStoreId) {
                $newPostBlogId = $this->findBlogForStore($postStoreId);
                if (in_array($newPostBlogId, $handledBlogs)) {
                    continue;
                }
                $insertNewPostQuery = "INSERT INTO $blogPostTable (`blog_id`, `title`, `post_content`, `summary_content`, `status`, `created_time`, `update_time`, `identifier`, `user`, `update_user`, `meta_keywords`, `meta_description`, `comments`, `image`, `small_image`) VALUES (?)";
                $insertNewPostQuery = $this->connection->quoteInto($insertNewPostQuery, array(
                    $newPostBlogId,
                    $post["title"],
                    $post["post_content"],
                    $post["summary_content"],
                    $post["status"],
                    $post["created_time"],
                    $post["update_time"],
                    $post["identifier"],
                    $post["user"],
                    $post["update_user"],
                    $post["meta_keywords"],
                    $post["meta_description"],
                    $post["comments"],
                    $post["image"],
                    $post["small_image"],
                ));
                $this->connection->query($insertNewPostQuery);
                $handledBlogs[] = $newPostBlogId;
                $this->newPosts[$postId][] = array(
                    "post_id" => $this->connection->lastInsertId(),
                    "blog_id" => $newPostBlogId,
                );
            }

            $this->duplicatePostCats($postId, $firstPostBlogId);
            $this->duplicatePostTags($postId, $firstPostBlogId);
            $this->duplicateComments($postId, $firstPostBlogId);
        }

        $this->installer->run("
            ALTER TABLE $blogPostTable ADD UNIQUE KEY `identifier` (`blog_id`, `identifier`);
        ");
    }

    /**
     * @param int $postId
     * @param int $firstPostBlogId
     */
    protected function duplicateComments($postId, $firstPostBlogId)
    {
        /** @var $post Fontis_Blog_Model_Post */
        $post = Mage::getModel("blog/post")->load($postId);
        $comments = $post->getComments();
        foreach ($comments as $comment) {
            $comment->setBlogId($firstPostBlogId)->save();
        }

        $threadedComments = Mage::getModel("blog/post")->load($postId)->getThreadedComments();
        foreach ($this->newPosts[$postId] as $newPost) {
            $newPostId = $newPost["post_id"];
            $newPostBlog = $newPost["blog_id"];
            $this->processComments($threadedComments, null, $newPostId, $newPostBlog);
        }
    }

    /**
     * @param array $comments
     * @param int|null $inReplyTo
     * @param int $newPostId
     * @param int $newPostBlog
     */
    protected function processComments($comments, $inReplyTo, $newPostId, $newPostBlog)
    {
        foreach ($comments as $comment) {
            /** @var $newComment Fontis_Blog_Model_Comment */
            $newComment = Mage::getModel("blog/comment")->setData($comment["comment"]->getData());
            $newComment->setId(null);
            $newComment->setInReplyTo($inReplyTo)
                ->setPostId($newPostId)
                ->setBlogId($newPostBlog);
            $newComment->save();
            $this->processComments($comment["children"], $newComment->getId(), $newPostId, $newPostBlog);
        }
    }

    /**
     * @param int $postId
     * @param int $firstPostBlogId
     */
    protected function duplicatePostCats($postId, $firstPostBlogId)
    {
        $blogCatTable = $this->installer->getTable("blog/cat");
        $blogPostCatTable = $this->installer->getTable("blog/post_cat");

        $getPostCatsQuery = "SELECT `cat_id` FROM $blogPostCatTable WHERE `post_id` = $postId";
        $catIds = $this->connection->fetchCol($getPostCatsQuery);
        foreach ($catIds as $catId) {
            $newCatIds = $this->newCats[$catId];
            $where1 = $this->connection->quoteInto("WHERE `cat_id` IN (?)", $newCatIds);
            $postIds = array_merge(array(array("post_id" => $postId, "blog_id" => $firstPostBlogId)), $this->newPosts[$postId]);
            foreach ($postIds as $newPost) {
                $newPostId = $newPost["post_id"];
                $newPostBlog = $newPost["blog_id"];
                $where2 = $this->connection->quoteInto("AND `blog_id` = ?", $newPostBlog);
                $catsInBlog = $this->connection->fetchCol("SELECT `cat_id` FROM `$blogCatTable` $where1 $where2");
                foreach ($catsInBlog as $newCatId) {
                    $this->connection->query("INSERT INTO $blogPostCatTable (`cat_id`, `post_id`) VALUES ($newCatId, $newPostId)");
                }
            }
        }
    }

    /**
     * @param int $postId
     * @param int $firstPostBlogId
     */
    protected function duplicatePostTags($postId, $firstPostBlogId)
    {
        $blogTagTable = $this->installer->getTable("blog/tag");
        $blogPostTagTable = $this->installer->getTable("blog/post_tag");

        $getPostTagsQuery = "SELECT `tag_id` FROM $blogPostTagTable WHERE `post_id` = $postId";
        $tagIds = $this->connection->fetchCol($getPostTagsQuery);
        foreach ($tagIds as $tagId) {
            $newTagIds = $this->newTags[$tagId];
            $where1 = $this->connection->quoteInto("WHERE `tag_id` IN (?)", $newTagIds);
            $postIds = array_merge(array(array("post_id" => $postId, "blog_id" => $firstPostBlogId)), $this->newPosts[$postId]);
            foreach ($postIds as $newPost) {
                $newPostId = $newPost["post_id"];
                $newPostBlog = $newPost["blog_id"];
                $where2 = $this->connection->quoteInto("AND `blog_id` = ?", $newPostBlog);
                $tagsInBlog = $this->connection->fetchCol("SELECT `tag_id` FROM `$blogTagTable` $where1 $where2");
                foreach ($tagsInBlog as $newTagId) {
                    $this->connection->query("INSERT INTO $blogPostTagTable (`tag_id`, `post_id`) VALUES ($newTagId, $newPostId)");
                }
            }
        }
    }

    /**
     * @param int $storeId
     * @return int|null
     */
    protected function findBlogForStore($storeId)
    {
        foreach ($this->blogs as $blogId => $stores) {
            if (in_array($storeId, $stores)) {
                return $blogId;
            }
        }
        return null;
    }

    /**
     * Check to make sure two stores have identical core settings.
     * Currently the list is the title and the route.
     *
     * @param int $store1
     * @param int $store2
     * @return bool
     */
    protected function checkImportantBlogDetails($store1, $store2)
    {
        $settings = array(
            "fontis_blog/blog/title",
            "fontis_blog/routing/blog",
        );

        foreach ($settings as $setting) {
            if (Mage::getStoreConfig($setting, $store1) != Mage::getStoreConfig($setting, $store2)) {
                return false;
            }
        }

        return true;
    }
}

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$migrate = new Fontis_Blog_Migrate($installer);
$migrate->run();
