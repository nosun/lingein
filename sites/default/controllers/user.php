<?php
class User_Controller extends Bl_Controller
{
  public function loginAction()
  {
    global $user;
    $sid = $user->sid;
    $userInstance = User_Model::getInstance();
    if ($userInstance->logged()) {
      if(isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"]){
        header("Location: ".$_SERVER["HTTP_REFERER"]);
      }else{
        gotoUrl('user/panel');
      }
    }
    if ($this->isPost()) {
      
      if (!isset($_POST['username']) || strlen(trim($_POST['username'])) < 3) {
        setMessage(t('Username must be at least 3 characters long.'), 'error');
        gotoUrl('user/login');
      }
      //test whether the given username is an email address.
      $isEmail = $userInstance->isValidEmail($_POST['username']);
      if (!$uid = $userInstance->validate(trim($_POST['username']), $_POST['password'], $isEmail)) {
        setMessage(t('Username or Password is invalid'), 'error');
        gotoUrl('user/login');
      } else {
        $userInstance->setLogin($uid);
        /*setMessage(t('Landing successful'));*/
        log::save('user', trim($_POST['username']) . ' login.');
        $reffer_url = key_exists('login_redirectUrl', $_SESSION)?$_SESSION['login_redirectUrl']:null;
        unset($_SESSION['login_redirectUrl']);
        
        if(isset($reffer_url)){
        	header("Location: ".$reffer_url);
        }else{
        	gotoUrl('user/panel');
        }
      }
    } else {
      //store the referrer url in a session variable.
    	$referer_url = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:null;
    	
    	if ($this->needRedirectAfterLogin($referer_url)) {
    	    if(isset($_SESSION["login_redirectUrl"])){
    			unset($_SESSION["login_redirectUrl"]);
    		}
    		$_SESSION["login_redirectUrl"] = $referer_url;
    	}
      $this->view->render('login.phtml');
    }
  }

  public function logoutAction()
  {
    $userInstance = User_Model::getInstance();
    if ($userInstance->logged()) {
      $userInstance->setLogout();
    }
    gotoUrl('user/login');
  }

