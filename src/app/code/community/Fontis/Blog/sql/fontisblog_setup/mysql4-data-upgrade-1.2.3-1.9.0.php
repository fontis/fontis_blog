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

$installer->startSetup();

$blogTable = $this->getTable("blog/blog");
$blogConfigTable = $this->getTable("blog/config");
$blogStoreTable = $this->getTable("blog/store");
$postTable = $this->getTable("blog/post");
$postStoreTable = $this->getTable("blog/legacy_post_store");

$installer->run("RENAME TABLE `$blogTable` TO `$postTable`");
$installer->run("RENAME TABLE `$blogStoreTable` TO `$postStoreTable`");

$query = "
CREATE TABLE `$blogTable` (
    `blog_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `route` VARCHAR(50) NOT NULL,
    `status` TINYINT(2) NOT NULL DEFAULT 1,
    PRIMARY KEY (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

";

$query .= "
CREATE TABLE `$blogConfigTable` (
    `config_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `blog_id` INT(11) UNSIGNED NOT NULL,
    `key` VARCHAR(255) NOT NULL,
    `value` TEXT NOT NULL DEFAULT '',
    PRIMARY KEY (`config_id`),
    FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`),
    UNIQUE KEY `blog_config_setting` (`blog_id`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

";

$query .= "
CREATE TABLE `$blogStoreTable` (
    `blog_id` INT(11) UNSIGNED NOT NULL,
    `store_id` SMALLINT(5) NOT NULL,
    PRIMARY KEY (`blog_id`, `store_id`),
    FOREIGN KEY (`blog_id`) REFERENCES `$blogTable` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$installer->run($query);

$installer->endSetup();
