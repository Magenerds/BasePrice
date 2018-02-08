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
 * @subpackage Model
 * @copyright  Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @link       http://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Model\Plugin;

use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Framework\Pricing\Render;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\View\LayoutInterface;

/**
 * Class AfterPrice
 * @package Magenerds\BasePrice\Model\Plugin
 */
class AfterPrice
{
    /**
     * Hold final price code
     *
     * @var string
     */
    const FINAL_PRICE = 'final_price';

    /**
     * Hold tier price code
     *
     * @var string
     */
    const TIER_PRICE = 'tier_price';

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var []
     */
    protected $afterPriceHtml = [];

    /**
     * @param LayoutInterface $layout
     */
    public function __construct(
        LayoutInterface $layout
    ){
        $this->layout = $layout;
    }

    /**
     * Plugin for price rendering in order to display after price information
     *
     * @param Render $subject
     * @param $renderHtml string
     * @return string
     */
    public function aroundRender(Render $subject, \Closure $closure, ...$params)
    {
        // run default render first
        $renderHtml = $closure(...$params);

        try{
            // Get Price Code and Product
            list($priceCode, $productInterceptor) = $params;
            $emptyTierPrices = empty($productInterceptor->getTierPrice());

            // If it is final price block and no tier prices exist set additional render
            // If it is tier price block and tier prices exist set additional render
            if ((static::FINAL_PRICE === $priceCode && $emptyTierPrices) || (static::TIER_PRICE === $priceCode && !$emptyTierPrices)) {
                $renderHtml .= $this->getAfterPriceHtml(productInterceptor);
            }
        } catch (\Exception $ex) {
            // if an error occurs, just render the default since it is preallocated
            return $renderHtml;
        }

        return $renderHtml;
    }

    /**
     * Renders and caches the after price html
     *
     * @return null|string
     */
    protected function getAfterPriceHtml(SaleableInterface $product)
    {
        // check if product is available
        if (!$product) return '';

        // if a grouped product is given we need the current child
        if ($product->getTypeId() == 'grouped') {
            $product = $product->getPriceInfo()
                ->getPrice(FinalPrice::PRICE_CODE)
                ->getMinProduct();
        }

        // check if price for current product has been rendered before
        if (!array_key_exists($product->getId(), $this->afterPriceHtml)) {
            $afterPriceBlock = $this->layout->createBlock(
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
            $this->afterPriceHtml[$product->getId()] = $afterPriceBlock->toHtml();
        }

        return $this->afterPriceHtml[$product->getId()];
    }
}