  public function registerAction()
  {
    $userInstance = User_Model::getInstance();
    $siteInstance = Site_Model::getInstance();
    if ($userInstance->logged()) {
      gotoUrl('');
    }
    if ($this->isPost()) {
      $invalidUsername = array(
        'admin',
        'administrator',
      );
      $post = $_POST;
      $postnew = callFunction('register', 'before', $post);
      if (isset($postnew) && $postnew) {
        $post = $postnew;
      }
      if (!isset($post['username']) || strlen(trim($post['username'])) < 3) {
        setMessage(t('Username must be at least 3 characters long.'), 'error');
      } else if (in_array(trim($post['username']), $invalidUsername)) {
        setMessage(t('Username is invalid.'), 'error');
      } else if ($userInstance->getUserInfoByName(trim($post['username']))) {
        setMessage(t('An account already exists.'), 'error');
      } else if (!isset($post['password']) || strlen($post['password']) < 5) {
        setMessage(t('Password must be at least 5 characters long.'), 'error');
      } else if (!isset($post['confirm_password']) || $post['password'] !== $post['confirm_password']) {
        setMessage(t('The passwords you have entered do not match. Please try again.'), 'error');
      } else if (!isset($post['email']) || false !== strpos($post['email'], '..') 
        || !preg_match('/^.{1,64}@[^@]{1,255}$/', trim($post['email']))) {
        setMessage(t('This email address is invalid.'), 'error');
      } else if ($userInstance->isValidEmail($post['email'])){
        setMessage(t('This email address is already registered. Please use another email account.'), 'error');
      }
      else {
        $post['name'] = trim($post['username']);
        $post['passwd'] = trim($post['password']);
        $roles = array();
        $uid = $userInstance->insertUser($post, $roles);
        if ($uid) {
          $userInstance->setLogin($uid);
          $_SESSION['from_register'] = 1;
          widgetCallFunctionAll('register');
          //发送用户注册邮件
          $emailSetting = Bl_Config::get('userRegisterEmail');
          if (isset($emailSetting) && $emailSetting['status']) {
            $stmpSetting = Bl_Config::get('stmp');
            if (isset($stmpSetting) && $stmpSetting['stmpserver'] && $stmpSetting['stmpuser'] && $stmpSetting['stmppasswd'] && $post['email']) {
              $mailInstance = new Mail_Model($stmpSetting);
              $email[] = $post['email'];
              $siteInfo = Bl_Config::get('siteInfo', array());
              if(key_exists('ccadmin', $emailSetting) && $emailSetting['ccadmin'] == '1'){
              	$email[] = isset($siteInfo['email']) ? $siteInfo['email'] : null;
          	  }
          	   $emailSetting['title'] = $mailInstance->ReplaceMailToken($emailSetting['title']);
              $emailSetting['content'] = $mailInstance->ReplaceMailToken($emailSetting['content']);
              if ($mailInstance->sendMail($email, $emailSetting['title'], $emailSetting['content'], $emailSetting['type'])) {
                setMessage('We sent an email to you.');
              } else {
                setMessage('Met error when sending mail, you may not get the email for register.', 'error');
              }
            } else {
              setMessage('Mail server information is not configured properly, please check', 'error');
            }
          }

          gotoUrl('user/registersucc');
        } else {
          setMessage(t('Register fail.'), 'error');
        }
      }
    }else {
      //store the referrer url in a session variable.
    	$referer_url = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:null;
    	if ($this->needRedirectAfterLogin($referer_url)) {
    	    if(isset($_SESSION["login_redirectUrl"])){
    			unset($_SESSION["login_redirectUrl"]);
    		}
    		$_SESSION["login_redirectUrl"] = $referer_url;
    	}
    }
    
    $countries = $siteInstance->getCountries();
    $cid = key($countries);
    $provinces = $siteInstance->getProvinces($cid);
    $this->view->render('register.phtml', array(
      'countries' => isset($countries) ? $countries : array(),
      'provinces' => isset($provinces) ? $provinces : array(),
    ));
  }

  public function registersuccAction()
  {
    if (!isset($_SESSION['from_register'])) {
      gotoUrl('');
    }
    $reffer_url = key_exists('login_redirectUrl', $_SESSION)?$_SESSION['login_redirectUrl']:null;
    unset($_SESSION['login_redirectUrl']);
    unset($_SESSION['from_register']);
    
    $this->view->assign('redirect_url', $reffer_url);
    $this->view->render('registersucc.phtml');
  }

