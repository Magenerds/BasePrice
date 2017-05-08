<?php
/**
 * Magenerds\BasePrice\Model\Plugin\Grouped
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
                    'name', 'price',  'special_price', 'special_from_date', 'special_to_date',
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