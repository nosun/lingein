<?php
class Mail_Model extends Bl_Model
{
	public $_mail;

	private $_var;

  /**
   * @return Mail_Model
   */
  public static function getInstance()
  {
    return parent::getInstance(__CLASS__);
  }

  public function __construct($set)
  {
  	require LIBPATH.'/class.phpmailer.php';
	  $this->_mail = new PHPMailer();
	  $this->_mail->IsSMTP();
		$this->_mail->CharSet = "utf-8";
		$this->_mail->SMTPDebug = 0;
		$this->_mail->SMTPAuth = true;
		$this->_mail->SMTPSecure = "ssl";
		$this->_mail->Host = $set['stmpserver'];
		$this->_mail->Port = $set['stmpport'];
		$this->_mail->Username = $set['stmpuser'];
		$this->_mail->Password = $set['stmppasswd'];
//		$this->_mail->SMTPDebug = true;

		if(!isset($this->_var)){
			$this->_var = new stdClass();
		}
		
		$this->_var->mailfrom = isset($set['mailfrom']) ? $set['mailfrom'] : $set['stmpuser'];
		$this->_var->mailfromname = $set['mailfromname'] ? $set['mailfromname'] : $set['stmpuser'];
		$this->_var->mailreply = $set['mailreply'] ? $set['mailreply'] : $set['stmpuser'];
		$this->_var->mailreplyname = $set['mailreplyname'] ? $set['mailreplyname'] : $set['stmpuser'];
	}

	/**
	 *
	 * 发送邮件方法
	 * @param string $address 发送邮件地址，多个用,隔开
	 * @param string $subject 邮件标题
	 * @param string $content 邮件内容
	 */
  public function sendMail($address, $subject, $content, $ishtml , $username = '')
  {
    $this->_mail->SetFrom($this->_var->mailfrom, $this->_var->mailfromname);
    $this->_mail->AddReplyTo($this->_var->mailreply, $this->_var->mailreplyname);
    $this->_mail->Subject = $subject;
    if ($ishtml == 'html') {
    	$this->_mail->MsgHTML($content);
    } else {
    	$this->_mail->Body = $content;
    }
    $this->_mail->ClearAddresses();
    $this->_mail->ClearBCCs();
    if (is_array($address)) {
      if (isset($address[0]))
      $this->_mail->AddAddress($address[0], $username);
      if (isset($address[1]))
      $this->_mail->AddBCC($address[1], $username);
    } else {
      $this->_mail->AddAddress($address, $username);
    }
    if(!$this->_mail->Send()) {
      return false;
    } else {
      return true;
    }
  }

