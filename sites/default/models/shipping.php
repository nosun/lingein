<?php
class Shipping_Model extends Bl_Model
{
  /**
   * @return Payment_Model
   */
  public static function getInstance()
  {
    return parent::getInstance(__CLASS__);
  }

  public function shippingList($front = false)
  {
    $shippingList = Bl_Plugin::getList('shipping');
    foreach($shippingList as $k => $v) {
      $array = Bl_Config::get('shipping.' . $k, array('status' => false, 'visible' => true));
      $v->status = isset($array['status']) ? $array['status'] : false;
      $v->visible = isset($array['visible']) ? $array['visible'] : true;
      $v->weight = isset($array['weight']) ? $array['weight'] : 0;
      if ($front) {
        if (isset($array['name_f'])) {
          $v->name = $array['name_f'];
        }
        if (isset($array['descripe_f'])) {
          $v->description = $array['descripe_f'];
        }
      }
    }
    // add fast(amazon) shipping method
    $fastShippingMethod = new stdClass();
    $fastShippingMethod->status = 1;
    $fastShippingMethod->visible = 1;
    $fastShippingMethod->weight = 0;
    $fastShippingMethod->name = "DHL/UPS/FEDEX";
    $fastShippingMethod->description = "3 - 5 business days. More cheaper when purchased items reached some amount.";
    $shippingList['fast'] = $fastShippingMethod;
    return $this->sortingByWeight($shippingList);
  }
  
  public function sortingByWeight($arr) {
    $newarr = array();
    $result = array();
    $key = array();
    if (is_array($arr)) {
      foreach ($arr as $k => $v) {
        $v->weight = isset($v->weight) ? $v->weight : 0;
        $newarr[$v->weight][$k] = $v;
        $key[$v->weight][$k] = $k;
      }
      krsort($newarr);
      foreach ($newarr as $k => $v) {
        foreach($v as $k2 => $v2) {
          $result[$k2] = $v2;
        }
      }
    }
    return $result;
  }
  
  public function getAvaiableShippingMethod($userDeliveryRecord, $cartInfo) {
  	if (!isset($userDeliveryRecord)) {
  		return array();
  	}
  	$shippingMethodList = array();
  	$shippingMoneyList = array();
  	$shippingInfoList = $this->shippingList(true);
  	foreach ($shippingInfoList as $key => $shippingInfo) {
  		if ($shippingInfo->status == 1 && $shippingInfo->visible == 1 && $key != 'fast') {
	  		$shippingFee = $this->getShippingFee($key, $userDeliveryRecord, $cartInfo);
	  		if ($shippingFee >= 0) {
	  			$shippingMethod = array('name' => $key,
	  									'displayName' => $shippingInfo->name,
	  									'description' => $shippingInfo->description,
	  									'shippingFee' => c($shippingFee));
	  			$shippingMethodList[$key] = $shippingMethod;
	  			$shippingMoneyList[$key] = $shippingFee;
	  		}
  		}
  	}
  	if ($userDeliveryRecord->delivery_country == "United States") {
  		$amazonShippingInfo = $this->getAmazonShippingInfo($userDeliveryRecord, $cartInfo);
  		if ($amazonShippingInfo['success'] && $amazonShippingInfo['isFulfillable']) {
  			$shippingMethod = array('name' => 'fast',
  					'displayName' => 'DHL/UPS/FEDEX',
  					'description' => '3 - 5 business days. More cheaper when purchased items reached some amount.',
  					'shippingFee' => c($amazonShippingInfo['estimatedFees']));
  			$shippingMethodList['fast'] = $shippingMethod;
  			$shippingMoneyList['fast'] = $amazonShippingInfo['estimatedFees'];
  			//$_SESSION['shippingMoneyList'] = $shippingMoneyList;
  			//return $shippingMethodList;
  			if (key_exists('ups', $shippingMethodList)) {
  				unset($shippingMethodList['ups']);
  			}
  		}
  	}  
  	$_SESSION['shippingMoneyList'] = $shippingMoneyList;
  	return $shippingMethodList;
  }
  
  public function getShippingFee($shippingMethod, $userDeliveryRecord, $cartInfo) {
  	$shippingFee = 0.0;
	$pid = $userDeliveryRecord->delivery_pid;
	$cid = $userDeliveryRecord->delivery_cid;
    $configure = Bl_Config::get('shipping.'.$shippingMethod, 0);
    $pid == 0 ? $pid = 'null' : 1;
    $i = 0;
    if (isset($configure['setting'])) {
      foreach ($configure['setting'] as $k => $v) {
        if (array_key_exists('0', $v['area'])) {
          $configure = $v;
          $i ++;
          break;
        } else if (array_key_exists($cid, $v['area'])) {
      		if (in_array($pid, $v['area'][$cid]) || in_array('null', $v['area'][$cid]) || in_array('0', $v['area'][$cid])) {
      			$configure = $v;
      			$i ++;
      			break;
      		}
      	}
      }
    }
    if ($i == 0) {
    	return -1;
    }
    $shippingInstance = Bl_Plugin::getInstance('shipping', $shippingMethod);
    $shippingInstance->initialize($configure);
    $shipping_money = $shippingInstance->calculate($cartInfo->goods_weight, $cartInfo->goods_amount, $cartInfo->goods_number);
    $shipping_money_new = callFunction('getShippingMoney', $shippingMethod, $cartInfo->goods_weight, $cartInfo->goods_amount, $cartInfo->goods_number, $cid, $pid);
    if (isset($shipping_money_new) && $shipping_money_new) {
      $shipping_fee = $shipping_money_new;
    } else {
    	$shipping_fee = $shipping_money;
    }
    if (!isset($shipping_money)) {
     	$shipping_fee = -1;
    }
	return $shipping_fee;
  }
  
