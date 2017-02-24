<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     FBAOutboundServiceMWS
 *  @copyright   Copyright 2009 Amazon.com, Inc. All Rights Reserved.
 *  @link        http://mws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2010-06-01
 */
/******************************************************************************* 
 * 
 *  FBA Outbound Service MWS PHP5 Library
 *  Generated: Tue Jul 13 00:22:05 PDT 2010
 * 
 */

/**
 *  @see FBAOutboundServiceMWS_Model
 */
require_once ('FBAOutboundServiceMWS/Model.php');  

    

/**
 * FBAOutboundServiceMWS_Model_CODSettings
 * 
 * Properties:
 * <ul>
 * 
 * <li>IsCODRequired: bool</li>
 * <li>CODCharge: FBAOutboundServiceMWS_Model_Currency</li>
 * <li>CODChargeTax: FBAOutboundServiceMWS_Model_Currency</li>
 * <li>ShippingCharge: FBAOutboundServiceMWS_Model_Currency</li>
 * <li>ShippingChargeTax: FBAOutboundServiceMWS_Model_Currency</li>
 *
 * </ul>
 */ 
class FBAOutboundServiceMWS_Model_CODSettings extends FBAOutboundServiceMWS_Model
{


    /**
     * Construct new FBAOutboundServiceMWS_Model_CODSettings
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>IsCODRequired: bool</li>
     * <li>CODCharge: FBAOutboundServiceMWS_Model_Currency</li>
     * <li>CODChargeTax: FBAOutboundServiceMWS_Model_Currency</li>
     * <li>ShippingCharge: FBAOutboundServiceMWS_Model_Currency</li>
     * <li>ShippingChargeTax: FBAOutboundServiceMWS_Model_Currency</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'IsCODRequired' => array('FieldValue' => null, 'FieldType' => 'bool'),
        'CODCharge' => array('FieldValue' => null, 'FieldType' => 'FBAOutboundServiceMWS_Model_Currency'),
        'CODChargeTax' => array('FieldValue' => null, 'FieldType' => 'FBAOutboundServiceMWS_Model_Currency'),
        'ShippingCharge' => array('FieldValue' => null, 'FieldType' => 'FBAOutboundServiceMWS_Model_Currency'),
        'ShippingChargeTax' => array('FieldValue' => null, 'FieldType' => 'FBAOutboundServiceMWS_Model_Currency'),
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the IsCODRequired property.
     * 
     * @return bool Name
     */
    public function getIsCODRequired()
    {
        return $this->_fields['IsCODRequired']['FieldValue'];
    }

    /**
     * Sets the value of the IsCODRequired property.
     * 
     * @param bool IsCODRequired
     * @return this instance
     */
    public function setIsCODRequired($value) 
    {
        $this->_fields['IsCODRequired']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the IsCODRequired and returns this instance
     * 
     * @param bool $value IsCODRequired
     * @return FBAOutboundServiceMWS_Model_IsCODRequired instance
     */
    public function withIsCODRequired($value)
    {
        $this->setIsCODRequired($value);
        return $this;
    }


    /**
     * Checks if IsCODRequired is set
     * 
     * @return bool true if IsCODRequired  is set
     */
    public function isSetIsCODRequired()
    {
        return !is_null($this->_fields['IsCODRequired']['FieldValue']);
    }

    /**
     * Gets the value of the CODCharge property.
     * 
     * @return Currency CODCharge
     */
    public function getCODCharge() 
    {
        return $this->_fields['CODCharge']['FieldValue'];
    }

    /**
     * Sets the value of the CODCharge property.
     * 
     * @param Currency CODCharge
     * @return this instance
     */
    public function setCODCharge($value) 
    {
        $this->_fields['CODCharge']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CODCharge and returns this instance
     * 
     * @param Currency $value CODCharge
     * @return FBAOutboundServiceMWS_Model_CODCharge instance
     */
    public function withCODCharge($value)
    {
        $this->setCODCharge($value);
        return $this;
    }


    /**
     * Checks if CODCharge is set
     * 
     * @return bool true if CODCharge  is set
     */
    public function isSetCODCharge()
    {
        return !is_null($this->_fields['CODCharge']['FieldValue']);
    }

    /**
     * Gets the value of the CODChargeTax property.
     * 
     * @return Currency CODChargeTax
     */
    public function getCODChargeTax() 
    {
        return $this->_fields['CODChargeTax']['FieldValue'];
    }

    /**
     * Sets the value of the CODChargeTax property.
     * 
     * @param Currency CODChargeTax
     * @return this instance
     */
    public function setCODChargeTax($value) 
    {
        $this->_fields['CODChargeTax']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CODChargeTax and returns this instance
     * 
     * @param Currency $value CODChargeTax
     * @return this instance
     */
    public function withCODChargeTax($value)
    {
        $this->setCODChargeTax($value);
        return $this;
    }

    /**
     * Checks if CODChargeTax is set
     * 
     * @return bool true if CODChargeTax  is set
     */
    public function isSetCODChargeTax()
    {
        return !is_null($this->_fields['CODChargeTax']['FieldValue']);
    }

    /**
     * Gets the value of the ShippingCharge property.
     * 
     * @return Currency ShippingCharge
     */
    public function getShippingCharge()
    {
        return $this->_fields['ShippingCharge']['FieldValue'];
    }

    /**
     * Sets the value of the ShippingCharge property.
     * 
     * @param Currency ShippingCharge
     * @return this instance
     */
    public function setShippingCharge($value)
    {
        $this->_fields['ShippingCharge']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ShippingCharge and returns this instance
     * 
     * @param Currency $value ShippingCharge
     * @return FBAOutboundServiceMWS_Model_Address instance
     */
    public function withShippingCharge($value)
    {
        $this->setShippingCharge($value);
        return $this;
    }


    /**
     * Checks if ShippingCharge is set
     * 
     * @return bool true if ShippingCharge  is set
     */
    public function isSetShippingCharge()
    {
        return !is_null($this->_fields['ShippingCharge']['FieldValue']);
    }

    /**
     * Gets the value of the ShippingChargeTax property.
     * 
     * @return Currency ShippingChargeTax
     */
    public function getShippingChargeTax()
    {
        return $this->_fields['ShippingChargeTax']['FieldValue'];
    }

    /**
     * Sets the value of the ShippingChargeTax property.
     * 
     * @param Currency ShippingChargeTax
     * @return this instanceCODCharge
     */
    public function setShippingChargeTax($value)
    {
        $this->_fields['ShippingChargeTax']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ShippingChargeTax and returns this instance
     * 
     * @param Currency $value ShippingChargeTax
     * @return FBAOutboundServiceMWS_Model_Address instance
     */
    public function withShippingChargeTax($value)
    {
        $this->setShippingChargeTax($value);
        return $this;
    }


    /**
     * Checks if ShippingChargeTax is set
     * 
     * @return bool true if ShippingChargeTax  is set
     */
    public function isSetShippingChargeTax()
    {
        return !is_null($this->_fields['ShippingChargeTax']['FieldValue']);
    }

}
