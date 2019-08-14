<?php
class Rejoiner_Acr_Block_Adminhtml_Preinstalled_Form extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_sourceRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('attr_name', array(
            'label' => Mage::helper('adminhtml')->__('Attribute Name'),
            'style' => 'width:120px',
            'renderer' => $this->_getSourceRenderer()
        ));
        $this->addColumn('value', array(
            'label' => Mage::helper('adminhtml')->__('Value'),
            'style' => 'width:120px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Attribute');
    }

    protected function _getSourceRenderer()
    {
        if (!$this->_sourceRenderer) {
            $this->_sourceRenderer = Mage::getSingleton('core/layout')->createBlock(
                'rejoiner_acr/adminhtml_form_field_source', 'google_anal',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_sourceRenderer;
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getSourceRenderer()->calcOptionHash($row->getData('attr_name')),
            'selected="selected"'
        );
    }
}