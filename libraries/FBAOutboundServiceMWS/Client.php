<?php
/** 
 *  PHP Version 5
 *
 *  @category    Amazon
 *  @package     FBAOutboundServiceMWS
 *  @copyright   Copyright 2009 Amazon.com, Inc. All Rights Reserved.
 *  @link        http://mws.amazon.com
 *  @license     http://aws.amazon.com/apache2.0  Apache License, Version 2.0
 *  @version     2010-10-01
 */
/******************************************************************************* 
 * 
 *  FBA Outbound Service MWS PHP5 Library
 *  Generated: Thu Oct 28 10:48:26 UTC 2010
 * 
 */

/**
 *  @see FBAOutboundServiceMWS_Interface
 */
require_once ('FBAOutboundServiceMWS/Interface.php');

/**
 * Outbound fulfillment service
 * 
 * FBAOutboundServiceMWS_Client is an implementation of FBAOutboundServiceMWS
 *
 */
class FBAOutboundServiceMWS_Client implements FBAOutboundServiceMWS_Interface
{

    /** @var string */
    private  $_awsAccessKeyId = null;

    /** @var string */
    private  $_awsSecretAccessKey = null;

    /** @var array */
    private  $_config = array ('ServiceURL' => 'http://localhost:8000/',
                               'UserAgent' => 'FBAOutboundServiceMWS PHP5 Library',
                               'SignatureVersion' => 2,
                               'SignatureMethod' => 'HmacSHA256',
                               'ProxyHost' => null,
                               'ProxyPort' => -1,
                               'MaxErrorRetry' => 3
                               );

    private $_serviceVersion = '2010-10-01';

    const REQUEST_TYPE = "POST";

    const MWS_CLIENT_VERSION = "2014-02-20";

    /**
     * Construct new Client
     *
     * @param string $awsAccessKeyId AWS Access Key ID
     * @param string $awsSecretAccessKey AWS Secret Access Key
     * @param array $config configuration options.
     * @param array $attributes user-agent attributes
     * Valid configuration options are:
     * <ul>
     * <li>ServiceURL</li>
     * <li>UserAgent</li>
     * <li>SignatureVersion</li>
     * <li>TimesRetryOnError</li>
     * <li>ProxyHost</li>
     * <li>ProxyPort</li>
     * <li>MaxErrorRetry</li>
     * </ul>
     */

    public function __construct(
    $awsAccessKeyId, $awsSecretAccessKey, $config, $applicationName, $applicationVersion, $attributes = null)
    {
        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');

        $this->_awsAccessKeyId = $awsAccessKeyId;
        $this->_awsSecretAccessKey = $awsSecretAccessKey;
        if (!is_null($config)) $this->_config = array_merge($this->_config, $config);
        $this->setUserAgentHeader($applicationName, $applicationVersion, $attributes);
    }

  /**
   * Sets a MWS compliant HTTP User-Agent Header value.
   * $attributeNameValuePairs is an associative array.
   *
   * @param $applicationName
   * @param $applicationVersion
   * @param $attributes
   * @return unknown_type
   */
  public function setUserAgentHeader(
      $applicationName,
      $applicationVersion,
      $attributes = null) {

    if (is_null($attributes)) {
      $attributes = array ();
    }

    $this->_config['UserAgent'] =
        $this->constructUserAgentHeader($applicationName, $applicationVersion, $attributes);
  }

  /**
   * Construct a valid MWS compliant HTTP User-Agent Header. From the MWS Developer's Guide, this
   * entails:
   * "To meet the requirements, begin with the name of your application, followed by a forward
   * slash, followed by the version of the application, followed by a space, an opening
   * parenthesis, the Language name value pair, and a closing paranthesis. The Language parameter
   * is a required attribute, but you can add additional attributes separated by semi-colons."
   *
   * @param $applicationName
   * @param $applicationVersion
   * @param $additionalNameValuePairs
   * @return unknown_type
   */
  private function constructUserAgentHeader($applicationName, $applicationVersion, $attributes = null) {

    if (is_null($applicationName) || $applicationName === "") {
      throw new InvalidArguementException('$applicationName cannot be null.');
    }
     
    if (is_null($applicationVersion) || $applicationVersion === "") {
      throw new InvalidArguementException('$applicationVersion cannot be null.');
    }
     
    $userAgent =
    $this->quoteApplicationName($applicationName)
        . '/'
        . $this->quoteApplicationVersion($applicationVersion);

    $userAgent .= ' (';

    $userAgent .= 'Language=PHP/' . phpversion();
    $userAgent .= '; ';
    $userAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
    $userAgent .= '; ';
    $userAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;

    foreach ($attributes as $key => $value) {
      if (is_null($value) || $value === '') {
        throw new InvalidArgumentException("Value for $key cannot be null or empty.");
      }
        
      $userAgent .= '; '
        . $this->quoteAttributeName($key)
        . '='
        . $this->quoteAttributeValue($value);
    }
    $userAgent .= ')';

    return $userAgent;
  }

  /**
   * Collapse multiple whitespace characters into a single ' ' character.
   * @param $s
   * @return string
   */
  private function collapseWhitespace($s) {
    return preg_replace('/ {2,}|\s/', ' ', $s);
  }

