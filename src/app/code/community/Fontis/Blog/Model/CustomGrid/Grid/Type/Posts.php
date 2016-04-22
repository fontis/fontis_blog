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

class Fontis_Blog_Model_CustomGrid_Grid_Type_Posts extends BL_CustomGrid_Model_Grid_Type_Abstract
{
    /**
     * @return string[]|string
     */
    protected function _getSupportedBlockTypes()
    {
        return array(
            "blog/manage_post_grid",
        );
    }

    /**
     * @param string $blockType
     * @return array
     */
    protected function _getBaseEditableFields($blockType)
    {
        $fields = array(
            "status" => array(
                "type"        => "select",
                "form_values" => Mage::getSingleton("blog/status")->toOptionArray(),
            ),
        );

        return $fields;
    }

    /**
     * @param string $blockType
     * @return string[]
     */
    protected function _getEntityRowIdentifiersKeys($blockType)
    {
        return array("post_id");
    }

    /**
     * @param string $blockType
     * @param BL_CustomGrid_Object $config
     * @param array $params
     * @param mixed $entityId
     * @return Fontis_Blog_Model_Post
     */
    protected function _loadEditedEntity($blockType, BL_CustomGrid_Object $config, array $params, $entityId)
    {
        return Mage::getModel("blog/post")->load($entityId);
    }

    /**
     * @param string $blockType
     * @return string
     */
    protected function _getEditRequiredAclPermissions($blockType)
    {
        return "blog/posts";
    }

    /**
     * @param string $blockType
     * @param BL_CustomGrid_Object $config
     * @param array $params
     * @param Fontis_Blog_Model_Post $entity
     * @return string
     */
    protected function _getLoadedEntityName($blockType, BL_CustomGrid_Object $config, array $params, $entity)
    {
        return $entity->getTitle();
    }
}
