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
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class Data
 * @package Magenerds\BasePrice\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Holds the configuration path for conversion mapping
     */
    const CONVERSION_CONFIG_PATH = 'baseprice/general/conversion';

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PriceHelper $priceHelper
     * @param SerializerInterface $serializer
     */
    public function __construct(
        Context $context,
        PriceHelper $priceHelper,
        SerializerInterface $serializer
    ){
        $this->priceHelper = $priceHelper;
        $this->serializer = $serializer;

        parent::__construct($context);
    }

    /**
     * Returns the configured conversion rate
     *
     * @param $product Product
     * @return int
     */
    public function getConversion($product)
    {
        $productUnit = $product->getData('baseprice_product_unit');
        $referenceUnit = $product->getData('baseprice_reference_unit');

        $configArray = $this->serializer->unserialize($this->scopeConfig->getValue(
            self::CONVERSION_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
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
     * @param Product $product
     * @return mixed
     */
    public function getBasePriceText(Product $product)
    {
        $template = $this->scopeConfig->getValue(
            'baseprice/general/template',
            ScopeInterface::SCOPE_STORE
        );

        $basePrice = $this->getBasePrice($product);

        if (!$basePrice) return '';

        return str_replace(
            '{REF_UNIT}', $this->getReferenceUnit($product), str_replace(
            '{REF_AMOUNT}', $this->getReferenceAmount($product), str_replace(
                '{BASE_PRICE}', $this->priceHelper->currency($basePrice), $template)
        ));
    }

    /**
     * Returns the reference unit of current product
     *
     * @return string
     */
    public function getReferenceUnit(Product $product)
    {
        return __($product->getAttributeText('baseprice_reference_unit'));
    }

    /**
     * Returns the reference amount of current product
     *
     * @return float
     */
    public function getReferenceAmount(Product $product)
    {
        return round($product->getData('baseprice_reference_amount'), 2);
    }

    /**
     * Calculates the base price for given product
     *
     * @return float|string
     */
    public function getBasePrice(Product $product)
    {
        $productPrice = round($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(), PriceCurrencyInterface::DEFAULT_PRECISION);
        $conversion = $this->getConversion($product);
        $referenceAmount = $product->getData('baseprice_reference_amount');
        $productAmount = $product->getData('baseprice_product_amount');

        $basePrice = 0;
        if ($productPrice && $conversion && $referenceAmount && $productAmount && $productAmount > 0) {
            $basePrice = $productPrice * $conversion * $referenceAmount / $productAmount;
        }

        return $basePrice;
    }
}