  /**
   * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
   * and '/' characters from a string.
   * @param $s
   * @return string
   */
  private function quoteApplicationName($s) {
    $quotedString = $this->collapseWhitespace($s);
    $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
    $quotedString = preg_replace('/\//', '\\/', $quotedString);

    return $quotedString;
  }

  /**
   * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
   * and '(' characters from a string.
   *
   * @param $s
   * @return string
   */
  private function quoteApplicationVersion($s) {
    $quotedString = $this->collapseWhitespace($s);
    $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
    $quotedString = preg_replace('/\\(/', '\\(', $quotedString);

    return $quotedString;
  }

  /**
   * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
   * and '=' characters from a string.
   *
   * @param $s
   * @return unknown_type
   */
  private function quoteAttributeName($s) {
    $quotedString = $this->collapseWhitespace($s);
    $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
    $quotedString = preg_replace('/\\=/', '\\=', $quotedString);

    return $quotedString;
  }

  /**
   * Collapse multiple whitespace characters into a single ' ' and backslash escape ';', '\',
   * and ')' characters from a string.
   *
   * @param $s
   * @return unknown_type
   */
  private function quoteAttributeValue($s) {
    $quotedString = $this->collapseWhitespace($s);
    $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
    $quotedString = preg_replace('/\\;/', '\\;', $quotedString);
    $quotedString = preg_replace('/\\)/', '\\)', $quotedString);

    return $quotedString;
    }

    // Public API ------------------------------------------------------------//



