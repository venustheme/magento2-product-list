<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Productlist
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Productlist\Block\Adminhtml\Widget;

class CategoryTab extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_categoryList;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Ves\Productlist\Model\Config\Source\CategoryList $categoryList,
        array $data = []
        ){
        parent::__construct($context, $data);
        $this->_categoryList = $categoryList;
    }
    /** Rows cache
     *
     * @var array|null
     */
    private $_arrayRowsCache;

    /**
     * @var Source Products
     */
    protected $_categoryRender;

    /**
     * @var isAjax
     */
    protected $_isAjax;

    /**
     * Retrieve source column renderer
     *
     * @return Customergroup
     */
    protected function _getCategoryRender()
    {
    	if (!$this->_categoryRender) {
    		$this->_categoryRender = $this->getLayout()->createBlock(
    			'Ves\Productlist\Block\Adminhtml\Form\Field\CategoryList',
    			'',
    			['data' => ['is_render_to_js_template' => true]]
    			);
    		$this->_categoryRender->setClass('source_select');
    	}
    	return $this->_categoryRender;
    }

    /**
     * Retrieve yes/no column renderer
     *
     * @return Customergroup
     */
    protected function _getAjaxRenderer()
    {
        if (!$this->_isAjax) {
            $this->_isAjax = $this->getLayout()->createBlock(
                'Ves\Productlist\Block\Adminhtml\Form\Field\AjaxType',
                '',
                ['data' => ['is_render_to_js_template' => true]]
                );
            $this->_isAjax->setClass('ajax_type');
        }
        return $this->_isAjax;
    }

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
    	$this->addColumn(
    		'category_id',
    		['label' => __('Category'),
    		'renderer' => $this->_getCategoryRender()]
    		);
    	$this->addColumn(
    		'item_title',
    		['label' => __('Title'),'style' => 'width:150px']
    		);
    	$this->addColumn(
    		'item_class',
    		['label' => __('Class'),'style' => 'width:140px']
    		);
        $this->addColumn(
            'ajax_type',
            ['label' => __('Ajax'),
            'renderer' => $this->_getAjaxRenderer(),'style' => 'width:40px']
            );
        $this->addColumn(
            'position',
            [
            'label' => __('Position'),
            'style' => 'width:40px']
            );
        $this->_addAfter = false;
    }

    /**
     * Prepare existing row data object
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     */

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getCategoryRender()->calcOptionHash($row->getData('category_id'))] =
        'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
            );

        $optionExtraAttr['option_' . $this->_getAjaxRenderer()->calcOptionHash($row->getData('ajax_type'))] =
        'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
            );
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = [];
        $temp = []; // save item position
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        $value = $element->getValue();
        if(is_array($value)){
            unset($value['__empty']);
        }
        if(!is_array($value)){
            if(base64_decode($value, true) == true){
                $value = base64_decode($value);
                if(base64_decode($value, true) == true) {
                    $value = base64_decode($value);
                }
            }
            $value = unserialize($value);
        }
        if ( $value && is_array($value) ) {
            foreach ($value as $rowId => $row) {
                if(is_array($row)){
                    $rowColumnValues = [];
                    foreach ($row as $key => $row_value) {
                        $row[$key] = $this->escapeHtml($row_value);
                        if($key == 'position'){
                            $row[$key] = (int)$row['position'];
                        }
                        $row[$key] = htmlspecialchars_decode($row_value);
                        $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key];
                    }
                    if(isset($row['position'])){
                        $temp[$rowId] = $row['position'];
                    }
                    $row['_id'] = $rowId;
                    $row['column_values'] = $rowColumnValues;
                    $result[$rowId] = new \Magento\Framework\DataObject($row);
                    $this->_prepareArrayRow($result[$rowId]);
                }
            }
        }
        asort($temp);
        $rows = [];
        foreach ($temp as $k => $v) {
            $rows[$k] = $result[$k];
        }
        $this->_arrayRowsCache = $rows;
        return $this->_arrayRowsCache;
    }



    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_isPreparedToRender) {
            $this->_prepareToRender();
            $this->_isPreparedToRender = true;
        }
        if (empty($this->_columns)) {
            throw new Exception('At least one column must be defined.');
        }
        return parent::_toHtml();
    }
}