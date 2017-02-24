<?php
class Comment_Controller extends Bl_Controller
{
  private $_commentModel;
  
  public function init()
  {
    $this->_commentModel = Comment_Model::getInstance();
  }
  
  public function insertAction()
  {
    global $user;
    if ($this->isPost()) {
      $post = $_POST;
      if (!$post['nickname']){
        exit('Name can not be empty!');
      }

      if ($post['comment']) {
        exit('Review content can not be empty');
      }
      $post['comment'] = trim($post['comment']);
      $post['comment'] = strip_tags($post['comment']);
      if (!$post['subject']) {
        $post['subject'] = substr($post['comment'], 0, 50);
        $pos = strpos($post['subject'], "\n");
        if($pos !== false){
        	$post['subject'] = strpos($post['subject'], $needle);
        }
      }
      $post['comment'] = preg_replace("/\r\n(\r\n)+/", "</p><p>", $post['comment']);
      $post['comment'] = preg_replace("/\n(\n)+/", "</p><p>", $post['comment']);
      
      $post['comment'] = str_ireplace("\r\n", "</br>", $post['comment']);
      $post['comment'] = str_ireplace("\n", "</br>", $post['comment']);
      
      $uid = isset($user->uid) ? $user->uid : 0;
      $nickname = (isset($_POST['nickname']) && $_POST['nickname']) ? $_POST['nickname'] : (isset($user->nickname) ? $user->nickname : $user->name);
      $productInstance = Product_Model::getInstance();
      if ($productInstance->getProductInfo($post['pid'])) {
        $status = Bl_Config::get('isNeedAudit', 1) == 1 ? 0 : 1;
        $cid = $this->_commentModel->insertComment($uid, $post['subject'], $post['comment'], $nickname, $status);
        if ($cid) {
          cache::remove('product-' . $post['pid']);
          $this->_commentModel->insertProductComments($post['pid'],$cid);
          if (isset($post['rating'])) {
            $grade = $post['rating'];
            widgetCallFunction('fivestars', 'setstars', $post['pid'], $cid, $grade);
            cache::remove('productStart-' . $post['pid']);
          }
        }
        
        
      	//TODO send mail. Temporarily Added.
        $stmpSetting = Bl_Config::get('stmp', 0);
        if ($stmpSetting['stmpserver'] && $stmpSetting['stmpuser'] && $stmpSetting['stmppasswd']) {
        	$mailInstance = isset($mailInstance) ? $mailInstance : new Mail_Model($stmpSetting);
        	
        	$userInstance = User_Model::getInstance();
        	$userInfo = $userInstance->getUserInfo($uid);
        	
        	//$emailQueue[] = $userInfo->email;
        	$emailQueue[] = 'mingdacomment@gmail.com';
        	
        	$emailSetting = array();
            $siteInfo = Bl_Config::get('siteInfo', array());
            $emailSetting['title'] = $siteInfo['sitename'] . ' Reviews-'. strval($cid). ': '. $post['subject'];
            
            $productInstance = Product_Model::getInstance();
            $productInfo = $productInstance->getProductInfo($post['pid']);
            
            $content = '<font face="verdana" size="2">-------------------------------------------------------<br/>';
            $content .= 'User Name:'. $_POST['nickname']. '<br/>';
            $content .= 'Product: <a href="'.$siteInfo['siteurl'] .'/'. $productInfo->url .'">'. $productInfo->name .'</a><br/>';
            $content .= 'Product SN: '. $productInfo->sn .'<br/>';
            $content .= '-------------------------------------------------------<br/><br/><font size="3">';
            $content .= $post['comment'];
            $content .= '</font></font>';

            $emailSetting['content'] = $content;
            $mailInstance->sendMail($emailQueue, $emailSetting['title'], $emailSetting['content'], 'html', $userInfo->name);
            //give customer feedback in the future.
        }

        
        $reffer_url = $_SERVER["HTTP_REFERER"];
        if(isset($post['referer']) && $post['referer']) {
          gotoUrl($post['referer']); 
        } elseif (isset($reffer_url) && $reffer_url) {
          header("Location: ".$reffer_url); 
        } else {
          gotoUrl('comment/insert');
        }
      } else {
        //TODO
        exit('No goods');
      }
    } else {
      $this->view->render('user/addComment.phtml', array());
    }
  }


