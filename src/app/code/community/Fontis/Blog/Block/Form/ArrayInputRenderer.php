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

/**
 * @method Fontis_Blog_Block_Form_ArrayInputRenderer setAddButtonLabel(string $label)
 * @method string getAddButtonLabel()
 * @method Fontis_Blog_Block_Form_ArrayInputRenderer setRemoveButtonLabel(string $label)
 * @method string getRemoveButtonLabel()
 * @method string getHtmlId()
 */
class Fontis_Blog_Block_Form_ArrayInputRenderer extends Mage_Adminhtml_Block_Template
{
    /**
     * @var string
     */
    protected $_addButtonLabel;

    /**
     * @var string
     */
    protected $_removeButtonLabel;

    /**
     * @var array
     */
    protected $_columns = array();

    /**
     * @var array
     */
    protected $_arrayRowsCache = null;

    /**
     * @return Fontis_Blog_Block_Form_ArrayInputRenderer
     */
    protected function _beforeToHtml()
    {
        if ($addButtonlabel = $this->getAddButtonLabel()) {
            $this->_addButtonLabel = $addButtonlabel;
        } else {
            $this->_addButtonLabel = Mage::helper("blog")->__("Add %s", ucfirst($this->getInputName()));
        }

        if ($removeButtonlabel = $this->getRemoveButtonLabel()) {
            $this->_removeButtonLabel = $removeButtonlabel;
        } else {
            $this->_removeButtonLabel = Mage::helper("adminhtml")->__("Delete");
        }
        return parent::_beforeToHtml();
    }

    /**
     * Add a column to array-grid
     *
     * @param string $name
     * @param array $params
     */
    public function addColumn($name, $params)
    {
        $this->_columns[$name] = array(
            "name"      => $name,
            "label"     => empty($params["label"]) ? "Column" : $params["label"],
            "size"      => empty($params["size"]) ? false : $params["size"],
            "style"     => empty($params["style"]) ? null : $params["style"],
            "class"     => empty($params["class"]) ? null : $params["class"],
            "type"      => empty($params["type"]) ? "text" : $params["type"],
            "renderer"  => false,
        );
    }

    /**
     * Obtain existing data.
     * Each row will be an instance of Varien_Object
     *
     * @return Varien_Object[]
     */
    public function getArrayRows()
    {
        if ($this->_arrayRowsCache !== null) {
            return $this->_arrayRowsCache;
        }

        $result = array();
        $values = $this->getValues();
        foreach ($values as $row) {
            $data = array(
                $this->getInputName()   => $row["name"],
                "_id"                   => $row["identifier"],
            );
            $result[$row["identifier"]] = Mage::getModel("varien/object", $data);
        }
        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }

    /**
     * @param array $column
     * @return string
     */
    protected function _renderCellTemplate(array $column)
    {
        $columnName = $column["name"];
        $inputName = $this->getInputName() . "s[#{_id}][" . $columnName . "]";

        $rendered = '<input type="'. $column["type"] . '" name="' . $inputName . '" value="#{' . $columnName . '}" ' . ($column["size"] ? 'size="' . $column["size"] . '"' : "") . '/>';

        return $rendered;
    }

    /**
     * @param array $column
     * @return string
     */
    protected function _renderEmptyInput(array $column)
    {
        $column["type"] = "hidden";
        $html = $this->_renderCellTemplate($column);

        $columnName = $column['name'];
        $html = str_replace('#{_id}', uniqid(), $html);
        $html = str_replace("#{" . $columnName . "}", '', $html);

        return $html;
    }
}
