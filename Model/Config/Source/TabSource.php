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
class TabSource implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $sources = [];
        $sources[] = [
        'value' => 'latest',
        'label' => 'Latest'];
        $sources[] = [
        'value' => 'new_arrival',
        'label' => 'New Arrival'];
        $sources[] = [
        'value' => 'special',
        'label' => 'Special'];
        $sources[] = [
        'value' => 'most_popular',
        'label' => 'Most Popular'];
        $sources[] = [
        'value' => 'best_seller',
        'label' => 'Best Seller'];
        $sources[] = [
        'value' => 'top_rated',
        'label' => 'Top Rated'];
        $sources[] = [
        'value' => 'random',
        'label' => 'Random'];
        $sources[] = [
        'value' => 'featured',
        'label' => 'Featured'];
        $sources[] = [
        'value' => 'deals',
        'label' => 'Deals'];
        return $sources;
    }
}