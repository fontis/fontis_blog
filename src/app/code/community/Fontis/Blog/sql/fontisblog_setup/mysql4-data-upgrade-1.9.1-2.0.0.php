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

$postTable = $installer->getTable("blog/post");
$installer->run("ALTER TABLE $postTable DROP `cat_id`");

$postStoreTable = $installer->getTable("blog/legacy_post_store");
$catStoreTable = $installer->getTable("blog/legacy_cat_store");
$installer->run("DROP TABLE $postStoreTable, $catStoreTable;");

$postTagTable = $installer->getTable("blog/post_tag");
$installer->run("ALTER TABLE `$postTagTable` ADD CONSTRAINT UNIQUE (`tag_id`, `post_id`)");
$postCatTable = $installer->getTable("blog/post_cat");
$installer->run("ALTER TABLE `$postCatTable` ADD CONSTRAINT UNIQUE (`cat_id`, `post_id`)");

$catTable = $installer->getTable("blog/cat");
$installer->run("ALTER TABLE `$catTable` ADD COLUMN `image` VARCHAR(255)");

$migrateAuthors = new Fontis_Blog_Model_Resource_Migrate_Authors($installer);
$migrateAuthors->run();

$installer->endSetup();