    /**
     * Get Package Tracking Details
     * Gets the tracking details for a shipment package.
     *
     *
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsRequest request
     * or FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsRequest object itself
     * @see FBAOutboundServiceMWS_Model_GetPackageTrackingDetails
     * @return FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsResponse FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function getPackageTrackingDetails($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsRequest) {
            require_once ('FBAOutboundServiceMWS/Model/GetPackageTrackingDetailsRequest.php');
            $request = new FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/GetPackageTrackingDetailsResponse.php');
        return FBAOutboundServiceMWS_Model_GetPackageTrackingDetailsResponse::fromXML($this->_invoke($this->_convertGetPackageTrackingDetails($request)));
    }


            
    /**
     * List All Fulfillment Orders 
     * Gets the first set of fulfillment orders that are currently being
     * fulfilled or that were being fulfilled at some time in the past
     * (as specified by the query parameters). Also returns a NextToken
     * which can be used iterate through the remaining fulfillment orders
     * (via the ListAllFulfillmentOrdersByNextToken operation).
     * If a NextToken is not returned, it indicates the end-of-data.
     * 
     * If the QueryStartDateTime is set, the results will include all orders
     * currently being fulfilled, and all orders that were being fulfilled
     * since that date and time.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersRequest request
     * or FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersRequest object itself
     * @see FBAOutboundServiceMWS_Model_ListAllFulfillmentOrders
     * @return FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersResponse FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function listAllFulfillmentOrders($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersRequest) {
            require_once ('FBAOutboundServiceMWS/Model/ListAllFulfillmentOrdersRequest.php');
            $request = new FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/ListAllFulfillmentOrdersResponse.php');
        return FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersResponse::fromXML($this->_invoke($this->_convertListAllFulfillmentOrders($request)));
    }


            
    /**
     * Get Fulfillment Preview 
     * Get estimated shipping dates and fees for all
     * available shipping speed given a set of seller SKUs and quantities
     * If "ShippingSpeedCategories" are inputed, only previews for those options will be returned.
     * 
     * If "ShippingSpeedCategories" are not inputed, then previews for all available options
     * are returned.
     * The service will return the fulfillment estimates for a set of Seller
     * SKUs and quantities.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest request
     * or FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest object itself
     * @see FBAOutboundServiceMWS_Model_GetFulfillmentPreview
     * @return FBAOutboundServiceMWS_Model_GetFulfillmentPreviewResponse FBAOutboundServiceMWS_Model_GetFulfillmentPreviewResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function getFulfillmentPreview($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest) {
            require_once ('FBAOutboundServiceMWS/Model/GetFulfillmentPreviewRequest.php');
            $request = new FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/GetFulfillmentPreviewResponse.php');
        return FBAOutboundServiceMWS_Model_GetFulfillmentPreviewResponse::fromXML($this->_invoke($this->_convertGetFulfillmentPreview($request)));
    }


            
    /**
     * Get Service Status 
     * Request to poll the system for availability.
     * Status is one of GREEN, RED representing:
     * GREEN: This API section of the service is operating normally.
     * RED: The service is disrupted.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_GetServiceStatusRequest request
     * or FBAOutboundServiceMWS_Model_GetServiceStatusRequest object itself
     * @see FBAOutboundServiceMWS_Model_GetServiceStatus
     * @return FBAOutboundServiceMWS_Model_GetServiceStatusResponse FBAOutboundServiceMWS_Model_GetServiceStatusResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function getServiceStatus($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_GetServiceStatusRequest) {
            require_once ('FBAOutboundServiceMWS/Model/GetServiceStatusRequest.php');
            $request = new FBAOutboundServiceMWS_Model_GetServiceStatusRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/GetServiceStatusResponse.php');
        return FBAOutboundServiceMWS_Model_GetServiceStatusResponse::fromXML($this->_invoke($this->_convertGetServiceStatus($request)));
    }


            
    /**
     * List All Fulfillment Orders By Next Token 
     * Gets the next set of fulfillment orders that are currently being
     * being fulfilled or that were being fulfilled at some time in the
     * past.
     * If a NextToken is not returned, it indicates the end-of-data.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenRequest request
     * or FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenRequest object itself
     * @see FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextToken
     * @return FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenResponse FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function listAllFulfillmentOrdersByNextToken($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenRequest) {
            require_once ('FBAOutboundServiceMWS/Model/ListAllFulfillmentOrdersByNextTokenRequest.php');
            $request = new FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/ListAllFulfillmentOrdersByNextTokenResponse.php');
        return FBAOutboundServiceMWS_Model_ListAllFulfillmentOrdersByNextTokenResponse::fromXML($this->_invoke($this->_convertListAllFulfillmentOrdersByNextToken($request)));
    }


            
    /**
     * Get Fulfillment Order 
     * Get detailed information about a FulfillmentOrder.  This includes the
     * original fulfillment order request, the status of the order and its
     * items in Amazon's fulfillment network, and the shipments that have been
     * generated to fulfill the order.
     * 
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest request
     * or FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest object itself
     * @see FBAOutboundServiceMWS_Model_GetFulfillmentOrder
     * @return FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function getFulfillmentOrder($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest) {
            require_once ('FBAOutboundServiceMWS/Model/GetFulfillmentOrderRequest.php');
            $request = new FBAOutboundServiceMWS_Model_GetFulfillmentOrderRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/GetFulfillmentOrderResponse.php');
        return FBAOutboundServiceMWS_Model_GetFulfillmentOrderResponse::fromXML($this->_invoke($this->_convertGetFulfillmentOrder($request)));
    }


            
    /**
     * Cancel Fulfillment Order 
     * Request for Amazon to no longer attempt to fulfill an existing
     * fulfillment order. Amazon will attempt to stop fulfillment of all
     * items that haven't already shipped, but cannot guarantee success.
     * Note: Items that have already shipped cannot be cancelled.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_CancelFulfillmentOrderRequest request
     * or FBAOutboundServiceMWS_Model_CancelFulfillmentOrderRequest object itself
     * @see FBAOutboundServiceMWS_Model_CancelFulfillmentOrder
     * @return FBAOutboundServiceMWS_Model_CancelFulfillmentOrderResponse FBAOutboundServiceMWS_Model_CancelFulfillmentOrderResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function cancelFulfillmentOrder($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_CancelFulfillmentOrderRequest) {
            require_once ('FBAOutboundServiceMWS/Model/CancelFulfillmentOrderRequest.php');
            $request = new FBAOutboundServiceMWS_Model_CancelFulfillmentOrderRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/CancelFulfillmentOrderResponse.php');
        return FBAOutboundServiceMWS_Model_CancelFulfillmentOrderResponse::fromXML($this->_invoke($this->_convertCancelFulfillmentOrder($request)));
    }


            
    /**
     * Create Fulfillment Order 
     * The SellerFulfillmentOrderId must be unique for all fulfillment
     * orders created by the seller. If your system already has a
     * unique order identifier, then that may be a good value to put in
     * this field.
     * This DisplayableOrderDateTime will appear as the "order date" in
     * recipient-facing materials such as the packing slip.  The format
     * must be timestamp.
     * The DisplayableOrderId will appear as the "order id" in those
     * materials, and the DisplayableOrderComment will appear as well.
     * 
     * ShippingSpeedCategory is the Service Level Agreement for how long it
     * will take a shipment to be transported from the fulfillment center
     * to the recipient, once shipped. no default.
     * The following shipping speeds are available for US domestic:
     * * Standard, 3-5 business days
     * * Expedited, 2 business days
     * * Priority, 1 business day
     * Shipping speeds may vary elsewhere.  Please consult your manual for published SLAs.
     * DestinationAddress is the address the items will be shipped to.
     * FulfillmentPolicy indicates how unfulfillable items should be
     * handled. default is FillOrKill.
     * * FillOrKill if any item is determined to be unfulfillable
     * before any items have started shipping, the entire order is
     * considered unfulfillable.  Once any part of the order has
     * started shipping, as much of the order as possible will be
     * shipped.
     * * FillAll never consider any item unfulfillable.  Items must
     * either be fulfilled or merchant-cancelled.
     * * FillAllAvailable fulfill as much of the order as possible.
     * 
     * FulfillmentMethod indicates the intended recipient channel for the
     * order whether it be a consumer order or inventory return.
     * default is Consumer.
     * The available methods to fulfill a given order:
     * * Consumer indicates a customer order, this is the default.
     * * Removal indicates that the inventory should be returned to the
     * specified destination address.
     * 
     * 
     * NotificationEmailList can be used to provide a list of e-mail
     * addresses to receive ship-complete e-mail notifications. These
     * e-mails are customer-facing e-mails sent by FBA on behalf of
     * the seller.
     * 
     * @param mixed $request array of parameters for FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest request
     * or FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest object itself
     * @see FBAOutboundServiceMWS_Model_CreateFulfillmentOrder
     * @return FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse
     *
     * @throws FBAOutboundServiceMWS_Exception
     */
    public function createFulfillmentOrder($request)
    {
        if (!$request instanceof FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest) {
            require_once ('FBAOutboundServiceMWS/Model/CreateFulfillmentOrderRequest.php');
            $request = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest($request);
        }
        require_once ('FBAOutboundServiceMWS/Model/CreateFulfillmentOrderResponse.php');
        return FBAOutboundServiceMWS_Model_CreateFulfillmentOrderResponse::fromXML($this->_invoke($this->_convertCreateFulfillmentOrder($request)));
    }

