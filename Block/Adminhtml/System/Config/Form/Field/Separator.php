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
namespace Ves\Productlist\Block\Adminhtml\System\Config\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Separator extends Template implements RendererInterface
{	
	/**
	 * @param  AbstractElement $element 
	 * @return string
	 */
	public function render(AbstractElement $element)
	{
		$html = '';
		$html .= '<div class="system-heading" style="border-bottom: 1px solid #dfdfdf;font-size: 1.7rem;color: #666;border-left: #CCC solid 5px;padding: 2px 12px;text-align: left !important;margin-left: 5%;margin-top: 20px;margin-bottom: 20px;">';
		$html .= $element->getLabel();
		$html .= '</div>';
		return $html;
	}
}