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

class Fontis_Blog_Model_Mysql4_Blog extends Mage_Core_Model_Mysql4_Abstract
{
    const PK_FIELD = "blog_id";

    protected function _construct()
    {
        // Note that blog_id refers to the primary key field in your database table.
        $this->_init("blog/blog", self::PK_FIELD);
    }

    /**
     * @param Mage_Core_Model_Abstract $blog
     * @return Fontis_Blog_Model_Mysql4_Blog
     * @throws Mage_Core_Exception
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $blog)
    {
        /** @var $helper Fontis_Blog_Helper_Data */
        $helper = Mage::helper("blog");

        if (!$this->isUniqueRoute($blog)) {
            Mage::throwException($helper->__("Blog route already exists for one of the selected stores."));
        }

        if ($this->isNumericRoute($blog)) {
            Mage::throwException($helper->__("Blog route cannot consist only of numbers."));
        }

        if ($helper->checkForReservedWord($blog->getData("route"))) {
            Mage::throwException($helper->__("Blog route cannot be a reserved word."));
        }

        $this->validateSettings($blog);

        return $this;
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return bool
     */
    public function isUniqueRoute(Mage_Core_Model_Abstract $object)
    {
        $mainTable = $this->getMainTable();
        $blogStores = $object->getStores();
        if (!$blogStores) {
            // We end up in this situation when saving a new blog for the first time
            // when Magento is in single store mode.
            $blogStores = array(Mage::app()->getStore(true)->getId());
        }

        $select = $this->_getReadAdapter()->select()
            ->from($mainTable)
            ->where($mainTable . ".route = ?", $object->getData("route"))
            ->join(array("fbs" => $this->getTable("blog/store")), $mainTable . ".blog_id = `fbs`.blog_id")
            ->where("`fbs`.store_id in (?) ", $blogStores);
        if ($object->getId()) {
            $select->where($mainTable . ".blog_id <> ?", $object->getId());
        }

        if ($this->_getReadAdapter()->fetchRow($select)) {
            return false;
        }

        return true;
    }

    /**
     * @param Mage_Core_Model_Abstract $blog
     * @return int|bool
     */
    protected function isNumericRoute(Mage_Core_Model_Abstract $blog)
    {
        return preg_match("/^[0-9]+$/", $blog->getData("route"));
    }

