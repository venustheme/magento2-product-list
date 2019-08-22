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

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\Category $categoryModel
		)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->_categoryModel = $categoryModel;
		parent::__construct($context);
	}

	public function execute()
	{
		$this->_view->loadLayout();
		$params = $this->getRequest()->getParams();
		if (!$this->getRequest()->isAjax() || empty($params)) {
			return;
		}
		$collection = [];
		$category = $this->_categoryModel->load($params['tab']['category_id']);
		$collection = $category->getProductCollection()->addAttributeToSelect('*');
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
}