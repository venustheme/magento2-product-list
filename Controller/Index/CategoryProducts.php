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
namespace Ves\Productlist\Controller\Index;

class CategoryProducts extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
	protected $_categoryModel;

	/**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
	protected $_localeDate;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\Category $categoryModel,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
		)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->_categoryModel = $categoryModel;
		$this->_localeDate               = $localeDate;
		parent::__construct($context);
	}

	public function execute()
	{
		$this->_view->loadLayout();
		$params = $this->getRequest()->getParams();
		if (!$this->getRequest()->isAjax() || empty($params)) {
			return;
		}
		$number_item = isset($params['number_item'])?(int)$params['number_item']:5;
		$productsource = isset($params['productsource'])?$params['productsource']:'latest';
		$collection = $this->getProductCollecionBySource($params['tab']['category_id'], $productsource, $number_item);
		$data = [];
		$_productCollection = [];

		// OWL Carousel
      	// Convert to multiple row
		if($params['layout_type'] == 'owl_carousel'){
			$column = 6;
			$number_item_percolumn = $params['number_item_percolumn'];
			$large_max_items = $params['large_max_items'];
			$large_items = $params['large_items'];
			$total = $collection->count();
			if($total%$number_item_percolumn == 0){
				$column = $total/$number_item_percolumn;
			}else{
				$column = floor($total/$number_item_percolumn)+1;
			}
			if($column<$large_max_items) $column = $large_max_items;
			$i = $x = 0;
			foreach ($collection as $_product) {
				if($i<$column){
					$i++;
				}else{
					$i = 1;
					$x++;
				}
				$_productCollection[$i][$x] = $_product;
			}
		}
		// Bootstrap Carousel
		if($params['layout_type'] == 'bootstrap_carousel'){
			$_productCollection = $collection;
		}
		unset($params['type']);
		unset($params['cache_lifetime']);
		unset($params['cache_tags']);
		$data['layout_type'] = $params['layout_type'];
		$data['tab'] = $params['tab'];
		$data['ajaxBlockId'] = $params['ajaxBlockId'];
		$data['html'] = $this->_view->getLayout()->createBlock('Ves\Productlist\Block\Ajax')
		->assign('collection',$_productCollection)
		->assign('tab', $data['tab'])
		->setData($params)->toHtml();
		$this->getResponse()->representJson(
			$this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($data)
			);
	}

	public function getProductCollecionBySource($category_id, $source_key = "latest", $pagesize=5, $curpage = 1){
		$collection = [];
		$category = $this->_categoryModel->load($category_id);
		$collection = $category->getProductCollection()->addAttributeToSelect('*');
		switch ($source_key) {
            case 'latest':
				//Write code at here
				$collection->addStoreFilter()
							->setPageSize($pagesize)
							->setCurPage($curpage)
							->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
            break;
            case 'new_arrival':
				//Write code at here
				$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
				$todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
				$collection->addStoreFilter()->addAttributeToFilter(
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
							)
							->setPageSize($pagesize)
							->setCurPage($curpage)
							->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
            break;
            case 'special':
				//Write code at here
				$collection->addAttributeToSelect('*')
						->addStoreFilter()
						->addMinimalPrice()
						->addUrlRewrite()
						->addTaxPercents()
						->addFinalPrice();

				$collection->setPageSize($pagesize)
						->setCurPage($curpage)
						->getSelect()->group("e.entity_id");

				$collection->getSelect()->order("e.entity_id DESC")->where('price_index.final_price < price_index.price');
            break;
            case 'most_popular':
            //Write code at here
            break;
            case 'best_seller':
            //Write code at here
            break;
            case 'top_rated':
            //Write code at here
            break;
			case 'random':
				//Write code at here
				$collection->addStoreFilter()
							->setPageSize($pagesize)
							->setCurPage($curpage)
							->getSelect()->group("e.entity_id");
				$collection->getSelect()->order('rand()');
            break;
            case 'featured':
				//Write code at here
				$collection->addAttributeToFilter(array(array( 'attribute'=>'featured', 'eq' => '1')))
							->addStoreFilter()
							->setPageSize($pagesize)
							->setCurPage($curpage)
							->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
            break;
            case 'deals':
				//Write code at here
				$todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
				$todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
				$collection->addStoreFilter()->addAttributeToFilter(
								'special_from_date',
								[
								'or' => [
								0 => ['date' => true, 'to' => $todayEndOfDayDate],
								1 => ['is' => new \Zend_Db_Expr('null')],
								]
								],
								'left'
								)->addAttributeToFilter(
								'special_to_date',
								[
								'or' => [
								0 => ['date' => true, 'from' => $todayStartOfDayDate],
								1 => ['is' => new \Zend_Db_Expr('not null')],
								]
								],
								'left'
								)->addAttributeToFilter(
								[
								['attribute' => 'special_from_date', 'is' => new \Zend_Db_Expr('not null')],
								['attribute' => 'special_to_date', 'is' => new \Zend_Db_Expr('not null')],
								]
								)->addAttributeToSort(
								'special_from_date',
								'desc'
								)
								->setPageSize($pagesize)
								->setCurPage($curpages)
								->getSelect()->order("e.entity_id DESC")->group("e.entity_id");
            break;
        }
		return $collection;
	}
	
}