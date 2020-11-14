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
 * @copyright  Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @link       https://www.techdivision.com/
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
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var string
     */
    protected $_configurablePricesJson;

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
        \Magento\Catalog\Model\Product $product,
		array $data = []
	){
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_helper = $helper;
        $this->_product = $product;
		parent::__construct($context, $data);
	}

    /**
     * Loops through all childs and returns true if at least one of the childs has a base price amount set.
     * This extra check is needed to fix a bug where the html tag was not created if the parent product
     * didn't have a product amount configured but one of the childs did. This resulted in the base price not
     * showing at all because the js didn't have a container to add the base price into.
     *
     * @return bool
     */
    public function hasChildWithBasePrice(): bool {

        if(!$this->isConfigurable()) {
            return false;
        }

        foreach ($this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct()) as $child) {
            if(!empty($child->getData('baseprice_product_amount'))) {
                return true;
            }
        }

        return false;
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

        return $moduleEnabled && (!empty($productAmount) || $this->hasChildWithBasePrice());
    }

	/**
	 * Retrieve current product
	 *
	 * @return \Magento\Catalog\Model\Product
	 */
	public function getProduct()
	{
        return $this->_product;
	}

    /**
     * Returns the base price information
     */
    public function getBasePrice()
    {
        return $this->_helper->getBasePriceText($this->getProduct());
    }
}