        // Private API ------------------------------------------------------------//

    /**
     * Invoke request and return response
     */
    private function _invoke(array $parameters)
    {
        $actionName = $parameters["Action"];
        $response = array();
        $responseBody = null;
        $statusCode = 200;

        /* Submit the request and read response body */
        try {

            // Ensure the endpoint URL is set.
            if (empty($this->_config['ServiceURL'])) {
                throw new MarketplaceWebService_Exception(
                    array('ErrorCode' => 'InvalidServiceUrl',
                          'Message' => "Missing serviceUrl configuration value. You may obtain a list of valid MWS URLs by consulting the MWS Developer's Guide, or reviewing the sample code published along side this library."));
            }

            /* Add required request parameters */
            $parameters = $this->_addRequiredParameters($parameters);

            $shouldRetry = false;
            $retries = 0;
            do {
                try {
                        $response = $this->_httpPost($parameters);
                        $httpStatus = $response['Status'];

                        switch ($httpStatus)
                        {
                            case 200:
                                $shouldRetry = false;
                                break;

                            case 500:
                            case 503:
                                require_once('FBAOutboundServiceMWS/Model/ErrorResponse.php');
                                $errorResponse = FBAOutboundServiceMWS_Model_ErrorResponse::fromXML($response['ResponseBody']);

                                // We will not retry throttling errors since this would just add to the throttling problem.
                                $errors = $errorResponse->getError();
                                $shouldRetry = ($errors[0]->getCode() === 'RequestThrottled') ? false : true;

                                if ($shouldRetry && $retries <= $this->config['MaxErrorRetry'])
                                {
                                    $this->_pauseOnRetry(++$retries);
                                }
                                else
                                {
                                    throw $this->_reportAnyErrors($response['ResponseBody'], $response['Status']);
                                }
                                break;

                            default:
                                    $shouldRetry = false;
                                    throw $this->_reportAnyErrors($response['ResponseBody'], $response['Status']);
                                break;
                        }
                /* Rethrow on deserializer error */
                } catch (Exception $e) {
                    require_once ('FBAOutboundServiceMWS/Exception.php');
                    throw new FBAOutboundServiceMWS_Exception(array('Exception' => $e, 'Message' => $e->getMessage()));
                }

            } while ($shouldRetry);

        } catch (FBAOutboundServiceMWS_Exception $se) {
            throw $se;
        } catch (Exception $t) {
            throw new FBAOutboundServiceMWS_Exception(array('Exception' => $t, 'Message' => $t->getMessage()));
        }

        return $response['ResponseBody'];
    }

    /**
     * Look for additional error strings in the response and return formatted exception
     */
    private function _reportAnyErrors($responseBody, $status, Exception $e =  null)
    {
        $exProps = array();
        $exProps["StatusCode"] = $status;
        
        libxml_use_internal_errors(true);  // Silence XML parsing errors
        $xmlBody = simplexml_load_string($responseBody);
        
        if ($xmlBody !== false) {  // Check XML loaded without errors
            $exProps["XML"] = $responseBody;
            $exProps["ErrorCode"] = $xmlBody->Error->Code;
            $exProps["Message"] = $xmlBody->Error->Message;
            $exProps["ErrorType"] = !empty($xmlBody->Error->Type) ? $xmlBody->Error->Type : "Unknown";
            $exProps["RequestId"] = !empty($xmlBody->RequestID) ? $xmlBody->RequestID : $xmlBody->RequestId; // 'd' in RequestId is sometimes capitalized
        } else { // We got bad XML in response, just throw a generic exception
            $exProps["Message"] = "Internal Error";
        }
        
        require_once ('FBAOutboundServiceMWS/Exception.php');
        return new FBAOutboundServiceMWS_Exception($exProps);
    }



    /**
     * Perform HTTP post with exponential retries on error 500 and 503
     *
     */
    private function _httpPost(array $parameters)
    {
        $query = $this->_getParametersAsString($parameters);
        $url = parse_url ($this->_config['ServiceURL']);
        $scheme = '';
        if (isset($url['port'])) {
            $port = $url['port'];
        } else {
            $port = null;
        }

        switch ($url['scheme']) {
            case 'https':
                $scheme = 'https://';
                $port = $port === null ? 443 : $port;
                break;
            default:
                $scheme = '';
                $port = $port === null ? 80 : $port;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $scheme . $url['host'] . $url['path']);
        curl_setopt($ch, CURLOPT_PORT, $port);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_config['UserAgent']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_HEADER, true); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($this->_config['ProxyHost'] != null && $this->_config['ProxyPort'] != -1)
        {
            curl_setopt($ch, CURLOPT_PROXY, $this->_config['ProxyHost'] . ':' . $this->_config['ProxyPort']);
        }

        $response = "";
        $response = curl_exec($ch);

        if ($response === false) {
            $errorResponse = curl_error($ch);
            require_once("FBAOutboundServiceMWS/Exception.php");
            curl_close($ch);

            throw new FBAOutboundServiceMWS_Exception(array(
                'Message' => $errorResponse,
                'ErrorType' => 'HTTP'
            ));
        }

