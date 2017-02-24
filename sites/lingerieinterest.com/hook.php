<?php
//公共钩子
function hook_page($instance){
$productInstance = Product_Model::getInstance();
   $frontInstance = Front_Model::getInstance();
   $siteInstance = Site_Model::getInstance();

   //购物车中商品数量 和 总价
   $Temp_carts = $frontInstance->getCartProductList();
   $goodsAmount = isset($Temp_carts->goods_amount)? $Temp_carts->goods_amount : 0;
   $goodsInCart = isset($Temp_carts->goods_number)? $Temp_carts->goods_number : 0;
   $instance->view->assign('goodsInCart', $goodsInCart);
   $instance->view->assign('goodsAmount', $goodsAmount);

   //站点信息
   $siteInfo = $frontInstance->getSiteInfo();
   $instance->view->assign('siteInfo', $siteInfo);

   //取商品属性
   //$fieldsList = $productInstance->getProductFieldsIndex('handbags');
   //$instance->view->assign('fieldsList', isset($fieldsList) ? $fieldsList : null);

  //使用的贷币列表
  $currencyList = $siteInstance->getCurrenciesList();
   $instance->view->assign('currencyList', $currencyList);

  //公司联系方式
  $contactway = $frontInstance->getContactWay();
  $instance->view->assign('contactway', $contactway);

   //产品分类
  $termsList = $frontInstance->getTermsList();
  $instance->view->assign('category', isset($termsList) ? $termsList : array());

  //产品品牌
  $brands = $frontInstance->getBrandsList();
  $instance->view->assign('brands', isset($brands)?$brands : array());
  
  //产品标签
  $tagsList = $frontInstance->getTagsList();
  $instance->view->assign('tagsList', isset($tagsList)? $tagsList : array());
  
  //TopSearch
  $topsearch = widgetCallFunction('topsearch', 'getResult');
  $instance->view->assign('topsearch', $topsearch);

  //Help列表
  $contentInstance = Content_Model::getInstance();
//  $helpIds = $contentInstance->getArticleUnderType(4);
//  $helpList = array();
//  if(isset($helpIds) && $helpIds){
//    for($i=0;$i<count($helpIds)-1;$i++){
//      $contTypeInfo = $contentInstance->getArticleTypeInfo($helpIds[$i]);
//      $helpList[$i] = $contTypeInfo;
//      $contTypeArticleList = $contentInstance->getArticleList(array('atid'=>$helpIds[$i]),1,3);
//      $helpList[$i]->sub = (isset($contTypeArticleList) && $contTypeArticleList) ? $contTypeArticleList : null;
//    }
//  }
//  $instance->view->assign('helpList', $helpList);

  //文章列表
  $news_list= $frontInstance->getArticleList(array('type_id'=>'news','status'=>1),1,6);
  $instance->view->assign('news_list', $news_list);
  
  $articleTypeList = $contentInstance->getArticleTypeList();
  $articleTypeList2 = array();
  $helpList = array();
  foreach($articleTypeList as $index=>$type){
  	$articleTypeList2[$type->name] = $type;
  	$articleList = $frontInstance->getArticleList(array('type_id'=>$type->type_id,'status'=>1),1,6);
  	$helpList[$type->name] = $articleList;
  }

  $instance->view->assign('helpList', $helpList);
  $instance->view->assign('articleTypeList', $articleTypeList2);

  //随机评论
  $commentList = widgetCallFunction('randominfo', 'getinfo', 'comments', array('status' => 1), 4);
  $instance->view->assign('commentList', $commentList);
   //stotags链接索引
  $seoLinks = array( '#' => 'tag-0-9', 'A' => 'tag-a', 'B' => 'tag-b', 'C' => 'tag-c', 'D' => 'tag-d',
    'E' => 'tag-e', 'F' => 'tag-f', 'G' => 'tag-g', 'H' => 'tag-h', 'I' => 'tag-i', 'J' => 'tag-j', 'K' => 'tag-k',
    'L' => 'tag-l', 'M' => 'tag-m', 'N' => 'tag-n', 'O' => 'tag-o', 'P' => 'tag-p', 'Q' => 'tag-q', 'R' => 'tag-r',
    'S' => 'tag-s', 'T' => 'tag-t', 'U' => 'tag-u', 'V' => 'tag-v', 'W' => 'tag-w', 'X' => 'tag-x', 'Y' => 'tag-y', 'Z' => 'tag-z',
  );
  $instance->view->assign('seoLinks',$seoLinks);
}

