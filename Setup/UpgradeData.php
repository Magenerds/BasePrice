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
 */
namespace Magenerds\BasePrice\Setup;


use Magenerds\BasePrice\Helper\Data;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Config\Model\ResourceModel\Config as ResourceConfig;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    const UNIT_ATTRIBUTE_CODES = ['baseprice_product_unit', 'baseprice_reference_unit'];

    const OPTIONS_SORT_ORDER = ['kg', 'g', 'mg', 'l', 'ml', 'km', 'm', 'cm', 'mm', 'qm', 'cbm', 'pc'];

    /**
     * Conversion rates from InstallData.
     * Used to add all conversion rates when first installing the module.
     * @see \Magenerds\BasePrice\Setup\InstallData::setSystemConfiguration()
     */
    const DEFAULT_DATA_TEMPLATE = [
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
                    'conversion_rate' => '0.001'
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

    /**
     * @var EavSetupFactory
     */
    public $eavSetupFactory;

    /**
     * @var ProductAttributeOptionManagementInterface
     */
    private $productAttributeOptionManagementInterface;

    /**
     * @var ResourceConfig
     */
    private $configResource;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;


    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface,
        ResourceConfig $configResource,
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        EavConfig $eavConfig,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->productAttributeOptionManagementInterface = $productAttributeOptionManagementInterface;
        $this->configResource = $configResource;
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $options = ['km', 'qm', 'cbm', 'pc'];

            // holds the conversion rates
            $dataTemplate = [
                'km' => [
                    [
                        'reference_unit' => 'm',
                        'conversion_rate' => '0.001',
                    ],
                    [
                        'reference_unit' => 'cm',
                        'conversion_rate' => '0.00001',
                    ],
                    [
                        'reference_unit' => 'mm',
                        'conversion_rate' => '0.000001',
                    ],
                ],
                'm' => [
                    [
                        'reference_unit' => 'km',
                        'conversion_rate' => '1000',
                    ],
                ],
                'cm' => [
                    [
                        'reference_unit' => 'km',
                        'conversion_rate' => '100000',
                    ],
                ],
                'mm' => [
                    [
                        'reference_unit' => 'km',
                        'conversion_rate' => '1000000',
                    ],
                ],

            ];

            $this->addOption($eavSetup, $options);
            $this->eavConfig->clear();
            $this->addToSystemConfiguration($dataTemplate);
            $this->sortOptions($setup);
        }
        $setup->endSetup();
    }

    /**
     * @param $eavSetup
     * @param array $options
     */
    protected function addOption($eavSetup, array $options)
    {
        foreach (self::UNIT_ATTRIBUTE_CODES as $attributeCode) {
            $attributeId = $eavSetup->getAttribute(Product::ENTITY, $attributeCode, 'attribute_id');

            $eavSetup->addAttributeOption([
                'attribute_id' => $attributeId,
                'values' => $options,
            ]);
        }
    }

    /**
     * Sets conversations for added default units
     *
     * @param array $dataTemplate
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function addToSystemConfiguration(array $dataTemplate)
    {
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

        //Config value is not available during first installation -> maybe config cache
        if (is_null($this->scopeConfig->getValue(Data::CONVERSION_CONFIG_PATH))) {
            $data = [];
            $dataTemplate = array_merge_recursive(self::DEFAULT_DATA_TEMPLATE, $dataTemplate);
        } else {
            $data = $this->serializer->unserialize($this->scopeConfig->getValue(Data::CONVERSION_CONFIG_PATH));
        }

        // iterate over attribute options in order to replace labels with option ids
        foreach ($dataTemplate as $unit => $unitData) {
            foreach ($unitData as $key => $unitDataEntry) {
                $data[] = [
                    'product_unit' => $productUnitOptions[$unit],
                    'reference_unit' => $referenceUnitOptions[$unitDataEntry['reference_unit']],
                    'conversion_rate' => $unitDataEntry['conversion_rate'],
                ];
            }
        }

        //save system configuration
        $this->configResource->saveConfig(
            Data::CONVERSION_CONFIG_PATH,
            $this->serializer->serialize($data),
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
    }

    /**
     * Sort options of the attribute(s) based on self::OPTIONS_SORT_ORDER
     *
     * @param ModuleDataSetupInterface $setup
     * @param array $attributes
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sortOptions(ModuleDataSetupInterface $setup, array $attributes = [])
    {
        if (empty($attributes)) {
            $attributes = self::UNIT_ATTRIBUTE_CODES;
        }

        //iterate through given attribute_codes
        foreach ($attributes as $attributeCode) {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);

            //iterate through options
            foreach ($attribute->getOptions() as $option) {
                //get sort order based on options label
                $sortOrder = array_search($option->getLabel(), self::OPTIONS_SORT_ORDER);
                $table = $setup->getTable('eav_attribute_option');
                $optionId = $attribute->getSource()->getOptionId($option->getValue());

                if ($optionId != '' && ! is_null($optionId)) {
                    $setup->getConnection()->update($table, ['sort_order' => $sortOrder], 'option_id=' . $optionId);
                }
            }
        }
    }
}