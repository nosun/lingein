<?php
class Payment_Controller extends Bl_Controller
{
  public static function __router($paths)
  {
    $paymentInstance = Payment_Model::getInstance();
    if (!isset($paths[1]) || !($instance = $paymentInstance->getPaymentInstance($paths[1]))) {
      goto404(t('Payment not found'));
    }
    $action = array_shift($paths);
    $paths[0] = $instance;
    return array(
      'action' => $action,
      'arguments' => $paths,
    );
  }

  /**
   * @param Payment_Server_Interface $instance
   */
  public function noticeAction($instance)
  {
  	if ($instance instanceof Payment_Server_Interface) {
  		$return = $instance->callback();
  		if (isset($return) && $return['verified']) {
  			$orderInstance = Order_Model::getInstance();
  			$orderInfo = $orderInstance->getOrederInfoByNumber($return['orderNumber']);
  			if ($return['updateOrderStatus'] && true === $orderInstance->dealOrderStatus($return)) {
  				widgetCallFunctionAll('pay', $return);
  				callFunction('order', 'after_pay', array('oid' => $orderInfo->oid));
  				if ($orderInfo->delivery_country == "United States" && $orderInfo->shipping_method == "fast") {
  					$orderInfo->items = $orderInstance->getOrderItems($orderInfo->oid);
  					if(Shipping_Model::getInstance()->createAmazonShippingOrder($orderInfo)) {
  						$orderInstance->updateStatusOrder($orderInfo->oid, array('status_shipping' => 1));
  					}
  				}
  			}
  		}
  	} else {
  		// TODO 通知接口无效
  	}
  }

  /**
   * @param Payment_Interface $instance
   */
  public function callbackAction($instance, $orderNumber=null)
  {
    if ($instance instanceof Payment_Interface) {
      $return = $instance->callback($orderNumber);
      $orderInstance = Order_Model::getInstance();
      $number = $return['orderNumber'];
      
      $orderInfo = $orderInstance->getOrederInfoByNumber($number);
      if ($return['updateOrderStatus'] && true === $orderInstance->dealOrderStatus($return)) {
        callFunction('order', 'after_pay', array('oid' => $orderInfo->oid));
      }
      $orderInfo = $orderInstance->getOrederInfoByNumber($number);
      
      if (isset($return) && $return['verified']) {
        //支付通知邮件
        $emailSetting = Bl_Config::get('orderPayEmail', array(0));
        if ($emailSetting['status']) {
          $stmpSetting = Bl_Config::get('stmp', 0);
          if ($stmpSetting['stmpserver'] && $stmpSetting['stmpuser'] && $stmpSetting['stmppasswd']) {
            $mailInstance = new Mail_Model($stmpSetting);
            $email[] = $orderInfo->delivery_email;
            $siteInfo = Bl_Config::get('siteInfo', array());
            if(key_exists('ccadmin', $emailSetting) && $emailSetting['ccadmin'] == '1'){
              	$email[] = isset($siteInfo['email']) ? $siteInfo['email'] : null;
          	  }
            //added by pzzhang: FIX BUG: Can Not Render the Payment Notice Email Parameters.
            $emailSetting['title'] = $mailInstance->ReplaceMailToken($emailSetting['title'], $orderInfo);
          	$emailSetting['content'] = $mailInstance->ReplaceMailToken($emailSetting['content'], $orderInfo);
          	//end added by pzzhang.
            if ($mailInstance->sendMail($email, $emailSetting['title'], $emailSetting['content'], $emailSetting['type'])) {
              setMessage(t('Payment Success!'));
            } else {
              setMessage(t('Payment Success, met trouble on sending mail.'), 'error');
            }
          } else {
            setMessage(t('Mail server information is not configured properly, please check'), 'error');
          }
        }
        
        $productInstance = Product_Model::getInstance();
        if ($orderInfo->items) {
  	      foreach ($orderInfo->items as $k => $v) {
  	        $orderInfo->goods_number += $v->qty;
  	        $productInstance->updateTransactions($v->pid, $v->qty);
  	      }
        }
        if (isset($return['redirect']) && !empty($return['redirect'])) {
          gotoUrl($return['redirect']);
        }

      } else {
      	if(isset($return) && isset($return['message'])){
      		setMessage(t($return['message']), 'error');
      	}
      }
    } else {
      setMessage(t('Payment does not exist'), 'error');
    }
    
    if(isset($orderInfo)){
        $paymentInstance = Payment_Model::getInstance();
        $paymentList = $paymentInstance->getPaymentsList();
        $orderInfo->payment = $paymentList[$orderInfo->payment_method];
        $shippingInstance = Shipping_Model::getInstance();
        $shippingList = $shippingInstance->shippingList(true);
        $orderInfo->shipping = $shippingList[$orderInfo->shipping_method];
    }
    
    $this->view->render('checkoutcallback.phtml', array(
      'orderInfo' => isset($orderInfo) ? $orderInfo : null,
      'message' => isset($return['message']) ? $return['message'] : '',
      'verified' => isset($return['verified']) ? $return['verified'] : false,
    ));
  }

  public function failAction($instance)
  {
    echo '<h1>ERROR</h1>';
    var_dump($_GET, $_POST);
  }
}
