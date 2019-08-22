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
namespace Ves\Productlist\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
	protected $_filterProvider;

	/** @var \Magento\Store\Model\StoreManagerInterface */
	protected $_storeManager;

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $registry
		){
		parent::__construct($context);
		$this->_filterProvider = $filterProvider;
		$this->_storeManager = $storeManager;
		$this->_coreRegistry = $registry;
	}

	/**
     * Check product is new
     *
     * @param  Mage_Catalog_Model_Product $_product
     * @return bool
     */
	public function checkProductIsNew($_product = null) {
		$from_date = $_product->getNewsFromDate();
		$to_date = $_product->getNewsToDate();
		$is_new = false;
		$is_new = $this->isNewProduct($from_date, $to_date);
		$today = strtotime("now");

		if(!($from_date && $to_date)){
			return false;
		}

		if ($from_date && $to_date) {
			$from_date = strtotime($from_date);
			$to_date = strtotime($to_date);
			if ($from_date <= $today && $to_date >= $today) {
				$is_new = true;
			}
		}
		elseif ($from_date && !$to_date) {
			$from_date = strtotime($from_date);
			if ($from_date <= $today) {
				$is_new = true;
			}
		}elseif (!$from_date && $to_date) {
			$to_date = strtotime($to_date);
			if ($to_date >= $today) {
				$is_new = true;
			}
		}
		return $is_new;
	}

	public function isNewProduct( $created_date, $num_days_new = 3) {
		$check = false;

		$startTimeStamp = strtotime($created_date);
		$endTimeStamp = strtotime("now");

		$timeDiff = abs($endTimeStamp - $startTimeStamp);
        $numberDays = $timeDiff/86400;// 86400 seconds in one day

        // and you might want to convert to integer
        $numberDays = intval($numberDays);
        if($numberDays <= $num_days_new) {
        	$check = true;
        }

        return $check;
    }

    public function subString($text, $length = 100, $replacer = '...', $is_striped = true) {
    	$text = ($is_striped == true) ? strip_tags($text) : $text;
    	if (strlen($text) <= $length) {
    		return $text;
    	}
    	$text = substr($text, 0, $length);
    	$pos_space = strrpos($text, ' ');
    	return substr($text, 0, $pos_space) . $replacer;
    }

	public function filter($str)
	{
		$html = $this->decodeWidgets($str);
		$html = $this->decodeImg($html);
		$html = $this->_filterProvider->getPageFilter()->filter($html);
		return $html;
	}

	public function getCustomerDataUrl(){
		$url = $this->_storeManager
		->getStore()
		->getUrl('customer/section/load',["update_section_id"=>true,"sections"=>"cart"]);
		return $url;
	}

	public function getRefreshCartUrl(){
		$url = $this->_storeManager
		->getStore()
		->getUrl('checkout/cart/add', ['ves'=>1, 'refresh'=>1]);
		return $url;
	}

	public function getAddToCartUrl(\Magento\Catalog\Model\Product $_product){
		$url = $this->_storeManager
		->getStore()
		->getUrl('productlist/index/quickview',["id"=>$_product->getId()]);
		return $url;
	}

	public function getCoreRegistry(){
		return $this->_coreRegistry;
	}

	public function decodeWidgets($str){
		$result = '';
		$imgs = [];
		$firstPosition = 0;
		$i = 0;
		$count = substr_count($str, 'title="{{widget');
		for ($i=0; $i < $count; $i++) {
			if($firstPosition==0) $tmp = $firstPosition;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $firstPosition;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if( strpos($img, '{{widget')){
				$f = strpos($img, "{{widget", 0);
				$n = strpos($img, '"', $f);
				$widgetCode = substr($img, $f, ($n-$f));
				$widgetHtml = $this->filter(html_entity_decode($widgetCode));
				if($i==0) $result = $str;
				$result = str_replace($img, $widgetHtml, $result);
				$str = str_replace($img, '', $str);
			}
		}

		$count = substr_count($str, 'title="{widget');
		for ($i=0; $i < $count; $i++) {
			if($firstPosition==0) $tmp = $firstPosition;
			$firstPosition = strpos($str, "<img", $tmp);
			$nextPosition = strpos($str, "/>", $firstPosition);
			$tmp = $firstPosition;
			$length = $nextPosition - $firstPosition;
			$img = substr($str, $firstPosition, $length+2);
			if( strpos($img, '{widget')){
				$f = strpos($img, "{widget", 0);
				$n = strpos($img, '"', $f);
				$widgetCode = '{' . substr($img, $f, ($n-$f)) . '}';
				$widgetHtml = $this->filter(html_entity_decode($widgetCode));
				if($i==0) $result = $str;
				$result = str_replace($img, $widgetHtml, $result);
				$str = str_replace($img, '', $str);
			}
		}

		if($result!=''){
			return $result;
		}
		return $str;
	}

	public function decodeImg($str){
        $count = substr_count($str, "<img");
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $firstPosition = 0;
        for ($i=0; $i < $count; $i++) {
            if($firstPosition==0) $tmp = $firstPosition;
            $firstPosition = strpos($str, "<img", $tmp);
            $nextPosition = strpos($str, "/>", $firstPosition);
            $tmp = $nextPosition;
            $length = $nextPosition - $firstPosition;
            $img = substr($str, $firstPosition, $length+2);
            $newImg = $this->_filterProvider->getPageFilter()->filter($img);
            $f = strpos($newImg, 'src="', 0)+5;
            $n = strpos($newImg, '"', $f+5);
            $src = substr($newImg, $f, ($n-$f));
            if( !strpos($img, 'placeholder.gif')){
                $src1 = '';
                if( strpos($newImg, '___directive')){
                    $e = strpos($newImg, '___directive', 0) + 13;
                    $e1 = strpos($newImg, '/key', 0);
                    $src1 = substr($newImg, $e, ($e1-$e));
                    $src1 = base64_decode($src1);
                }else{
                    $mediaP = strpos($src, "wysiwyg", 0);
                    $src1 = substr($src, $mediaP);
                    $src1 = '{{media url="'.$src1.'"}}';
                }
                $newImg = str_replace($src, $src1, $newImg);
                $str = str_replace($img, $newImg, $str);
            }
        }
        return $str;
    }
}