  public function infoAction()
  {
    global $user;
    $userInstance = User_Model::getInstance();
    $siteInstance = Site_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    if ($this->isPost()) {
      $post = $_POST;
      
      if (!isset($post['email']) 
          || false !== strpos($post['email'], '..') 
          || !preg_match('/^.{1,64}@[^@]{1,255}$/', trim($post['email']))) {
          setMessage(t('This email address is invalid.'), 'error');
      }else{
        isset($post['cid']) ? $set['cid'] = $post['cid'] : 0;
        isset($post['pid']) ? $set['pid'] = $post['pid'] : 0;
        isset($post['country']) ? $set['country'] = $post['country'] : '';
        isset($post['province']) ? $set['province'] = $post['province'] : '';
        isset($post['city']) ? $set['city'] = $post['city'] : '';
        isset($post['nickname']) ? $set['nickname'] = $post['nickname'] : '';
        isset($post['email']) ? $set['email'] = $post['email'] : '';
        isset($post['phone']) ? $set['phone'] = $post['phone'] : '';
        isset($post['mobile']) ? $set['mobile'] = $post['mobile'] : '';
        isset($post['gender']) ? $set['gender'] = $post['gender'] : '';
        isset($post['postcode']) ? $set['postcode'] = $post['postcode'] : '';
        isset($post['area']) ? $set['area'] = $post['area'] : '';
        isset($post['data']) ? $set['data'] = $post['data'] : '';
        if (trim($post['birthday']) != '' && preg_match('/^(\w{4})[-\/](\w{1,2})[-\/](\w{1,2})$/', trim($post['birthday']), $matches)) {
          $set['birthday'] = $matches[1] . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . str_pad($matches[3], 2, '0', STR_PAD_LEFT);
        } else {
          $set['birthday'] = '';
        }
        $result = $userInstance->updateUser($user->uid, $set);
        if ($result) {
          setMessage(t('Modify success'));
        } else {
          setMessage(t('Modified failure'));
        }
      }
      gotoUrl('user/info');
    } else {
      $countries = $siteInstance->getCountries();
      $cid = isset($user->cid) ? $user->cid : key($countries);
      $provinces = $siteInstance->getProvinces($cid);
      $user = $userInstance->getUserInfo($user->uid);
      $this->view->assign('tmark', 'info');
      $this->view->assign('templatefile', 'u_userinfo.phtml');
      $this->view->render('personalcenter.phtml', array(
        'user' => isset($user) ? $user : null,
        'countries' => isset($countries) ? $countries : array(),
        'provinces' => isset($provinces) ? $provinces : array(),
      ));
    }
  }

  public function pwdAction()
  {
    global $user;
    $userInstance = User_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    if ($this->isPost()) {
      $post = $_POST;
      if ($post['newpwd'] != $post['newpwd2']){
        setMessage(t('The two passwords are not equal'));
      } else {
        if ($uid = $userInstance->validate($user->name, $post['oldpwd'])) {
          $result = $userInstance->updateUser($user->uid, array('passwd' => $post['newpwd']));
          if ($result) {
            setMessage(t('Modify success'));
          } else {
            setMessage(t('Modified failure'));
          }
        } else {
          setMessage(t('The old password mistake'));
        }
      }
      gotoUrl('user/pwd');

    } else {
      $this->view->assign('tmark', 'info');
      $this->view->assign('templatefile', 'u_userpwd.phtml');
      $this->view->render('personalcenter.phtml', array(
        'user' => isset($user) ? $user : null,
      ));
    }
  }

  public function addresslistAction()
  {
    global $user;
    $userInstance = User_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    $adressList = $userInstance->getDeliveryRecordList($user->uid);
    $this->view->assign('tmark', 'address');
    $this->view->assign('templatefile', 'u_addresslist.phtml');
    $this->view->render('personalcenter.phtml', array(
      'adressList' => isset($adressList) ? $adressList : null,
    ));
  }

  public function editaddressAction($rid = null)
  {
    global $user;
    $userInstance = User_Model::getInstance();
    $siteInstance = Site_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    if ($this->isPost()) {
      $post = $_POST;
      $set = array(
        'delivery_first_name' => $post['delivery_first_name'],
        'delivery_last_name' => $post['delivery_last_name'],
        'delivery_email' => $post['delivery_email'],
        'delivery_phone' => $post['delivery_phone'],
        'delivery_mobile' => $post['delivery_mobile'],
        'delivery_city' => $post['delivery_city'],
        'delivery_cid' => $post['delivery_cid'],
        'delivery_pid' => $post['delivery_pid'],
        'delivery_address' => $post['delivery_address'],
        'delivery_postcode' => $post['delivery_postcode'],
        'delivery_time' => $post['delivery_time'],
        'default' => isset($post['default']) ? 1 : 0,
      );
      list($set['delivery_country'], $set['delivery_province']) = $siteInstance->getCountryProvincesNames($post['delivery_cid'], $post['delivery_pid']);
    	if(empty($set['delivery_province']) && $post['delivery_or_province'])
      {
      	$set['delivery_province'] = $post['delivery_or_province'];
      }
      if ($post['rid']) {
        $status = $userInstance->updateDeliveryRecord($post['rid'], $set);
      } else {
        $status = $userInstance->insertDeliveryRecord($set);
      }
      if ($status) {
        setMessage(t('The address was successfully updated.'));
      } elseif ($status == 0) {
        setMessage(t('No updates.'));
      } else {
        setMessage(t('Updating address information failed.'));
      }
      gotoUrl('user/addresslist');
    } else {
      if (isset($rid)) {
        $fitter = array('uid' => $user->uid);
        $addressInfo = $userInstance->getDeliveryRecordInfo($rid, $fitter);
      }
      $siteInstance = Site_Model::getInstance();
      $countries = $siteInstance->getCountries();
      $cid = isset($addressInfo->delivery_cid) && $addressInfo->delivery_cid ? $addressInfo->delivery_cid : key($countries);
      $provinces = $siteInstance->getProvinces($cid);
      $this->view->assign('tmark', 'address');
      $this->view->assign('templatefile', 'u_addressinfo.phtml');
      $this->view->render('personalcenter.phtml', array(
        'addressInfo' => isset($addressInfo) ? $addressInfo : null,
        'countries' => isset($countries) ? $countries : null,
        'provinces' => isset($provinces) ? $provinces : null,
        'cid' => isset($cid) ? $cid : null
      ));
    }
  }

