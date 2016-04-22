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

class Fontis_Blog_Helper_Image extends Mage_Core_Helper_Abstract
{
    /**
     * @param Fontis_Blog_Model_Post $post
     * @param string $imageFieldName
     */
    public function processUploadPost(Fontis_Blog_Model_Post $post, $imageFieldName)
    {
        $filename = $this->_processUpload($imageFieldName, $post->getImageBasePath());
        if ($filename) {
            $post->setData($imageFieldName, $filename);
        }
    }

    /**
     * @param Fontis_Blog_Model_Cat $cat
     * @param string $imageFieldName
     */
    public function processUploadCat(Fontis_Blog_Model_Cat $cat, $imageFieldName)
    {
        $path = Fontis_Blog_Model_Cat::CAT_IMAGE_BASEPATH;
        $filename = $this->_processUpload($imageFieldName, $path);
        if ($filename) {
            $cat->setData($imageFieldName, $filename);
        }
    }

    /**
     * @param string $imageFieldName
     * @param string $imageUploadPath
     * @return string|null
     */
    public function _processUpload($imageFieldName, $imageUploadPath)
    {
        if (!empty($_FILES[$imageFieldName]["name"]) && file_exists($_FILES[$imageFieldName]["tmp_name"])) {
            /** @var $uploader Varien_File_Uploader */
            $uploader = Mage::getModel("varien/file_uploader", $imageFieldName);
            $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"))
                ->setAllowRenameFiles(true)
                ->setFilesDispersion(false);
            $path = Mage::getBaseDir("media") . DS . $imageUploadPath;
            $result = $uploader->save($path, $_FILES[$imageFieldName]["name"]);
            return $result["file"];
        } else {
            return null;
        }
    }

    /**
     * @param Fontis_Blog_Model_Blog $blog
     * @param string $imageFieldName
     */
    public function processUploadBlog(Fontis_Blog_Model_Blog $blog, $imageFieldName)
    {
        $path = Fontis_Blog_Helper_Data::BLOG_MEDIA_TOPLEVEL . DS . Fontis_Blog_Helper_Data::BLOG_MEDIA_MAIN;
        $filename = $this->_processUploadBlog($imageFieldName, $path);
        if ($filename) {
            $blog->setSetting($imageFieldName, $filename);
        } else {
            $origSetting = $blog->getOrigSetting($imageFieldName);
            if ($origSetting === null) {
                // Cannot be null.
                $origSetting = "";
            }
            $blog->setSetting($imageFieldName, $origSetting);
        }
    }

    /**
     * @param string $imageFieldName
     * @param string $imageUploadPath
     * @return string|null
     */
    public function _processUploadBlog($imageFieldName, $imageUploadPath)
    {
        $fileParams = array("error", "name", "size", "tmp_name", "type");
        $actualParams = array();
        $splitKey = explode("/", $imageFieldName);
        if (!empty($_FILES["settings"]["name"][$splitKey[0]][$splitKey[1]]["value"]) && file_exists($_FILES["settings"]["tmp_name"][$splitKey[0]][$splitKey[1]]["value"])) {
            foreach ($fileParams as $param) {
                if (isset($_FILES["settings"][$param][$splitKey[0]][$splitKey[1]])) {
                    $actualParams[$param] = $_FILES["settings"][$param][$splitKey[0]][$splitKey[1]]["value"];
                }
            }

            if (count($actualParams)) {
                /** @var $uploader Varien_File_Uploader */
                $uploader = Mage::getModel("varien/file_uploader", $actualParams);
                $uploader->setAllowedExtensions(array("jpg", "jpeg", "gif", "png"))
                    ->setAllowRenameFiles(true)
                    ->setFilesDispersion(false);
                $path = Mage::getBaseDir("media") . DS . $imageUploadPath;
                $result = $uploader->save($path, $actualParams["name"]);
                return $result["file"];
            }
        }
        return null;
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return array
     */
    public function getImageDimensions(Fontis_Blog_Model_Post $post)
    {
        return $this->_getDimensions($post->getImageFile());
    }

    /**
     * @param Fontis_Blog_Model_Post $post
     * @return array
     */
    public function getSmallImageDimensions(Fontis_Blog_Model_Post $post)
    {
        return $this->_getDimensions($post->getSmallImageFile());
    }

    /**
     * @param string $filename
     * @return array
     */
    protected function _getDimensions($filename)
    {
        if (is_readable($filename)) {
            list($width, $height) = getimagesize($filename);
        } else {
            $width = 0;
            $height = 0;
        }
        return array($width, $height);
    }
}
