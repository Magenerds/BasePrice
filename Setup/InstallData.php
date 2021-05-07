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
 * @subpackage Setup
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Setup;

use Magenerds\BasePrice\Helper\Data;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallData
 * @package Magenerds\BasePrice\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    protected $productAttributeOptionManagementInterface;

    /**
     * @var ResourceConfig
     */
    protected $configResource;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface
     * @param ResourceConfig $configResource
     * @param Config $eavConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface,
        ResourceConfig $configResource,
        Config $eavConfig,
        SerializerInterface $serializer
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productAttributeOptionManagementInterface = $productAttributeOptionManagementInterface;
        $this->configResource = $configResource;
        $this->eavConfig = $eavConfig;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var $eavSetup EavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'baseprice_product_amount',
            [
                'type' => 'decimal',
                'label' => 'Product Amount',
                'input' => 'text',
                'required' => false,
                'sort_order' => 1,
                'visible' => true,
                'note' => 'Leave empty to disable baseprice for this product',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'baseprice_product_unit',
            [
                'type' => 'varchar',
                'label' => 'Product unit',
                'input' => 'select',
                'required' => false,
                'sort_order' => 2,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'baseprice_reference_amount',
            [
                'type' => 'decimal',
                'label' => 'Reference Amount',
                'input' => 'text',
                'required' => false,
                'sort_order' => 3,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'baseprice_reference_unit',
            [
                'type' => 'varchar',
                'label' => 'Reference unit',
                'input' => 'select',
                'required' => false,
                'sort_order' => 4,
                'visible' => true,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        foreach (['baseprice_product_unit', 'baseprice_reference_unit'] as $attributeCode) {
            $attributeId = $eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'attribute_id');

            $eavSetup->addAttributeOption([
                'attribute_id' => $attributeId,
                'values' => ['kg', 'g', 'mg', 'l', 'ml', 'm', 'cm', 'mm']
            ]);
        }

        // clean cache so that newly created attributes will be loaded from database
        $this->eavConfig->clear();
        $this->setSystemConfiguration();
    }

    /**
     * Sets up the conversations for default units
     */
    protected function setSystemConfiguration()
    {
        // holds the conversion rates
        $dataTemplate = [
            'kg' => [
                [
                    'reference_unit' => 'g',
                    'conversion_rate' => '0.001'
                ],
                [
                    'reference_unit' => 'mg',
                    'conversion_rate' => '0.000001'
                ]
            ],
            'g' => [
                [
                    'reference_unit' => 'kg',
                    'conversion_rate' => '1000'
                ],
                [
                    'reference_unit' => 'mg',
                    'conversion_rate' => '0.001'
                ]
            ],
            'mg' => [
                [
                    'reference_unit' => 'kg',
                    'conversion_rate' => '1000000'
                ],
                [
                    'reference_unit' => 'g',
                    'conversion_rate' => '1000'
                ]
            ],
            'l' => [
                [
                    'reference_unit' => 'ml',
                    'conversion_rate' => '0.001'
                ]
            ],
            'ml' => [
                [
                    'reference_unit' => 'l',
                    'conversion_rate' => '1000'
                ]
            ],
            'm' => [
                [
                    'reference_unit' => 'cm',
                    'conversion_rate' => '0.01'
                ],
                [
                    'reference_unit' => 'mm',
                    'conversion_rate' => '0.001'
                ]
            ],
            'cm' => [
                [
                    'reference_unit' => 'm',
                    'conversion_rate' => '100'
                ],
                [
                    'reference_unit' => 'mm',
                    'conversion_rate' => '0.1'
                ]
            ],
            'mm' => [
                [
                    'reference_unit' => 'm',
                    'conversion_rate' => '1000'
                ],
                [
                    'reference_unit' => 'cm',
                    'conversion_rate' => '10'
                ]
            ],
        ];

        // get all attribute options for product unit
        $productUnitOptions = [];
        foreach ($this->productAttributeOptionManagementInterface->getItems('baseprice_product_unit') as $option) {
            $productUnitOptions[$option->getLabel()] = $option->getValue();
        }

        // get all attribute options for reference unit
        $referenceUnitOptions = [];
        foreach ($this->productAttributeOptionManagementInterface->getItems('baseprice_reference_unit') as $option) {
            $referenceUnitOptions[$option->getLabel()] = $option->getValue();
        }

        // iterate over attribute options in order to replace labels with option ids
        $data = [];
        foreach ($dataTemplate as $unit => $unitData) {
            foreach ($unitData as $key => $unitDataEntry) {
                $data[] = [
                    'product_unit' => $productUnitOptions[$unit],
                    'reference_unit' => $referenceUnitOptions[$unitDataEntry['reference_unit']],
                    'conversion_rate' => $unitDataEntry['conversion_rate']
                ];
            }
        }

        // save system configuration
        $this->configResource->saveConfig(
            Data::CONVERSION_CONFIG_PATH,
            $this->serializer->serialize($data),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
