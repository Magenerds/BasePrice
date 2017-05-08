<?php
/**
 * Magenerds\BasePrice\Model\Plugin\AfterPrice
 *
 * Copyright (c) 2016 TechDivision GmbH
 * All rights reserved
 *
 * This product includes proprietary software developed at TechDivision GmbH, Germany
 * For more information see http://www.techdivision.com/
 *
 * To obtain a valid license for using this software please contact us at
 * license@techdivision.com
 */

/**
 * @category   Magenerds
 * @package    Magenerds_BasePrice
 * @subpackage Model
 * @copyright  Copyright (c) 2016 TechDivision GmbH (http://www.techdivision.com)
 * @version    ${release.version}
 * @link       http://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Model\Plugin;

/**
 * Class AfterPrice
 * @package Magenerds\BasePrice\Model\Plugin
 */
class AfterPrice
{
    /**
     * Holds the registry key for the product
     */
    const PRODUCT_REGISTRY_KEY = 'magenerds_baseprice_product';

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * @var array
     */
    protected $_afterPriceHtml = [];

    /**
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Registry $registry
    ){
        $this->_layout = $layout;
        $this->_coreRegistry = $registry;
    }

    /**
     * Plugin in order to get the current product for price rendering
     *
     * @param \Magento\Framework\Pricing\Render $subject
     * @param $renderHtml
     * @return string
     */
    public function beforeRender(
        \Magento\Framework\Pricing\Render $subject,
        $priceCode,
        \Magento\Catalog\Model\Product $saleableItem,
        array $arguments = []
    ){
        $this->_coreRegistry->unregister(self::PRODUCT_REGISTRY_KEY);
        $this->_coreRegistry->register(self::PRODUCT_REGISTRY_KEY, $saleableItem);
    }

    /**
     * Plugin for price rendering in order to display after price information
     *
     * @param \Magento\Framework\Pricing\Render $subject
     * @param $renderHtml string
     * @return string
     */
    public function afterRender(\Magento\Framework\Pricing\Render $subject, $renderHtml)
    {
        // check if html is empty
        if ($renderHtml == '' || str_replace("\n", "", $renderHtml) == '') {
            return $renderHtml;
        }

        return $renderHtml . $this->_getAfterPriceHtml();
    }

    /**
     * Renders and caches the after price html
     *
     * @return null|string
     */
    protected function _getAfterPriceHtml()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->_coreRegistry->registry(self::PRODUCT_REGISTRY_KEY);

        // check if product is available
        if (!$product) return '';

        // if a grouped product is given we need the current child
        if ($product->getTypeId() == 'grouped') {
            $product = $product->getPriceInfo()
                ->getPrice(\Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE)
                ->getMinProduct();
        }

        // check if price for current product has been rendered before
        if (!array_key_exists($product->getId(), $this->_afterPriceHtml)) {
            $afterPriceBlock = $this->_layout->createBlock(
                'Magenerds\BasePrice\Block\AfterPrice',
                'baseprice_afterprice_' . $product->getId(),
                ['product' => $product]
            );

            // use different templates for configurables and other product types
            if ($product->getTypeId() == 'configurable') {
                $templateFile = 'Magenerds_BasePrice::configurable/afterprice.phtml';
            } else {
                $templateFile = 'Magenerds_BasePrice::afterprice.phtml';
            }

            $afterPriceBlock->setTemplate($templateFile);
            $this->_afterPriceHtml[$product->getId()] = $afterPriceBlock->toHtml();
        }

        return $this->_afterPriceHtml[$product->getId()];
    }
}