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

abstract class Fontis_Blog_Model_CustomGrid_Custom_Column_AbstractList extends BL_CustomGrid_Model_Custom_Column_Abstract
{
    /**
     * @var string
     */
    protected $_renderer = null;

    protected function _prepareConfig()
    {
        $blogHelper = Mage::helper("blog");

        $this->addCustomizationParam(
            "display_ids",
            array(
                "label"        => $blogHelper->__("Display IDs"),
                "group"        => $blogHelper->__("Rendering"),
                "description"  => $blogHelper->__('Choose "<strong>Yes</strong>" to display IDs instead of names'),
                "type"         => "select",
                "source_model" => "adminhtml/system_config_source_yesno",
                "value"        => 0,
            ),
            10
        );

        return parent::_prepareConfig();
    }

    public function applyToGridCollection(
        Varien_Data_Collection_Db $collection,
        Mage_Adminhtml_Block_Widget_Grid $gridBlock,
        BL_CustomGrid_Model_Grid $gridModel,
        $columnBlockId,
        $columnIndex,
        array $params,
        Mage_Core_Model_Store $store
    ) {
    }

    public function getForcedBlockValues(
        Mage_Adminhtml_Block_Widget_Grid $gridBlock,
        BL_CustomGrid_Model_Grid $gridModel,
        $columnBlockId,
        $columnIndex,
        array $params,
        Mage_Core_Model_Store $store
    ) {
        if ($this->_renderer === null) {
            return array();
        }

        $displayIds = $this->_extractBoolParam($params, 'display_ids');

        return array(
            'renderer'                  => $this->_renderer,
            'result_separator'          => $this->_extractStringParam($params, 'separator', ', ', true),
            'display_ids'               => $displayIds,
            'filter_condition_callback' => array($this, 'addFilterToGridCollection'),
        );
    }

    /**
     * @param Varien_Data_Collection_Db $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $columnBlock
     * @return Fontis_Blog_Model_CustomGrid_Custom_Column_AbstractList
     */
    abstract public function addFilterToGridCollection(Varien_Data_Collection_Db $collection, Mage_Adminhtml_Block_Widget_Grid_Column $columnBlock);
}
