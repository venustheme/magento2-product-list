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
namespace Ves\Productlist\Controller\Adminhtml\Widget;

class Save extends \Magento\Widget\Controller\Adminhtml\Widget\Instance\Save
{
    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if (!$widgetInstance) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        $params = $this->getRequest()->getPost('parameters', []);

		$field_pattern = ["pretext","pretext_html","shortcode","html","raw_html","content","tabs","latestmod_desc","custom_css","block_params"];
		$widget_types = ["Ves\BaseWidget\Block\Widget\Accordionbg"];

		foreach ($params as $k => $v) {
			if(0 < strpos($k, 'class') || 0 < strpos($k, 'Class')) {
				continue;
			}

			if(is_array($params[$k]) || !$this->isBase64Encoded($params[$k])) {
				if(in_array($k, $field_pattern) || preg_match("/^tabs(.*)/", $k) || preg_match("/^content_(.*)/", $k) || (preg_match("/^header_(.*)/", $k) && in_array($type, $widget_types))) {
					if(is_array($params[$k])){
						$params[$k] = base64_encode(serialize($params[$k]));
					}elseif(!$this->isBase64Encoded($params[$k])){
						$params[$k] = base64_encode($params[$k]);
					}
				}
			}
			
		}

        $widgetInstance->setTitle(
            $this->getRequest()->getPost('title')
        )->setStoreIds(
            $this->getRequest()->getPost('store_ids', [0])
        )->setSortOrder(
            $this->getRequest()->getPost('sort_order', 0)
        )->setPageGroups(
            $this->getRequest()->getPost('widget_instance')
        )->setWidgetParameters(
            $params
        );
        try {
            $widgetInstance->save();
            $this->messageManager->addSuccess(__('The widget instance has been saved.'));
            if ($this->getRequest()->getParam('back', false)) {
                $this->_redirect(
                    'adminhtml/*/edit',
                    ['instance_id' => $widgetInstance->getId(), '_current' => true]
                );
            } else {
                $this->_redirect('adminhtml/*/');
            }
            return;
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->_logger->critical($exception);
            $this->_redirect('adminhtml/*/edit', ['_current' => true]);
            return;
        }
    }

	public function isBase64Encoded($data) {
		if(base64_encode(base64_decode($data)) === $data){
			return true;
		}
		return false;
	}
}