  private function getAmazonShippingInfo($userDeliveryRecord, $cartInfo) {
  	if (!isset($cartInfo->product)) {
  		return false;
  	}
  	include_once (LIBPATH . '/FBAOutboundServiceMWS/config.inc.php');
  	 
  	$config = array (
  			'ServiceURL' => MWS_ENDPOINT_URL,
  			'ProxyHost' => null,
  			'ProxyPort' => -1,
  			'MaxErrorRetry' => 3
  	);
  	 
  	$service = new FBAOutboundServiceMWS_Client(
  			ACCESS_KEY_ID,
  			SECRET_ACCESS_KEY,
  			$config,
  			APPLICATION_NAME,
  			APPLICATION_VERSION);
  	$request = new FBAOutboundServiceMWS_Model_GetFulfillmentPreviewRequest();
  	$request->setSellerId(SELLER_ID);
  	 
  	$address = new FBAOutboundServiceMWS_Model_Address();
  	$address->setName($userDeliveryRecord->delivery_first_name . ' ' . $userDeliveryRecord->delivery_last_name);
  	$address->setLine1($userDeliveryRecord->delivery_address);
  	$address->setCity($userDeliveryRecord->delivery_city);
  	$stateOrProvinceCode = User_Model::getInstance()->getStateOrProvinceCode($userDeliveryRecord->delivery_cid, $userDeliveryRecord->delivery_pid);
  	if ($stateOrProvinceCode === false) {
  		return false;
  	}
  	$address->setStateOrProvinceCode($stateOrProvinceCode->code);
  	$address->setPostalCode($userDeliveryRecord->delivery_postcode);
  	$address->setCountryCode('US');
  	$address->setPhoneNumber($userDeliveryRecord->delivery_mobile);
  	$request->setAddress($address);
  	 
  	$shippingSpeedCategoryList = new FBAOutboundServiceMWS_Model_ShippingSpeedCategoryList();
  	$shippingSpeedCategoryList->setmember('Standard');
  	$request->setShippingSpeedCategories($shippingSpeedCategoryList);

  	$orderItemList = array();
  	$productInstance = Product_Model::getInstance();
  	$totalQty = 0;
	foreach($cartInfo->product as $item) {
		$productInfo = $productInstance->getProductInfo($item->pid);
	  	$orderItem = new FBAOutboundServiceMWS_Model_FulfillmentOrderItem();
	  	
	  	$sellerSKU = "MC_" . $productInfo->sn . '_' .  $this->normalizeSizeData($item->data['Size']);
	  	$orderItemId = "MC_" . $productInfo->sn;
	  	$orderItem->setSellerSKU($sellerSKU);
	  	$orderItem->setSellerFulfillmentOrderItemId($orderItemId);
	  	$orderItem->setQuantity($item->qty);
	  	$totalQty += $item->qty;
	  	$orderItemList[] = $orderItem;
	}
  	$fulfillmentOrderItems = new FBAOutboundServiceMWS_Model_FulfillmentOrderItemList();
  	$fulfillmentOrderItems->setmember($orderItemList);
  	$request->setItems($fulfillmentOrderItems);
  	
  	$amazonResult = $this->invokeGetFulfillmentPreview($service, $request);
  	$basePayment = 9.5;
  	$extraShippingFeePerProduct = 2.0; // 每件衣服多加2美元
  	$amazonResult['estimatedFees'] = $basePayment + $extraShippingFeePerProduct * $totalQty;
  	return $amazonResult;
  }
  
  private function normalizeSizeData($sizeData) {
  	$sizeArray = preg_split('/\s+/', $sizeData);
  	$size = '';
  	foreach ($sizeArray as $sizeItem) {
  		$size .= ucfirst($sizeItem);
  	}
  	return $size;
  }
  
