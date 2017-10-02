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
 * @copyright  Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @link       http://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 * @author     Jürgen "Atlan" Schuch <juergen@atmage.de>
 */
namespace Magenerds\BasePrice\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class InstallData
 * @package Magenerds\BasePrice\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterface
     */
    protected $_productAttributeOptionManagementInterface;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_configResource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * Constructor
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface
     * @param \Magento\Config\Model\ResourceModel\Config $configResource
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface,
        \Magento\Config\Model\ResourceModel\Config $configResource,
        \Magento\Eav\Model\Config $eavConfig
    ){
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_productAttributeOptionManagementInterface = $productAttributeOptionManagementInterface;
        $this->_configResource = $configResource;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var $eavSetup EavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'baseprice_product_amount',
            [
                'type' => 'decimal',
                'label' => 'Product Amount',
                'input' => 'text',
                'required' => false,
                'sort_order' => 1,
                'visible' => true,
                'note' => 'Leave empty to disable baseprice for this product',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'baseprice_product_unit',
            [
                'type' => 'varchar',
                'label' => 'Product unit',
                'input' => 'select',
                'required' => false,
                'sort_order' => 2,
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'baseprice_reference_amount',
            [
                'type' => 'decimal',
                'label' => 'Reference Amount',
                'input' => 'text',
                'required' => false,
                'sort_order' => 3,
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'baseprice_reference_unit',
            [
                'type' => 'varchar',
                'label' => 'Reference unit',
                'input' => 'select',
                'required' => false,
                'sort_order' => 4,
                'visible' => true,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Base Price',
                'used_in_product_listing' => true,
                'visible_on_front' => false
            ]
        );

        foreach (['baseprice_product_unit', 'baseprice_reference_unit'] as $attributeCode) {
            $attributeId = $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, 'attribute_id');

            $eavSetup->addAttributeOption([
                'attribute_id' => $attributeId,
                'values' => ['kg', 'g', 'mg', 'l', 'ml', 'm', 'cm', 'mm']
            ]);
        }

        // clean cache so that newly created attributes will be loaded from database
        $this->_eavConfig->clear();
        $this->_setSystemConfiguration();
    }

    /**
     * Sets up the conversations for default units
     */
    protected function _setSystemConfiguration()
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
                    'conversion_rate' => '0.000001'
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
                    'conversion_rate' => '0.001'
                ],
                [
                    'reference_unit' => 'mm',
                    'conversion_rate' => '0.000001'
                ]
            ],
            'cm' => [
                [
                    'reference_unit' => 'm',
                    'conversion_rate' => '1000'
                ],
                [
                    'reference_unit' => 'mm',
                    'conversion_rate' => '0.001'
                ]
            ],
            'mm' => [
                [
                    'reference_unit' => 'm',
                    'conversion_rate' => '1000000'
                ],
                [
                    'reference_unit' => 'cm',
                    'conversion_rate' => '1000'
                ]
            ],
        ];

        // get all attribute options for product unit
        $productUnitOptions = [];
        foreach ($this->_productAttributeOptionManagementInterface->getItems('baseprice_product_unit') as $option) {
            $productUnitOptions[$option->getLabel()] = $option->getValue();
        }

        // get all attribute options for reference unit
        $referenceUnitOptions = [];
        foreach ($this->_productAttributeOptionManagementInterface->getItems('baseprice_reference_unit') as $option) {
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

        $objectManager = ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();

        if($version < 2.2) {
            $serializedData = serialize($data);
        } else {
            $serializedData = $objectManager->get(Json::class)->serialize($data);
        }

        // save system configuration
        $this->_configResource->saveConfig(
            \Magenerds\BasePrice\Helper\Data::CONVERSION_CONFIG_PATH,
            $serializedData,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }
}
