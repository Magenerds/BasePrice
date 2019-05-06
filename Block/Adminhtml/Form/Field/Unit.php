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
namespace Magenerds\BasePrice\Block\Adminhtml\Form\Field;

/**
 * Class Unit
 * @package Magenerds\BasePrice\Block\Adminhtml\Form\Field
 */
class Unit extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeOptionManagementInterface
     */
    protected $_productAttributeOptionManagementInterface;

    /**
     * @var string
     */
    protected $_attributeCode;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface
     * @param $attributeCode
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Catalog\Api\ProductAttributeOptionManagementInterface $productAttributeOptionManagementInterface,
        $attributeCode,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->_productAttributeOptionManagementInterface = $productAttributeOptionManagementInterface;
        $this->_attributeCode = $attributeCode;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->addOption('', __('-- Select value --'));
            foreach ($this->_productAttributeOptionManagementInterface->getItems($this->_attributeCode) as $item) {
                $this->addOption($item->getValue(), $item->getLabel());
            }
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value) {
        return $this->setName($value);
    }
}