  public function deleteaddressAction($rid)
  {
    global $user;
    $userInstance = User_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    $fitter = array('uid' => $user->uid);
    if (!$userInstance->getDeliveryRecordInfo($rid, $fitter)) {
      goto404(t('Delete failure'));
    }
    if ($userInstance->deleteDeliveryRecord($rid, $user->uid)) {
      setMessage(t('Delete success'));
    } else {
      setMessage(t('Delete failure'));
    }
    gotoUrl('user/addresslist');
  }

  public function ajaxgetaddressAction($rid)
  {
    $userInstance = User_Model::getInstance();
    $addressInfo = $userInstance->getDeliveryRecordInfo($rid);
    echo json_encode($addressInfo);
  }

  public function ajaxAddAddressAction()
  {
  	global $user;
  	$result = array(
  			"success" => false,
  			"action" => "",
  			"data" => "");
  	$userInstance = User_Model::getInstance();
  	$siteInstance = Site_Model::getInstance();
  	if (!$userInstance->logged())
  	{
  		$result["success"] = false;
  		$result["action"] = "window.location=" . url("user/login");
  	}
  	else
  	{
  		$post = $_POST;
  		$addedAddress = $this->addAddress($post, $error);
  		if ($addedAddress === false)
  		{
  			$result["success"] = false;
  			$result["data"] = $error;
  		}
  		else
  		{
  			$addedAddress = (object)$addedAddress;
  			$cartItemIds = explode(',', $post['pids']);
  			$cartInfo = Cart_Model::getInstance()->getCartProductList($cartItemIds, null, null, false);
  			$shippingMethodList = Shipping_Model::getInstance()->getAvaiableShippingMethod($addedAddress, $cartInfo);
  				
  			$result["success"] = true;
  			$result["data"] = $userInstance->getDeliveryRecordList($user->uid);
  			$result['shippingMethodList'] = $shippingMethodList;
  		}
  	}
  
  	echo json_encode($result);
  }
  
  public function ajaxUpdateAddressAction()
  {
  	global $user;
  	$result = array(
  			"success" => false,
  			"action" => "",
  			"data" => "");
  	$userInstance = User_Model::getInstance();
  	$siteInstance = Site_Model::getInstance();
  	if (!$userInstance->logged())
  	{
  		$result["success"] = false;
  		$result["action"] = "window.location=" . url("user/login");
  	}
  	else
  	{
  		$post = $_POST;
  		$updatedAddress = $this->updateAddress($post, $error);
  		if ($updatedAddress === false)
  		{
  			$result["success"] = false;
  			$result["data"] = $error;
  		}
  		else
  		{
  			$updatedAddress = (object)$updatedAddress;
  			$cartItemIds = explode(',', $post['pids']);
  			$cartInfo = Cart_Model::getInstance()->getCartProductList($cartItemIds, null, null, false);
  			$shippingMethodList = Shipping_Model::getInstance()->getAvaiableShippingMethod($updatedAddress, $cartInfo);
  			
  			$result["success"] = true;
  			$result["data"] = $userInstance->getDeliveryRecordList($user->uid);
  			$result['shippingMethodList'] = $shippingMethodList;
  		}
  	}
  	echo json_encode($result);
  }
  