  public function ajaxUpdateUserAttitudeAction(){
    
  }


  public function addReplyAction()
  {
    global $user;
    if ($this->isPost()) {
      $post = $_POST;
      if (!$post['uid']){
        exit('You need to first login to add reply!');
      }
      if (!$post['review_comment']) {
        exit('Review comment should not be empty!');
      }
      if(strpos($post['review_comment'], '<a ') !== false || strpos($post['review_comment'], 'href') !== false){
        $arr = array();
        $arr['ok'] = false;
      }else{
        $uid = isset($user->uid) ? $user->uid : 0;
        $nickname = (isset($user->nickname) && $user->nickname != '') ? $user->nickname : $user->name;
        $email = $user->email;
        $status = Bl_Config::get('isNeedAudit', 1) == 1 ? 0 : 1;

      	$post['review_comment'] = preg_replace("/\r\n(\r\n)+/", "</p><p>", $post['review_comment']);
      	$post['review_comment'] = preg_replace("/\n(\n)+/", "</p><p>", $post['review_comment']);
      	$post['review_comment'] = str_ireplace("\r\n", "</br>", $post['review_comment']);
      	$post['review_comment'] = str_ireplace("\n", "</br>", $post['review_comment']);
        
        $cid = $this->_commentModel->insertComment($uid, $email, $post['review_comment'], $nickname, $status, '', $post['replyid']);

        //TODO send mail. Temporarily Added.
        $stmpSetting = Bl_Config::get('stmp', 0);
        if ($stmpSetting['stmpserver'] && $stmpSetting['stmpuser'] && $stmpSetting['stmppasswd']) {
        	$mailInstance = isset($mailInstance) ? $mailInstance : new Mail_Model($stmpSetting);
        	
        	$userInstance = User_Model::getInstance();
        	$userInfo = $userInstance->getUserInfo($uid);
        	
        	$emailQueue[] = $userInfo->email;
        	$emailQueue[] = 'mingdacomment@gmail.com';
            $commentInstance = Comment_Model::getInstance();
            $commentInfo = $commentInstance->getCommentInfo($post['replyid']);
            
        	$emailSetting = array();
            $siteInfo = Bl_Config::get('siteInfo', array());
            $emailSetting['title'] = $siteInfo['sitename'] . ' Review Replies-'. $cid . ': '. $commentInfo->subject;
            
            $productInstance = Product_Model::getInstance();
            $productInfo = $productInstance->getProductInfo($commentInfo->pid);
            
            $content = '<font face="verdana" size="2">-------------------------------------------------------<br/>';
            $content .= 'User Name:'. $userInfo->name. '<br/>';
            $content .= 'Product: <a href="'.$siteInfo['siteurl'] .'/'. $productInfo->url .'">'. $productInfo->name .'</a><br/>';
            $content .= 'Product SN: '. $productInfo->sn .'<br/>';
            $content .= 'Original Comment ID: '. $commentInfo->cid .'<br/>';
            $content .= 'Original Comment: '. $commentInfo->comment. '<br/>';
            $content .= '-------------------------------------------------------<br/><br/><font size="3">';
            
            $content .= $post['review_comment'];
            $content .= '</font></font>';
            
            $emailSetting['content'] = $content;
            $mailInstance->sendMail($emailQueue, $emailSetting['title'], $emailSetting['content'], 'html', $userInfo->name);
            //give customer feedback in the future.
        }
        
        $arr = $this->_commentModel->getReplyCommentInfo($cid);
        $arr->time = date('M d, Y', $arr->timestamp);
        $arr->ok = true;
      }
      echo json_encode($arr);
    }
  }

}