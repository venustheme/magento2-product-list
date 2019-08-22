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
namespace Ves\Productlist\Model\ResourceModel\Reports\Product;
use Magento\Reports\Model\ResourceModel\Product\Collection as ProductCollection;
/**
 * Product Most Viewed Collection
 */
class Collection extends ProductCollection
{
    /**
     * Add Views Count
     *
     * @param string $from
     * @param string $to
     * @return \Ves\Productlist\Model\ResourceModel\Reports\Product\Collection
     */
    public function addViewsCount($from='', $to='')
    {
        /**
         * Getting event type id for catalog_product_view event
         */
        $eventTypes = $this->_eventTypeFactory->create()->getCollection();
        foreach ($eventTypes as $eventType) {
            if ($eventType->getEventName() == 'catalog_product_view') {
                $productViewEvent = (int)$eventType->getId();
                break;
            }
        }
        $this->getSelect()->reset()->from(
            ['report_table_views' => $this->getTable('report_event')],
            ['views' => 'COUNT(report_table_views.event_id)']
        )->join(
            ['e' => $this->getProductEntityTableName()],
            'e.entity_id = report_table_views.object_id'
        )->where(
            'report_table_views.event_type_id = ?',
            $productViewEvent
        )->group(
            'e.entity_id'
        )->order(
            'views ' . self::SORT_ORDER_DESC
        )->having(
            'COUNT(report_table_views.event_id) > ?',
            0
        );
        if ($from != '' && $to != '') {
            $this->getSelect()->where('logged_at >= ?', $from)->where('logged_at <= ?', $to);
        }
        return $this;
    }
}