  public function parsePostData($post, &$set, $rid=null)
  {
  	global $user;
  	$suffix = "";
  	if (isset($rid))
  	{
  		$suffix = "_" . $rid;
  	}
  	$set = array(
  			'delivery_first_name' => $post['delivery_first_name' . $suffix],
  			'delivery_last_name' => $post['delivery_last_name' . $suffix],
  			'delivery_email' => $user->email,
  			'delivery_mobile' => $post['delivery_mobile' . $suffix],
  			'delivery_city' => $post['delivery_city' . $suffix],
  			'delivery_address' => $post['delivery_address' . $suffix],
  			'delivery_postcode' => $post['delivery_postcode' . $suffix],
  			'default' => isset($post['default' . $suffix]) && (int)$post['default' . $suffix] > 0 ? 1 : 0,
  	);
  	$cid = isset($post['delivery_cid' . $suffix]) ? $post['delivery_cid' . $suffix] : $post['delivery_cid_select' . $suffix];
  	$set['delivery_cid'] = $cid;
  	$pid = isset($post['delivery_pid' . $suffix]) ? $post['delivery_pid' . $suffix] : $post['delivery_pid_select' . $suffix];
  	$set['delivery_pid'] = $pid;
  	list($set['delivery_country'], $set['delivery_province']) = Site_Model::getInstance()->getCountryProvincesNames($set['delivery_cid'], $set['delivery_pid']);
  	if(empty($set['delivery_province']))
  	{
  		$set['delivery_province'] = $post['delivery_province' . $suffix];
  	}
  }
  
  public function addAddress($post, &$error)
  {
  	if (!$this->checkAddress($post, null, $error))
  	{
  		return false;
  	}
  	$this->parsePostData($post, $set);
  	$status = User_Model::getInstance()->insertDeliveryRecord($set);
  	if (!$status)
  	{
  		$error = t('insert shipping address failed.');
  		return false;
  	}
  	return $set;
  }
  
  public function updateAddress($post, &$error)
  {
  	if (!$this->checkAddress($post, null, $error))
  	{
  		return false;
  	}
  	if (isset($post['delivery_rid']) && ($rid = (int)$post['delivery_rid']) <= 0)
  	{
  		return false;
  	}
  	$this->parsePostData($post, $set);
  	if (!User_Model::getInstance()->updateDeliveryRecord($rid, $set))
  	{
  		$error = t('update address failed');
  		return false;
  	}
  	return $set;
  }
  public function checkAddress($post, $rid = null, &$error = null)
  {
  	$suffix = "";
  	if (isset($rid))
  	{
  		$suffix = "_" . $rid;
  	}
  	if (!isset($post['delivery_first_name' . $suffix]) || trim($post['delivery_first_name' . $suffix]) == "")
  	{
  		$error = t('first name is empty');
  		return false;
  	}
  	if (!isset($post['delivery_last_name' . $suffix]) || trim($post['delivery_last_name' . $suffix]) == "")
  	{
  		$error = t('last name is empty');
  		return false;
  	}
  	if (!isset($post['delivery_address' . $suffix]) || trim($post['delivery_address' . $suffix]) == "")
  	{
  		$error = t('address is empty');
  		return false;
  	}
  	if (!isset($post['delivery_city' . $suffix]) || trim($post['delivery_city' . $suffix]) == "")
  	{
  		$error = t('city is empty');
  		return false;
  	}
  	if ((!isset($post['delivery_province' . $suffix]) || trim($post['delivery_province' . $suffix]) == "") &&
  	(!isset($post['delivery_pid' . $suffix]) || (int)$post['delivery_pid' . $suffix] <= 0) &&
  	(!isset($post['delivery_pid_select' . $suffix]) || (int)$post['delivery_pid_select' . $suffix] <= 0))
  	{
  		$error = t('province is empty');
  		return false;
  	}
  	if ((!isset($post['delivery_cid' . $suffix]) || trim($post['delivery_cid' . $suffix]) == "") &&
  	(!isset($post['delivery_cid_select' . $suffix]) || trim($post['delivery_cid_select' . $suffix]) == ""))
  	{
  		$error = t('country is empty');
  		return false;
  	}
  	if (!isset($post['delivery_mobile' . $suffix]) || trim($post['delivery_mobile' . $suffix]) == "")
  	{
  		$error = t('phone number is empty');
  		return false;
  	}
  	return true;
  }
  
