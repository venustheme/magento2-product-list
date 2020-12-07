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
namespace Ves\Productlist\Model;

class Product extends \Magento\Framework\DataObject
{
     /**
     * Block cache tag
     */
     const CACHE_CATEGORY_TAG = 'ves_productlist_categorytab';

    /**
     * Page cache tag
     */
    const CACHE_TAG = 'ves_productlist_tab';
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_reportCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * \Magento\Framework\App\ResourceConnection
     * @var [type]
     */
    protected $_resource;

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration = null;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockFilter;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory 
     * @param \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection         
     * @param \Magento\Catalog\Model\Product\Visibility                      $catalogProductVisibility 
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface           $localeDate               
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager             
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                    $date                     
     * @param \Magento\Framework\App\ResourceConnection                      $resource                 
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State              $productState             
     * @param \Magento\Catalog\Model\ProductFactory                          $productFactory           
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface      $stockConfiguration       
     * @param \Magento\CatalogInventory\Helper\Stock                         $stockFilter              
     * @param array                                                          $data                     
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $reportCollection,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $productState,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        array $data = []
        ) {
        $this->_localeDate               = $localeDate;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_reportCollection         = $reportCollection;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_storeManager             = $storeManager;
        $this->date                      = $date;
        $this->_resource                 = $resource;
        $this->productState              = $productState;
        $this->productFactory            = $productFactory;
        $this->stockConfiguration        = $stockConfiguration;
        $this->stockFilter               = $stockFilter;
        parent::__construct($data);
    }

    /**
     * New arrival product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getNewarrivalProducts($config = [])
    {
        $todayStartOfDayDate = $this->_localeDate->date()
            ->setTime(0, 0)
            ->format('Y-m-d H:i:s');

        $todayEndOfDayDate = $this->_localeDate->date()
            ->setTime(23, 59, 59)
            ->format('Y-m-d H:i:s');

        $product = $this->productFactory->create();

        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter()->addAttributeToFilter(
            'news_from_date',
            [
            'or' => [
            0 => ['date' => true, 'to' => $todayEndOfDayDate],
            1 => ['is' => new \Zend_Db_Expr('null')],
            ]
            ],
            'left'
            )->addAttributeToFilter(
            'news_to_date',
            [
            'or' => [
            0 => ['date' => true, 'from' => $todayStartOfDayDate],
            1 => ['is' => new \Zend_Db_Expr('null')],
            ]
            ],
            'left'
            )->addAttributeToFilter(
            [
            ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
            ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
            ]
            )->addAttributeToSort(
            'news_from_date',
            'desc'
            );

            if(isset($config['pagesize']) && $config['pagesize']){
                $collection->setPageSize((int)$config['pagesize']);
            }

            if(isset($config['curpage']) && $config['curpage']){
                $collection->setCurPage((int)$config['curpage']);
            }
            
            $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
            return $collection;
        }

    /**
     * Latest product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getLatestProducts($config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter();

        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }

        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Best seller product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getBestsellerProducts($config = [])
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter()
        ->joinField(
            'qty_ordered',
            $this->_resource->getTableName('sales_bestsellers_aggregated_monthly'),
            'qty_ordered',
            'product_id=entity_id',
            'at_qty_ordered.store_id=' . (int)$storeId,
            'at_qty_ordered.qty_ordered > 0',
            'left'
        );
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }

        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Random product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getRandomProducts($config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter();

        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order('rand()');
        return $collection;
    }

    /**
     * Top rated product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getTopratedProducts($config = [])
    {
        $storeId = $this->_storeManager->getStore(true)->getId();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter()
        ->joinField(
            'ves_review',
            $this->_resource->getTableName('review_entity_summary'),
            'reviews_count',
            'entity_pk_value=entity_id',
            'at_ves_review.store_id=' . (int)$storeId,
            'ves_review > 0',
            'left'
        );
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order('ves_review DESC');
        return $collection;
    }

    /**
     * Speical product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getSpecialProducts($config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter()
        ->addMinimalPrice()
        ->addUrlRewrite()
        ->addTaxPercents()
        ->addFinalPrice();
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->group("e.entity_id");
        $collection->getSelect()->order("e.entity_id DESC")->where('price_index.final_price < price_index.price');
        return $collection;
    }

    /**
     * Speical product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getDealsProducts($config = [])
    {
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $product = $this->productFactory->create();
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $product->getResourceCollection();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*');

        $collection->addStoreFilter()->addAttributeToFilter(
            'special_from_date',
            [
                'or' => [
                    0 => ['date' => true, 'to' => $todayStartOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter(
            'special_to_date',
            [
                'or' => [
                    0 => ['date' => true, 'from' => $todayEndOfDayDate],
                    1 => ['is' => new \Zend_Db_Expr('null')],
                ]
            ],
            'left'
        )->addAttributeToFilter([
            ['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
            ['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
        ])
        ->addMinimalPrice()
        ->addUrlRewrite()
        ->addTaxPercents()
        ->addFinalPrice();
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->where('price_index.final_price < price_index.price');
        return $collection;
    }

    /**
     * Most viewed product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getMostViewedProducts($config = [])
    {
        /** @var $collection \Magento\Reports\Model\ResourceModel\Product\CollectionFactory */
        $collection = $this->_reportCollection->create()->addAttributeToSelect('*')->addViewsCount();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addStoreFilter();
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    /**
     * Featured product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getFeaturedProducts($config = [])
    {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        if (isset($config['categories'])) {
            if(!is_array($config['categories'])){
                $config['categories'] = explode(",", $config['categories']);
            }
            if ($this->productState->isFlatEnabled()) {
                $collection->joinField(
                    'category_id',
                    $this->_resource->getTableName('catalog_category_product'),
                    'category_id', 
                    'product_id = entity_id',
                    'category_id in (' . implode($config['categories'], ",") . ')' ,
                    'at_category_id.category_id == NULL',
                    'left'
                );
            } else {
                $collection->joinField(
                    'category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                    array('finset' => $config['categories']),
                ));
            }
        }
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds())
        ->addAttributeToSelect('*')
        ->addAttributeToFilter(array(array( 'attribute'=>'featured', 'eq' => '1')))
        ->addStoreFilter();
        if(isset($config['pagesize']) && $config['pagesize']){
            $collection->setPageSize((int)$config['pagesize']);
        }
        if(isset($config['curpage']) && $config['curpage']){
            $collection->setCurPage((int)$config['curpage']);
        }
        $collection->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
        return $collection;
    }

    public function getProductBySource($source_key, $config = [])
    {
        $collection = '';
        switch ($source_key) {
            case 'latest':
            $collection = $this->getLatestProducts($config);
            break;
            case 'new_arrival':
            $collection = $this->getNewarrivalProducts($config);
            break;
            case 'special':
            $collection = $this->getSpecialProducts($config);
            break;
            case 'most_popular':
            $collection = $this->getMostViewedProducts($config);
            break;
            case 'best_seller':
            $collection = $this->getBestsellerProducts($config);
            break;
            case 'top_rated':
            $collection = $this->getTopratedProducts($config);
            break;
            case 'random':
            $collection = $this->getRandomProducts($config);
            break;
            case 'featured':
            $collection = $this->getFeaturedProducts($config);
            break;
            case 'deals':
            $collection = $this->getDealsProducts($config);
            break;
        }

        if (!$this->stockConfiguration->isShowOutOfStock()) {
            $this->stockFilter->addInStockFilterToCollection($collection);
        }
        return $collection;
    }

}