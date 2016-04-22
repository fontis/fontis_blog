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

class Fontis_Blog_Model_Resource_Migrate_Authors
{
    /**
     * @var Mage_Core_Model_Resource_Setup
     */
    protected $installer;

    /**
     * @var Varien_Db_Adapter_Interface
     */
    protected $connection;

    /**
     * @var Fontis_Blog_Helper_Data
     */
    protected $helper;

    /**
     * @var string
     */
    protected $postTable;

    /**
     * @var string
     */
    protected $authorTable;

    /**
     * @param Mage_Core_Model_Resource_Setup $installer
     */
    public function __construct($installer)
    {
        $this->installer = $installer;
        $this->connection = $installer->getConnection();
        $this->helper = Mage::helper("blog");
        $this->postTable = $installer->getTable("blog/post");
        $this->authorTable = $installer->getTable("blog/author");
    }

    public function run()
    {
        $this->createAuthorFields();
        $this->migrateAuthors();
        $this->cleanupOldAuthorFields();
        $this->setupDBStrictness();
    }

    protected function createAuthorFields()
    {
        $this->installer->run("
            CREATE TABLE `{$this->authorTable}` (
              `author_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `identifier` varchar(255) NOT NULL,
              `name` varchar(255) NOT NULL,
              PRIMARY KEY (`author_id`),
              UNIQUE KEY `identifier` (`identifier`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ");

        $this->installer->run("ALTER TABLE `{$this->postTable}` ADD COLUMN `author_id` int(11) unsigned AFTER `blog_id`");
    }

    /**
     * @param string $identifier
     * @param string $name
     * @return int
     */
    protected function createAuthor($identifier, $name)
    {
        $insertNewAuthorQuery = "INSERT INTO `{$this->authorTable}` (`identifier`, `name`) VALUES (?)";
        $this->connection->query($this->connection->quoteInto($insertNewAuthorQuery, array($identifier, $name)));
        return (int) $this->connection->lastInsertId();
    }

    /**
     * @param string $identifier
     * @return int
     */
    protected function getAuthorId($identifier)
    {
        $selectAuthorQuery = "SELECT `author_id` FROM `{$this->authorTable}` WHERE `identifier` = ?";
        $authorId = $this->connection->fetchOne($this->connection->quoteInto($selectAuthorQuery, $identifier));
        if ($authorId) {
            return (int) $authorId;
        } else {
            return null;
        }
    }

    protected function migrateAuthors()
    {
        $selectPostsQuery = "SELECT `post_id`, `user` FROM `{$this->postTable}`";
        $rows = $this->connection->fetchPairs($selectPostsQuery);
        foreach ($rows as $postId => $user) {
            $identifier = $this->helper->toAscii($user);
            $authorId = $this->getAuthorId($identifier);
            if (!$authorId) {
                $authorId = $this->createAuthor($identifier, $user);
            }
            $this->connection->update($this->postTable, array("author_id" => $authorId), array("post_id = ?" => $postId));
        }
    }

    protected function cleanupOldAuthorFields()
    {
        $this->connection->dropColumn($this->postTable, "user");
    }

    protected function setupDBStrictness()
    {
        $this->connection->modifyColumn($this->postTable, "author_id", "int(11) unsigned NOT NULL");
        $this->connection->addForeignKey("author_id", $this->postTable, "author_id", $this->authorTable, "author_id");
    }
}