  public function getPasswordAction()
  {
    if ($this->isPost()) {
      $username = isset($_POST['username']) ? $_POST['username'] : null;
      $email = isset($_POST['email']) ? $_POST['email'] : null;
      $this->_getPasswordSendMail($email);
      gotoUrl('user/login');
    } else {
      $this->view->render('getpassword.phtml');
    }
  }

  private function _getPasswordSendMail($email)
  {
    $userInstance = User_Model::getInstance();
    $userId = $userInstance->isValidEmail($email);
    if(!$userId){
      setMessage(t('No user matches the email provided.'), 'error');
      return false;
    }
    $userInfo = $userInstance->getUserInfo($userId);
    $username = $userInfo->name;
    if (!isset($userInfo) || !$userInfo) {
      setMessage(t('No user matches the email provided.'), 'error');
      return false;
    }
    if ($userInfo->email != $email) {
      setMessage(t('Input mailbox and register mailbox inconsistent'), 'error');
      return false;
    }
    $emailSetting = Bl_Config::get('getPasswordEmail');
    if (!isset($emailSetting) || !$emailSetting['status']) {
      
    }
    $stmpSetting = Bl_Config::get('stmp');
    if (!isset($stmpSetting) || !$stmpSetting['stmpserver']
    || !$stmpSetting['stmpuser'] || !$stmpSetting['stmppasswd']) {
      setMessage(t('Mail server information is not configured properly, please check'), 'error');
      return false;
    }
    $token = randomString(16);
    $time = time();
    $data = array(
      'token' => $token,
      'time' => $time,
    );
    if(!$userInstance->updateUser($userInfo->uid, array('data' => $data))) {
      setMessage(t('Database error'), 'error');
      return false;
    }
    $mailInstance = new Mail_Model($stmpSetting);
    $url = url('user/editpassword/' . $username . '/' . $token);
    $str = '<a href="' . $url . '"> Click here to change the password!</a>';
    $emailSetting['content'] = str_replace('{[url]}', $str, $emailSetting['content']);
    $emails[] = $email;
    $siteInfo = Bl_Config::get('siteInfo', array());

    if(key_exists('ccadmin', $emailSetting) && $emailSetting['ccadmin'] == '1'){
        $emails[] = isset($siteInfo['email']) ? $siteInfo['email'] : null;
    }
    $emailSetting['title'] = $mailInstance->ReplaceMailToken($emailSetting['title'], null, $userId);
    $emailSetting['content'] = $mailInstance->ReplaceMailToken($emailSetting['content'], null, $userId);
    $title = $emailSetting['title'];
    $content =  $emailSetting['content'];
    if (!$mailInstance->sendMail($emails, $title, $content, 'html')) {
      setMessage(t('send mail error'), 'error');
      return false;
    }
    setMessage(t('We have sent an email to '. $email.', please check for next step.'));
    return true;
  }

