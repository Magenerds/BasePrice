<?php
/**
 * Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit
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
 * @subpackage Block
 * @copyright  Copyright (c) 2016 TechDivision GmbH (http://www.techdivision.com)
 * @version    ${release.version}
 * @link       http://www.techdivision.com/
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