    /**
     * Before "Before Save" validation for any settings that have a backend model.
     *
     * @param Mage_Core_Model_Abstract $blog
     */
    protected function validateSettings(Mage_Core_Model_Abstract $blog)
    {
        /** @var $imageHelper Fontis_Blog_Helper_Image */
        $imageHelper = Mage::helper("blog/image");
        $configTree = Mage::getModel("adminhtml/config")->getSections()->fontis_blog->groups;
        foreach ($blog->getSetting() as $groupName => $groupItems) {
            foreach ($groupItems as $settingName => $setting) {
                if (isset($setting["inherit"]) && $setting["inherit"]) {
                    continue;
                }
                $configNode = $configTree->{$groupName}->fields->{$settingName};
                if (isset($configNode->frontend_type) && $configNode->frontend_type == "image") {
                    if (isset($setting["value"]["delete"]) && $setting["value"]["delete"]) {
                        $blog->setSetting("$groupName/$settingName", "");
                    } else {
                        $imageHelper->processUploadBlog($blog, "$groupName/$settingName");
                    }
                    continue;
                }
                if (!isset($configNode->backend_model)) {
                    continue;
                }
                if (is_array($setting) && isset($setting["value"])) {
                    $value = $setting["value"];
                } else {
                    $value = $setting;
                }
                $backendModel = Mage::getSingleton($configNode->backend_model);
                if (method_exists($backendModel, "validate")) {
                    $backendModel->validate($value);
                }
            }
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Fontis_Blog_Model_Mysql4_Blog
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $this->saveStores($object, $writeAdapter);
        $this->saveSettings($object, $writeAdapter);

        return parent::_afterSave($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param Varien_Db_Adapter_Interface $writeAdapter
     */
    protected function saveStores(Mage_Core_Model_Abstract $object, $writeAdapter)
    {
        $tableName = $this->getTable("blog/store");
        $condition = $writeAdapter->quoteInto(self::PK_FIELD . " = ?", $object->getId());
        $writeAdapter->delete($tableName, $condition);

        if ($object->getData("stores")) {
            foreach ((array) $object->getData("stores") as $store) {
                $storeArray = array(
                    self::PK_FIELD  => $object->getId(),
                    "store_id"      => $store,
                );
                $writeAdapter->insert($tableName, $storeArray);
            }
        } else {
            $storeArray = array(
                self::PK_FIELD  => $object->getId(),
                "store_id"      => Mage::app()->getStore(true)->getId(),
            );
            $writeAdapter->insert($tableName, $storeArray);
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param Varien_Db_Adapter_Interface $writeAdapter
     */
    protected function saveSettings(Mage_Core_Model_Abstract $object, $writeAdapter)
    {
        $tableName = $this->getTable("blog/config");
        $condition = $writeAdapter->quoteInto(self::PK_FIELD . " = ?", $object->getId());
        $writeAdapter->delete($tableName, $condition);

        foreach ($object->getSetting() as $groupName => $groupItems) {
            foreach ($groupItems as $settingName => $setting) {
                if (is_array($setting) && isset($setting["inherit"]) && $setting["inherit"]) {
                    $object->unsetSetting("$groupName/$settingName");
                    continue;
                }
                $settingArray = array(
                    self::PK_FIELD => $object->getId(),
                    "key" => $groupName . "/" . $settingName,
                );
                if (is_array($setting) && isset($setting["value"])) {
                    $settingArray["value"] = $setting["value"];
                    $object->setSetting("$groupName/$settingName", $setting["value"]);
                } else {
                    $settingArray["value"] = $setting;
                }
                $writeAdapter->insert($tableName, $settingArray);
            }
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @return Fontis_Blog_Model_Mysql4_Blog
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $readAdapter = $this->_getReadAdapter();
        $this->loadStores($object, $readAdapter);
        $this->loadSettings($object, $readAdapter);

        return parent::_afterLoad($object);
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param Varien_Db_Adapter_Interface $readAdapter
     */
    protected function loadStores(Mage_Core_Model_Abstract $object, $readAdapter)
    {
        $select = $readAdapter->select()
            ->from($this->getTable("blog/store"))
            ->columns("store_id")
            ->where(self::PK_FIELD . " = ?", $object->getId());

        if ($data = $readAdapter->fetchAll($select)) {
            $storesArray = array();
            foreach ($data as $row) {
                $storesArray[] = $row["store_id"];
            }
            $object->setData("stores", $storesArray);
        }
    }

    /**
     * @param Mage_Core_Model_Abstract $object
     * @param Varien_Db_Adapter_Interface $readAdapter
     */
    protected function loadSettings(Mage_Core_Model_Abstract $object, $readAdapter)
    {
        $select = $readAdapter->select()
            ->from($this->getTable("blog/config"))
            ->where(self::PK_FIELD . " = ?", $object->getId());

        if ($data = $readAdapter->fetchAll($select)) {
            $settings = array();
            foreach ($data as $row) {
                $splitKey = explode("/", $row["key"]);
                if (!isset($settings[$splitKey[0]])) {
                    $settings[$splitKey[0]] = array();
                }
                $settings[$splitKey[0]][$splitKey[1]] = $row["value"];
            }
            $object->setSetting($settings);
        }

        $object->setOrigSetting();
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $select->join(array("fbs" => $this->getTable("blog/store")), $this->getMainTable() . ".blog_id = `fbs`.blog_id")
                ->where("`fbs`.store_id in (" . Mage_Core_Model_App::ADMIN_STORE_ID . ", ?) ", (int) $object->getStoreId())
                ->order("store_id DESC")
                ->limit(1);
        }

        return $select;
    }

    /**
     * Should only be used to retrieve the ID of the blog in single blog mode.
     *
     * @return int
     */
    public function getFirstBlogId()
    {
        return $this->_getReadAdapter()->fetchOne("SELECT `" . self::PK_FIELD . "` FROM `" . $this->getMainTable() . "` ORDER BY " . self::PK_FIELD . " LIMIT 1");
    }
}
