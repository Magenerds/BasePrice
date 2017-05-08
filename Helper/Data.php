<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

/**
 * @category   Magenerds
 * @package    Magenerds_BasePrice
 * @subpackage Helper
 * @copyright  Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @link       http://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Helper;

/**
 * Class Data
 * @package Magenerds\BasePrice\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Holds the configuration path for conversion mapping
     */
    const CONVERSION_CONFIG_PATH = 'baseprice/general/conversion';

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    ){
        $this->_priceHelper = $priceHelper;
        parent::__construct($context);
    }

    /**
     * Returns the configured conversion rate
     *
     * @param $product \Magento\Catalog\Model\Product
     * @return int
     */
    public function getConversion($product)
    {
        $productUnit = $product->getData('baseprice_product_unit');
        $referenceUnit = $product->getData('baseprice_reference_unit');

        $configArray = unserialize($this->scopeConfig->getValue(
            self::CONVERSION_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));

        foreach ($configArray as $config) {
            if ($config['product_unit'] == $productUnit
                && $config['reference_unit'] == $referenceUnit)
            {
                return $config['conversion_rate'];
            }
        }

        return 1;
    }

    /**
     * Returns the base price text according to the configured template
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return mixed
     */
    public function getBasePriceText(\Magento\Catalog\Model\Product $product)
    {
        $template = $this->scopeConfig->getValue(
            'baseprice/general/template',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $basePrice = $this->getBasePrice($product);

        if (!$basePrice) return '';

        return str_replace(
            '{REF_UNIT}', $this->getReferenceUnit($product), str_replace(
            '{REF_AMOUNT}', $this->getReferenceAmount($product), str_replace(
            '{BASE_PRICE}', $this->_priceHelper->currency($basePrice), $template)
        ));
    }

    /**
     * Returns the reference unit of current product
     *
     * @return string
     */
    public function getReferenceUnit(\Magento\Catalog\Model\Product $product)
    {
        return $product->getAttributeText('baseprice_reference_unit');
    }

    /**
     * Returns the reference amount of current product
     *
     * @return float
     */
    public function getReferenceAmount(\Magento\Catalog\Model\Product $product)
    {
        return round($product->getData('baseprice_reference_amount'), 2);
    }

    /**
     * Calculates the base price for given product
     *
     * @return float|string
     */
    public function getBasePrice(\Magento\Catalog\Model\Product $product)
    {
        $productPrice = $product->getFinalPrice();
        $conversion = $this->getConversion($product);
        $referenceAmount = $product->getData('baseprice_reference_amount');
        $productAmount = $product->getData('baseprice_product_amount');

        $basePrice = 0;
        if ($productPrice && $conversion && $referenceAmount && $productAmount) {
            $basePrice = $productPrice * $conversion * $referenceAmount / $productAmount;
        }

        return $basePrice;
    }
}
