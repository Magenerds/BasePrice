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
 * @subpackage Block
 * @copyright  Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @link       http://www.techdivision.com/
 * @author     Florian Sydekum <f.sydekum@techdivision.com>
 */
namespace Magenerds\BasePrice\Block;

/**
 * Class AfterPrice
 * @package Magenerds\BasePrice\Block
 */
class AfterPrice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magenerds\BasePrice\Helper\Data
     */
    protected $_helper;

    /**
     * @var string
     */
    protected $_configurablePricesJson;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magenerds\BasePrice\Helper\Data $helper
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magenerds\BasePrice\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
		array $data = []
	){
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_helper = $helper;
        $this->_registry = $registry;
		parent::__construct($context, $data);
	}

    /**
     * Returns the configuration if module is enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        $moduleEnabled = $this->_scopeConfig->getValue(
            'baseprice/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $productAmount = $this->getProduct()->getData('baseprice_product_amount');

        return $moduleEnabled && !empty($productAmount);
    }

    /**
     * @return bool
     */
    public function isConfigurable():bool {
        return $this->getProduct()->getTypeId() == 'configurable';
    }

	/**
	 * Retrieve current product
	 *
	 * @return \Magento\Catalog\Model\Product
	 */
	public function getProduct()
	{
        return $this->_registry->registry('current_product');
	}

    /**
     * Returns the base price information
     */
    public function getBasePrice()
    {
        return $this->_helper->getBasePriceText($this->getProduct());
    }
}