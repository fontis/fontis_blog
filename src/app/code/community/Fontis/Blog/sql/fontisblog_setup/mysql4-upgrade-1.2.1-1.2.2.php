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
/** @var $helper Fontis_Blog_Helper_Data */
$helper = Mage::helper("blog");

$installer->startSetup();

$tagTable = $this->getTable("blog/tag");

$installer->run("ALTER TABLE $tagTable
ADD `identifier` VARCHAR(255) NOT NULL DEFAULT ''");

$conn = $installer->getConnection();
$query = "SELECT * FROM $tagTable";
$rows = $conn->fetchAll($query);
foreach ($rows as $row) {
    $installer->run("UPDATE $tagTable
    SET `identifier` = '" . $helper->toAscii($row["name"]) . "'
    WHERE `tag_id` = " . $row["tag_id"] . ";");
}

$installer->run("ALTER TABLE $tagTable
ADD UNIQUE KEY `identifier` (`identifier`);
");

$installer->endSetup();