        curl_close($ch);

        $splitResponses = preg_split("/(?:\r?\n){2}/", $response);
        for ($count = 0; $count < count($splitResponses); $count++) {
            $headers = $splitResponses[$count];

            if (1 !== preg_match("/(\\S+) +(\\d+) +([^\n\r]+)(?:\r?\n|\r|$)/", $headers, $matches)) {
                // can't parse the status line; assuming invalid and ignoring
                continue;
            }
            list($protocol, $code, $text) = array_slice($matches, 1);

            $body = null;
            if (1 === preg_match("/[cC]ontent-[lL]ength: +(?:\\d+)(?:\r?\n|\r|$)/", $headers) ||
                1 === preg_match("/Transfer-Encoding: +(?!identity[\r\n;= ])(?:[^\r\n]+)(?:\r?\n|\r|$)/i", $headers)) {
                $body = $splitResponses[++$count];

                return array('Status' => (int)$code, 'ResponseBody' => $body);
            }
        }

        // Response code and text will be the last parsed; this is the most likely to be useful.
        require_once ('FBAOutboundServiceMWS/Exception.php');
        throw new FBAOutboundServiceMWS_Exception(array(
            'Message' => 'Failed to parse valid HTTP response (' . $text . ')',
            'ErrorCode' => (int)$code,
            'ErrorType' => 'HTTP'
        ));
    }

    /**
     * Exponential sleep on failed request
     * @param retries current retry
     * @throws FBAOutboundServiceMWS_Exception if maximum number of retries has been reached
     */
    private function _pauseOnRetry($retries)
    {
        $delay = (int) (pow(4, $retries) * 100000) ;
        usleep($delay);
    }

    /**
     * Add authentication related and version parameters
     */
    private function _addRequiredParameters(array $parameters)
    {
        $parameters['AWSAccessKeyId'] = $this->_awsAccessKeyId;
        $parameters['Timestamp'] = $this->_getFormattedTimestamp();
        $parameters['Version'] = $this->_serviceVersion;
        $parameters['SignatureVersion'] = $this->_config['SignatureVersion'];
        if ($parameters['SignatureVersion'] > 1) {
            $parameters['SignatureMethod'] = $this->_config['SignatureMethod'];
        }
        $parameters['Signature'] = $this->_signParameters($parameters, $this->_awsSecretAccessKey);

        return $parameters;
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            if (!is_null($key) && $key !=='' && !is_null($value) && $value!=='')
            {
                $queryParameters[] = $key . '=' . $this->_urlencode($value);
            }
        }
        return implode('&', $queryParameters);
    }


    /**
     * Computes RFC 2104-compliant HMAC signature for request parameters
     * Implements AWS Signature, as per following spec:
     *
     * Signature Version 0: This is not supported in the MWS.
     *
     * Signature Version 1: This is not supported in the MWS.
     *
     * Signature Version is 2, string to sign is based on following:
     *
     *    1. The HTTP Request Method followed by an ASCII newline (%0A)
     *    2. The HTTP Host header in the form of lowercase host, followed by an ASCII newline.
     *    3. The URL encoded HTTP absolute path component of the URI
     *       (up to but not including the query string parameters);
     *       if this is empty use a forward '/'. This parameter is followed by an ASCII newline.
     *    4. The concatenation of all query string components (names and values)
     *       as UTF-8 characters which are URL encoded as per RFC 3986
     *       (hex characters MUST be uppercase), sorted using lexicographic byte ordering.
     *       Parameter names are separated from their values by the '=' character
     *       (ASCII character 61), even if the value is empty.
     *       Pairs of parameter and values are separated by the '&' character (ASCII code 38).
     *
     */
    private function _signParameters(array $parameters, $key) {
        $signatureVersion = $parameters['SignatureVersion'];
        $algorithm = "HmacSHA1";
        $stringToSign = null;
        if (0 === $signatureVersion) {
            throw new InvalidArguementException(
                'Signature Version 0 is no longer supported. Only Signature Version 2 is supported.');
        } else if (1 === $signatureVersion) {
            throw new InvalidArguementException(
                'Signature Version 1 is no longer supported. Only Signature Version 2 is supported.');
        } else if (2 === $signatureVersion) {
            $algorithm = $this->_config['SignatureMethod'];
            $parameters['SignatureMethod'] = $algorithm;
            $stringToSign = $this->_calculateStringToSignV2($parameters);
        } else {
            throw new Exception("Invalid Signature Version specified");
        }
        return $this->_sign($stringToSign, $key, $algorithm);
    }

    /**
     * Calculate String to Sign for SignatureVersion 2
     * @param array $parameters request parameters
     * @return String to Sign
     */
    private function _calculateStringToSignV2(array $parameters) {
        $parsedUrl = parse_url($this->_config['ServiceURL']);
        $endpoint = $parsedUrl['host'];
        if (array_key_exists('port', $parsedUrl)) {
          $endpoint .= ':' . $parsedUrl['port'];
        }

        $data = 'POST';
        $data .= "\n";
        $endpoint = parse_url ($this->_config['ServiceURL']);
        $data .= $endpoint['host'];
        $data .= "\n";
        $uri = array_key_exists('path', $endpoint) ? $endpoint['path'] : null;
        if (!isset ($uri)) {
            $uri = "/";
        }
        $uriencoded = implode("/", array_map(array($this, "_urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->_getParametersAsString($parameters);
        return $data;
    }

    private function _urlencode($value) {
        return str_replace('%7E', '~', rawurlencode($value));
    }


    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $key, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception ("Non-supported signing method specified");
        }
        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }


    /**
     * Formats date as ISO 8601 timestamp
     */
    private function _getFormattedTimestamp()
    {
        return gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
    }



    /**
     * Convert GetPackageTrackingDetailsRequest to name value pairs
     */
    private function _convertGetPackageTrackingDetails($request) {

        $parameters = array();
        $parameters['Action'] = 'GetPackageTrackingDetails';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetPackageNumber()) {
            $parameters['PackageNumber'] =  $request->getPackageNumber();
        }

        return $parameters;
    }


                                                
    /**
     * Convert ListAllFulfillmentOrdersRequest to name value pairs
     */
    private function _convertListAllFulfillmentOrders($request) {
        
        $parameters = array();
        $parameters['Action'] = 'ListAllFulfillmentOrders';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetQueryStartDateTime()) {
            $parameters['QueryStartDateTime'] =  $request->getQueryStartDateTime();
        }
        if ($request->isSetFulfillmentMethod()) {
            $fulfillmentMethodlistAllFulfillmentOrdersRequest = $request->getFulfillmentMethod();
            foreach  ($fulfillmentMethodlistAllFulfillmentOrdersRequest->getmember() as $memberfulfillmentMethodIndex => $memberfulfillmentMethod) {
                $parameters['FulfillmentMethod' . '.' . 'member' . '.'  . ($memberfulfillmentMethodIndex + 1)] =  $memberfulfillmentMethod;
            }
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetFulfillmentPreviewRequest to name value pairs
     */
    private function _convertGetFulfillmentPreview($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetFulfillmentPreview';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetIncludeCODFulfillmentPreview()) {
            $parameters['IncludeCODFulfillmentPreview'] =  $request->getIncludeCODFulfillmentPreview();
        }
        if ($request->isSetAddress()) {
            $addressgetFulfillmentPreviewRequest = $request->getAddress();
            if ($addressgetFulfillmentPreviewRequest->isSetName()) {
                $parameters['Address' . '.' . 'Name'] =  $addressgetFulfillmentPreviewRequest->getName();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetLine1()) {
                $parameters['Address' . '.' . 'Line1'] =  $addressgetFulfillmentPreviewRequest->getLine1();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetLine2()) {
                $parameters['Address' . '.' . 'Line2'] =  $addressgetFulfillmentPreviewRequest->getLine2();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetLine3()) {
                $parameters['Address' . '.' . 'Line3'] =  $addressgetFulfillmentPreviewRequest->getLine3();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetDistrictOrCounty()) {
                $parameters['Address' . '.' . 'DistrictOrCounty'] =  $addressgetFulfillmentPreviewRequest->getDistrictOrCounty();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetCity()) {
                $parameters['Address' . '.' . 'City'] =  $addressgetFulfillmentPreviewRequest->getCity();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetStateOrProvinceCode()) {
                $parameters['Address' . '.' . 'StateOrProvinceCode'] =  $addressgetFulfillmentPreviewRequest->getStateOrProvinceCode();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetCountryCode()) {
                $parameters['Address' . '.' . 'CountryCode'] =  $addressgetFulfillmentPreviewRequest->getCountryCode();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetPostalCode()) {
                $parameters['Address' . '.' . 'PostalCode'] =  $addressgetFulfillmentPreviewRequest->getPostalCode();
            }
            if ($addressgetFulfillmentPreviewRequest->isSetPhoneNumber()) {
                $parameters['Address' . '.' . 'PhoneNumber'] =  $addressgetFulfillmentPreviewRequest->getPhoneNumber();
            }
        }
        if ($request->isSetItems()) {
            $itemsgetFulfillmentPreviewRequest = $request->getItems();
            foreach ($itemsgetFulfillmentPreviewRequest->getmember() as $memberitemsIndex => $memberitems) {
                if ($memberitems->isSetSellerSKU()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'SellerSKU'] =  $memberitems->getSellerSKU();
                }
                if ($memberitems->isSetQuantity()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'Quantity'] =  $memberitems->getQuantity();
                }
                if ($memberitems->isSetSellerFulfillmentOrderItemId()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'SellerFulfillmentOrderItemId'] =  $memberitems->getSellerFulfillmentOrderItemId();
                }

            }
        }
        if ($request->isSetShippingSpeedCategories()) {
            $shippingSpeedCategoriesgetFulfillmentPreviewRequest = $request->getShippingSpeedCategories();
            foreach  ($shippingSpeedCategoriesgetFulfillmentPreviewRequest->getmember() as $membershippingSpeedCategoriesIndex => $membershippingSpeedCategories) {
                $parameters['ShippingSpeedCategories' . '.' . 'member' . '.'  . ($membershippingSpeedCategoriesIndex + 1)] =  $membershippingSpeedCategories;
            }
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetServiceStatusRequest to name value pairs
     */
    private function _convertGetServiceStatus($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetServiceStatus';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert ListAllFulfillmentOrdersByNextTokenRequest to name value pairs
     */
    private function _convertListAllFulfillmentOrdersByNextToken($request) {
        
        $parameters = array();
        $parameters['Action'] = 'ListAllFulfillmentOrdersByNextToken';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetNextToken()) {
            $parameters['NextToken'] =  $request->getNextToken();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert GetFulfillmentOrderRequest to name value pairs
     */
    private function _convertGetFulfillmentOrder($request) {
        
        $parameters = array();
        $parameters['Action'] = 'GetFulfillmentOrder';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetSellerFulfillmentOrderId()) {
            $parameters['SellerFulfillmentOrderId'] =  $request->getSellerFulfillmentOrderId();
        }

        return $parameters;
    }
        
                                                
    /**
     * Convert CancelFulfillmentOrderRequest to name value pairs
     */
    private function _convertCancelFulfillmentOrder($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CancelFulfillmentOrder';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetSellerFulfillmentOrderId()) {
            $parameters['SellerFulfillmentOrderId'] =  $request->getSellerFulfillmentOrderId();
        }

        return $parameters;
    }
        
                                        
    /**
     * Convert CreateFulfillmentOrderRequest to name value pairs
     */
    private function _convertCreateFulfillmentOrder($request) {
        
        $parameters = array();
        $parameters['Action'] = 'CreateFulfillmentOrder';
        if ($request->isSetSellerId()) {
            $parameters['SellerId'] =  $request->getSellerId();
        }
        if ($request->isSetMarketplace()) {
            $parameters['Marketplace'] =  $request->getMarketplace();
        }
        if ($request->isSetSellerFulfillmentOrderId()) {
            $parameters['SellerFulfillmentOrderId'] =  $request->getSellerFulfillmentOrderId();
        }
        if ($request->isSetDisplayableOrderId()) {
            $parameters['DisplayableOrderId'] =  $request->getDisplayableOrderId();
        }
        if ($request->isSetDisplayableOrderDateTime()) {
            $parameters['DisplayableOrderDateTime'] =  $request->getDisplayableOrderDateTime();
        }
        if ($request->isSetDisplayableOrderComment()) {
            $parameters['DisplayableOrderComment'] =  $request->getDisplayableOrderComment();
        }
        if ($request->isSetShippingSpeedCategory()) {
            $parameters['ShippingSpeedCategory'] =  $request->getShippingSpeedCategory();
        }
        if ($request->isSetDestinationAddress()) {
            $destinationAddresscreateFulfillmentOrderRequest = $request->getDestinationAddress();
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetName()) {
                $parameters['DestinationAddress' . '.' . 'Name'] =  $destinationAddresscreateFulfillmentOrderRequest->getName();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetLine1()) {
                $parameters['DestinationAddress' . '.' . 'Line1'] =  $destinationAddresscreateFulfillmentOrderRequest->getLine1();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetLine2()) {
                $parameters['DestinationAddress' . '.' . 'Line2'] =  $destinationAddresscreateFulfillmentOrderRequest->getLine2();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetLine3()) {
                $parameters['DestinationAddress' . '.' . 'Line3'] =  $destinationAddresscreateFulfillmentOrderRequest->getLine3();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetDistrictOrCounty()) {
                $parameters['DestinationAddress' . '.' . 'DistrictOrCounty'] =  $destinationAddresscreateFulfillmentOrderRequest->getDistrictOrCounty();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetCity()) {
                $parameters['DestinationAddress' . '.' . 'City'] =  $destinationAddresscreateFulfillmentOrderRequest->getCity();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetStateOrProvinceCode()) {
                $parameters['DestinationAddress' . '.' . 'StateOrProvinceCode'] =  $destinationAddresscreateFulfillmentOrderRequest->getStateOrProvinceCode();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetCountryCode()) {
                $parameters['DestinationAddress' . '.' . 'CountryCode'] =  $destinationAddresscreateFulfillmentOrderRequest->getCountryCode();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetPostalCode()) {
                $parameters['DestinationAddress' . '.' . 'PostalCode'] =  $destinationAddresscreateFulfillmentOrderRequest->getPostalCode();
            }
            if ($destinationAddresscreateFulfillmentOrderRequest->isSetPhoneNumber()) {
                $parameters['DestinationAddress' . '.' . 'PhoneNumber'] =  $destinationAddresscreateFulfillmentOrderRequest->getPhoneNumber();
            }
        }
        if ($request->isSetFulfillmentPolicy()) {
            $parameters['FulfillmentPolicy'] =  $request->getFulfillmentPolicy();
        }
        if ($request->isSetFulfillmentMethod()) {
            $parameters['FulfillmentMethod'] =  $request->getFulfillmentMethod();
        }
        if ($request->isSetShipFromCountryCode()) {
            $parameters['ShipFromCountryCode'] =  $request->getShipFromCountryCode();
        }
        if ($request->isSetNotificationEmailList()) {
            $notificationEmailListcreateFulfillmentOrderRequest = $request->getNotificationEmailList();
            foreach  ($notificationEmailListcreateFulfillmentOrderRequest->getmember() as $membernotificationEmailListIndex => $membernotificationEmailList) {
                $parameters['NotificationEmailList' . '.' . 'member' . '.'  . ($membernotificationEmailListIndex + 1)] =  $membernotificationEmailList;
            }
        }
        if ($request->isSetCODSettings()) {
            $cODSettings = $request->getCODSettings();
            if ($cODSettings->isSetIsCODRequired()) {
                $parameters['CODSettings' . '.' . 'IsCODRequired'] =  $cODSettings->getIsCODRequired();
            }
            if ($cODSettings->isSetCODCharge()) {
                $cODCharge = $cODSettings->getCODCharge();
                if ($cODCharge->isSetCurrencyCode()) {
                    $parameters['CODSettings' . '.' . 'CODCharge' . '.' . 'CurrencyCode'] = $cODCharge->getCurrencyCode();
                }
                if ($cODCharge->isSetValue()) {
                    $parameters['CODSettings' . '.' . 'CODCharge' . '.' . 'Value'] = $cODCharge->getValue();
                }
            }
            if ($cODSettings->isSetCODChargeTax()) {
                $cODChargeTax = $cODSettings->getCODChargeTax();
                if ($cODChargeTax->isSetCurrencyCode()) {
                    $parameters['CODSettings' . '.' . 'CODChargeTax' . '.' . 'CurrencyCode'] = $cODChargeTax->getCurrencyCode();
                }
                if ($cODChargeTax->isSetValue()) {
                    $parameters['CODSettings' . '.' . 'CODChargeTax' . '.' . 'Value'] = $cODChargeTax->getValue();
                }
            }
            if ($cODSettings->isSetShippingCharge()) {
                $shippingCharge = $cODSettings->getShippingCharge();
                if ($shippingCharge->isSetCurrencyCode()) {
                    $parameters['CODSettings' . '.' . 'ShippingCharge' . '.' . 'CurrencyCode'] = $shippingCharge->getCurrencyCode();
                }
                if ($shippingCharge->isSetValue()) {
                    $parameters['CODSettings' . '.' . 'ShippingCharge' . '.' . 'Value'] = $shippingCharge->getValue();
                }
            }
            if ($cODSettings->isSetShippingChargeTax()) {
                $shippingChargeTax = $cODSettings->getShippingChargeTax();
                if ($shippingChargeTax->isSetCurrencyCode()) {
                    $parameters['CODSettings' . '.' . 'ShippingChargeTax' . '.' . 'CurrencyCode'] = $shippingChargeTax->getCurrencyCode();
                }
                if ($shippingChargeTax->isSetValue()) {
                    $parameters['CODSettings' . '.' . 'ShippingChargeTax' . '.' . 'Value'] = $shippingChargeTax->getValue();
                }
            }
        }
        if ($request->isSetItems()) {
            $itemscreateFulfillmentOrderRequest = $request->getItems();
            foreach ($itemscreateFulfillmentOrderRequest->getmember() as $memberitemsIndex => $memberitems) {
                if ($memberitems->isSetSellerSKU()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'SellerSKU'] =  $memberitems->getSellerSKU();
                }
                if ($memberitems->isSetSellerFulfillmentOrderItemId()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'SellerFulfillmentOrderItemId'] =  $memberitems->getSellerFulfillmentOrderItemId();
                }
                if ($memberitems->isSetQuantity()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'Quantity'] =  $memberitems->getQuantity();
                }
                if ($memberitems->isSetGiftMessage()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'GiftMessage'] =  $memberitems->getGiftMessage();
                }
                if ($memberitems->isSetDisplayableComment()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'DisplayableComment'] =  $memberitems->getDisplayableComment();
                }
                if ($memberitems->isSetFulfillmentNetworkSKU()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'FulfillmentNetworkSKU'] =  $memberitems->getFulfillmentNetworkSKU();
                }
                if ($memberitems->isSetOrderItemDisposition()) {
                    $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'OrderItemDisposition'] =  $memberitems->getOrderItemDisposition();
                }
                if ($memberitems->isSetPerUnitDeclaredValue()) {
                    $perUnitDeclaredValuemember = $memberitems->getPerUnitDeclaredValue();
                    if ($perUnitDeclaredValuemember->isSetCurrencyCode()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitDeclaredValue' . '.' . 'CurrencyCode'] =  $perUnitDeclaredValuemember->getCurrencyCode();
                    }
                    if ($perUnitDeclaredValuemember->isSetValue()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitDeclaredValue' . '.' . 'Value'] =  $perUnitDeclaredValuemember->getValue();
                    }
                }
                if ($memberitems->isSetPerUnitPrice()) {
                    $perUnitPricemember = $memberitems->getPerUnitPrice();
                    if ($perUnitPricemember->isSetCurrencyCode()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitPrice' . '.' . 'CurrencyCode'] =  $perUnitPricemember->getCurrencyCode();
                    }
                    if ($perUnitPricemember->isSetValue()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitPrice' . '.' . 'Value'] =  $perUnitPricemember->getValue();
                    }
                }
                if ($memberitems->isSetPerUnitTax()) {
                    $perUnitTaxmember = $memberitems->getPerUnitTax();
                    if ($perUnitTaxmember->isSetCurrencyCode()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitTax' . '.' . 'CurrencyCode'] =  $perUnitTaxmember->getCurrencyCode();
                    }
                    if ($perUnitTaxmember->isSetValue()) {
                        $parameters['Items' . '.' . 'member' . '.'  . ($memberitemsIndex + 1) . '.' . 'PerUnitTax' . '.' . 'Value'] =  $perUnitTaxmember->getValue();
                    }
                }

            }
        }

        return $parameters;
    }
        
                                                                                                                                                                                                                                                                                
}