  public function ReplaceMailToken($content, $orderInfo = null, $uid = null)
  {
     global $user;
     if(!isset($uid)){
     	$uid = $user->uid;
     }
     $userInstance = User_Model::getInstance();
     $userInfo = $userInstance->getUserInfo($uid);
     //the $userInfo is not always corrected. Maybe the admin changed the status, and sent the mail.
     //So add a workaround for the correct $userInfo;
     
     if(isset($orderInfo)){
        $userInfo = User_Model::getInstance()->getUserInfo($orderInfo->uid);
     }

     $siteInfo = Bl_Config::get('siteInfo', array());
     $contactWay = Bl_Config::get('contactWay', array());
     
     $usertokens = array('name', 'email');
     $sitetokens = array('sitename', 'siteurl', 'email', 'copyright', 'template');
     $ordertokens = array('oid', 'number','delivery_first_name','delivery_last_name','delivery_email',
       'delivery_phone','delivery_mobile','delivery_country','delivery_province','delivery_city',
       'delivery_address','delivery_postcode','delivery_time','payment_method','payment_status',
       'shipping_method','shipping_no','shipping_fee','paySubmit','goods_number'
     );

     $content = str_replace('{[time]}', date('Y-m-d H:i:s', TIMESTAMP), $content);

     foreach ($usertokens as $v) {
       $content = str_replace('{[user.' . $v . ']}', $userInfo->$v, $content);
     }

     foreach ($sitetokens as $v) {
       if($v =='siteurl'){
        $siteurl = str_replace('http://', '', $siteInfo[$v]);
        $siteurl = trim($siteurl, '/');

        $siteInfo[$v] = 'http://' . $siteurl . '/';
       }else if($v == 'template'){
       	$siteInfo['template'] = Bl_Config::get('template', 'default');
       }
       
       $content = str_replace('{[site.' . $v . ']}', $siteInfo[$v], $content);
     }

     foreach ($ordertokens as $v) {
       if(isset($orderInfo)){
         if($v == 'shipping_method'){
           if(strtoupper($orderInfo->$v) == 'EMS'){
             $content = str_replace('{[order.' . $v . ']}', 'Standard Shipping', $content);    
           }else if(strtoupper($orderInfo->$v) == 'UPS'){
             $content = str_replace('{[order.' . $v . ']}', 'Expedited Shipping', $content);    
           }else{
             $content = str_replace('{[order.' . $v . ']}', $orderInfo->$v, $content);
           }
         }else if($v == 'shipping_fee' && isset($orderInfo->fees['shipping']->fee_value)){
           $content = str_replace('{[order.' . $v . ']}', c($orderInfo->fees['shipping']->fee_value), $content);
         }else if($v == 'payment_status' && isset($orderInfo->status_payment)){
           $status_str = 'Not Paid';
           if($orderInfo->status_payment == 1){
             $status_str = 'Paid';
           }
           $content = str_replace('{[order.' . $v . ']}', $status_str, $content);
         }else if($v == 'shipping_no' && isset($orderInfo->data['shipping_no'])){
         	$content = str_replace('{[order.' . $v . ']}', $orderInfo->data['shipping_no'], $content);
         }
         else if(property_exists($orderInfo, $v)){
           $content = str_replace('{[order.' . $v . ']}', $orderInfo->$v, $content);
         }
       }
     }

       if (isset($orderInfo) && isset($orderInfo->items)) {
       $str = '<table style="border-collapse: collapse; border-spacing: 0px; " align="center" cellpadding="0" cellspacing="2" width="95%">';
       $str .= '<tbody>';
       $str .= '<tr class="formHeader" style="background-image: none; background-attachment: scroll; background-color: rgb(255, 247, 247); border: 1px solid rgb(238, 206, 207); color: rgb(97, 97, 97); font-size: 12px; margin-bottom: 15px; padding: 5px 10px; background-position: 0px 0px; background-repeat: repeat repeat; ">
					<td class="In_line" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(238, 206, 207); border-top-width: 1px; border-top-style: solid; border-top-color: rgb(238, 206, 207); color: rgb(51, 51, 51); font-weight: bold; padding: 5px; font-family: Arial; " align="center" bgcolor="#FFF7F7" height="29">
						<strong style="font-weight: bold; ">Image</strong>
					</td>
					<td class="In_line" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(238, 206, 207); border-top-width: 1px; border-top-style: solid; border-top-color: rgb(238, 206, 207); color: rgb(51, 51, 51); font-weight: bold; padding: 5px; font-family: Arial; " bgcolor="#FFF7F7" height="29">
						<strong style="font-weight: bold; ">Item Description</strong>
					</td>
					<td class="In_line" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(238, 206, 207); border-top-width: 1px; border-top-style: solid; border-top-color: rgb(238, 206, 207); color: rgb(51, 51, 51); font-weight: bold; padding: 5px; font-family: Arial; " align="center" bgcolor="#FFF7F7" width="79">
						<strong style="font-weight: bold; ">Quantity</strong>
					</td>
						<td class="In_line" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(238, 206, 207); border-top-width: 1px; border-top-style: solid; border-top-color: rgb(238, 206, 207); color: rgb(51, 51, 51); font-weight: bold; padding: 5px; font-family: Arial; " align="center" bgcolor="#FFF7F7" width="84">
							<strong style="font-weight: bold; ">Unit Price</strong>
						</td>
					<td class="In_line" style="border-bottom-width: 1px; border-bottom-style: solid; border-bottom-color: rgb(238, 206, 207); border-top-width: 1px; border-top-style: solid; border-top-color: rgb(238, 206, 207); color: rgb(51, 51, 51); font-weight: bold; padding: 5px; font-family: Arial; " align="center" bgcolor="#FFF7F7" width="76">
							<strong style="font-weight: bold; ">Subtotal</strong>
					</td>
				</tr>';
       if(is_array($orderInfo->items)){
         foreach ($orderInfo->items as $v) {
           $str .= '<tr style="border: 1px solid rgb(238, 206, 207); "><td style="padding: 5px; font-family: verdana; " align="center" height="100"><a href="' . url($v->url) . '" style="color: rgb(136, 34, 68); text-decoration: none; font-weight: bold; font-family: verdana; "><img style="border: medium none; " height="100" src="' . urlimg('admin_product_album', $v->filepath) . '"/></a></td>'.
           '<td style="padding: 5px; font-family: verdana; " align="left"><a style="color: rgb(136, 34, 68); text-decoration: none; font-weight: bold; font-family: verdana; font-size:12px;" href="'.url($v->url). '">' . $v->name . '</a><br/>';
         if (isset($v->data) && $v->data != '') {
             foreach ($v->data as $k1 => $v1) {
               $str .= '<p style="margin: 0px; padding: 3px 0px; font-size: 12px; color: rgb(51, 51, 51); "><p style="margin: 0px; padding: 3px 0px; font-size: 12px; color: rgb(51, 51, 51); "><strong>'.$k1.':</strong><span>&nbsp;</span>&nbsp;&nbsp;<span>'.$v1.'</span></p>';
             }
           }
           $str .= '</td><td style="padding: 5px; font-family: verdana; " align="center"><span>'. $v->qty .'</span></td>';
           $str .= '<td style="padding: 5px; font-family: verdana; " align="center"><span style="color:#a72d2c;font-weight: bold; font-size:12px;">'. (isset($v->pay_price) ? c($v->pay_price) : null) .'</span></td>';
           $str .= '<td style="padding: 5px; font-family: verdana; " align="center"><span style="color:#a72d2c;font-weight: bold; font-size:12px;">'. (isset($v->total_amount) ? c($v->total_amount) : null) .'</span></td>';
           $str .= '</tr>';
         }
       }

       if(!isset($orderInfo->data['shipping_no']) || $orderInfo->data['shipping_no'] == ''){
	       $str .= '<tr style="border: 1px solid rgb(238, 206, 207); "><td colspan="6" class="td-last" style="background-color: rgb(255, 247, 247); font-size: 13px; padding: 5px; font-family: verdana; " align="right" height="74">';
	       $str .= 'Items Total: <span style="color:#a72d2c;padding: 0px 40px; font-weight: bold; ">';
	       $str .= isset($orderInfo->goods_number)?strval($orderInfo->goods_number): strval(count($orderInfo->items));
	       $str .= '</span><span>&nbsp;</span><br />';
	       $str .= 'Subtotal: <span style="color:#a72d2c;padding: 0px 20px; font-weight: bold; ">' . (isset($orderInfo->total_amount) ? c($orderInfo->total_amount) : null) . '</span><span>&nbsp;</span><br />';
	       if(is_array($orderInfo->fees)) foreach ($orderInfo->fees as $v) {
	         $str .= '<span style="text-transform:capitalize;">'. t($v->fee_key) . '</span>: <span style="color:#a72d2c;padding: 0px 20px; font-weight: bold; ">' . c($v->fee_value) . '</span><span>&nbsp;</span><br />';
	       }
	
	       $str .= 'Total Payment:<span style="color:#a72d2c;padding: 0px 20px; font-weight: bold; "><strong style="font-weight: bold; ">' .(isset($orderInfo->pay_amount) ? c($orderInfo->pay_amount) : null) . '</strong></span></td></tr>';
       }
       
       $str .= '</tbody></table>';
       $content = str_replace('{[order.items]}', $str, $content);
     }

     return $content;
  }
}
