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
namespace Ves\Productlist\Block\Adminhtml\Form\Field;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * HTML select element block with customer groups options
 */
class CategoryList extends \Magento\Framework\View\Element\Html\Select
{

    static $arr = array();
    static $tmp = array();
    /**
     * Customer groups cache
     *
     * @var array
     */
    private $_sourceList;

    /**
     * Flag whether to add group all option or no
     *
     * @var bool
     */
    protected $_addGroupAllOption = true;

    /**
     * @var GroupManagementInterface
     */
    protected $groupManagement;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper = null;

    /**
     * @param \Magento\Framework\View\Element\Context $context               
     * @param GroupManagementInterface                $groupManagement       
     * @param GroupRepositoryInterface                $groupRepository       
     * @param SearchCriteriaBuilder                   $searchCriteriaBuilder 
     * @param array                                   $data                  
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        GroupManagementInterface $groupManagement,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
        ) {
        parent::__construct($context, $data);
        $this->_collectionFactory = $collectionFactory;
        $this->groupManagement = $groupManagement;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_escaper = $context->getEscaper();
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Retrieve allowed customer groups
     *
     * @param int $groupId return name by customer group id
     * @return array|string
     */
    /*protected function _getCategoryList($groupId = null)
    {
        $collection = $this->_collectionFactory->create();
        $collection->addAttributeToSelect('name');
        $options = [];
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getId().'__'.$category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }*/

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getCategoryList() as $categoryId => $_category) {
                $this->addOption($_category['value'], addslashes($_category['label']));
            }
        }
        return parent::_toHtml();
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function _getCategoryList()
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