  public function editPasswordAction($username = null, $token = null)
  {
    if ($this->isPost()) {
      $username = isset($_POST['username']) ? $_POST['username'] : null;
      $token = isset($_POST['token']) ? $_POST['token'] : null;
      $newpwd = isset($_POST['newpwd']) ? $_POST['newpwd'] : null;
      $newpwd2 = isset($_POST['newpwd2']) ? $_POST['newpwd2'] : null;
      if (!isset($newpwd) || !isset($newpwd2) || $newpwd != $newpwd2) {
        setMessage(t('2 times the password'));
      } elseif (strlen($newpwd)< 5 || strlen($newpwd)> 12) {
        setMessage(t('Password must be greater than 5 persons, and less than 12'), 'error');
      }else {
        if (isset($username) && $username && isset($token) && $token) {
          $userInstance = User_Model::getInstance();
          $userInfo = $userInstance->getUserInfoByName($username);
          if (!isset($userInfo)) {
              setMessage(t('This user no longer exists'), 'error');
          } else {
            $datatoken = isset($userInfo->data['token']) ? $userInfo->data['token'] : null;
            $time = time();
            if (isset($userInfo->data['time']) && $userInfo->data['time'] > ($time - 3600*24)) {
              if (isset($datatoken) && $datatoken == $token) {
                $result = $userInstance->updateUser($userInfo->uid, array('passwd' => $newpwd));
                if ($result) {
                  setMessage(t('Modify success'));
                  gotoUrl('user/login');
                } else {
                  setMessage(t('Modified failure'));
                }
              } else {
                setMessage(t('Input mailbox and register mailbox inconsistent'), 'error');
              }
            } else {
              setMessage(t('Token overtime'), 'error');
            }
          }
        } else {
          setMessage(t('error'), 'error');
        }
      }
      gotoUrl('user/editpassword/' . $username . '/' . $token);
    } else {
      if (!isset($username)) {
        goto404();
      }
      $this->view->render('editpassword.phtml', array(
        'username' => $username,
        'token' => $token,
      ));
    }
  }
  
  public function getUserIntegralAction()
  {
  	$userInstance = User_Model::getInstance();
  	$integral = $userInstance->getUserIntegral();
  	return $integral ? $integral : '0';
  }
  
  public function addwholesaleuserAction()
  {
  	if ($this->isPost()) {
  		$ret = array();
  		$post = $_POST;
  		if (!isset($post['name']) || trim($post['name']) == "") {
  			$ret['success'] = false;
  			$ret['message'] = 'Please input your name.';
  			echo json_encode($ret);
  			return;
  		}
  		if (!isset($post['email']) || trim($post['email']) == "") {
  			$ret['success'] = false;
  			$ret['message'] = 'Please input your email.';
  			echo json_encode($ret);
  			return;
  		} else {
  			if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
  				$ret['success'] = false;
  				$ret['message'] = 'Invalid email.';
  				echo json_encode($ret);
  				return;
  			}
  		}
  		if (!isset($post['country']) || trim($post['country']) == "") {
  			$ret['success'] = false;
  			$ret['message'] = 'Please input your conutry name.';
  			echo json_encode($ret);
  			return;
  		}
  		if (!isset($post['phone']) || trim($post['phone']) == "") {
  			$ret['success'] = false;
  			$ret['message'] = 'Please input your phone number.';
  			echo json_encode($ret);
  			return;
  		}
  		if(!WholesaleUser_Model::getInstance()->insert($post)) {
  			$ret['success'] = false;
  			$ret['message'] = 'Add wholesale info failed.';
  			echo json_encode($ret);
  			return;
  		} else {
  			$ret['success'] = true;
  			echo json_encode($ret);
  		}
  	}
  }
  
  private function needRedirectAfterLogin($referrer){
  	if(!isset($referrer)){
  		return false;
  	}
  	$referrer = strtolower($referrer);
  	if(strpos($referrer, '/user/') > 0){
  		//don't make this redirect.
  		return false;
  	}
  	return true;
  }

}
