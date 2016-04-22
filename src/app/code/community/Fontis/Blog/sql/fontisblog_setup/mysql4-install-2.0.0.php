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

/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

// Tables
$blogTable = $installer->getTable("blog/blog");
$blogStoreTable = $installer->getTable("blog/store");
$blogConfigTable = $installer->getTable("blog/config");
$catTable = $installer->getTable("blog/cat");
$authorTable = $installer->getTable("blog/author");
$postTable = $installer->getTable("blog/post");
$commentTable = $installer->getTable("blog/comment");
$postCatTable = $installer->getTable("blog/post_cat");
$tagTable = $installer->getTable("blog/tag");
$postTagTable = $installer->getTable("blog/post_tag");

$installer->startSetup();

$installer->run("
CREATE TABLE `$blogTable` (
  `blog_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `route` varchar(50) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 1,
  PRIMARY KEY (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$blogStoreTable` (
  `blog_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) NOT NULL,
  PRIMARY KEY (`blog_id`, `store_id`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$blogConfigTable` (
  `config_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`config_id`),
  UNIQUE KEY `blog_config_setting` (`blog_id`, `key`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$catTable` (
  `cat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `sort_order` tinyint(6) NOT NULL,
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  `image` varchar(255),
  PRIMARY KEY (`cat_id`),
  UNIQUE KEY `identifier` (`blog_id`, `identifier`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$authorTable` (
  `author_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`author_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$postTable` (
  `post_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `author_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `post_content` text NOT NULL,
  `summary_content` text NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 0,
  `created_time` datetime DEFAULT NULL,
  `update_time` datetime DEFAULT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `update_user` varchar(255) NOT NULL DEFAULT '',
  `meta_keywords` text NOT NULL,
  `meta_description` text NOT NULL,
  `comments` tinyint(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `small_image` varchar(255) NOT NULL,
  PRIMARY KEY (`post_id`),
  UNIQUE KEY `identifier` (`blog_id`, `identifier`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`),
  FOREIGN KEY (`author_id`) REFERENCES `$authorTable` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$commentTable` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` int(11) unsigned NOT NULL,
  `blog_id` int(11) unsigned NOT NULL,
  `comment` text NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT 0,
  `created_time` datetime DEFAULT NULL,
  `user` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `in_reply_to` int(11) unsigned DEFAULT NULL,
  `customer_id` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_id`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`),
  FOREIGN KEY (`post_id`) REFERENCES `$postTable` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$postCatTable` (
  `cat_id` smallint(6) unsigned DEFAULT NULL,
  `post_id` smallint(6) unsigned DEFAULT NULL,
  UNIQUE KEY (`cat_id`, `post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$tagTable` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `identifier` (`blog_id`, `identifier`),
  UNIQUE KEY `name` (`blog_id`, `name`),
  FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `$postTagTable` (
  `tag_id` int(11) unsigned NOT NULL,
  `post_id` int(11) unsigned NOT NULL,
  UNIQUE KEY (`tag_id`, `post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
