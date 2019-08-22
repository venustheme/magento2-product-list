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
namespace Ves\Productlist\Model\Config\Source;
class CategoryList implements \Magento\Framework\Option\ArrayInterface
{
    static $arr = array();
    static $tmp = array();

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @param \Magento\Cms\Model\Block $blockModel
     */
    public function __construct(
    	\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Escaper $escaper
    	) {
        $this->_escaper = $escaper;
    	$this->_categoryFactory = $categoryFactory;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	$collection = $this->_categoryFactory->create();

        $root_parent_id = 1;
        $root_parent_collection = $collection->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('is_active','1')
        ->addAttributeToFilter('level', '0')
        ->addAttributeToFilter('parent_id',array('eq' => "0"));
        
        if(0 < $root_parent_collection->getSize()) {
            $root_parent_id = $root_parent_collection->getFirstItem()->getId();
        }
        $arr = $this->getTreeCategories($root_parent_id);
        return $arr;
    }


    public function getTreeCategories($parentId,$level = 0, $caret = ' _ '){
        $allCats = $this->_categoryFactory->create()->getCollection()
        ->addAttributeToSelect('*')
        ->addAttributeToFilter('is_active','1')
        ->addAttributeToSort('position', 'asc'); 
        if ($parentId) {
            $allCats->addAttributeToFilter('parent_id',array('eq' => $parentId));
        }

        $prefix = "";
        if($level) {
            $prefix = "|_";
            for($i=0;$i < $level; $i++) {
                $prefix .= $caret;
            }
        }
        foreach($allCats as $category)
        {
            if(!isset(self::$tmp[$category->getId()])) {
                self::$tmp[$category->getId()] = $category->getId();
                $tmp["value"] = $category->getId();
                $tmp["label"] = $prefix."(ID:".$category->getId().") ".addslashes($category->getName());
                $arr[] = $tmp;
                $subcats = $category->getChildren();
                if($subcats != ''){ 
                    $arr = array_merge($arr, $this->getTreeCategories($category->getId(),(int)$level + 1, $caret.' _ '));
                }

            }
            
        }
        return isset($arr)?$arr:array();
    }
}