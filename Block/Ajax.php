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
namespace Ves\Productlist\Block;

class Ajax extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context     
     * @param \Magento\Framework\Url\Helper\Data     $urlHelper   
     * @param array                                  $data        
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        array $data = []
        ) {
        parent::__construct($context, $data);
    }

	public function getConfig($key, $default = '')
	{
		if($this->hasData($key))
		{
			return $this->getData($key);
		}
		return $default;
	}

	public function _toHtml(){
		if($template = $this->getConfig('template')){
			$this->setTemplate($template);
		}else{
			$layout_type = $this->getConfig('layout_type');
			if($layout_type == 'owl_carousel'){
                $this->setTemplate('Ves_Productlist::widget/owlcarousel/ajax.phtml');
            }
    		if($layout_type == 'bootstrap_carousel'){
                $this->setTemplate('Ves_Productlist::widget/bootstrapcarousel/ajax.phtml');
            }
		}
		return parent::_toHtml();
	}
	
	public function getProductHtml($data){
		$template = 'Ves_Productlist::widget/owlcarousel/items.phtml';
		
		if(isset($data['template'])) {
			$template = $data['template'];
		}elseif(isset($data['product_template'])){
			$template = $data['product_template'];
		}else{
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

	    }

	    unset($data['type']);
		unset($data['cache_lifetime']);
		unset($data['cache_tags']);

        $html = $this->getLayout()->createBlock('Ves\Productlist\Block\ProductList')->setData($data)->setTemplate($template)->toHtml();
        return $html;
    }
}