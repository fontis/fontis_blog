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

class Fontis_Blog_Block_Manage_Blog_Edit_Tab_Settings extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * @var Mage_Core_Model_Config_Element
     */
    protected $_configGroups = null;

    /**
     * @return Fontis_Blog_Block_Manage_Blog_Edit_Tab_Settings
     */
    protected function _prepareForm()
    {
        $blogHelper = Mage::helper("blog");
        /** @var $blog Fontis_Blog_Model_Blog */
        $blog = Mage::registry("blog_data");
        /** @var $form Varien_Data_Form */
        $form = Mage::getModel("varien/data_form");
        $this->setForm($form);
        $this->_prepareConfigSettings();

        foreach ($this->_configGroups as $groupName => $group) {
            $groupConfig = array(
                "legend"    => $blogHelper->__($group["label"]),
                "comment"   => isset($group["comment"]) ? $group["comment"] : "",
                "onclick"   => "Fieldset.toggleCollapse('settings_$groupName'); return false",
            );
            $fieldset = $form->addFieldset("settings_$groupName", $groupConfig);
            $fieldset->addType("blogimage", Mage::getConfig()->getBlockClassName("blog/form_image"));
            $fieldset->getRenderer()->setTemplate("fontis/blog/forms/fieldset.phtml");
            foreach ($group["fields"] as $fieldName => $field) {
                $type = $this->_prepareFieldType($field);
                $fieldId = $this->prepareFieldId($groupName, $fieldName);

                $key = "$groupName/$fieldName";
                $canInherit = $field["show_in_default"] == "1" ? true : false;
                $value = $blog->getSetting($key);
                if (is_array($value) && isset($value["value"])) {
                    $value = $value["value"];
                }
                if (isset($field["depends"])) {
                    $this->prepareDepends($fieldId, $field, $groupName);
                }

                $fieldConfig = array(
                    "name"          => "settings[" . $groupName . "][" . $fieldName . "][value]",
                    "label"         => $blogHelper->__($field["label"]),
                    "class"         => $this->_prepareFieldClasses($field),
                    "style"         => isset($field["style"]) ? $field["style"] : "",
                    "values"        => $this->_prepareFieldValues($field, $type),
                    "note"          => $this->_prepareFieldComment($field, $value, $blogHelper),
                    "value"         => $value,
                    "disabled"      => ($canInherit ? !$blog->hasSetting($key) : false),
                    "can_inherit"   => $canInherit,
                    "setting_key"   => $key,
                );
                $field = $fieldset->addField($fieldId, $type, $fieldConfig);
                $field->getRenderer()->setTemplate("fontis/blog/forms/fieldset/element.phtml");
            }
        }

        return parent::_prepareForm();
    }

    /**
     * @param string $groupName
     * @param string $fieldName
     * @return string
     */
    protected function prepareFieldId($groupName, $fieldName)
    {
        return "$groupName--$fieldName";
    }

    protected function _prepareConfigSettings()
    {
        $this->_configGroups = Mage::getModel("adminhtml/config")->getSections()->fontis_blog->groups->asArray();
    }

    /**
     * @param array $field
     * @return string
     */
    protected function _prepareFieldType($field)
    {
        if (isset($field["frontend_type"])) {
            if ($field["frontend_type"] == "image") {
                return "blogimage";
            } else {
                return $field["frontend_type"];
            }
        } else {
            return "text";
        }
    }

    /**
     * @param array $field
     * @param mixed $value
     * @param Mage_Core_Helper_Abstract $helper
     * @return string
     */
    protected function _prepareFieldComment($field, $value, Mage_Core_Helper_Abstract $helper)
    {
        $comment = "";
        if (isset($field["comment"])) {
            $commentInfo = $field["comment"];
            if (is_array($commentInfo)) {
                if (isset($commentInfo["model"])) {
                    $model = Mage::getModel($commentInfo["model"]);
                    if (method_exists($model, "getCommentText")) {
                        $comment = $model->getCommentText($field, $value);
                    }
                }
            } else {
                $comment = $helper->__($commentInfo);
            }
        }
        return $comment;
    }

    /**
     * @param array $field
     * @return string
     */
    protected function _prepareFieldClasses(array $field)
    {
        $class = "";
        if (isset($field["validate"])) {
            $class .= $field["validate"] . " ";
        }
        if (isset($field["frontend_class"])) {
            $class .= $field["frontend_class"] . " ";
        }
        return $class;
    }

    /**
     * Most of this method is copied from Mage_Adminhtml_Block_System_Config_Form, the idea
     * being to replicate how the system configuration is rendered.
     *
     * @param array $field
     * @param string $fieldType
     * @return array|null
     */
    protected function _prepareFieldValues($field, $fieldType)
    {
        if (!isset($field["source_model"])) {
            return null;
        }

        // determine callback for the source model
        $factoryName = $field["source_model"];
        $method = false;
        if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
            array_shift($matches);
            list($factoryName, $method) = array_values($matches);
        }

        $sourceModel = Mage::getSingleton($factoryName);
        if ($method) {
            if ($fieldType == "multiselect") {
                $optionArray = $sourceModel->$method();
            } else {
                $optionArray = array();
                foreach ($sourceModel->$method() as $value => $label) {
                    $optionArray[] = array("label" => $label, "value" => $value);
                }
            }
        } else {
            $optionArray = $sourceModel->toOptionArray($fieldType == "multiselect");
        }
        return $optionArray;
    }

    /**
     * @param string $fieldId
     * @param array $field
     * @param string $groupName
     */
    protected function prepareDepends($fieldId, $field, $groupName)
    {
        foreach ($field["depends"] as $dependentName => $dependent) {
            // The fieldset node indicates that the dependent setting is in a different group.
            // Why Magento didn't just use "group" instead of "fieldset" is beyond me.
            if (isset($dependent["fieldset"])) {
                $dependentFieldGroupName = $dependent["fieldset"];
                if (!isset($this->_configGroups[$dependentFieldGroupName])) {
                    // If this points to a group that doesn't exist, just fall back to the current group.
                    $dependentFieldGroupName = $groupName;
                }
            } else {
                $dependentFieldGroupName = $groupName;
            }

            $dependentValue = isset($dependent["value"]) ? $dependent["value"] : $dependent;

            $dependentId = $this->prepareFieldId($dependentFieldGroupName, $dependentName);
            if (isset($dependent["separator"])) {
                $dependentValue = explode((string)$dependent["separator"], $dependentValue);
            }

            $this->_getDependencyManager()
                ->addFieldMap($fieldId, $fieldId)
                ->addFieldMap($dependentId, $dependentId)
                ->addFieldDependence($fieldId, $dependentId, $dependentValue);
        }
    }

    /**
     * Return dependency block object.
     *
     * This isn't done through the layout system because we want it to be lazy-loaded.
     *
     * @return Mage_Adminhtml_Block_Widget_Form_Element_Dependence
     */
    protected function _getDependencyManager()
    {
        if (!$this->getLayout()->getBlock("setting_dependency_manager")) {
            /** @var $formAfter Mage_Core_Block_Text_List */
            $formAfter = $this->getChild("form_after");
            $formAfter->append($this->getLayout()->createBlock("adminhtml/widget_form_element_dependence", "setting_dependency_manager"));
        }
        return $this->getLayout()->getBlock("setting_dependency_manager");
    }

    /** These methods are necessary only to fulfil the interface's contract. */

    /**
     * This value is set in the layout.
     * Unless otherwise overridden, it's pulled out of Varien_Object's _data array.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return parent::getTabLabel();
    }

    /**
     * This value is set in the layout.
     * Unless otherwise overridden, it's pulled out of Varien_Object's _data array.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return parent::getTabTitle();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if (!$this->hasCanShowTab()) {
            return true;
        }
        return parent::getCanShowTab();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return (bool) parent::getIsHidden();
    }
}