  private function invokeGetFulfillmentPreview(FBAOutboundServiceMWS_Interface $service, $request) {
  	$amazonResult = array();
  	try {
  		$response = $service->getFulfillmentPreview($request);
  		if ($response->isSetGetFulfillmentPreviewResult()) {
  			$getFulfillmentPreviewResult = $response->getGetFulfillmentPreviewResult();
  			if ($getFulfillmentPreviewResult->isSetFulfillmentPreviews()) {
  				$fulfillmentPreviews = $getFulfillmentPreviewResult->getFulfillmentPreviews();
  				$memberList = $fulfillmentPreviews->getmember();
  				foreach ($memberList as $member) {
  					if ($member->isSetShippingSpeedCategory())
  					{
  						$amazonResult['speedCategory'] = $member->getShippingSpeedCategory();
  					}
  					if ($member->isSetIsFulfillable())
  					{
  						$amazonResult['isFulfillable'] = ($member->getIsFulfillable() != "false");
  					}
  					/*
  					if ($member->isSetEstimatedFees()) {
  						$amazonResult['estimatedFees'] = 0.0;
  						$estimatedFees = $member->getEstimatedFees();
  						$member1List = $estimatedFees->getmember();
  						foreach ($member1List as $member1) {
  							if ($member1->isSetAmount()) {
  								$amount = $member1->getAmount();
  								if ($amount->isSetValue())
  								{
  									$amazonResult['estimatedFees'] += doubleval($amount->getValue());
  								}
  							}
  						}
  					}*/
  				}
  			}
  		}
  		$amazonResult['success'] = true;
  	} catch (FBAOutboundServiceMWS_Exception $ex) {
  		$exception_info = array();
  		$exception_info[] = "Caught Exception: " . $ex->getMessage();
  		$exception_info[] = "Response Status Code: " . $ex->getStatusCode();
  		$exception_info[] = "Error Code: " . $ex->getErrorCode();
  		$exception_info[] = "Error Type: " . $ex->getErrorType();
  		$exception_info[] = "Request ID: " . $ex->getRequestId();
  		$exception_info[] = "Request ID: " . $ex->getRequestId();
  		log::save('error', 'amazon fulfillment service error', $exception_info);
  		$amazonResult['success'] = false;
  	}
  	return $amazonResult;
  }

  public function createAmazonShippingOrder($order) {

  	include_once (LIBPATH . '/FBAOutboundServiceMWS/config.inc.php');
  	
  	$config = array (
  			'ServiceURL' => MWS_ENDPOINT_URL,
  			'ProxyHost' => null,
  			'ProxyPort' => -1,
  			'MaxErrorRetry' => 3
  	);
  	
  	$service = new FBAOutboundServiceMWS_Client(
  			ACCESS_KEY_ID,
  			SECRET_ACCESS_KEY,
  			$config,
  			APPLICATION_NAME,
  			APPLICATION_VERSION);
  	$request = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderRequest();
  	$request->setSellerId(SELLER_ID);
  	$request->setSellerFulfillmentOrderId($order->number);
  	$address = new FBAOutboundServiceMWS_Model_Address();
  	$address->setName($order->delivery_first_name . ' ' . $order->delivery_last_name);
  	$address->setLine1($order->delivery_address);
  	$address->setCity($order->delivery_city);
  	$stateOrProvinceCode = User_Model::getInstance()->getStateOrProvinceCode($order->delivery_cid, $order->delivery_pid);
  	if ($stateOrProvinceCode === false) {
  		return false;
  	}
  	$address->setStateOrProvinceCode($stateOrProvinceCode->code);
  	$address->setPostalCode($order->delivery_postcode);
  	$address->setCountryCode('US');
  	$address->setPhoneNumber($order->delivery_mobile);
  	$request->setDestinationAddress($address);
  	
  	$request->setShippingSpeedCategory('Standard');
  	$request->setDisplayableOrderId($order->number);
  	$request->setDisplayableOrderDateTime(date('c'));
  	$request->setDisplayableOrderComment($order->number);
  	$orderItemList = array();
  	$productInstance = Product_Model::getInstance();
  	foreach($order->items as $item) {
  		$sellerSKU = "MC_" . $item->sn . '_' . $this->normalizeSizeData($item->data['Size']);
  		$orderItemId = "MC_" . $item->sn;
  		$orderItem = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItem();
  		$orderItem->setSellerSKU($sellerSKU);
  		$orderItem->setSellerFulfillmentOrderItemId($orderItemId);
  		$orderItem->setQuantity($item->qty);
  		$orderItemList[] = $orderItem;
  	}
  	$fulfillmentOrderItems = new FBAOutboundServiceMWS_Model_CreateFulfillmentOrderItemList();
  	$fulfillmentOrderItems->setmember($orderItemList);
  	$request->setItems($fulfillmentOrderItems);
  	 
  	return $this->invokeCreateFulfillmentOrder($service, $request);
  }
  
  private function invokeCreateFulfillmentOrder(FBAOutboundServiceMWS_Interface $service, $request)
  {
  	try {
  		$response = $service->createFulfillmentOrder($request);
  		return true;
  	} catch (FBAOutboundServiceMWS_Exception $ex) {
  		log::save('exception', 'fba exception', $ex);
  		return false;
  	}
  }
  
}