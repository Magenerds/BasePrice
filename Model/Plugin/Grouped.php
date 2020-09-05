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
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Model\Plugin;

use Magento\GroupedProduct\Model\Product\Type\Grouped as CoreGrouped;

/**
 * Class AfterPrice
 * @package Magenerds\BasePrice\Model\Plugin
 */
class Grouped extends CoreGrouped
{
    /**
     * @inheritdoc
     * overwritten in order to add base price attributes to select
     */
    public function getAssociatedProducts($product)
    {
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = [];

            $this->setSaleableStatus($product);

            $collection = $this->getAssociatedProductCollection(
                $product
            )->addAttributeToSelect(
                [
                    'name', 'price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id', 'image',
                    'baseprice_reference_amount', 'baseprice_product_amount', 'baseprice_product_unit', 'baseprice_reference_unit', 'baseprice_reference_amount'
                ]
            )->addFilterByRequiredOptions()->setPositionOrder()->addStoreFilter(
                $this->getStoreFilter($product)
            )->addAttributeToFilter(
                'status',
                ['in' => $this->getStatusFilters($product)]
            );

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }
}
