<?php
/**
 * Magenerds\BasePrice\Block\Adminhtml\System\Config\Form\Field\ConversionMapping
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
namespace Magenerds\BasePrice\Block\Adminhtml\System\Config\Form\Field;

/**
 * Class ConversionMapping
 * @package Magenerds\BasePrice\Block\Adminhtml\System\Config\Form\Field
 */
class ConversionMapping extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit
     */
    protected $_productUnitRenderer;

    /**
     * @var \Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit
     */
    protected $_referenceUnitRenderer;

    /**
     * Internal constructor
     */
    protected function _construct()
    {
        $this->addColumn('product_unit',
            [
                'label' => __('Product unit'),
                'renderer' => $this->getProductUnitRenderer()
            ]
        );
        $this->addColumn('reference_unit',
            [
                'label' => __('Reference unit'),
                'renderer' => $this->getReferenceUnitRenderer()
            ]
        );
        $this->addColumn('conversion_rate', ['label' => __('Conversion rate')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    /**
     * Returns the product unit renderer
     *
     * @return \Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductUnitRenderer()
    {
        if (!$this->_productUnitRenderer) {
            $this->_productUnitRenderer = $this->getLayout()->createBlock(
                '\Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit',
                'product_unit',
                ['data' => ['is_render_to_js_template' => true], 'attributeCode' => 'baseprice_product_unit']
            );
        }
        return $this->_productUnitRenderer;
    }

    /**
     * Returns the reference unit renderer
     *
     * @return \Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit|\Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getReferenceUnitRenderer()
    {
        if (!$this->_referenceUnitRenderer) {
            $this->_referenceUnitRenderer = $this->getLayout()->createBlock(
                '\Magenerds\BasePrice\Block\Adminhtml\Form\Field\Unit',
                '',
                ['data' => ['is_render_to_js_template' => true], 'attributeCode' => 'baseprice_reference_unit']
            );
        }
        return $this->_referenceUnitRenderer;
    }

    /**
     * Prepares the array's row
     *
     * @param \Magento\Framework\DataObject $row
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $productUnit = $row->getProductUnit();
        $referenceUnit = $row->getReferenceUnit();
        $options = [];
        if ($productUnit) {
            $options['option_' . $this->getProductUnitRenderer()->calcOptionHash($productUnit)] = 'selected="selected"';
        }
        if ($referenceUnit) {
            $options['option_' . $this->getReferenceUnitRenderer()->calcOptionHash($referenceUnit)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == "active") {
            $this->_columns[$columnName]['class'] = 'input-text required-entry validate-number';
            $this->_columns[$columnName]['style'] = 'width:50px';
        }

        return parent::renderCellTemplate($columnName);
    }
}