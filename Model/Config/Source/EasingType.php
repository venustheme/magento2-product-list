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
class EasingType implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
	{

		$easing_types = [
			"swing",
			"easeInQuad",
			"easeOutQuad",
			"easeInOutQuad",
			"easeInCubic",
			"easeOutCubic",
			"easeInOutCubic",
			"easeInQuart",
			"easeOutQuart",
			"easeInOutQuart",
			"easeInQuint",
			"easeOutQuint",
			"easeInOutQuint",
			"easeInSine",
			"easeOutSine",
			"easeInOutSine",
			"easeInExpo",
			"easeOutExpo",
			"easeInOutExpo",
			"easeInCirc",
			"easeOutCirc",
			"easeInOutCirc",
			"easeInElastic",
			"easeOutElastic",
			"easeInOutElastic",
			"easeInBack",
			"easeOutBack",
			"easeInOutBack",
			"easeInBounce",
			"easeOutBounce",
			"easeInOutBounce"];
		$easingType = [];
		foreach ($easing_types as $key => $value) {
			$type = [];
			$type['label'] = $type['value'] = $value;
			$easingType[] = $type;
		}
		return $easingType;
	}
}