function hook_comment_insert(Comment_Controller $instance)
  {
  //exit;
    global $user;
    $commentModel = Comment_Model::getInstance();
    if ($instance->isPost()) {
      $post = $_POST;
      if (!$post['comment']) {
        exit('Review content should not be empty. ');
      }
    	if(isset($post['comment_token']) && $post['comment_token'] != $_SESSION['comment_token']) {
    	   
      	setMessage('Token error!', 'error');
      	gotoBack();
      }
      
      
      $post['comment'] = trim($post['comment']);
      $post['comment'] = strip_tags($post['comment']);
      if (!$post['subject']) {
        $post['subject'] = substr($post['comment'], 0, 50);
        $pos = strpos($post['subject'], "\n");
        if($pos !== false){
        	$post['subject'] = substr($post['subject'], 0, $pos);
        }
      }
      $post['comment'] = preg_replace("/\r\n(\r\n)*/", "</p><p>", $post['comment']);
      $post['comment'] = preg_replace("/\n(\n)*/", "</p><p>", $post['comment']);
      
      $post['comment'] = str_ireplace("\r\n", "</br>", $post['comment']);
      $post['comment'] = str_ireplace("\n", "</br>", $post['comment']);
      
      $uid = isset($user->uid) ? $user->uid : 0;
      $nickname = (isset($_POST['nickname']) && $_POST['nickname']) ? $_POST['nickname'] : (isset($user->nickname) ? $user->nickname : '');
      $productInstance = Product_Model::getInstance();
      if ($productInstance->getProductInfo($post['pid'])) {
        $status = Bl_Config::get('isNeedAudit', 1) == 1 ? 0 : 1;
        $cid = $commentModel->insertComment($uid, $post['subject'], $post['comment'], $nickname, $status);
        if ($cid) {
          cache::remove('product-' . $post['pid']);
          $commentModel->insertProductComments($post['pid'],$cid);
          if (isset($post['rating'])) {
            $grade = $post['rating'];
            widgetCallFunction('fivestars', 'setstars', $post['pid'], $cid, $grade);
            cache::remove('productStart-' . $post['pid']);
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
    }
  }

//首页扩展
function hook_default_index(Default_Controller $instance)
{

	$frontInstance = Front_Model::getInstance();
	//幻灯片
	$siteInsance = Site_Model::getInstance();
	$hdp = $siteInsance->getcarouselphotoList(1,20);
	$instance->view->assign('hdpArr', $hdp);

  //获取首页  meta 信息和  var 信息
  $frontInstance->getPageVariableByKey($instance, 'index');

	//获取热门商品
  $hotProductList = $frontInstance->getProductsListBySpecial(array('termname' => 'Hot Products'), 1, 4);
   if(isset($hotProductList) && $hotProductList){
      foreach ($hotProductList as $hot){
        $hot->star = widgetCallFunction('fivestars','getaveragestars',$hot->pid);
     }
    }
  $instance->view->assign('hotProductList', $hotProductList);


   //获取新品列表
   $newProductListBySpecial = $frontInstance->getProductsListBySpecial(array('termname' =>'New Arrivals'), 1, 24);
   $newProductList = $frontInstance->getProductsList(array('termname' =>'New Arrivals'), 1, 24);
   $remains = 24 - count($newProductListBySpecial);
   foreach($newProductList as $k=>$v){
   	if($remains == 0) break;
   	$remains--;
   	if(!key_exists($k, $newProductListBySpecial)){
   		$newProductListBySpecial[$k]= $newProductList[$k];
   	}
   }
   
   if(isset($newProductListBySpecial) && $newProductListBySpecial){
      foreach ($newProductListBySpecial as $hot){
        $hot->star = widgetCallFunction('fivestars','getaveragestars',$hot->pid);
    }
   }
    $instance->view->assign('newProductList', $newProductListBySpecial);

  //页脚推荐产品列表
  $recommendProductList = $frontInstance->getProductsListBySpecial(array('termname' =>'Recommended'), 1, 18);
    if(isset($recommendProductList) && $recommendProductList){
      foreach ($recommendProductList as $hot){
        $hot->star = widgetCallFunction('fivestars','getaveragestars',$hot->pid);
     }
    }
    $instance->view->assign('recommendProductList', $recommendProductList);
    
    
  $disProductList = $frontInstance->getProductsListBySpecial(array('termname' =>'Discounted'), 1, 18);
    if(isset($disProductList) && $disProductList){
      foreach ($disProductList as $hot){
        $hot->star = widgetCallFunction('fivestars','getaveragestars',$hot->pid);
     }
    }
    $instance->view->assign('disProductList', $disProductList);
    $instance->view->assign('isHomeIndex', true);
	$instance->indexAction();
}
function hook_getProductList(Product_Controller $instance, $productList, $termInfo){
   $taxonomyInstance = Taxonomy_Model::getInstance();
   $productInstance = Product_Model::getInstance();
   foreach ($productList as $product){
      $tids = $productInstance->getProductTerms($product->pid);
      if(isset($tids) && $tids){
      	foreach ($tids as $tid){
      		$termInfo = $taxonomyInstance->getTermInfo($tid);
      		if($termInfo->vid ==8) {
      			$product->discount = $termInfo->name;
      			break;
      		}
      	}
     }
    
     //added by pzzhang to support direct purchase.
     $productInstance->getProductTypeAndTypeField($product);
     $type = $product->type;
     $product->type = $productInstance->getTypeInfo($type);
     $product->fields = $productInstance->getTypeFieldsList($type);
    }
  return $productList;
};

//新闻列表
//function hook_article_list(Article_Controller $instance, $type = 'news', $page =1)
//{
//  $urls = array(
//    'news' => 'newslist',
//    'faq' => 'faqslist',
//  );
//  $articleInstance = Content_Model::getInstance();
////  $newsAtid = Bl_Config::get('newsAtid', 1);
////  $faqAtid = Bl_Config::get('faqAtid', 3);
////  $idArray = array('news' => $newsAtid, 'faq' => $faqAtid, );
////  if (!array_key_exists($type, $idArray)) {
////    goto404('Page not found.');
////  }
////  $atid = $idArray[$type];
//  if ($type == 'faq') {
//    $pageRows = 2;
//  } else {
//    $pageRows = 30;
//  }
////  $filter = array();
////  $s = $articleInstance->getArticleUnderType($atid);
////  if ($atid != 'all') {
////    $filter['atid'] = $atid;
////  }
////  $articleInfo->firstType = $articleInstance->getArticleTypeInfo($atid);
////  if ($articleInfo->firstType && $articleInfo->secondType = $articleInstance->getArticleTypeInfo($articleInfo->firstType->parent)) {
////    list($articleInfo->firstType, $articleInfo->secondType) = array($articleInfo->secondType, $articleInfo->firstType);
////  }
//
//
//  $filter = array('type_id' => $type, 'status' => 1);
//
//  $articleList = $articleInstance->getArticleList($filter, $page, $pageRows);
//  $articlecount = $articleInstance->getArticleCount($filter);
//  $instance->view->render('articlelist.phtml', array(
//   'type' => strtoupper($type),
//   'articleList' => $articleList,
//   'pagination' => pagination($urls[$type].'/%d', $articlecount, $pageRows, $page),
//  ));
//}

function hook_product_browse($instance, $url = '' , $page = 1){
  $frontInstance = Front_Model::getInstance();
  $productInstance = Product_Model::getInstance();
  $taxonomyInstance = Taxonomy_Model::getInstance();
  $url2 = basename($url, '.html');
  list($directory_path_alias) = $urlArray = explode('+', $url2);
  if (isset($directory_path_alias) && $directory_path_alias && $directory_path_alias != 'all' && $directory_path_alias != 'allgoods') {
    $termInfo = $taxonomyInstance->getTermInfoByPathAlias($directory_path_alias);
    if (isset($termInfo) && $termInfo) {
      $filter['directory_tid'] = $termInfo->tid;
      $parent = $taxonomyInstance->getTermParents($termInfo->tid);
      $termInfo->parent = $parent;
      $termGrade = 1;
      if (isset($parent[0]) && $parent[0]) {
        $termGrade = 2;
      }
      if (isset($parent[1]) && $parent[1]) {
        $termGrade = 3;
      }
      $instance->view->assign('termGrade', $termGrade);
    } else {
      $filter = null;
    }
    $instance->view->assign('termInfo', $termInfo);
    $similarproduct2 = widgetCallFunction('randominfo', 'getinfo', 'products', $filter, 40);
    $similarproduct = widgetCallFunction('randominfo', 'getinfo', 'products_2', $filter, 5);
  }

  $instance->view->assign('similarproduct2', isset($similarproduct2) ? $similarproduct2 : array());
  $instance->view->assign('similarproduct', isset($similarproduct) ? $similarproduct : array());
  
  $termsList = $frontInstance->getTermsList();
  $instance->view->assign('termsList', $termsList);
  $instance->browseAction($url , $page);
  
}

function hook_getrandom($op, $instance, $filter)
{
  if ($op == 'browse') {
    
    $filter['directory_tid'] = $filter['tid'];
    $filter['level'] = 'self';
    $similarproduct = widgetCallFunction('randominfo', 'getinfo', 'products', $filter, 24);
  } elseif ($op == 'product') {
    $filter['directory_tid'] = $filter['tid'];
    $filter['level'] = 'self';
    $similarproduct = widgetCallFunction('randominfo', 'getinfo', 'products', $filter, 24);
    $frontInstance = Front_Model::getInstance();
    $bestProductList = $frontInstance->getProductsListBySpecial(array('termname' => 'Recommended', 'tids' => $filter['tid'], 'orderby' => 'rand()'), 1, 4);
    $instance->view->assign('bestProductList', $bestProductList);
    $_SESSION['comment_token'] = $productCommentToken = randomString(16);
    $instance->view->assign('comment_token', $productCommentToken);
  } elseif ($op == 'search') {
    $keyword = $filter['keywords'];
    $similarproduct = widgetCallFunction('randominfo', 'getinfo', 'products_2', null, 24);
  }
  $instance->view->assign('recommandProductList', $similarproduct);
}

function product_info($instance, $product){
  $frontInstance = Front_Model::getInstance();
  $productInstance = Product_Model::getInstance();
  $commentInstance = Comment_Model::getInstance();

  //TODO: Add the recently feature in the future with simplified data structure.
  
  /*$_SESSION['recently'] = isset($_SESSION['recently']) ? $_SESSION['recently'] : array();
  $_SESSION['recently'][$product->pid] = $product;
  $_SESSION['recently'] = array_slice($_SESSION['recently'], 0, 5, true);
  $recently = $_SESSION['recently'];
  $instance->view->assign('recently', $recently);*/

  $commentList = $frontInstance->getCommentsListByProductId($product->pid, $filter = array('status' => 1), 1, null);
  $commentCount = $commentInstance->getCommentsCountByProductId($product->pid, $filter = array('status' => 1));
  
  foreach($commentList as $k=>$v){
    $v->children = $commentInstance->getReplayInfo($v->cid);
  }
  
  $instance->view->assign('commentCount', $commentCount);
  $instance->view->assign('commentList', $commentList);

  $similarproduct2 = widgetCallFunction('randominfo', 'getinfo', 'products', array('directory_tid' => $product->directory_tid), 40);
  $instance->view->assign('similarproduct2', $similarproduct2);

  $similarproduct3 = widgetCallFunction('randominfo', 'getinfo', 'products_2', array('directory_tid' => $product->directory_tid), 5);
  $instance->view->assign('similarproduct3', $similarproduct3);

  $similararticles = widgetCallFunction('randominfo', 'getinfo', 'articles', null, 10);
  $instance->view->assign('similararticles', $similararticles);

}  

//商品显示页面
function hook_product_term(Product_Controller $instance, $tid, $path)
{
  $path = basename($path, '.html');
  $productInstance = Product_Model::getInstance();
  if (!$product = $productInstance->getProductInfoByPathAlias($path)) {
    goto404('Product path alias <em>' . $path . '.html</em> not found.');
  }
  product_info($instance, $product);
  //获取商品评论列表
  $path = $path . '.html';
  $instance->termAction($tid, $path);
}

//商品显示页面
function hook_product_view(Product_Controller $instance, $pid){
	$productInstance = Product_Model::getInstance();
  if (!$product = $productInstance->getProductInfo($pid)){
    goto404('Product <em>' . $pid . '</em> not found.');
  }
  product_info($instance, $product);
  $instance->viewAction($pid);
}


//留言入库
function hook_guestbook_myadd($instance)
{
  $frontInstance = Front_Model::getInstance();
if ($instance->isPost()) {
      $post = $_POST;
      if (isset($post['data']) && $post['data']) {
        $post['data'] = serialize($post['data']);
      }
      if ($frontInstance->insertWebsiteMessage($post)) {
        setMessage('Thank you very much for your feedback');
      } else {
        setMessage('Sorry, the operation failed');
      }
    }
    gotoUrl('contactus.html');
}

function hook_product_paymentc(Product_Controller $instance, $value = 0)
{
  echo c($value);
  exit;
}

  /*发送下单邮件*/
function hook_cart_sendmail (Cart_Controller $instance, $oid)
{
    $orderInstance = Order_Model::getInstance();
    $orderInfo = $orderInstance->getOrderInfo($oid);
    if(count($orderInfo)>0){
      $emailSetting = Bl_Config::get('orderTradingEmail');
      $stmpSetting = Bl_Config::get('stmp');
      $email = $orderInfo->delivery_email;

      if (isset($stmpSetting) && $stmpSetting['stmpserver'] && $stmpSetting['stmpuser'] && $stmpSetting['stmppasswd'] && $email) {
        $mailInstance = new Mail_Model($stmpSetting);
        //$email = isset($user->email) ? $user->email : null;
        if ($mailInstance->sendMail($email, $emailSetting['title'], $emailSetting['content'], $emailSetting['type'])) {
          setMessage('Sending order email successfully.');
        } else {
            setMessage('Encounter error when sending email.', 'error');
          }
		} else {
			setMessage('Mail server information is not configured properly, please check', 'error');
      }
    }
}
//构建新的会员首页
function hook_user_home($instance){

  $orderInstance = Order_Model::getInstance();
  global $user;
  $instance->view->assign('user', $user);
    $userInstance = User_Model::getInstance();
    if (!$userInstance->logged()) {
      gotoUrl('user/login');
    }
    $filter['uid'] = $user->uid;
    $filter['status'] = 0;
     $ordersList = $orderInstance->getOrdersList($filter, 1, 5);
    foreach ($ordersList as $k => $v) {
      $ordersItems =  $orderInstance->getOrderItems($v->oid);
      $ordersItems = array_splice($ordersItems, 0, 1);
      $ordersList[$k]->firstitem = isset($ordersItems[0]) ? $ordersItems[0] : null;
    }
    $orderscount = $orderInstance->getOrdersCount($filter);

    $instance->view->assign('orderscount', $orderscount);
    $instance->view->assign('templatefile', 'u_userindex.phtml');
    $instance->view->addCss(url('styles/themes/base/jquery-ui-1.8.19.custom.css'));

   $instance->view->render('personalcenter.phtml', array(
      'ordersList' => $ordersList,
    ));
}
/**
 * seotags按拼音索引hook
 */
function hook_default_seotags(Default_Controller $instance, $index = 'A', $page = 1)
{
  $frontInstance = Front_Model::getInstance();
  $page = intval($page);
  if ($page < 1 ) {
    $page = 1;
  }
  $url = 'tag-'.strtolower($index);
  $pageSize = 200;
  $tagsInfo = widgetCallFunction('seotags', 'hook_index', null, $index, $url, $page, $pageSize);
  $breadcrumb = array();
  $breadcrumb[] = array(
    'title' => t('Home'),
    'path' => '',
  );
  $breadcrumb[] = array(
    'title' => $url,
  );
  setBreadcrumb($breadcrumb);
  $frontInstance->getPageVariableByKey($instance, 'index');
  $instance->view->render('seotags.phtml', array(
    'index' => $index,
    'tagsList' => $tagsInfo['tagsList'],
    'pagination' => $tagsInfo['pagination'],
    ));
}

function hook_payment_fail(Payment_Controller $instance, $payment) {
	gotoUrl('/');
}
/*
function hook_product_info_after($instance){
	if($instance){
	$instance->url = $instance->path_alias .'-p'.$instance->sn.'.html';
	return $instance;
	}
	
}*/
