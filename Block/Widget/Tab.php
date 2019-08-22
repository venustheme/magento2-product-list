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
namespace Ves\Productlist\Block\Widget;

class Tab extends \Magento\Catalog\Block\Product\AbstractProduct implements \Magento\Widget\Block\BlockInterface
{

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $pager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Report Product collection factory
     *
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_reportCollection;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;

    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;

    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Ves\Productlist\Model\Product
     */
    protected $_productModel;

    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $_blockModel;

    protected $_conditionCollection;

   /**
    * @param \Magento\Catalog\Block\Product\Context                    $context                  
    * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory 
    * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection         
    * @param \Magento\Catalog\Model\Product\Visibility                 $catalogProductVisibility 
    * @param \Magento\Framework\App\Http\Context                       $httpContext              
    * @param \Magento\Rule\Model\Condition\Sql\Builder                 $sqlBuilder               
    * @param \Magento\CatalogWidget\Model\Rule                         $rule                     
    * @param \Magento\Widget\Helper\Conditions                         $conditionsHelper         
    * @param \Ves\Productlist\Model\Product                            $productModel             
    * @param \Magento\Cms\Model\Block                                  $blockModel               
    * @param array                                                     $data                     
    */
   public function __construct(
    \Magento\Catalog\Block\Product\Context $context,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection,
    \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
    \Magento\Framework\App\Http\Context $httpContext,
    \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
    \Magento\CatalogWidget\Model\Rule $rule,
    \Magento\Widget\Helper\Conditions $conditionsHelper,
    \Ves\Productlist\Model\Product $productModel,
    \Magento\Cms\Model\Block $blockModel,
    array $data = []
    ) {
    $this->_productCollectionFactory = $productCollectionFactory;
    $this->_reportCollection = $reportCollection;
    $this->_catalogProductVisibility = $catalogProductVisibility;
    $this->httpContext = $httpContext;
    $this->sqlBuilder = $sqlBuilder;
    $this->rule = $rule;
    $this->conditionsHelper = $conditionsHelper;
    $this->_productModel = $productModel;
    $this->_blockModel = $blockModel;
    parent::__construct(
        $context,
        $data
        );
}

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $conditions = $this->getData('tabs');
        if($tabs = $this->getConfig('tabs')){
            $conditions = $conditions.".".md5($tabs);
        }
        return [
        'VES_PRODUCTS_LIST_TAB_WIDGET',
        $this->_storeManager->getStore()->getId(),
        $this->_design->getDesignTheme()->getId(),
        $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
        $conditions
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->_conditionCollection = $this->getProductsByCondition();
        $template = $this->getConfig('block_template');
        $layout_type = $this->getConfig('layout_type');
        if($template){
            $this->setTemplate($template);
        }else{
            if($layout_type == 'owl_carousel'){
                $this->setTemplate('widget/owlcarousel/tab.phtml');    
            }
            if($layout_type == 'bootstrap_carousel'){
                $this->setTemplate('widget/bootstrapcarousel/tab.phtml');    
            }
        }
        $this->_eventManager->dispatch(
            'ves_product_list_collection',
            ['product_list' => $this]
            );
        return parent::_beforeToHtml();
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductsByCondition()
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
        ->addStoreFilter()
        ->setPageSize($this->getConfig('number_item',12))
        ->setCurPage(1);

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);
        return $collection;
    }

    public function getProductHtml($data){
        $template = '';
        $layout_type = $this->getConfig('layout_type');
        if($layout_type == 'owl_carousel'){
            $template = 'Ves_Productlist::widget/owlcarousel/items.phtml';
        }
        if($layout_type == 'bootstrap_carousel'){
            $template = 'Ves_Productlist::widget/bootstrapcarousel/items.phtml';
        }
        if($productTemplate = $this->getConfig('product_template')){
            $template = $productTemplate;
        }
        unset($data['type']);
        unset($data['cache_lifetime']);
        unset($data['cache_tags']);
        $html = $this->getLayout()->createBlock('Ves\Productlist\Block\ProductList')->setData($data)->setTemplate($template)->toHtml();
        return $html;
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    protected function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
        ? $this->getData('conditions_encoded')
        : $this->getData('conditions');
        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        return $this->rule->getConditions();
    }

    public function getConfig($key, $default = '')
    {
        if($this->hasData($key) && $this->getData($key))
        {
            return $this->getData($key);
        }
        return $default;
        
    }

    public function getCmsBlockModel(){
        return $this->_blockModel;
    }

    public function getTabs(){
        $tabs = $this->getConfig('tabs');
        if($tabs){
            $a = '';
            if(base64_decode($tabs, true) == true){
                $tabs = str_replace(" ", "+", $tabs);
                $tabs = base64_decode($tabs);
                if(base64_decode($tabs, true) == true) {
                    $tabs = base64_decode($tabs);
                }
            }
            try{
                $tabs = unserialize($tabs);
            }catch(\Exception $e){
                die($this->getConfig('tabs'));
            }
            if(isset($tabs['__empty'])) unset($tabs['__empty']);
            usort($tabs,function($a, $b){
                if ($a["position"] == $b["position"]) {
                    return 0;
                }
                return ($a["position"] < $b["position"]) ? -1 : 1;
            }); 
            return $tabs;
        }
        return false;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Catalog\Model\Product::CACHE_TAG];
    }

    public function getProductsBySource($source_key){
        $config = [];
        $config['pagesize'] = $this->getConfig('number_item',12);
        $collection = $this->_productModel->getProductBySource($source_key, $config);
        if($this->_conditionCollection->count()){
            $conditionProductIds = $this->_conditionCollection->getAllIds();
            $collection->addAttributeToFilter('entity_id',array('in' => $conditionProductIds));
        }
        return $collection;
    }

    public function getAjaxUrl(){
        return $this->getUrl('productlist/index/product');
    }
}
